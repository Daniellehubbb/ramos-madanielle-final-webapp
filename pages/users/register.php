<?php
session_start();

include('../../connection/dbconn.php');

$success = ""; 
$error = "";  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $first_name = trim($_POST['first_name']);
  $middle_name = trim($_POST['middle_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password !== $confirm_password) {
    $error = "Passwords do not match!";
  } else {
    try {
      $checkQuery = "SELECT * FROM users WHERE email = :email";
      $checkStmt = $connection->prepare($checkQuery);
      $checkStmt->execute([':email' => $email]);

      if ($checkStmt->rowCount() > 0) {
        $error = "Email already registered!";
      } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertQuery = "INSERT INTO users (first_name, middle_name, last_name, email, password, created_at)
                        VALUES (:first_name, :middle_name, :last_name, :email, :password, NOW())";

        $stmt = $connection->prepare($insertQuery);
        $stmt->execute([
          ':first_name' => $first_name,
          ':middle_name' => $middle_name,
          ':last_name' => $last_name,
          ':email' => $email,
          ':password' => $hashedPassword
        ]);

       
        if ($stmt->rowCount() > 0) {
          $success = "Registration successful! Redirecting to login page...";
          echo "<script>
                  alert('$success');
                  window.location.href = 'login.php';
                </script>";
          exit();
        } else {
          $error = "Something went wrong. Please try again.";
        }
      }
    } catch (PDOException $e) {
      $error = "Database error: " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | CashMate</title>
  <link rel="stylesheet" href="../../assets/css/style.css" /> 
</head>

<body>
  <div class="register-container">
    <div class="register-logo">
      <img src="../../assets/images/logo.png" alt="CashMate Logo">
      <h1>CashMate</h1>
    </div>
    <h2>Create Your Account</h2>
    <p>Join CashMate to manage your income, budget, and expenses effortlessly.</p>

    <form method="POST" action="">
      <div id="errorMsg" class="error-message">
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
      </div>

      <input type="text" name="first_name" placeholder="First Name" required>
      <input type="text" name="middle_name" placeholder="Middle Name (Optional)">
      <input type="text" name="last_name" placeholder="Last Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      
      <button type="submit" class="btn-register">Register</button>
    </form>

    <div class="links">
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</body>
</html>
