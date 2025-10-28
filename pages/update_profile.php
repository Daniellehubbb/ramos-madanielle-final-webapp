<?php
session_start();
include '../connection/dbconn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error'] = "First name, last name, and email are required.";
        header("Location: profile.php");
        exit();
    }

    try {
        $query = "UPDATE users 
                  SET first_name = ?, middle_name = ?, last_name = ?, email = ?
                  WHERE user_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$first_name, $middle_name, $last_name, $email, $user_id]);


        
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
