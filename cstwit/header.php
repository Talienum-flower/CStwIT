<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CStwiT</title>
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="<?php echo $base_url ?? ''; ?>index.php" class="logo">
                    <svg class="logo-svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.643 4.937c-.835.37-1.732.62-2.675.733.962-.576 1.7-1.49 2.048-2.578-.9.534-1.897.922-2.958 1.13-.85-.904-2.06-1.47-3.4-1.47-2.572 0-4.658 2.086-4.658 4.66 0 .364.042.718.12 1.06-3.873-.195-7.304-2.05-9.602-4.868-.4.69-.63 1.49-.63 2.342 0 1.616.823 3.043 2.072 3.878-.764-.025-1.482-.234-2.11-.583v.06c0 2.257 1.605 4.14 3.737 4.568-.392.106-.803.162-1.227.162-.3 0-.593-.028-.877-.082.593 1.85 2.313 3.198 4.352 3.234-1.595 1.25-3.604 1.995-5.786 1.995-.376 0-.747-.022-1.112-.065 2.062 1.323 4.51 2.093 7.14 2.093 8.57 0 13.255-7.098 13.255-13.254 0-.2-.005-.402-.014-.602.91-.658 1.7-1.477 2.323-2.41z"></path>
                    </svg>
                </a>
                <h1 class="site-title">Welcome to CStwiT</h1>
            </div>
            
            <div class="search-container">
                <form action="<?php echo $base_url ?? ''; ?>search.php" method="GET">
                    <input type="text" name="q" placeholder="Search" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <?php if (!$isLoggedIn): ?>
            <div class="auth-links">
                <a href="<?php echo $base_url ?? ''; ?>pages/login.html" class="btn btn-login">Log in</a>
                <a href="<?php echo $base_url ?? ''; ?>pages/login.html" class="btn btn-signup">Sign up</a>
            </div>
            <?php else: ?>
            <div class="user-nav">
                <a href="<?php echo $base_url ?? ''; ?>profile.php" class="profile-link">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="<?php echo $base_url . $_SESSION['profile_image']; ?>" alt="Profile" class="profile-pic-small">
                    <?php else: ?>
                        <div class="profile-pic-placeholder-small">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-container">