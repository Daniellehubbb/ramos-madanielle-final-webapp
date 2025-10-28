<?php
// Start session para magamit sa login tracking
session_start();

// Include database connection (PDO)
include('../../connection/dbconn.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  try {
    // Check if user exists
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $connection->prepare($query);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password and user existence
    if ($user && password_verify($password, $user['password'])) {
      // Store user info in session
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['email'] = $user['email'];
       $_SESSION['first_name'] = $user['first_name'];

      // Redirect to dashboard
      echo "<script>
              alert('Login successful! Redirecting to dashboard...');
              window.location.href = '../dashboard.php';
            </script>";
      exit();
    } else {
      $error = "Invalid email or password.";
    }
  } catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | CashMate</title>
  <link rel="stylesheet" href="../../assets/css/style.css" /> 
</head>

<body>
  <div class="login-container">
    <div class="login-logo">
      <img src="../../assets/images/logo.png" alt="CashMate Logo">
      <h1>CashMate</h1>
    </div>
    <h2>Welcome Back!</h2>
    <p>Login to track your expenses and manage your finances easily.</p>

    <!-- Login Form -->
    <form method="POST" action="">
      <div id="errorMsg" class="error-message">
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
      </div>

      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="links">
      <p>Don’t have an account? <a href="register.php">Register here</a></p>
    </div>
  </div>
</body>
</html>
