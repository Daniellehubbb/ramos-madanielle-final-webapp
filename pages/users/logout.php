<?php
session_start();

// Only logout when confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
  $_SESSION = [];
  session_unset();
  session_destroy();

  echo "<script>
          alert('You have been logged out successfully.');
          window.location.href = '../../index.html';
        </script>";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Logout Confirmation</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    .logout-page {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: transparent;
    }

    /*  Overlay Background */
    .logout-page .logout-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      display: flex;
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(6px);
      z-index: 9999;
      padding: 20px;
      animation: logoutFadeIn 0.3s ease-in-out;
    }

    /*  Modal Container */
    .logout-page .logout-box {
      background: linear-gradient(145deg, #ffffff 0%, #f3f7fb 100%);
      padding: 35px 40px;
      border-radius: 18px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      text-align: center;
      width: 100%;
      max-width: 370px;
      animation: logoutSlideUp 0.4s ease-out;
      position: relative;
      overflow: hidden;
    }

    .logout-page .logout-box h2 {
      color: #111;
      font-size: 1.1rem;
      margin-bottom: 25px;
      line-height: 1.4;
    }

    /*  Buttons */
    .logout-page .logout-actions {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    .logout-page .logout-actions button {
      flex: 1 1 45%;
      padding: 12px 0;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .logout-page .logout-confirm {
      background: linear-gradient(135deg, #00bfa6, #00e0be);
      color: #fff;
    }

    .logout-page .logout-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 191, 166, 0.4);
    }

    .logout-page .logout-cancel {
      background: #edf1f5;
      color: #333;
    }

    .logout-page .logout-cancel:hover {
      background: #e2e8ee;
      transform: translateY(-2px);
    }

    /*  Animations */
    @keyframes logoutFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes logoutSlideUp {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* 📱 Mobile Responsiveness */
    @media (max-width: 480px) {
      .logout-page .logout-box {
        padding: 28px 25px;
        border-radius: 14px;
      }

      .logout-page .logout-box h2 {
        font-size: 1rem;
      }

      .logout-page .logout-actions {
        flex-direction: column;
        gap: 10px;
      }

      .logout-page .logout-actions button {
        width: 100%;
        padding: 14px 0;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body class="logout-page">
  <div class="logout-overlay">
    <div class="logout-box">
      <h2>Are you sure you want to logout?</h2>
      <div class="logout-actions">
        <button class="logout-confirm" onclick="confirmLogout()">Yes, Logout</button>
        <button class="logout-cancel" onclick="cancelLogout()">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    function confirmLogout() {
      window.location.href = 'logout.php?confirm=true';
    }

    function cancelLogout() {
      window.history.back();
    }
  </script>
</body>
</html>
