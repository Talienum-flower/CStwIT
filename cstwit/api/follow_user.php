<?php
// Include database and session
include '../config/database.php';
include '../config/session.php';

$followed_id = $_POST['user_id'];
$follower_id = $_SESSION['user_id'];

// Fetch user information for usernames
$follower_username = ''; // Fetch from DB or Session
$followed_username = ''; // Fetch from DB based on followed_id

// Assuming you have a method to retrieve the usernames
$follower_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$follower_stmt->execute([$follower_id]);
$follower_user = $follower_stmt->fetch();
if ($follower_user) {
    $follower_username = $follower_user['username'];
}

// Similarly get followed username
$followed_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$followed_stmt->execute([$followed_id]);
$followed_user = $followed_stmt->fetch();
if ($followed_user) {
    $followed_username = $followed_user['username'];
}

// Prepare the insertion statement
$stmt = $conn->prepare("
    INSERT INTO follows (follower_id, follower_username, followed_id, followed_username) 
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE id=id"); // Update logic can be modified as needed

// Execute the statement with parameters
$stmt->execute([$follower_id, $follower_username, $followed_id, $followed_username]);

// Redirect after insertion
header("Location: ../client/index.php");
exit; // Always good to add exit after header redirection
?>
