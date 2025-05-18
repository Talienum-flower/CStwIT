<?php
// File: api/comment_post.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    error_log("Error: User not logged in. Session user_id not set.");
    die("Error: User not logged in.");
}

include '../config/database.php';
if (!file_exists('notifications_helper.php')) {
    error_log("notifications_helper.php not found in " . __DIR__);
    die("Error: notifications_helper.php not found.");
}
include 'notifications_helper.php';
error_log("notifications_helper.php included successfully in comment_post.php. Function exists: " . (function_exists('createNotification') ? 'yes' : 'no'));

// Debug: Show received POST data
error_log("Received POST data: " . print_r($_POST, true));
var_dump($_POST);

if (isset($_POST['post_id'], $_POST['comment'])) {
    try {
        $post_id = (int)$_POST['post_id'];
        $user_id = $_SESSION['user_id'];
        $comment = trim($_POST['comment']);

        if (empty($comment)) {
            error_log("Error: Comment is empty for post_id=$post_id, user_id=$user_id");
            die("Error: Comment cannot be empty.");
        }

        // Insert the comment
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$post_id, $user_id, $comment]);
        error_log("Comment inserted for post_id=$post_id, user_id=$user_id");

        // Fetch the post's owner
        $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Post fetch result: " . print_r($post, true));

        if ($post && $post['user_id'] != $user_id) {
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $commenter = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Commenter fetch result: " . print_r($commenter, true));

            if ($commenter) {
                $message = $commenter['username'] . " commented on your post";
                if (function_exists('createNotification')) {
                    $success = createNotification($conn, $post['user_id'], $user_id, $post_id, 'comment', $message);
                    error_log("createNotification result for comment: " . ($success ? "success" : "failed") . " | user_id=" . $post['user_id'] . ", related_user_id=$user_id, post_id=$post_id");
                } else {
                    error_log("createNotification function not defined in comment_post.php");
                }
            } else {
                error_log("Failed to fetch commenter username for user_id=$user_id");
            }
        } else {
            error_log("No notification created: post_owner_id=" . ($post['user_id'] ?? 'not found') . " matches user_id=$user_id or post not found");
        }

        header("Location: ../client/index.php");
        exit();
    } catch (PDOException $e) {
        error_log("Comment submission failed: " . $e->getMessage());
        die("Comment submission failed: " . $e->getMessage());
    }
} else {
    error_log("Error: Missing required fields (post_id or comment). Received: " . print_r($_POST, true));
    die("Error: Missing required fields (post_id or comment).");
}
?>