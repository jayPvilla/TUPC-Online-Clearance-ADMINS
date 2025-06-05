<?php
ob_start();
session_start();
include("../db_connection.php");

$allowed_roles = ['REGISTRAR', 'ACCOUNTANT', 'MATH & SCIENCES', 'LIBERAL ARTS', 'INDUSTRIAL TECHNOLOGY', 'INDUSTRIAL EDUCATION', 'ENGINEERING', 'DPECS', 'CAMPUS LIBRARIAN', 'GUIDANCE COUNSELOR', 'HEAD OF STUDENT AFFAIRS', 'ASST. DIR. FOR ACADEMIC AFFAIRS'];

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_roles)) {
    die("Unauthorized access.");
}

$user_type = $_SESSION["user_type"];
$user_name = $_SESSION["user_name"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['selected_ids'])) {
        $selected_ids = array_map('intval', $_POST['selected_ids']);

        $success = true;
        $errors = [];

        foreach ($selected_ids as $id) {
            if (isset($_POST['approve_selected'])) {
                $update_query = "UPDATE student_forms SET `$user_type` = 1, `{$user_type}_time` = NOW() WHERE id = $id";
            } elseif (isset($_POST['decline_selected'])) {
                $remarks = isset($_POST['remarks'][$id]) ? $conn->real_escape_string($_POST['remarks'][$id]) : '';
                $update_query = "UPDATE student_forms SET `$user_type` = 3, `{$user_type}_time` = NOW(), `{$user_type}_remarks` = '$remarks' WHERE id = $id";
            }

            if (isset($update_query) && !$conn->query($update_query)) {
                $success = false;
                $errors[] = "Error on ID $id: " . $conn->error;
            }
        }

        if ($success) {
            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show'>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                <strong>Success!</strong> Selected records have been processed.
            </div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger alert-dismissible fade show'>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                <strong>Error!</strong> " . implode("<br>", $errors) . "
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TUPC Online Clearance Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css" />
</head>

<body class="bg-light text-dark" style="font-family: 'Poppins', sans-serif;">

    <button id="hamburger-btn" class="hamburger" aria-label="Toggle Sidebar">&#9776;</button>
    <?php include("sidebar.html"); ?>

    <div class="main-content p-4">
        <div class="container-fluid">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3">
            <input type="text" id="searchInput" class="form-control w-100 w-md-50" placeholder="Search by name or course...">

            <select id="sortSelect" class="form-select w-100 w-md-25">
                <option value="">Sort By</option>
                <option value="fullname">Full Name (A-Z)</option>
                <option value="fullname-desc">Full Name (Z-A)</option>
                <option value="course">Course (A-Z)</option>
                <option value="course-desc">Course (Z-A)</option>
                <option value="created_at">Date Requested (Newest)</option>
                <option value="created_at-desc">Date Requested (Oldest)</option>
            </select>
        </div>

            <?php
            if ($user_type == "ASST. DIR. FOR ACADEMIC AFFAIRS") {
                $query = "SELECT * FROM student_forms 
                        WHERE registrar_approval = 1 
                        AND `ASST. DIR. FOR ACADEMIC AFFAIRS` = 2 
                        AND ACCOUNTANT = 1 
                        AND `LIBERAL ARTS` = 1 
                        AND `MATH & SCIENCES` = 1 
                        AND DPECS = 1 
                        AND mainDept = 1 
                        AND courseShopAd = 1 
                        AND `CAMPUS LIBRARIAN` = 1 
                        AND `GUIDANCE COUNSELOR` = 1 
                        AND `HEAD OF STUDENT AFFAIRS` = 1";
                $result = $conn->query($query);
                $pending_count = $result ? $result->num_rows : 0;
            } elseif ($user_type == "HEAD OF STUDENT AFFAIRS") {
                $query = "SELECT * FROM student_forms 
                        WHERE registrar_approval = 1 
                        AND ACCOUNTANT = 1 
                        AND `LIBERAL ARTS` = 1 
                        AND `MATH & SCIENCES` = 1 
                        AND DPECS = 1 
                        AND mainDept = 1 
                        AND courseShopAd = 1 
                        AND `CAMPUS LIBRARIAN` = 1 
                        AND `GUIDANCE COUNSELOR` = 1 
                        AND `HEAD OF STUDENT AFFAIRS` = 2";
                $result = $conn->query($query);
                $pending_count = $result ? $result->num_rows : 0;
            } else {
                $query = "SELECT * FROM student_forms WHERE registrar_approval = 1 AND `{$user_type}` = 2";
                $result = $conn->query($query);
                $pending_count = $result ? $result->num_rows : 0;
            }
            ?>

            <h2 class="mb-4 text-center fw-bold">
                <?php echo ucfirst(htmlspecialchars($user_type)); ?> Account
                <small class="text-muted">(<?php echo $pending_count; ?> pending)</small>
            </h2>

            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>

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
                                <th>REMARKS</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            if ($result && $pending_count > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $credentials = json_decode($row['credentials'], true);
                                    $purpose = is_array($credentials) ? implode(", ", $credentials) : htmlspecialchars($row['credentials']);

                                    echo "<tr data-fullname='" . htmlspecialchars($row['fullname']) . "' 
                                            data-course='" . htmlspecialchars($row['course']) . "' 
                                            data-created_at='" . htmlspecialchars($row['created_at']) . "'>";
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
                                    echo "<td><input type='text' class='form-control' name='remarks[" . htmlspecialchars($row['id']) . "]'></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' class='text-muted text-center'>No pending requests found.</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>

                <div
                    class="sticky-action-bar bg-white border-top p-3 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="form-check mb-2 mb-md-0">
                        <input type="checkbox" class="form-check-input" id="checkAllBtn">
                        <label class="form-check-label" for="checkAllBtn">Check All</label>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="decline_selected" class="btn btn-outline-danger" disabled>
                            <i class="bi bi-x-circle"></i> Decline Selected
                        </button>
                        <button type="submit" name="approve_selected" class="btn btn-outline-success" disabled>
                            <i class="bi bi-check-circle"></i> Approve Selected
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const hamburger = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                }
            });
        });

        const checkAllBtn = document.getElementById('checkAllBtn');
        const checkboxes = document.querySelectorAll('.select-row');
        const actionButtons = document.querySelectorAll("button[name='approve_selected'], button[name='decline_selected']");

        function toggleButtons() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            actionButtons.forEach(btn => btn.disabled = !anyChecked);
        }

        checkAllBtn.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = checkAllBtn.checked);
            toggleButtons();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', toggleButtons));
        toggleButtons();
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const tableRows = document.querySelectorAll("tbody tr");

        searchInput.addEventListener("input", () => {
            const searchTerm = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                const name = row.getAttribute("data-fullname").toLowerCase();
                const course = row.getAttribute("data-course").toLowerCase();
                const visible = name.includes(searchTerm) || course.includes(searchTerm);
                row.style.display = visible ? "" : "none";
            });
        });

        sortSelect.addEventListener("change", () => {
            const value = sortSelect.value;
            const tbody = document.querySelector("tbody");

            const rowsArray = Array.from(tableRows).filter(row => row.style.display !== "none");

            const getAttr = (row, attr) => row.getAttribute(`data-${attr}`);

            const [key, order] = value.includes("-desc") ? [value.replace("-desc", ""), "desc"] : [value, "asc"];

            rowsArray.sort((a, b) => {
                const valA = getAttr(a, key).toLowerCase();
                const valB = getAttr(b, key).toLowerCase();

                if (valA < valB) return order === "asc" ? -1 : 1;
                if (valA > valB) return order === "asc" ? 1 : -1;
                return 0;
            });

            rowsArray.forEach(row => tbody.appendChild(row));
        });
    </script>

</body>

</html>

<?php ob_end_flush(); ?>