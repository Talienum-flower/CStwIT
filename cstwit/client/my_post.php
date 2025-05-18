<?php
// File: client/mypost.php
include 'includes/session.php';
include 'includes/header.php';
include 'includes/left_sidebar.php';
include 'includes/right_sidebar.php';
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to view your posts.";
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

try {
    // Fetch user details
    $stmtUser = $conn->prepare("SELECT name, username FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: login.php");
        exit();
    }

    // Fetch the number of posts for this user
    $stmtPosts = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmtPosts->execute([$userId]);
    $postCount = $stmtPosts->fetchColumn();

    // Fetch all posts by this user
    $stmt = $conn->prepare("SELECT id, content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch likes and comments count for each post
    $postsWithInteractions = [];
    foreach ($posts as $post) {
        // Count likes
        $stmtLikes = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
        $stmtLikes->execute([$post['id']]);
        $post['likes_count'] = $stmtLikes->fetchColumn();

        // Count comments
        $stmtComments = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmtComments->execute([$post['id']]);
        $post['comments_count'] = $stmtComments->fetchColumn();

        // Format the date to match the image (Jan 07, 2024)
        $date = new DateTime($post['created_at']);
        $post['formatted_date'] = $date->format('M d, Y');

        $postsWithInteractions[] = $post;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching posts: " . htmlspecialchars($e->getMessage());
    header("Location: login.php");
    exit();
}
?>

<style>
    /* General Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f5f5f5;
    min-height: 100vh;
}

/* Main Content Container */
main {
    margin: 0 auto;
    padding: 20px;
    max-width: 700px; /* Content width fits between sidebars */
    margin-top: 80px; /* Accounts for fixed header height */
    margin-left: 270px; /* Accounts for left sidebar (250px + padding) */
    margin-right: 320px; /* Accounts for right sidebar (300px + padding) */
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    min-height: calc(100vh - 100px);
}

/* Header and Post Count */
.my-post-header {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    text-align: center;
}

.post-count {
    font-size: 16px;
    color: #666;
    text-align: center;
    margin-bottom: 20px;
}

/* Post List */
.post-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.post-item {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.2s ease;
}

.post-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Post Header */
.post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ccc;
}

.name-username {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.user-username-date {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.post-date {
    margin-left: 5px;
}

.post-options {
    position: relative;
}

.options-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #666;
    cursor: pointer;
}

.options-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 25px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.options-menu.show {
    display: block;
}

.options-menu a, .options-menu button {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.options-menu a:hover, .options-menu button:hover {
    background-color: #f5f5f5;
}

.delete-btn {
    color: #d32f2f;
}

/* Post Content */
.post-content {
    font-size: 15px;
    color: #333;
    line-height: 1.5;
    margin-bottom: 15px;
    word-wrap: break-word;
}

/* Post Actions */
.post-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.interaction-stats {
    display: flex;
    gap: 15px;
}

.like-button, .comment-button {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    color: #666;
    font-size: 14px;
}

.like-button:hover, .comment-button:hover {
    color: #333;
}

.like-icon, .comment-icon {
    width: 20px;
    height: 20px;
}

.like-button.liked .like-icon {
    fill: #d32f2f;
    stroke: #d32f2f;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    color: #666;
    cursor: pointer;
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.modal-form textarea, .modal-form input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    resize: vertical;
}

.report-option-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.report-option-container label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #333;
}

#other-reason-container {
    display: none;
}

.button-container {
    display: flex;
    justify-content: flex-end;
}

.modal-form button[type="submit"] {
    padding: 8px 15px;
    background: linear-gradient(90deg, #ff5722, #d32f2f);
    color: #fff;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.modal-form button[type="submit"]:hover {
    background: linear-gradient(90deg, #e64a19, #b71c1c);
}

/* Responsive Design */
@media (max-width: 1024px) {
    main {
        margin-left: 270px; /* Adjusted for right sidebar width */
        margin-right: 270px;
        max-width: 600px;
    }
}

@media (max-width: 768px) {
    main {
        margin-left: 0;
        margin-right: 0;
        max-width: 100%;
        padding: 15px;
        margin-top: 70px; /* Adjusted for smaller header */
        border-radius: 0;
        box-shadow: none;
    }

    .my-post-header {
        font-size: 20px;
    }

    .post-count {
        font-size: 14px;
    }

    .post-item {
        padding: 10px;
    }

    .user-avatar {
        width: 35px;
        height: 35px;
    }

    .user-name {
        font-size: 14px;
    }

    .user-username-date {
        font-size: 12px;
    }

    .post-content {
        font-size: 14px;
    }

    .modal-content {
        width: 95%;
        padding: 15px;
    }

    .modal-title {
        font-size: 18px;
    }

    .modal-form textarea, .modal-form input[type="text"] {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    main {
        padding: 10px;
        margin-top: 60px; /* Further adjusted for smaller header */
    }

    .my-post-header {
        font-size: 18px;
    }

    .post-count {
        font-size: 13px;
    }

    .post-item {
        padding: 8px;
    }

    .user-avatar {
        width: 30px;
        height: 30px;
    }

    .user-name {
        font-size: 13px;
    }

    .user-username-date {
        font-size: 11px;
    }

    .post-content {
        font-size: 13px;
    }

    .like-button, .comment-button {
        font-size: 12px;
    }

    .like-icon, .comment-icon {
        width: 18px;
        height: 18px;
    }

    .modal-content {
        padding: 10px;
    }

    .modal-title {
        font-size: 16px;
    }

    .modal-form button[type="submit"] {
        font-size: 13px;
        padding: 6px 12px;
    }
}
</style>

<div>
    <h2 class="my-post-header">My Post</h2>
    <div class="post-count"><?php echo $postCount; ?> post<?php echo $postCount != 1 ? 's' : ''; ?></div>

    <!-- Post List -->
    <div class="post-list">
        <?php if (empty($postsWithInteractions)): ?>
            <p>You have no posts yet.</p>
        <?php else: ?>
            <?php foreach ($postsWithInteractions as $post): ?>
                <div class="post-item">
                    <div class="post-header">
                        <div class="user-info">
                            <div class="user-avatar"></div>
                            <div class="name-username">
                                <p class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Your Name'); ?></p>
                                <p class="user-username-date">
                                    @<?php echo htmlspecialchars($user['username'] ?? 'userme'); ?>
                                    <span class="post-date"><?php echo htmlspecialchars($post['formatted_date']); ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="post-options">
                            <button class="options-btn" onclick="toggleOptionsMenu(<?php echo $post['id']; ?>)">⋯</button>
                            <div id="options-menu-<?php echo $post['id']; ?>" class="options-menu">
                                <a href="#" class="view-option" data-post-id="<?php echo htmlspecialchars($post['id']); ?>">View</a>
                                <a href="#" class="edit-option" data-post-id="<?php echo htmlspecialchars($post['id']); ?>">Edit</a>
                                <button onclick="deletePost(<?php echo $post['id']; ?>)" class="delete-btn">Delete</button>
                                <a href="#" class="report-option" data-post-id="<?php echo htmlspecialchars($post['id']); ?>">Report</a>
                            </div>
                        </div>
                    </div>
                    <div class="post-content">
                        <?php 
                        // Display the actual content from database
                        $displayContent = htmlspecialchars($post['content']); 
                        echo $displayContent;
                        ?>
                    </div>
                  
                    <div class="post-actions">
                        <div class="interaction-stats">
                            <button class="like-button <?php echo (isset($_SESSION['liked_posts']) && in_array($post['id'], $_SESSION['liked_posts'])) ? 'liked' : ''; ?>" 
                                    onclick="likePost(<?php echo $post['id']; ?>, this)">
                                <svg class="like-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span class="like-count"><?php echo $post['likes_count']; ?></span>
                            </button>
                            <button class="comment-button" onclick="viewPost(<?php echo $post['id']; ?>)">
                                <svg class="comment-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                                <span class="comment-count"><?php echo $post['comments_count']; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Post Modal -->
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <button class="close-modal">×</button>
        <h3 class="modal-title">Edit Post</h3>
        <form id="edit-form" class="modal-form">
            <input type="hidden" id="edit-post-id" name="post_id">
            <textarea id="edit-content" name="content" placeholder="What's on your mind?"></textarea>
            <div class="button-container">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Post Modal -->
<div id="report-modal" class="modal">
    <div class="modal-content">
        <button class="close-modal">×</button>
        <h3 class="modal-title">Report Post</h3>
        <form id="report-form" class="modal-form">
            <input type="hidden" id="report-post-id" name="post_id">
            <div class="report-option-container">
                <label>
                    <input type="radio" name="reason" value="Inappropriate content" checked> Inappropriate content
                </label>
                <label>
                    <input type="radio" name="reason" value="Spam"> Spam
                </label>
                <label>
                    <input type="radio" name="reason" value="Harassment"> Harassment
                </label>
                <label>
                    <input type="radio" name="reason" value="False information"> False information
                </label>
                <label>
                    <input type="radio" name="reason" value="Other"> Other
                </label>
                <div id="other-reason-container">
                    <input type="text" id="other-reason" placeholder="Please specify the reason">
                </div>
            </div>
            <div class="button-container">
                <button type="submit">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle the options menu
    window.toggleOptionsMenu = function(postId) {
        const menuId = 'options-menu-' + postId;
        const menu = document.getElementById(menuId);
        
        // Close all other open menus first
        document.querySelectorAll('.options-menu.show').forEach(openMenu => {
            if (openMenu.id !== menuId) {
                openMenu.classList.remove('show');
            }
        });
        
        // Toggle this menu
        menu.classList.toggle('show');
    };

    // Close menu when clicking elsewhere on the page
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.options-btn')) {
            document.querySelectorAll('.options-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Edit Post
    document.querySelectorAll('.edit-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            
            // Find the post content
            const postElement = this.closest('.post-item');
            const postContent = postElement.querySelector('.post-content').innerText;
            
            // Populate the edit form
            document.getElementById('edit-post-id').value = postId;
            document.getElementById('edit-content').value = postContent;
            
            // Show the modal
            document.getElementById('edit-modal').style.display = 'flex';
        });
    });

    // Setup edit modal close functionality
    document.querySelector('#edit-modal .close-modal').addEventListener('click', function() {
        document.getElementById('edit-modal').style.display = 'none';
    });

    // Close edit modal when clicking outside
    window.addEventListener('click', function(e) {
        const editModal = document.getElementById('edit-modal');
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // Handle edit form submission
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const postId = document.getElementById('edit-post-id').value;
        const content = document.getElementById('edit-content').value.trim();
        
        if (!content) {
            alert('Post content cannot be empty.');
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../api/edit_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Reload the page to show updated content
                            window.location.reload();
                        } else {
                            alert("Failed to update post: " + response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing response: ", e);
                        alert("An error occurred while processing your request.");
                    }
                } else {
                    alert("Failed to update post: Server error " + xhr.status);
                }
            }
        };
        xhr.send("post_id=" + postId + "&content=" + encodeURIComponent(content));
    });

    // Delete Post
    window.deletePost = function(postId) {
        if (confirm('Are you sure you want to delete this post?')) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "../api/delete_post.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert("Failed to delete post: " + response.message);
                            }
                        } catch (e) {
                            console.error("Error parsing response: ", e);
                            alert("An error occurred while processing your request.");
                        }
                    } else {
                        alert("Failed to delete post: Server error " + xhr.status);
                    }
                }
            };
            xhr.send("post_id=" + postId);
        }
    };

    // View Post
    document.querySelectorAll('.view-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            window.location.href = `post.php?id=${postId}`;
        });
    });

    // Report Post
    const reportModal = document.getElementById('report-modal');
    document.querySelectorAll('.report-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            document.getElementById('report-post-id').value = postId;
            reportModal.style.display = 'flex';
        });
    });

    // Close report modal
    document.querySelector('#report-modal .close-modal').addEventListener('click', function() {
        reportModal.style.display = 'none';
        document.getElementById('report-form').reset();
        document.getElementById('other-reason-container').style.display = 'none';
    });

    // Close report modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === reportModal) {
            reportModal.style.display = 'none';
            document.getElementById('report-form').reset();
            document.getElementById('other-reason-container').style.display = 'none';
        }
    });

    // Show/hide other reason input
    document.querySelectorAll('input[name="reason"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('other-reason-container').style.display = 
                this.value === 'Other' ? 'block' : 'none';
            if (this.value !== 'Other') {
                document.getElementById('other-reason').value = '';
            }
        });
    });

    // Submit report
    document.getElementById('report-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const postId = document.getElementById('report-post-id').value;
        const selectedReason = document.querySelector('input[name="reason"]:checked').value;
        const reason = selectedReason === 'Other' ? 
            document.getElementById('other-reason').value.trim() : selectedReason;

        if (selectedReason === 'Other' && !reason) {
            alert('Please specify a reason for the report.');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../api/report_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert("Post has been reported. Thank you for helping us maintain community standards.");
                            reportModal.style.display = 'none';
                            document.getElementById('report-form').reset();
                            document.getElementById('other-reason-container').style.display = 'none';
                        } else {
                            alert("Failed to report post: " + response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing response: ", e);
                        alert("Post has been reported. Thank you for helping us maintain community standards.");
                        reportModal.style.display = 'none';
                        document.getElementById('report-form').reset();
                        document.getElementById('other-reason-container').style.display = 'none';
                    }
                } else {
                    alert("Failed to report post. Please try again later.");
                }
            }
        };
        xhr.send(`post_id=${postId}&reason=${encodeURIComponent(reason)}`);
    });

    // Like Post (Placeholder - Needs API integration)
    window.likePost = function(postId, button) {
        const xhr = new XMLHttpRequest();
        const isLiked = button.classList.contains('liked');
        const action = isLiked ? "unlike" : "like";
        xhr.open("POST", "../api/like_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.querySelector(`#post-${postId} .like-count`).innerText = response.likes;
                            button.classList.toggle('liked', response.isLiked);
                        } else {
                            console.error("Failed to " + action + " the post: " + response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing response: ", e);
                    }
                } else {
                    console.error("Failed to " + action + " the post: Server error " + xhr.status);
                }
            }
        };
        xhr.send("post_id=" + postId + "&action=" + action);
    };

    // View Post (Redirect to post.php for comments)
    window.viewPost = function(postId) {
        window.location.href = `post.php?id=${postId}`;
    };
});
</script>

<?php  ?>