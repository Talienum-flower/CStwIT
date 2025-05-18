<?php
// Include database and session, ban user
include '../../config/database.php';
include '../../admin/includes/session.php';
$user_id = $_GET['id'];
$conn->query("UPDATE users SET status = 'banned' WHERE id = $user_id");
header("Location: ../../admin/manage_users.php");
?>