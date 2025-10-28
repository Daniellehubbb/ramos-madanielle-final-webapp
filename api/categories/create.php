<?php
header("Content-Type: application/json");
include '../../connection/dbconn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "Unauthorized"]);
  http_response_code(401);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$category_name = trim($data['category_name'] ?? '');
$user_id = $_SESSION['user_id'];

if (empty($category_name)) {
  echo json_encode(["status" => "error", "message" => "Category name required"]);
  exit();
}

$stmt = $connection->prepare("INSERT INTO categories (category_name, user_id) VALUES (:name, :uid)");
$stmt->execute([':name' => $category_name, ':uid' => $user_id]);

echo json_encode(["status" => "success", "message" => "Category added successfully!"]);
?>
