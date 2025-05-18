<?php
// File: api/update_profile.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/session.php';
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "User not logged in";
    header("Location: ../client/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the logged-in user's data
try {
    $stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "User not found";
        header("Location: ../client/login.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching user: " . $e->getMessage();
    header("Location: ../client/profile.php");
    exit();
}

// Debug: Log the incoming data
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));

$username = $_POST['username'] ?? '';
$name = $_POST['name'] ?? '';
$bio = $_POST['bio'] ?? '';
$profile_pic = isset($_FILES['profile_pic']) && is_array($_FILES['profile_pic']) && !empty($_FILES['profile_pic']['name']) ? $_FILES['profile_pic']['name'] : '';

// Validate username: check for emptiness and uniqueness
if (empty($username)) {
    $_SESSION['error_message'] = "Username cannot be empty";
    header("Location: ../client/update_profile.php");
    exit();
}

// Check if the new username is already taken by another user
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Username already taken";
        header("Location: ../client/update_profile.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error checking username: " . $e->getMessage();
    header("Location: ../client/update_profile.php");
    exit();
}

// Fetch current profile_pic if no new file uploaded
$current_profile_pic = $user['profile_pic'] ?? 'default.jpg';
if (empty($profile_pic)) {
    try {
        $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_profile_pic = $stmt->fetchColumn() ?: 'default.jpg';
        $profile_pic = $current_profile_pic;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error fetching current profile: " . $e->getMessage();
        header("Location: ../client/profile.php");
        exit();
    }
}

// Handle profile picture upload
$file_data = $_FILES['profile_pic'] ?? null;
if ($file_data && is_array($file_data) && !empty($file_data['name']) && $file_data['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/uploads/';
    $target_path = $upload_dir . basename($file_data['name']);
    if (move_uploaded_file($file_data['tmp_name'], $target_path)) {
        $profile_pic = basename($file_data['name']);
    } else {
        $_SESSION['error_message'] = "Failed to upload profile picture";
        header("Location: ../client/profile.php");
        exit();
    }
} else if ($file_data && !is_array($file_data)) {
    $_SESSION['error_message'] = "Invalid file data: Expected array, got string";
    header("Location: ../client/profile.php");
    exit();
}

try {
    $sql = "UPDATE users SET username = ?, email = ?, name = ?, bio = ?, profile_pic = ? WHERE id = ?";
    $params = [$username, $user['email'], $name, $bio, $profile_pic, $user_id];

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Update session username to reflect the change
    $_SESSION['username'] = $username;

    $_SESSION['success_message'] = "Profile updated successfully";
    header("Location: ../client/profile.php");
    exit();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Profile update failed: " . $e->getMessage();
    header("Location: ../client/profile.php");
    exit();
}
?>