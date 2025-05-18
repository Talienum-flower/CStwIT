<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration and session
include '../config/database.php'; // Assumes this sets up $conn
include '../config/session.php'; // Assumes this starts the session and sets $user_id

try {
    // Make sure session user_id is set
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in");
    }
    
    // Define the profile images directory (updated path)
    $profile_images_dir = "../assets/uploads/";
    // Make sure the directory exists
    if (!is_dir($profile_images_dir)) {
        // If not, try to create it
        if (!mkdir($profile_images_dir, 0755, true)) {
            error_log("Failed to create profile images directory: $profile_images_dir");
        }
    }
    
    // Fetch users from the database, excluding the logged-in user
    $stmt = $conn->prepare("SELECT id, username, profile_pic, name, created_at FROM users WHERE status = 'active' AND id != ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $users_to_follow = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check follow status for each user
    foreach ($users_to_follow as &$user) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$_SESSION['user_id'], $user['id']]);
        $user['is_followed'] = $stmt->fetchColumn() > 0;
    }
    unset($user); // Unset reference to avoid issues

    // Ensure up to 5 users are shown
    $users_to_follow = array_slice($users_to_follow, 0, 5);

} catch (PDOException $e) {
    // Log the error and provide a fallback message
    error_log("Database error: " . $e->getMessage());
    $users_to_follow = []; // Fallback to empty array if query fails
}
?>
<style>
   .right-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    background-color: #fff;
    padding: 20px;
    box-sizing: border-box;
    border-left: 1px solid #ddd;
    font-family: Arial, sans-serif;
    overflow-y: auto;
}

.follow-suggestions {
    background-color: #fff;
    margin-top: 100px;
    border-radius: 10px;
    padding: 15px;
}

.suggestions-header h2 {
    font-size: 18px;
    color: #333;
    margin: 0 0 15px 0;
    font-weight: 600;
}

.suggestions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.suggestion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border-radius: 8px;
}

.suggestion-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.suggestion-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ddd;
}

.suggestion-info {
    display: flex;
    flex-direction: column;
}

.suggestion-fullname {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.suggestion-username {
    font-size: 13px;
    color: #666;
}

.suggestion-action .follow-btn {
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.suggestion-action .follow-btn:not(.followed) {
    background-color: #1e90ff;
    color: #fff;
}

.suggestion-action .follow-btn.followed {
    background-color: #e0e0e0;
    color: #333;
}

.suggestion-action .follow-btn:hover:not(.followed) {
    background-color: #187bcd;
}

.suggestion-action .follow-btn:hover.followed {
    background-color: #d0d0d0;
}

.suggestion-action .follow-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .right-sidebar {
        width: 250px;
    }

    .suggestion-item {
        padding: 8px;
    }

    .suggestion-avatar img {
        width: 35px;
        height: 35px;
    }

    .suggestion-fullname {
        font-size: 13px;
    }

    .suggestion-username {
        font-size: 12px;
    }

    .suggestion-action .follow-btn {
        padding: 5px 10px;
        font-size: 12px;
    }
}

@media (max-width: 768px) {
    .right-sidebar {
        width: 100%;
        height: auto;
        padding: 10px;
        position: fixed;
        bottom: 0;
        top: auto;
        right: 0;
        border-left: none;
        border-top: 1px solid #ddd;
        background-color: #fff;
        display: flex;
        justify-content: center;
    }

    .follow-suggestions {
        width: 100%;
        padding: 10px;
        box-shadow: none;
        border-radius: 0;
    }

    .suggestions-header h2 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .suggestions-list {
        flex-direction: row;
        overflow-x: auto;
        gap: 10px;
    }

    .suggestion-item {
        flex: 0 0 auto;
        width: 200px;
        flex-direction: column;
        align-items: flex-start;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .suggestion-user {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .suggestion-avatar img {
        width: 50px;
        height: 50px;
    }

    .suggestion-action {
        width: 100%;
    }

    .suggestion-action .follow-btn {
        width: 100%;
        padding: 8px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .suggestions-list {
        gap: 8px;
    }

    .suggestion-item {
        width: 160px;
        padding: 8px;
    }

    .suggestion-avatar img {
        width: 40px;
        height: 40px;
    }

    .suggestion-fullname {
        font-size: 12px;
    }

    .suggestion-username {
        font-size: 11px;
    }

    .suggestion-action .follow-btn {
        padding: 6px;
        font-size: 12px;
    }
}
    </style>

<div class="right-sidebar">
    <div class="follow-suggestions">
        <div class="suggestions-header">
            <h2>Who to follow?</h2>
        </div>
        
        <div class="suggestions-list">
            <?php if (empty($users_to_follow)): ?>
                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <span class="suggestion-name">No users to follow at this time.</span>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($users_to_follow as $user): ?>
                <div class="suggestion-item">
                    <div class="suggestion-user">
                        <div class="suggestion-avatar">
                            <?php if (!empty($user['profile_pic']) && file_exists("../assets/uploads/" . $user['profile_pic'])): ?>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>'s profile">
                            <?php else: ?>
                                <img src="../assets/images/default.jpg" alt="Default profile">
                            <?php endif; ?>
                        </div>
                        <div class="suggestion-info">
                            <?php if (!empty($user['name'])): ?>
                                <span class="suggestion-fullname"><?php echo htmlspecialchars($user['name']); ?></span>
                            <?php endif; ?>
                            <span class="suggestion-username">@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                    </div>
                    <div class="suggestion-action">
                        <button class="follow-btn <?php echo $user['is_followed'] ? 'followed' : ''; ?>" 
                                data-user-id="<?php echo $user['id']; ?>"
                                data-followed="<?php echo $user['is_followed'] ? 'true' : 'false'; ?>">
                            <?php echo $user['is_followed'] ? 'Following' : 'Follow'; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Handle follow button click
document.querySelectorAll('.follow-btn').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const isFollowed = this.getAttribute('data-followed') === 'true';
        const buttonElement = this;

        // Change button appearance immediately for faster UI feedback
        buttonElement.disabled = true;
        
        // Send AJAX request to toggle follow status
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../api/toggle_follow.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            buttonElement.disabled = false;
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Update button text and state
                        if (response.action === 'followed') {
                            buttonElement.textContent = 'Following';
                            buttonElement.classList.add('followed');
                            buttonElement.setAttribute('data-followed', 'true');
                        } else {
                            buttonElement.textContent = 'Follow';
                            buttonElement.classList.remove('followed');
                            buttonElement.setAttribute('data-followed', 'false');
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('An error occurred while processing the request.');
                }
            } else {
                alert('Request failed with status: ' + xhr.status);
            }
        };
        
        xhr.onerror = function() {
            buttonElement.disabled = false;
            alert('Request failed. Please try again.');
        };
        
        xhr.send('followed_id=' + encodeURIComponent(userId));
    });
});
</script>