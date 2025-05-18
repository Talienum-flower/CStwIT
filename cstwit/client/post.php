<?php
// File: client/post.php
include_once 'includes/session.php';
include_once 'includes/header.php';
include 'includes/left_sidebar.php';
include 'includes/right_sidebar.php';

// Include database configuration
include_once '../config/database.php';

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid post ID.");
}

$post_id = (int)$_GET['id'];
$post = null;
$comments = [];
$liked_by = [];

try {
    // Fetch post and owner details
    $stmt = $conn->prepare("
        SELECT p.content, p.created_at, p.likes, u.username, u.profile_pic
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Fetched post: " . print_r($post, true));

    if (!$post) {
        die("Post not found.");
    }

    // Fetch comments
    $stmt = $conn->prepare("
        SELECT c.comment, c.created_at, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched comments: " . print_r($comments, true));

    // Fetch users who liked the post (include user_id)
    $stmt = $conn->prepare("
        SELECT u.id, u.username
        FROM likes l
        JOIN users u ON l.user_id = u.id
        WHERE l.post_id = ?
    ");
    $stmt->execute([$post_id]);
    $liked_by = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched liked_by: " . print_r($liked_by, true));
} catch (PDOException $e) {
    error_log("Error fetching post details: " . $e->getMessage());
    die("An error occurred while loading the post.");
}

// Format the post date to match the image (e.g., Jan 07, 2024)
$date = new DateTime($post['created_at']);
$formatted_date = $date->format('M d, Y');

// Process post content to highlight hashtags
$content = htmlspecialchars($post['content']);
$hashtags = [];
preg_match_all('/#(\w+)/', $content, $matches);
if (!empty($matches[0])) {
    $hashtags = $matches[0];
    foreach ($matches[0] as $hashtag) {
        $content = str_replace($hashtag, "<span class=\"hashtag\">$hashtag</span>", $content);
    }
}

// Check if the current user has liked the post
$is_liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM likes 
        WHERE post_id = ? AND user_id = ?
    ");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    $is_liked = $stmt->fetchColumn() > 0;
}
?>

<style>
   
/* Container for centering between sidebars */
.container {
    margin-left: 125px;
    margin-right: 120px;
    padding: 80px 20px 20px 20px;
    min-height: calc(100vh - 80px);
    width:85%;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

.post {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.name-username {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.user-username-date {
    font-size: 12px;
    color: #666;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
}

.post-date {
    font-size: 12px;
    color: #666;
}

.post-content {
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
    line-height: 1.5;
}

.hashtag {
    color: #1e90ff;
    font-weight: 500;
}

.post-hashtags {
    font-size: 13px;
    color: #1e90ff;
    margin-bottom: 10px;
}

.post-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.comment-input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 13px;
    outline: none;
}

.interaction-stats {
    display: flex;
    gap: 15px;
    position: relative;
}

.like-button,
.comment-button {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
    cursor: pointer;
}

.like-icon,
.comment-icon {
    width: 16px;
    height: 16px;
}

.like-button.liked .like-icon {
    fill: #d32f2f;
    stroke: #d32f2f;
}

.like-button:hover .like-icon,
.comment-button:hover .comment-icon {
    stroke: #d32f2f;
}

.likes-dropdown {
    display: none;
    position: absolute;
    top: 30px;
    left: 0;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 10;
    max-height: 150px;
    overflow-y: auto;
}

.likes-dropdown.show {
    display: block;
}

.liker {
    display: block;
    padding: 8px 15px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
}

.liker:hover {
    background-color: #f5f5f5;
}

.comment-section {
    margin-top: 15px;
}

.comments-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.comment {
    background-color: #f9f9f9;
    padding: 10px;
    border-radius: 8px;
}

.comment strong {
    font-size: 13px;
    color: #333;
}

.comment .post-date {
    font-size: 11px;
    margin-left: 5px;
}

.comment-text {
    font-size: 13px;
    color: #333;
    margin-top: 5px;
}

/* Post Options */
.post-options {
    position: relative;
}

.three-dots {
    font-size: 20px;
    cursor: pointer;
    color: #666;
    padding: 5px;
}

.three-dots:hover {
    color: #333;
}

.post-options-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 25px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 10;
    min-width: 120px;
}

.post-options-menu.show {
    display: block;
}

.post-options-menu a {
    display: block;
    padding: 8px 15px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
}

.post-options-menu a:hover {
    background-color: #f5f5f5;
}

/* Modal Styles */
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
    padding: 20px;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    color: #666;
}

.close-modal:hover {
    color: #333;
}

.modal-content h3 {
    margin: 0 0 15px;
    font-size: 18px;
}

.report-reason {
    margin-bottom: 10px;
}

.report-reason label {
    font-size: 14px;
    color: #333;
    margin-left: 5px;
}

#other-reason-container {
    display: none;
    margin-top: 10px;
}

#other-reason {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

#edit-content {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    margin-bottom: 10px;
    resize: vertical;
}

#report-form, #edit-form {
    display: flex;
    flex-direction: column;
}

#report-form button, #edit-form button {
    align-self: flex-end;
    padding: 8px 15px;
    background-color: #1e90ff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

#report-form button:hover, #edit-form button:hover {
    background-color: #187bcd;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .container {
        margin-left: 200px;
        margin-right: 250px;
        padding: 70px 15px 15px 15px;
    }

    .post {
        padding: 15px;
    }

    .profile-pic {
        width: 35px;
        height: 35px;
    }

    .user-name {
        font-size: 13px;
    }

    .user-username-date,
    .post-date {
        font-size: 11px;
    }

    .post-content {
        font-size: 13px;
    }

    .post-hashtags {
        font-size: 12px;
    }

    .comment-input {
        font-size: 12px;
        padding: 6px;
    }

    .like-button,
    .comment-button {
        font-size: 12px;
    }

    .like-icon,
    .comment-icon {
        width: 14px;
        height: 14px;
    }

    .liker {
        font-size: 12px;
        padding: 6px 12px;
    }

    .comment strong {
        font-size: 12px;
    }

    .comment .post-date {
        font-size: 10px;
    }

    .comment-text {
        font-size: 12px;
    }
}

@media (max-width: 768px) {
    .container {
        margin-left: 0;
        margin-right: 0;
        padding: 60px 10px 10px 10px;
    }

    .post {
        padding: 10px;
    }

    .profile-pic {
        width: 30px;
        height: 30px;
    }

    .user-name {
        font-size: 12px;
    }

    .user-username-date,
    .post-date {
        font-size: 10px;
    }

    .post-content {
        font-size: 12px;
    }

    .post-hashtags {
        font-size: 11px;
    }

    .comment-input {
        font-size: 11px;
        padding: 5px;
    }

    .like-button,
    .comment-button {
        font-size: 11px;
    }

    .like-icon,
    .comment-icon {
        width: 12px;
        height: 12px;
    }

    .liker {
        font-size: 11px;
        padding: 5px 10px;
    }

    .comment {
        padding: 8px;
    }

    .comment strong {
        font-size: 11px;
    }

    .comment .post-date {
        font-size: 9px;
    }

    .comment-text {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 50px 5px 5px 5px;
    }

    .post {
        padding: 8px;
    }

    .profile-pic {
        width: 25px;
        height: 25px;
    }

    .user-name {
        font-size: 11px;
    }

    .user-username-date,
    .post-date {
        font-size: 9px;
    }

    .post-content {
        font-size: 11px;
    }

    .post-hashtags {
        font-size: 10px;
    }

    .comment-input {
        font-size: 10px;
        padding: 4px;
    }

    .like-button,
    .comment-button {
        font-size: 10px;
    }

    .like-icon,
    .comment-icon {
        width: 10px;
        height: 10px;
    }

    .liker {
        font-size: 10px;
        padding: 4px 8px;
    }

    .comment {
        padding: 6px;
    }

    .comment strong {
        font-size: 10px;
    }

    .comment .post-date {
        font-size: 8px;
    }

    .comment-text {
        font-size: 10px;
    }
}
@media (max-width: 1024px) {
    .post-options-menu a {
        font-size: 12px;
        padding: 6px 12px;
    }

    .modal-content {
        width: 95%;
    }
}

@media (max-width: 768px) {
    .post-options-menu a {
        font-size: 11px;
        padding: 5px 10px;
    }

    .modal-content h3 {
        font-size: 16px;
    }

    .report-reason label {
        font-size: 13px;
    }

    #other-reason, #edit-content {
        font-size: 13px;
    }

    #report-form button, #edit-form button {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .post-options-menu a {
        font-size: 10px;
        padding: 4px 8px;
    }

    .modal-content h3 {
        font-size: 14px;
    }

    .report-reason label {
        font-size: 12px;
    }

    #other-reason, #edit-content {
        font-size: 12px;
    }

    #report-form button, #edit-form button {
        font-size: 12px;
    }
}
</style>
    
<div class="container">
    <?php if ($post): ?>
        <div class="post">
            <div class="post-header">
                <div class="user-info">
                    <img src="../assets/uploads/<?php echo htmlspecialchars($post['profile_pic'] ?? 'default.jpg'); ?>" class="profile-pic" alt="Profile">
                    <div class="name-username">
                        <p class="user-name"><?php echo htmlspecialchars($post['username']); ?></p>
                        <p class="user-username-date">
                            @<?php echo htmlspecialchars($post['username']); ?>
                            <span class="post-date"><?php echo htmlspecialchars($formatted_date); ?></span>
                        </p>
                    </div>
                </div>
                <!-- Post Options -->
                <div class="post-options">
                    <span class="three-dots">⋯</span>
                    <div class="post-options-menu">
                        <a href="#" class="view-option" data-post-id="<?php echo $post_id; ?>">View</a>
                        <?php if (isset($_SESSION['user_id']) && isset($post['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                            <a href="#" class="edit-option" data-post-id="<?php echo $post_id; ?>">Edit</a>
                            <a href="#" class="delete-option" data-post-id="<?php echo $post_id; ?>">Delete</a>
                        <?php endif; ?>
                        <a href="#" class="report-option" data-post-id="<?php echo $post_id; ?>">Report</a>
                    </div>
                </div>
            </div>
            <!-- Rest of the post content remains unchanged -->
            <div class="post-content">
                <?php echo $content; ?>
            </div>
            <?php if (!empty($hashtags)): ?>
                <div class="post-hashtags">
                    <?php echo implode(' ', $hashtags); ?>
                </div>
            <?php endif; ?>
            <div class="post-actions">
                <input type="text" class="comment-input" id="comment-input-<?php echo $post_id; ?>" placeholder="Comment" onkeypress="handleCommentKeyPress(event, <?php echo $post_id; ?>)">
                <div class="interaction-stats">
                    <button class="like-button <?php echo $is_liked ? 'liked' : ''; ?>" onclick="toggleLikesDropdown(<?php echo $post_id; ?>, this)">
                        <svg class="like-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="like-count" id="likes-<?php echo $post_id; ?>"><?php echo htmlspecialchars($post['likes']); ?></span>
                    </button>
                    <div class="likes-dropdown" id="likes-dropdown-<?php echo $post_id; ?>">
                        <?php if (!empty($liked_by)): ?>
                            <?php foreach ($liked_by as $liker): ?>
                                <a href="profile.php?user_id=<?php echo urlencode($liker['id']); ?>" class="liker"><?php echo htmlspecialchars($liker['username']); ?></a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="liker">No likes yet.</div>
                        <?php endif; ?>
                    </div>
                    <button class="comment-button" onclick="toggleComments(<?php echo $post_id; ?>)">
                        <svg class="comment-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span class="comment-count"><?php echo count($comments); ?></span>
                    </button>
                </div>
            </div>
            <div class="comment-section" id="comment-section-<?php echo $post_id; ?>" style="display: <?php echo !empty($comments) ? 'block' : 'none'; ?>;">
                <?php if (!empty($comments)): ?>
                    <div class="comments-section">
                        <?php foreach ($comments as $comment): ?>
                            <?php
                            $comment_date = new DateTime($comment['created_at']);
                            $formatted_comment_date = $comment_date->format('M d, Y');
                            ?>
                            <div class="comment">
                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                <span class="post-date"><?php echo htmlspecialchars($formatted_comment_date); ?></span>
                                <div class="comment-text">
                                    <?php echo htmlspecialchars($comment['comment']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Post not found.</p>
    <?php endif; ?>
</div>
    <!-- Edit Modal -->
<div id="edit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h3>Edit Post</h3>
        <form id="edit-form">
            <input type="hidden" id="edit-post-id" name="post_id">
            <textarea id="edit-content" name="content" rows="4" required></textarea>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<!-- Report Modal -->
<div id="report-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h3>Report Post</h3>
        <form id="report-form">
            <input type="hidden" id="report-post-id" name="post_id">
            <div class="report-reason">
                <input type="radio" name="reason" value="Inappropriate Content" id="reason-inappropriate" required>
                <label for="reason-inappropriate">Inappropriate Content</label>
            </div>
            <div class="report-reason">
                <input type="radio" name="reason" value="Spam" id="reason-spam">
                <label for="reason-spam">Spam</label>
            </div>
            <div class="report-reason">
                <input type="radio" name="reason" value="Harassment" id="reason-harassment">
                <label for="reason-harassment">Harassment</label>
            </div>
            <div class="report-reason">
                <input type="radio" name="reason" value="Other" id="reason-other">
                <label for="reason-other">Other</label>
            </div>
            <div id="other-reason-container" style="display: none;">
                <textarea id="other-reason" name="other-reason" rows="4" placeholder="Specify reason..."></textarea>
            </div>
            <button type="submit">Submit Report</button>
        </form>
    </div>
</div>
</div>

<script>
// Toggle Likes Dropdown and Handle Like/Unlike
function toggleLikesDropdown(postId, button) {
    const dropdown = document.getElementById('likes-dropdown-' + postId);
    
    // Close all other open dropdowns
    document.querySelectorAll('.likes-dropdown.show').forEach(openDropdown => {
        if (openDropdown !== dropdown) {
            openDropdown.classList.remove('show');
        }
    });

    // Toggle this dropdown
    dropdown.classList.toggle('show');

    // Handle like/unlike action
    const xhr = new XMLHttpRequest();
    const isLiked = button.classList.contains('liked');
    const action = isLiked ? "unlike" : "like";
    xhr.open("POST", "../api/like_post.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById("likes-" + postId).innerText = response.likes;
                        button.classList.toggle('liked', response.isLiked);

                        // Update the dropdown content dynamically
                        const dropdownContent = document.getElementById('likes-dropdown-' + postId);
                        if (response.liked_by && response.liked_by.length > 0) {
                            dropdownContent.innerHTML = response.liked_by.map(user => 
                                `<a href="profile.php?user_id=${encodeURIComponent(user.id)}" class="liker">${user.username}</a>`
                            ).join('');
                        } else {
                            dropdownContent.innerHTML = '<div class="liker">No likes yet.</div>';
                        }
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
}

// Toggle Comments
function toggleComments(postId) {
    const commentSection = document.getElementById('comment-section-' + postId);
    commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
}

// Submit Comment
function submitComment(postId) {
    const input = document.getElementById('comment-input-' + postId);
    const comment = input.value.trim();
    if (comment.length === 0) return;
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../api/comment_post.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.location.reload();
                    } else {
                        console.error("Failed to add comment: " + response.message);
                    }
                } catch (e) {
                    console.error("Error parsing response: ", e);
                }
            } else {
                console.error("Failed to add comment: Server error " + xhr.status);
            }
        }
    };
    xhr.send("post_id=" + postId + "&comment=" + encodeURIComponent(comment));
    input.value = '';
}

// Handle Comment Key Press
function handleCommentKeyPress(event, postId) {
    if (event.key === 'Enter') {
        event.preventDefault();
        submitComment(postId);
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.like-button') && !event.target.closest('.likes-dropdown')) {
        document.querySelectorAll('.likes-dropdown.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Post Options and Modal Handling
document.addEventListener('DOMContentLoaded', function() {
    // Post Options Menu
    const threeDots = document.querySelectorAll('.three-dots');
    threeDots.forEach(dots => {
        dots.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.post-options-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });
    });

    // Close Menus on Click Outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.post-options')) {
            document.querySelectorAll('.post-options-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Edit Post
    document.querySelectorAll('.edit-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            const postElement = this.closest('.post');
            const postContent = postElement.querySelector('.post-content').innerText;
            document.getElementById('edit-post-id').value = postId;
            document.getElementById('edit-content').value = postContent;
            document.getElementById('edit-modal').style.display = 'flex';
        });
    });

    // Close Edit Modal
    document.querySelector('#edit-modal .close-modal').addEventListener('click', function() {
        document.getElementById('edit-modal').style.display = 'none';
    });

    // Close Edit Modal on Outside Click
    window.addEventListener('click', function(e) {
        const editModal = document.getElementById('edit-modal');
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // Handle Edit Form Submission
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
    document.querySelectorAll('.delete-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            if (confirm('Are you sure you want to delete this post?')) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "../api/delete_post.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    window.location.href = 'index.php'; // Redirect to home or relevant page
                                } else {
                                    alert("Failed to delete post: " + response.message);
                                }
                            } catch (e) {
                                console.error("Error parsing response: ", e);
                            }
                        } else {
                            alert("Failed to delete post: Server error " + xhr.status);
                        }
                    }
                };
                xhr.send("post_id=" + postId);
            }
        });
    });

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
    const closeModal = reportModal.querySelector('.close-modal');
    const reportForm = document.getElementById('report-form');
    const otherReasonContainer = document.getElementById('other-reason-container');
    const otherReasonInput = document.getElementById('other-reason');
    const reasonRadios = document.querySelectorAll('input[name="reason"]');

    document.querySelectorAll('.report-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            document.getElementById('report-post-id').value = postId;
            reportModal.style.display = 'flex';
        });
    });

    closeModal.addEventListener('click', function() {
        reportModal.style.display = 'none';
        reportForm.reset();
        otherReasonContainer.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === reportModal) {
            reportModal.style.display = 'none';
            reportForm.reset();
            otherReasonContainer.style.display = 'none';
        }
    });

    reasonRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            otherReasonContainer.style.display = this.value === 'Other' ? 'block' : 'none';
            if (this.value !== 'Other') {
                otherReasonInput.value = '';
            }
        });
    });

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const postId = document.getElementById('report-post-id').value;
        const selectedReason = document.querySelector('input[name="reason"]:checked').value;
        const reason = selectedReason === 'Other' ? otherReasonInput.value.trim() : selectedReason;

        if (selectedReason === 'Other' && !reason) {
            alert('Please specify a reason for the report.');
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../api/report_post.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    alert("Post has been reported. Thank you for helping us maintain community standards.");
                    reportModal.style.display = 'none';
                    reportForm.reset();
                    otherReasonContainer.style.display = 'none';
                } else {
                    alert("Failed to report post. Please try again later.");
                }
            }
        };
        xhr.send(`post_id=${postId}&reason=${encodeURIComponent(reason)}`);
    });
});
</script>

<?php  ?>