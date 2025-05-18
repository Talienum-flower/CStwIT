<?php
session_start();

/**
 * Generate a secure token and store it in the session
 */
function generateAuthToken($user_id) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['auth_token'] = $token;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['token_expiry'] = time() + 3600; // Token valid for 1 hour
    return $token;
}

/**
 * Verify the token from the session
 */
function verifyAuthToken($token) {
    if (
        isset($_SESSION['auth_token']) &&
        $_SESSION['auth_token'] === $token &&
        $_SESSION['token_expiry'] >= time()
    ) {
        return true;
    }
    return false;
}

/**
 * Securely hash a password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password with stored hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['auth_token']);
}

/**
 * Enforce login requirement
 */
function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. Please log in."]);
        exit;
    }
}

/**
 * Get the current logged-in user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>
