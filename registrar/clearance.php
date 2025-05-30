<?php
ob_start();
session_start();
include("../db_connection.php");

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
        
                <div class="table-responsive rounded shadow-sm">
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
                            function getStatusLabel($value) {
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
    
                            $query = "SELECT * FROM student_forms WHERE registrar_approval = 0";
                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    
                                    $credentials = json_decode($row['credentials'], true);
                                    $purpose = is_array($credentials) ? implode(", ", $credentials) : htmlspecialchars($row['credentials']);

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['accountant']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['dla']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['dms']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['dpecs']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['mainDept']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['courseShopAd']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['librarian']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['guidance']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['osa']) . "</td>";
                                    echo "<td>" . getStatusLabel($row['adaa']) . "</td>";
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
