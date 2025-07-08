<?php
ob_start();
session_start();
include("../db_connection.php");
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
            <div class="row g-4">
                <?php
                $departments = [
                    "LIBERAL ARTS",
                    "MATH & SCIENCES",
                    "DPECS",
                    "courseShopAd",
                    "INDUSTRIAL EDUCATION",
                    "ENGINEERING",
                    "CAMPUS LIBRARIAN",
                    "GUIDANCE COUNSELOR",
                    "HEAD OF STUDENT AFFAIRS",
                    "ASST. DIR. FOR ACADEMIC AFFAIRS",
                ];

                $query = "SELECT * FROM `admins accounts`";
                $result = $conn->query($query);

                $heads = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $heads[strtoupper(trim($row['type']))] = $row;
                    }
                }

                foreach ($departments as $dept) {
                    $key = strtoupper($dept);
                    $data = $heads[$key] ?? null;
                    $headName = $data ? htmlspecialchars($data['name']) : "TBD";
                    $modalId = "modal-" . md5($dept);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold"><?php echo $dept; ?></h5>
                                <p class="card-text text-muted mb-2"><?php echo $headName; ?></p>
                                <?php if ($data) { ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                                        See Profile
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="<?php echo $modalId; ?>Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="<?php echo $modalId; ?>Label">Edit Profile - <?php echo $dept; ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="update_admin.php"> <!-- Point to your PHP update handler -->
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>" />
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="name-<?php echo $modalId; ?>" name="name" placeholder="Name" value="<?php echo htmlspecialchars($data['name']); ?>" required />
                                                            <label for="name-<?php echo $modalId; ?>">Name</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="email" class="form-control" id="email-<?php echo $modalId; ?>" name="email" placeholder="Email" value="<?php echo htmlspecialchars($data['email']); ?>" required />
                                                            <label for="email-<?php echo $modalId; ?>">Email</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="password" class="form-control" id="password-<?php echo $modalId; ?>" name="password" placeholder="Password" value="<?php echo htmlspecialchars($data['password']); ?>" required />
                                                            <label for="password-<?php echo $modalId; ?>">Password</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_admin" class="btn btn-danger">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                        <?php } else { ?>
                                    <button class="btn btn-secondary btn-sm" disabled>No Profile</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

</body>
</html>
