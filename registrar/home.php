<?php
ob_start();
session_start();
include("../db_connection.php");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['selected_ids'])) {
        $selected_ids = array_map('intval', $_POST['selected_ids']);
        $ids_string = implode(',', $selected_ids);

        if (isset($_POST['approve_selected'])) {
            $update_query = "UPDATE student_forms SET registrar_approval = 1, registrar_approved_at = NOW() WHERE id IN ($ids_string)";
            $action = "approved";
        } elseif (isset($_POST['decline_selected'])) {
            $update_query = "UPDATE student_forms SET registrar_approval = 2 WHERE id IN ($ids_string)";
            $action = "declined";
        }

        if (isset($update_query) && $conn->query($update_query) === TRUE) {
            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show'>
                                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        <strong>Success!</strong> Selected records have been {$action}.
                                    </div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger alert-dismissible fade show'>
                                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        <strong>Error!</strong> {$conn->error}
                                    </div>";
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-warning alert-dismissible fade show'>
                                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                    <strong>Notice:</strong> No rows selected.
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

            <!-- Student Form Table -->
            <form method="post" action="">
                <div class="table-responsive rounded shadow-sm">
                    <table class="table table-hover align-middle table-bordered">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>SELECT</th>
                                <th>CTRL NO.</th>
                                <th>FULL NAME</th>
                                <th>COURSE</th>
                                <th>YEAR ADMITTED</th>
                                <th>GRADUATED IN TUPC</th>
                                <th>YEAR GRADUATED</th>
                                <th>NO. OF TERMS</th>
                                <th>HIGH SCHOOL</th>
                                <th>PURPOSE OF REQUEST</th>
                                <th>DATE REQUESTED</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            $query = "SELECT * FROM student_forms WHERE registrar_approval = 0";
                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $credentials = json_decode($row['credentials'], true);
                                    $purpose = is_array($credentials) ? implode(", ", $credentials) : htmlspecialchars($row['credentials']);

                                    echo "<tr>";
                                    echo "<td><input type='checkbox' class='form-check-input select-row' name='selected_ids[]' value='" . htmlspecialchars($row['id']) . "'></td>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['yearAdmitted']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['graduate']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['gradYear']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['terms']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['highSchool']) . "</td>";
                                    echo "<td>" . htmlspecialchars($purpose) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11' class='text-muted text-center'>No pending requests found.</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="sticky-action-bar bg-white border-top p-3 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="form-check mb-2 mb-md-0">
                        <input type="checkbox" class="form-check-input" id="checkAllBtn">
                        <label class="form-check-label" for="checkAllBtn">Check All</label>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="decline_selected" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Decline Selected
                        </button>
                        <button type="submit" name="approve_selected" class="btn btn-outline-success">
                            <i class="bi bi-check-circle"></i> Approve Selected
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <!-- Optional JavaScript -->
    <script>
        // Sidebar Toggle
        const hamburger = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Hide sidebar on nav link click (mobile)
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                }
            });
        });

        // Check All functionality
        const checkAllBtn = document.getElementById('checkAllBtn');
        checkAllBtn.addEventListener('change', () => {
            const checkboxes = document.querySelectorAll('.select-row');
            checkboxes.forEach(cb => cb.checked = checkAllBtn.checked);
        });
    </script>

</body>
</html>

<?php ob_end_flush(); ?>
