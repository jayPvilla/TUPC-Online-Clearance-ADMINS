<?php
  session_start(); // Start the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TUP Cavite - Online Clearance</title>
  <!-- Include Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to bottom,rgb(255, 255, 255),rgb(255, 141, 141));
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      display: flex;
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      max-width: 850px;
      width: 100%;
    }

    .left-section {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 40px 30px;
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      border-right: 1px solid #ddd;
    }

    .left-section img {
      width: 100px;
      height: 100px;
      margin-bottom: 20px;
    }

    .left-section h1 {
      font-size: 24px;
      color: #c62828;
      margin: 0;
      letter-spacing: 1px;
    }

    .left-section h2 {
      font-size: 18px;
      color: #000000;
      margin-top: 10px;
      letter-spacing: 1px;
    }

    .right-section {
      padding: 40px 30px;
      width: 320px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .right-section input[type="text"],
    .right-section input[type="password"] {
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 100%;
      text-align: center;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }

    .right-section input::placeholder {
      text-align: center;
      text-transform: capitalize;
    }

    .right-section label {
      font-size: 14px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }

    .right-section input[type="checkbox"] {
      margin-right: 8px;
    }

    .right-section button {
      padding: 10px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      text-transform: capitalize;
      font-family: 'Poppins', sans-serif;
    }

    .login-btn {
      background-color: #c62828;
      color: white;
      margin-bottom: 10px;
      width: 100%;
    }

    .signup-btn {
      background-color: #444;
      color: white;
      width: 50%;
      align-self: center;
    }

    .or-text {
      text-align: center;
      margin: 10px 0;
      font-size: 14px;
      color: #999;
    }

    .footer {
      text-align: center;
      padding: 15px 0;
      font-size: 14px;
      color: white;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        max-width: 100%;
        margin: 20px;
      }

      .left-section, .right-section {
        width: 100%;
        border-right: none;
        padding: 30px 20px;
      }

      .left-section h1 {
        font-size: 20px;
      }

      .left-section h2 {
        font-size: 16px;
      }

      .right-section input[type="text"],
      .right-section input[type="password"],
      .right-section button {
        font-size: 16px;
      }

      .signup-btn {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .left-section img {
        width: 80px;
        height: 80px;
      }

      .left-section h1 {
        font-size: 18px;
      }

      .left-section h2 {
        font-size: 14px;
      }

      .right-section {
        padding: 20px 15px;
      }

      .right-section input[type="text"],
      .right-section input[type="password"] {
        padding: 8px;
      }

      .footer {
        font-size: 12px;
        padding: 10px 0;
      }
    }
  </style>
</head>
<body>
  <div class="main-content">
    <div class="container">
      <div class="d-flex left-section">
        <img src="images/logo.png" alt="TUP Logo">
        <h1>ONLINE CLEARANCE</h1>
        <h2>TUP CAVITE</h2>
        <small>Registrar & Faculty</small>
      </div>

      <!-- LOGIN FORM -->
      <form action="./signIn_handler.php" method="post" class="right-section">
        <input type="text" placeholder="Email" id="loginEmail" name="logInEmail">
        <input type="password" placeholder="Password" id="loginPassword" name="logInPassword">
        <label><input type="checkbox" id="showPassword"> Show password</label>
        <button type="submit" class="login-btn" name="signIn">Login</button>
      </form>
    </div>
  </div>

  <div class="footer">
    Copyright &copy; All Rights Reserved 2025
  </div>

  <!-- Optional: JS to toggle password visibility -->
  <script>
    document.getElementById("showPassword").addEventListener("change", function () {
      const passwordInput = document.getElementById("loginPassword");
      passwordInput.type = this.checked ? "text" : "password";
    });
  </script>
</body>
</html>
