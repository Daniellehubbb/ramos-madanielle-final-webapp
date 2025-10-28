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
$id = $data['category_id'] ?? null;
$name = trim($data['category_name'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$id || empty($name)) {
  echo json_encode(["status" => "error", "message" => "Invalid input"]);
  exit();
}

$stmt = $connection->prepare("UPDATE categories SET category_name = :name WHERE category_id = :id AND user_id = :uid");
$stmt->execute([':name' => $name, ':id' => $id, ':uid' => $user_id]);

echo json_encode(["status" => "success", "message" => "Category updated successfully!"]);
?>
