<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');
$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../uploads/";
    $filename = uniqid() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "uploads/" . $filename;
    }
}

if ($content === '' && !$image_path) {
    http_response_code(400);
    echo json_encode(['error' => 'Post cannot be empty']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$user_id, $content, $image_path]);

echo json_encode(['success' => true]);
?>
