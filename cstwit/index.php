<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: pages/login.html');
    exit();
}

// Set base URL - adjust as needed based on your server configuration
$base_url = './';

// Include database connection
require_once 'api/config/database.php';
require_once 'includes/function.php';

// Get posts for feed
// In a real application, you would fetch posts from the database
// For now we'll create dummy data
$posts = [
    [
        'id' => 1,
        'user_id' => 2,
        'username' => 'exampleuser',
        'display_name' => 'Example User',
        'content' => 'This is a sample post to show how the feed will look.',
        'created_at' => '2025-04-20 10:30:00',
        'likes' => 5,
        'reposts' => 2
    ]
];

// Include header
include 'includes/header.php';
?>

<div class="main-container">
    <!-- Include sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main content area -->
    <main class="content-area">
        <div class="tabs">
            <div class="tab active">For you</div>
            <div class="tab">Following</div>
        </div>
        
        <div class="post-create">
            <form action="api/posts/create.php" method="POST">
                <textarea name="content" class="post-textarea" placeholder="What's happening?"></textarea>
                <div class="post-actions">
                    <button type="submit" class="btn btn-post">Post</button>
                </div>
            </form>
        </div>
        
        <div class="post-feed">
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <div class="post-user-pic profile-pic-small">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="post-user-info">
                        <span class="post-user-name"><?php echo htmlspecialchars($post['display_name']); ?></span>
                        <span class="username">@<?php echo htmlspecialchars($post['username']); ?></span>
                    </div>
                </div>
                
                <div class="post-content">
                    <?php echo htmlspecialchars($post['content']); ?>
                </div>
                
                <div class="post-actions-row">
                    <div class="post-action">
                        <i class="far fa-comment"></i>
                        <span>Comment</span>
                    </div>
                    <div class="post-action">
                        <i class="fas fa-retweet"></i>
                        <span>Repost (<?php echo $post['reposts']; ?>)</span>
                    </div>
                    <div class="post-action">
                        <i class="far fa-heart"></i>
                        <span>Like (<?php echo $post['likes']; ?>)</span>
                    </div>
                </div>
                
                <div class="comment-area" style="margin-top: 10px;">
                    <input type="text" class="comment-input" placeholder="Write a comment...">
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <!-- Right sidebar - Who to follow -->
    <aside class="follow-suggestions">
        <h3 class="follow-header">Who to follow?</h3>
        
        <div class="follow-user">
            <div class="profile-pic-small">
                <i class="fas fa-user"></i>
            </div>
            <div class="follow-user-info">
                <span class="follow-user-name">User Example</span>
                <span class="username">@userexample</span>
            </div>
            <button class="btn btn-follow">Follow</button>
        </div>
        
        <div class="follow-user">
            <div class="profile-pic-small">
                <i class="fas fa-user"></i>
            </div>
            <div class="follow-user-info">
                <span class="follow-user-name">Another User</span>
                <span class="username">@anotheruser</span>
            </div>
            <button class="btn btn-follow">Follow</button>
        </div>
    </aside>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>