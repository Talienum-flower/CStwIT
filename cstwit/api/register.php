<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../config/database.php';

// Debug: Show received POST data
var_dump($_POST);

if (isset($_POST['name'], $_POST['username'], $_POST['email'], $_POST['password'])) {
    try {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Validate inputs
        if (empty($name) || empty($username) || empty($email) || empty($_POST['password'])) {
            die("Error: All fields are required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Error: Invalid email format.");
        }

        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role, profile_pic) VALUES (?, ?, ?, ?, 'user', 'default.jpg')");
        $stmt->execute([$name, $username, $email, $password]);

        // Redirect to login page
        header("Location: ../client/login.php");
        exit();
    } catch (PDOException $e) {
        die("Registration failed: " . $e->getMessage());
    }
} else {
    die("Error: Missing required fields (name, username, email, or password).");
}
?>