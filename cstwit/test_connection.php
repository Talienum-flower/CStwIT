<?php
require_once "../config/auth.php";

// Simulate login
$user_id = 1; // Replace with real user ID from DB
$token = generateAuthToken($user_id);

echo "Token generated: $token<br>";

// Simulate check
if (verifyAuthToken($token)) {
    echo "✅Connected " . getCurrentUserId();
} else {
    echo "❌ Invalid or expired token.";
}
?>
