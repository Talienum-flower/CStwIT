<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Get user data if logged in
$username = $_SESSION['username'] ?? '';
$displayName = $_SESSION['display_name'] ?? '';
$profileImage = $_SESSION['profile_image'] ?? '';
?>

<aside class="sidebar">
    <?php if ($isLoggedIn): ?>
    <div class="profile-summary">
        <a href="<?php echo $base_url ?? ''; ?>profile.php" class="profile-link">
            <?php if (!empty($profileImage)): ?>
                <img src="<?php echo $base_url . $profileImage; ?>" alt="Profile" class="profile-pic">
            <?php else: ?>
                <div class="profile-pic-placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </a>
        <div class="profile-info">
            <a href="<?php echo $base_url ?? ''; ?>profile.php" class="profile-name">Your Profile</a>
            <span class="username">@<?php echo htmlspecialchars($username); ?></span>
        </div>
    </div>
    
    <nav class="main-nav">
        <ul>
            <li>
                <a href="<?php echo $base_url ?? ''; ?>index.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url ?? ''; ?>posts.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? ' active' : ''; ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>My posts</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url ?? ''; ?>notifications.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? ' active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url ?? ''; ?>settings.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? ' active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="logout-container">
        <form action="<?php echo $base_url ?? ''; ?>api/users/logout.php" method="POST">
            <button type="submit" class="btn btn-logout">Log out</button>
        </form>
    </div>
    <?php else: ?>
    <div class="guest-sidebar">
        <div class="guest-info">
            <p>Welcome to CStwiT</p>
            <p>Join the conversation today!</p>
        </div>
        <div class="guest-actions">
            <a href="<?php echo $base_url ?? ''; ?>pages/login.html" class="btn btn-login-sidebar">Log in</a>
            <a href="<?php echo $base_url ?? ''; ?>pages/login.html" class="btn btn-signup-sidebar">Sign up</a>
        </div>
    </div>
    <?php endif; ?>
</aside>