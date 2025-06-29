<?php
ob_start();
session_start();
include("../db_connection.php");

// Handle student update if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $program = trim($_POST['program']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    echo "<script>console.log('ID: " . $id . "');</script>";

    // Safety: fail if ID is empty
    if (empty($id)) {
        die("Error: Student ID is missing. Update aborted.");
    }

    // Get the original email from the DB
    $originalEmail = '';
    $getOriginal = $conn->prepare("SELECT email FROM student_accounts WHERE id = ?");
    $getOriginal->bind_param("s", $id); // CORRECT: id is VARCHAR so bind as string
    $getOriginal->execute();
    $getOriginal->bind_result($originalEmail);
    $getOriginal->fetch();
    $getOriginal->close();

    if (!$originalEmail) {
        echo "<script>alert('Original email not found.'); window.location = '" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }

    // Compare email ignoring spaces and case
    $emailChanged = strcasecmp(trim($email), trim($originalEmail)) !== 0;

    // If email was changed, check for duplicates (excluding this user)
    if ($emailChanged) {
        $check = $conn->prepare("SELECT id FROM student_accounts WHERE email = ? AND id <> ?");
        $check->bind_param("ss", $email, $id); // CORRECT: both are strings
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            echo "<script>alert('Email already exists. Please use a different email.'); window.location = '" . $_SERVER['PHP_SELF'] . "';</script>";
            exit();
        }

        $check->close();
    }

    // Prepare the update query
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($emailChanged) {
            $stmt = $conn->prepare("UPDATE student_accounts SET name = ?, program = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssss", $name, $program, $email, $hashedPassword, $id);
        } else {
            $stmt = $conn->prepare("UPDATE student_accounts SET name = ?, program = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssss", $name, $program, $hashedPassword, $id);
        }
    } else {
        if ($emailChanged) {
            $stmt = $conn->prepare("UPDATE student_accounts SET name = ?, program = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssss", $name, $program, $email, $id);
        } else {
            $stmt = $conn->prepare("UPDATE student_accounts SET name = ?, program = ? WHERE id = ?");
            $stmt->bind_param("sss", $name, $program, $id);
        }
    }

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating student: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TUPC Online Clearance - Registrar Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            transition: box-shadow 0.3s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .hamburger {
            position: fixed;
            top: 15px;
            left: 15px;
            font-size: 24px;
            border: none;
            background: none;
            z-index: 1050;
        }
    </style>
</head>
<body class="bg-light text-dark">

    <!-- Hamburger Menu -->
    <button id="hamburger-btn" class="hamburger">&#9776;</button>

    <!-- Sidebar -->
    <?php include("sidebar.html"); ?>

    <!-- Main Content -->
    <div class="main-content">
    <div class="container-fluid">
        <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-danger text-center">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody class="align-middle text-center">
            <?php
            // Fetch student accounts
            $query = "SELECT * FROM student_accounts"; // <-- make sure this table name is correct
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                $modalId = "modal-student-" . $row['id'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                        Edit Profile
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="<?php echo $modalId; ?>Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="<?php echo $modalId; ?>Label">Edit Profile - <?php echo htmlspecialchars($row['name']); ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" />
                                <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Name" value="<?php echo htmlspecialchars($row['name']); ?>" required />
                                <label>Name</label>
                                </div>
                                <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="program" placeholder="Program" value="<?php echo htmlspecialchars($row['program']); ?>" required />
                                <label>Program</label>
                                </div>
                                <div class="form-floating mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($row['email']); ?>" required />
                                <label>Email</label>
                                </div>
                                <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="password" placeholder="Password" value="<?php echo htmlspecialchars($row['password']); ?>" required />
                                <label>Password</label>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_student" class="btn btn-danger">Save Changes</button>
                            </div>
                            </form>
                        </div>
                        </div>
                    </div>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="5" class="text-center">No student accounts found.</td></tr>';
            }
            ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>


</body>
</html>
