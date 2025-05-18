<?php
// Start session
session_start();

// Define a constant for the project root path
define('PROJECT_ROOT', dirname(dirname(dirname(__FILE__))) . '/');

// Include required files
require_once PROJECT_ROOT . 'includes/function.php';
require_once dirname(__FILE__) . '/../config/database.php';

// Create database connection
$database = new Database();
$conn = $database->connect();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../../pages/login.html');
    exit;
}

// Get POST data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$display_name = trim($_POST['display_name'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$location = trim($_POST['location'] ?? '');
$profession = trim($_POST['profession'] ?? ''); // Note: profession isn't in your DB schema

// Validate inputs
$errors = [];

// Validate username
if (empty($username) || strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
}

// Check if username already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bindParam(1, $username);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $errors[] = 'Username already taken';
}

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

// Check if email already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bindParam(1, $email);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $errors[] = 'Email already registered';
}

// Validate password
if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

// If there are errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    header('Location: ../../pages/login.html');
    exit;
}

// Handle profile image upload
$profile_image_path = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../assets/images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_') . '.' . $file_extension;
    $target_file = $upload_dir . $filename;
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        $_SESSION['registration_errors'] = ['Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.'];
        header('Location: ../../pages/login.html');
        exit;
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
        $profile_image_path = 'assets/images/' . $filename;
    } else {
        $_SESSION['registration_errors'] = ['Failed to upload profile image.'];
        header('Location: ../../pages/login.html');
        exit;
    }
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (username, email, password, display_name, bio, profile_image, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bindParam(1, $username);
$stmt->bindParam(2, $email);
$stmt->bindParam(3, $hashedPassword);
$stmt->bindParam(4, $display_name);
$stmt->bindParam(5, $bio);
$stmt->bindParam(6, $profile_image_path);
$stmt->bindParam(7, $location);

if ($stmt->execute()) {
    // Get user ID
    $user_id = $conn->lastInsertId();
    
    // Create session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    
    // Redirect to homepage or dashboard
    header('Location: ../../index.php');
    exit;
} else {
    $_SESSION['registration_errors'] = ['Registration failed: Database error.'];
    header('Location: ../../pages/login.html');
    exit;
}
?>