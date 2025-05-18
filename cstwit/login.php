<?php
// Start session
session_start();

// Include database connection
require_once PROJECT_ROOT . 'includes/function.php';
require_once dirname(__FILE__) . '/../config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard or home page
    header('Location: ../../index.php');
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        // Query database for user
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login time
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Redirect to dashboard or home page
                header('Location: ../../index.php');
                exit;
            } else {
                // Password is incorrect
                $login_error = 'Invalid email or password';
            }
        } else {
            // User not found
            $login_error = 'Invalid email or password';
        }
        
        $stmt->close();
    }
}

// If we reach here, login failed - redirect back to login page with error
$_SESSION['login_error'] = $login_error ?? 'Login failed';
header('Location: ../../pages/login.html');
exit;
?>