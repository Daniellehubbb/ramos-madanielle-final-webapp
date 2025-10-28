<?php
header("Content-Type: application/json");
include '../../connection/dbconn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  http_response_code(401);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$category_id = $data['category_id'] ?? '';

if (empty($category_id)) {
  echo json_encode(["status" => "error", "message" => "Category ID is required"]);
  exit();
}

$user_id = $_SESSION['user_id'];

$check = $connection->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = :cid AND user_id = :uid");
$check->execute([':cid' => $category_id, ':uid' => $user_id]);
$count = $check->fetchColumn();

if ($count > 0) {
  echo json_encode([
    "status" => "error",
    "message" => "Cannot delete this category because it is linked to existing transactions."
  ]);
  exit();
}

$stmt = $connection->prepare("DELETE FROM categories WHERE category_id = :cid AND user_id = :uid");
$stmt->execute([':cid' => $category_id, ':uid' => $user_id]);

echo json_encode(["status" => "success", "message" => "Category deleted successfully!"]);
?>
