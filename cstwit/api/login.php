<?php
// Include database and handle login
include '../config/database.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Check if form data is received
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Username or password not provided.'
    ]);
    exit;
}

$username = trim($_POST['username']); // Trim to remove any whitespace
$password = $_POST['password'];
$is_admin = isset($_POST['is_admin']) ? 1 : 0;

// Debug: Log the input values
error_log("Username: $username, Is Admin: $is_admin");

// Prepare the query with case-insensitive comparison
$stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND LOWER(role) = LOWER(?)");
$stmt->execute([$username, $is_admin ? 'admin' : 'user']);
$user = $stmt->fetch();

// Check if user is found
if (!$user) {
    error_log("User not found for username: $username, role: " . ($is_admin ? 'admin' : 'user'));
    echo json_encode([
        'success' => false,
        'message' => 'Invalid login credentials: User not found.'
    ]);
    exit;
}

// Debug: Log the stored password hash
error_log("Stored hash for $username: " . $user['password']);

// Verify password
if (password_verify($password, $user['password'])) {
    session_start();
    if ($is_admin) {
        $_SESSION['admin_id'] = $user['id'];
        echo json_encode([
            'success' => true,
            'redirect' => '../admin/dashboard.php'
        ]);
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode([
            'success' => true,
            'redirect' => '../client/index.php'
        ]);
    }
} else {
    error_log("Password verification failed for $username");
    echo json_encode([
        'success' => false,
        'message' => 'Invalid login credentials: Incorrect password.'
    ]);
}
?>