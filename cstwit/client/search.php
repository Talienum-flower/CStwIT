<?php
session_start();

// Include database connection
include '../config/database.php';

$response = ['success' => false, 'message' => '', 'user' => null];

if (isset($_GET['query']) && !empty($_GET['query'])) {
    $raw_query = trim($_GET['query']);
    $query = '%' . $raw_query . '%';
    error_log("Search query: " . $raw_query);

    try {
        // Search for users (by username, name, or email)
        $stmt = $conn->prepare("
            SELECT id, username, profile_pic 
            FROM users 
            WHERE username LIKE ? OR name LIKE ? OR email LIKE ?
            LIMIT 1
        ");
        $stmt->execute([$query, $query, $query]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response['success'] = true;
            $response['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'profile_pic' => $user['profile_pic']
            ];
            error_log("Found user ID: " . $user['id']);
        } else {
            // Search comments to find a user
            $stmt = $conn->prepare("
                SELECT u.id, u.username, u.profile_pic 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.comment LIKE ?
                LIMIT 1
            ");
            $stmt->execute([$query]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response['success'] = true;
                $response['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'profile_pic' => $user['profile_pic']
                ];
                error_log("Found user ID from comment: " . $user['id']);
            } else {
                $response['message'] = "No users found matching '" . htmlspecialchars($raw_query) . "'";
            }
        }
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $response['message'] = "An error occurred during search";
    }
} else {
    $response['message'] = "Please enter a search query";
}

// Output JSON response for AJAX handling
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>