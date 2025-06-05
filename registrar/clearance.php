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
    <title>Registrar Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />

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

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <input type="text" id="searchInput" class="form-control w-25" placeholder="Search by name or course">

                <div class="ms-3">
                    <select id="sortSelect" class="form-select">
                        <option value="">Sort by</option>
                        <option value="1">Full Name ↑</option>
                        <option value="1-desc">Full Name ↓</option>
                        <option value="2">Course ↑</option>
                        <option value="2-desc">Course ↓</option>
                    </select>
                </div>
            </div>

            <!-- Session Flash Message -->
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
            <div class="table-container table-responsive rounded shadow-sm" style="max-height: 500px; overflow: auto;">
                <table class="table table-hover align-middle table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>CTRL NO.</th>
                            <th>FULL NAME</th>
                            <th>COURSE</th>
                            <th>ACCOUNTANT</th>
                            <th>DLA</th>
                            <th>DMS</th>
                            <th>DPECS</th>
                            <th>DIT/DED/DOE</th>
                            <th>COURSE SHOP/ADVISER</th>
                            <th>LIBRARY</th>
                            <th>GUIDANCE</th>
                            <th>OSA</th>
                            <th>ADAA</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        function getStatusLabel($value)
                        {
                            switch ($value) {
                                case 1:
                                    return '<span class="badge bg-success">Approved</span>';
                                case 0:
                                    return '<span class="badge bg-danger">Declined</span>';
                                case 2:
                                    return '<span class="badge bg-secondary">Pending</span>';
                                default:
                                    return '<span class="badge bg-dark">Unknown</span>';
                            }
                        }

                        $query = "SELECT * FROM student_forms WHERE registrar_approval = 1";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {

                                $credentials = json_decode($row['credentials'], true);
                                $purpose = is_array($credentials) ? implode(", ", $credentials) : htmlspecialchars($row['credentials']);

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                                echo "<td>" . getStatusLabel($row['ACCOUNTANT']) . "</td>";
                                echo "<td>" . getStatusLabel($row['LIBERAL ARTS']) . "</td>";
                                echo "<td>" . getStatusLabel($row['MATH & SCIENCES']) . "</td>";
                                echo "<td>" . getStatusLabel($row['DPECS']) . "</td>";
                                echo "<td>" . getStatusLabel($row['mainDept']) . "</td>";
                                echo "<td>" . getStatusLabel($row['courseShopAd']) . "</td>";
                                echo "<td>" . getStatusLabel($row['CAMPUS LIBRARIAN']) . "</td>";
                                echo "<td>" . getStatusLabel($row['GUIDANCE COUNSELOR']) . "</td>";
                                echo "<td>" . getStatusLabel($row['HEAD OF STUDENT AFFAIRS']) . "</td>";
                                echo "<td>" . getStatusLabel($row['ASST. DIR. FOR ACADEMIC AFFAIRS']) . "</td>";
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
            </form>

        </div>
    </div>

    <!-- Optional JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
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

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('keyup', function () {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const name = row.children[1].textContent.toLowerCase();
                    const course = row.children[2].textContent.toLowerCase();
                    const match = name.includes(searchTerm) || course.includes(searchTerm);
                    row.style.display = match ? '' : 'none';
                });
            });

            // Check All functionality (optional)
            const checkAllBtn = document.getElementById('checkAllBtn');
            if (checkAllBtn) {
                checkAllBtn.addEventListener('change', () => {
                    const checkboxes = document.querySelectorAll('.select-row');
                    checkboxes.forEach(cb => cb.checked = checkAllBtn.checked);
                });
            }
        });
        document.getElementById('sortSelect').addEventListener('change', function () {
            const value = this.value;
            if (!value) return;

            const descending = value.includes('-desc');
            const colIndex = parseInt(value);

            const rows = Array.from(document.querySelectorAll('tbody tr'));

            rows.sort((a, b) => {
                const textA = a.children[colIndex].textContent.trim().toLowerCase();
                const textB = b.children[colIndex].textContent.trim().toLowerCase();

                if (textA < textB) return descending ? 1 : -1;
                if (textA > textB) return descending ? -1 : 1;
                return 0;
            });

            const tbody = document.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        });
    </script>

</body>

</html>

<?php ob_end_flush(); ?>