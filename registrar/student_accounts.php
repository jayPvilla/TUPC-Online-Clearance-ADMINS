<?php
ob_start();
session_start();
include("../db_connection.php");

// Handle Modal Approval Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_student'])) {
    $id = intval($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $program = $conn->real_escape_string($_POST['program']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE student_accounts SET name='$name', program='$program', password='$hashedPassword' WHERE id=$id";
    } else {
        $sql = "UPDATE student_accounts SET name='$name', program='$program' WHERE id=$id";
    }

    if ($conn->query($sql)) {
        $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show'>
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            <strong>Success!</strong> Student account updated.
        </div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger alert-dismissible fade show'>
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            <strong>Error!</strong> {$conn->error}
        </div>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Registrar Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet"/>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style.css" />
</head>
<body class="bg-light text-dark" style="font-family: 'Poppins', sans-serif;">

<!-- Hamburger Menu -->
<button id="hamburger-btn" class="hamburger">&#9776;</button>

<!-- Sidebar -->
<?php include("sidebar.html"); ?>

<!-- Main Content -->
<div class="main-content p-4">
    <div class="container-fluid">
        <h2 class="mb-4 text-center fw-bold">Registrar Account</h2>

        <!-- Session Flash Message -->
        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
        ?>

        <!-- Student Account Table -->
        <div class="table-responsive rounded shadow-sm">
            <table class="table table-hover align-middle table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Action</th>
                        <th>TUPC ID</th>
                        <th>FULL NAME</th>
                        <th>PROGRAM</th>
                        <th>EMAIL</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php
                    $query = "SELECT * FROM student_accounts";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                                echo "<td>
                                    <button 
                                        type='button' 
                                        class='btn btn-sm btn-primary' 
                                        data-bs-toggle='modal' 
                                        data-bs-target='#approvalModal'
                                        data-id='" . htmlspecialchars($row['id']) . "'
                                        data-name='" . htmlspecialchars($row['name']) . "'
                                        data-program='" . htmlspecialchars($row['program']) . "'
                                        data-password=''>
                                        Review
                                    </button>
                                </td>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['program']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-muted text-center'>No student accounts found.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title fw-semibold" id="approvalModalLabel">Update Student Info</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="student_id" id="modalStudentId">

          <div class="mb-3">
            <label for="modalName" class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" id="modalName" required>
          </div>

          <div class="mb-3">
            <label for="modalProgram" class="form-label">Program</label>
            <input type="text" class="form-control" name="program" id="modalProgram" required>
          </div>

          <div class="mb-3">
            <label for="modalPassword" class="form-label">Password (leave blank to keep current)</label>
            <input type="text" class="form-control" name="password" id="modalPassword" placeholder="Leave blank to retain password">
          </div>
        </div>

        <div class="modal-footer justify-content-between px-4 pb-4">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_student" class="btn btn-success">Approve</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript -->
<script>
    // Sidebar Toggle
    document.getElementById('hamburger-btn')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('show');
    });

    // Fill modal data
    document.getElementById('approvalModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('modalStudentId').value = button.getAttribute('data-id');
        document.getElementById('modalName').value = button.getAttribute('data-name');
        document.getElementById('modalProgram').value = button.getAttribute('data-program');
        document.getElementById('modalPassword').value = '';
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>
