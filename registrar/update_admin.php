<?php
session_start();
include("../db_connection.php");

if (isset($_POST['update_admin'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate
    if (empty($id) || empty($name) || empty($email)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: admin_accounts.php"); // or your actual dashboard filename
        exit();
    }

    // OPTIONAL: hash password if you're not yet doing it
    // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate email (excluding current ID)
    $checkEmailQuery = "SELECT * FROM `admins accounts` WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already in use by another account.";
        header("Location: admin_accounts.php");
        exit();
    }

    // Update the record
    $updateQuery = "UPDATE `admins accounts` SET name = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $name, $email, $password, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin profile updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating admin profile: " . $stmt->error;
    }

    header("Location: admin_accounts.php");
    exit();
}
?>
