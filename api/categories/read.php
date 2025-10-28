<?php
header("Content-Type: application/json");
include '../../connection/dbconn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "Unauthorized"]);
  http_response_code(401);
  exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $connection->prepare("SELECT * FROM categories WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "data" => $categories]);
?>
