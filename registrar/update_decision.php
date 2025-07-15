<?php
session_start();
include("../db_connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"), true);
  $id = intval($data['id'] ?? 0);
  $decision = trim($data['decision'] ?? '');

  if ($id > 0) {
    $stmt = $conn->prepare("UPDATE student_forms SET decision = ? WHERE id = ?");
    $stmt->bind_param("si", $decision, $id);
    if ($stmt->execute()) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
  }
  $conn->close();
} else {
  echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
