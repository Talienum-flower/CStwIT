<?php
session_start();
require_once '../../config/database.php';

$user_id = $_SESSION['user_id'] ?? null;
$tab = $_GET['tab'] ?? 'for_you';

// "For You": show all posts; "Following": show posts from followed users
if ($tab === 'following' && $user_id) {
    $sql = "SELECT p.*, u.username FROM posts p
            JOIN follows f ON p.user_id = f.followed_id
            JOIN users u ON p.user_id = u.id
            WHERE f.follower_id = ?
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
} else {
    $sql = "SELECT p.*, u.username FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql);
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($posts);
?>
