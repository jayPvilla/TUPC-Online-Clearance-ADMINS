<?php
ob_start();
session_start();
include("../db_connection.php");

// Handle Modal Approval/Decline Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $departments = isset($_POST['departments']) ? $_POST['departments'] : [];

    $updates = [];

    foreach ($departments as $dept) {
        $field = "";

        switch ($dept) {
            case 'ACCOUNTANT': $field = "ACCOUNTANT"; break;
            case 'LIBERAL ARTS': $field = "LIBERAL ARTS"; break;
            case 'MATH & SCIENCES': $field = "MATH & SCIENCES"; break;
            case 'DPECS': $field = "DPECS"; break;
            case 'mainDept': $field = "mainDept"; break; // used by 2 checkboxes
            case 'GUIDANCE COUNSELOR': $field = "GUIDANCE COUNSELOR"; break;
            case 'CAMPUS LIBRARIAN': $field = "CAMPUS LIBRARIAN"; break;
            case 'HEAD OF STUDENT AFFAIRS': $field = "HEAD OF STUDENT AFFAIRS"; break;
            case 'ASST. DIR. FOR ACADEMIC AFFAIRS': $field = "ASST. DIR. FOR ACADEMIC AFFAIRS"; break;
        }

        if ($field) {
            $value = isset($_POST['approve_modal']) ? 4 : 3;
            $updates[] = "`$field` = $value";
        }
    }


    if (!empty($updates)) {
        $update_sql = "UPDATE student_forms SET " . implode(", ", $updates) . " WHERE id = $student_id";
        $registrar_approval = "UPDATE student_forms SET registrar_approval = 1, registrar_approved_at = NOW() WHERE id = $student_id";
        if ($conn->query($update_sql) === TRUE && $conn->query($registrar_approval) === TRUE) {
            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show'>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                <strong>Success!</strong> Student ID $student_id updated.
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
            <strong>Notice:</strong> No departments selected.
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
        <div class="table-responsive rounded shadow-sm">
            <table class="table table-hover align-middle table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ACTION</th>
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
                            echo "<td><button type='button' class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#approvalModal' data-id='" . htmlspecialchars($row['id']) . "'>Review</button></td>";
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
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" action="" onsubmit="return validateModalSelection();">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title fw-semibold" id="approvalModalLabel">Department Approval</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="student_id" id="modalStudentId">
          <p class="mb-3">Please select which departments to <strong>approve or decline</strong> for the selected student:</p>

          <div class="row g-2">
            <div class="col-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="ACCOUNTANT" id="chkAccountant">
                <label class="form-check-label" for="chkAccountant">Accountant</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="LIBERAL ARTS" id="chkDLA">
                <label class="form-check-label" for="chkDLA">Liberal Arts</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="MATH & SCIENCES" id="chkDMS">
                <label class="form-check-label" for="chkDMS">Math & Sciences</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="DPECS" id="chkDPECS">
                <label class="form-check-label" for="chkDPECS">DPECS</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="mainDept" id="chkMainDept">
                <label class="form-check-label" for="chkMainDept">DIT/DOE/DED</label>
              </div>
            </div>
            <div class="col-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="mainDept" id="chkCourseShopAd">
                <label class="form-check-label" for="chkCourseShopAd">Course/Shop/Adviser</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="GUIDANCE COUNSELOR" id="chkGuidance">
                <label class="form-check-label" for="chkGuidance">Guidance Counselor</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="CAMPUS LIBRARIAN" id="chkLibrarian">
                <label class="form-check-label" for="chkLibrarian">Campus Librarian</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="HEAD OF STUDENT AFFAIRS" id="chkOSA">
                <label class="form-check-label" for="chkOSA">Head of Student Affairs</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="departments[]" value="ASST. DIR. FOR ACADEMIC AFFAIRS" id="chkADAA">
                <label class="form-check-label" for="chkADAA">Asst. Dir. for Academic Affairs</label>
              </div>
            </div>
          </div>

          <div id="modalAlert" class="alert alert-warning mt-3 d-none" role="alert">
            Please select at least one department before submitting.
          </div>
        </div>

        <div class="modal-footer justify-content-center px-4 pb-4">
            <button type="submit" name="approve_modal" class="btn btn-success px-4" onclick="return confirmAction('approve');">
                <i class="bi bi-check-circle me-1"></i> Approve
            </button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- JavaScript -->
<script>
    // Sidebar Toggle
    const hamburger = document.getElementById('hamburger-btn');
    const sidebar = document.getElementById('sidebar');
    hamburger?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
    });

    // Hide sidebar on nav link click (mobile)
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar?.classList.remove('show');
            }
        });
    });

    // Pass student ID to modal
    const approvalModal = document.getElementById('approvalModal');
    approvalModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-id');
        document.getElementById('modalStudentId').value = studentId;
    });
    function validateModalSelection() {
        const checkboxes = document.querySelectorAll('#approvalModal input[type="checkbox"]:checked');
        const alertBox = document.getElementById('modalAlert');

        if (checkboxes.length === 0) {
        alertBox.classList.remove('d-none');
        return false;
        }

        alertBox.classList.add('d-none');
        return true;
    }

    function confirmAction(actionType) {
        return confirm(`Are you sure you want to ${actionType.toUpperCase()} the selected departments?`);
    }
</script>

</body>
</html>

<?php ob_end_flush(); ?>
