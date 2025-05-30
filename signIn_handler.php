<?php
    ob_start();
    session_start();
    include './db_connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signIn"])) {
        $logInEmail = trim($_POST["logInEmail"]);
        $logInPassword = trim($_POST["logInPassword"]);

        if (empty($logInEmail) || empty($logInPassword)) {
            echo "<script>alert('Email or Password is empty.'); history.back();</script>";
        } elseif (!filter_var($logInEmail, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Invalid email format.'); history.back();</script>";
        } else {
            $stmt = $conn->prepare("SELECT * FROM `admins accounts` WHERE email = ?");
            $stmt->bind_param("s", $logInEmail);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userID, $userEmail, $hashedPassword, $userType, $userName);
                $stmt->fetch();

                if ($hashedPassword == $logInPassword) {
                    $_SESSION["user_id"] = $userID;
                    $_SESSION["user_name"] = $userName;
                    $_SESSION["user_email"] = $userEmail;
                    $_SESSION["user_type"] = $userType;

                    if ($userType == "REGISTRAR"){
                        header("Location: registrar/home.php");
                    }
                    exit();
                } else {
                    echo "<script>alert('Incorrect password.'); history.back();</script>";
                }
            } else {
                echo "<script>alert('No account found with that email.'); history.back();</script>";
            }
            $stmt->close();
            $conn->close();
        }
    }
    ob_end_flush();
?>
