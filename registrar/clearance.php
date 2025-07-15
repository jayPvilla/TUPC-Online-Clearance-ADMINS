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

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../style.css" />
  <style>
    .highlight-row {
      background-color: #fff3cd; /* Bootstrap's yellow alert bg */
    }
  </style>
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
        echo htmlspecialchars($_SESSION['message']);
        unset($_SESSION['message']);
      }
      ?>

      <div class="table-container table-responsive rounded shadow-sm" style="max-height: 500px; overflow: auto;">
        <table class="table table-hover align-middle table-bordered">
          <thead class="table-dark text-center">
            <tr>
              <th>Action</th>
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
                case 4:
                  return '<span class="badge bg-secondary">Pending</span>';
                case 2:
                  return '';
                default:
                  return '<span class="badge bg-dark">Unknown</span>';
              }
            }

            $query = "SELECT * FROM student_forms WHERE registrar_approval = 1 ORDER BY id DESC";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {

                // Check statuses
                $statuses = [
                  $row['ACCOUNTANT'],
                  $row['LIBERAL ARTS'],
                  $row['MATH & SCIENCES'],
                  $row['DPECS'],
                  $row['mainDept'],
                  $row['courseShopAd'],
                  $row['CAMPUS LIBRARIAN'],
                  $row['GUIDANCE COUNSELOR'],
                  $row['HEAD OF STUDENT AFFAIRS'],
                  $row['ASST. DIR. FOR ACADEMIC AFFAIRS']
                ];

                $hasDeclinedOrPending = false;
                foreach ($statuses as $status) {
                  if ($status == 0 || $status == 4) {
                    $hasDeclinedOrPending = true;
                    break;
                  }
                }

                if ($hasDeclinedOrPending) {
                  $gearBtn = '<button class="btn btn-secondary btn-sm" disabled>
                                <i class="bi bi-envelope"></i>
                              </button>';
                } else {
                  $decision = htmlspecialchars($row['decision']);
                  $gearBtn = '<button class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#actionModal"
                                data-id="' . htmlspecialchars($row['id']) . '"
                                data-decision="' . $decision . '">
                                <i class="bi bi-envelope"></i>
                              </button>';
                }

                $rowClass = !$hasDeclinedOrPending ? 'highlight-row' : '';
                echo "<tr class='$rowClass'>";
                echo "<td class='text-center'>$gearBtn</td>";
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
              echo "<tr><td colspan='14' class='text-muted text-center'>No pending requests found.</td></tr>";
            }
            $conn->close();
            ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <!-- Action Modal -->
  <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="actionModalLabel">Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="decisionText" class="form-label">Decision</label>
          <textarea id="decisionText" class="form-control" rows="5"></textarea>
          <input type="hidden" id="studentIdInput">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="proceedBtn">Proceed</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const hamburger = document.getElementById('hamburger-btn');
      const sidebar = document.getElementById('sidebar');
      hamburger.addEventListener('click', () => sidebar.classList.toggle('show'));

      document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
          if (window.innerWidth <= 768) sidebar.classList.remove('show');
        });
      });

      const searchInput = document.getElementById('searchInput');
      searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
          const name = row.children[2].textContent.toLowerCase();
          const course = row.children[3].textContent.toLowerCase();
          row.style.display = (name.includes(searchTerm) || course.includes(searchTerm)) ? '' : 'none';
        });
      });

      document.getElementById('sortSelect').addEventListener('change', function () {
        const value = this.value;
        if (!value) return;
        const descending = value.includes('-desc');
        const colIndex = parseInt(value);
        const rows = Array.from(document.querySelectorAll('tbody tr'));
        rows.sort((a, b) => {
          const textA = a.children[colIndex + 1].textContent.trim().toLowerCase();
          const textB = b.children[colIndex + 1].textContent.trim().toLowerCase();
          if (textA < textB) return descending ? 1 : -1;
          if (textA > textB) return descending ? -1 : 1;
          return 0;
        });
        const tbody = document.querySelector('tbody');
        rows.forEach(row => tbody.appendChild(row));
      });

      const actionModal = document.getElementById('actionModal');
      actionModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-id');
        const decision = button.getAttribute('data-decision');
        document.getElementById('decisionText').value = decision || '';
        document.getElementById('studentIdInput').value = studentId;
      });

      const proceedBtn = document.getElementById('proceedBtn');
      proceedBtn.addEventListener('click', () => {
        const studentId = document.getElementById('studentIdInput').value;
        const decision = document.getElementById('decisionText').value.trim();
        if (!studentId) return alert("Invalid ID");
        if (confirm("Are you sure you want to update this decision?")) {
          fetch('update_decision.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: studentId, decision: decision})
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert("Updated!");
              location.reload();
            } else {
              alert("Update failed: " + data.error);
            }
          })
          .catch(() => alert("AJAX error."));
        }
      });
    });
  </script>

</body>

</html>

<?php ob_end_flush(); ?>
