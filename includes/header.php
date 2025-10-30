<?php

if (!isset($_SESSION)) {
  session_start();
}

if (!isset($connection)) {
  include __DIR__ . '/../connection/dbconn.php';
}

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

// Fetch user's first name
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$first_name = $user ? htmlspecialchars($user['first_name']) : 'User';
?>

<header class="cm-header">
  <div class="cm-header-left">
    <img src="../assets/images/logo.png" alt="CashMate Logo" class="cm-logo">
    <span class="cm-app-title">CashMate</span>
    
  </div>
</header>

<link rel="stylesheet" href="../assets/css/include.css">
