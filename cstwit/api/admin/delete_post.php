<?php
// Include database and session, delete post
include '../../config/database.php';
include '../../admin/includes/session.php';
$post_id = $_GET['id'];
$conn->query("DELETE FROM posts WHERE id = $post_id");
header("Location: ../../admin/manage_posts.php");
?>