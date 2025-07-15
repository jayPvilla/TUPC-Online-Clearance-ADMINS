<?php
ob_start();
session_start();
include("../db_connection.php");

// Handle add program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addProgram'])) {
  $department = trim($_POST['department']);
  $program_code = trim($_POST['code']);
  $program_name = trim($_POST['program_name']);

  if (!empty($department) && !empty($program_code) && !empty($program_name)) {
    $stmt = $conn->prepare("INSERT INTO programs (department, code, full_program) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $department, $program_code, $program_name);

    if ($stmt->execute()) {
      echo "<script>alert('Program added successfully.'); window.location.href = window.location.href;</script>";
    } else {
      echo "<script>alert('Database error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
  } else {
    echo "<script>alert('Please fill in all fields.');</script>";
  }
}

// Get programs for table
$sql = "SELECT department, code, full_program FROM programs ORDER BY department ASC, code ASC";
$result = $conn->query($sql);

$programs = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Registrar Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../style.css" />
</head>
<body class="bg-light text-dark">
  <!-- Hamburger Menu -->
  <button id="hamburger-btn" class="hamburger">&#9776;</button>
  <?php include("sidebar.html"); ?>
  <div class="main-content">
      <div class="container my-5">

        <h3>Programs by Department</h3>

        <!-- Add Program Button -->
        <div class="d-flex justify-content-end mb-3">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
            + Add Program
          </button>
        </div>

        <!-- Table -->
        <table class="table table-striped table-bordered">
          <thead class="table-dark">
            <tr>
              <th>Department</th>
              <th>Code</th>
              <th>Program Name</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($programs) > 0): ?>
              <?php foreach ($programs as $row): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['department']); ?></td>
                  <td><?php echo htmlspecialchars($row['code']); ?></td>
                  <td><?php echo htmlspecialchars($row['full_program']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="2">No programs found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Add Program Modal -->
        <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <div class="modal-header">
                  <h5 class="modal-title" id="addProgramModalLabel">Add New Program</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select type="text" class="form-select" id="department" name="department" required>
                      <option selected>Choose department</option>
                      <option value="INDUSTRIAL EDUCATION">Industrial Education</option>
                      <option value="INDUSTRIAL TECHNOLOGY">Industrial Technology</option>
                      <option value="ENGINEERING">Engineering</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="code" class="form-label">Program Code</label>
                    <input type="text" class="form-control" id="code" name="code" required>
                  </div>
                  <div class="mb-3">
                    <label for="program_name" class="form-label">Program Name</label>
                    <input type="text" class="form-control" id="program_name" name="program_name" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="addProgram" class="btn btn-primary">Save Program</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div>
  </div>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>
