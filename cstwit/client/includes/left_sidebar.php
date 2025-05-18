<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include '../config/database.php'; // Assumes this sets up $conn

// Get current page to highlight active nav item
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user data from the database
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT name, username, profile_pic FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $name = $user['name'] ?? 'User'; // Fallback if name is NULL
            $username = $user['username'] ?? 'yourusername'; // Fallback if username is NULL
            $profile_pic = $user['profile_pic'] ?? 'default.jpg'; // Default to default.jpg if not set
        } else {
            // Fallback if user not found
            $name = 'User';
            $username = 'yourusername';
            $profile_pic = 'default.jpg';
        }
    } catch (PDOException $e) {
        error_log("Database error fetching user: " . $e->getMessage());
        // Fallback on database error
        $name = 'User';
        $username = 'yourusername';
        $profile_pic = 'default.jpg';
    }
} else {
    // Fallback if session not set
    $name = 'User';
    $username = 'yourusername';
    $profile_pic = 'default.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <style>
        .left-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #fff;
            padding: 20px;
            box-sizing: border-box;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .profile-summary {
            margin-bottom: 30px;
            margin-top: 100px;
            
        }

        .profile-pic img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #ccc;
        }

        .profile-info h3 {
            color: #d32f2f;
            margin: 5px 0 0;
            margin-top: -40px;
            margin-left: 70px;
            font-size: 16px;
        }

        .profile-info p {
            color: #666;
            margin: 0;
            margin-left: 70px;
            font-size: 14px;
        }

        .cs-nav a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            padding: 10px 0;
            font-size: 16px;
        }

        .cs-nav a .nav-icon {
            margin-right: 10px;
            color: #333; /* Single color for all icons */
            font-size: 20px;
        }

        .logout-container {
            margin-top: 30px;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: linear-gradient(90deg, #ff5722, #d32f2f);
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 20px;
            font-size: 16px;
        }

        .cs-nav a:hover {
            background-color: #f5f5f5;
            color: #333;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .cs-nav a:hover .nav-icon {
            color: #333;
        }

        @media (max-width: 768px) {
            .cs-nav a:hover {
                background-color: #f5f5f5;
                color: #333;
                border-radius: 5px;
            }

            .cs-nav a:hover .nav-icon {
                color: #333;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .left-sidebar {
                width: 100%;
                height: auto;
                padding: 10px;
                position: fixed;
                bottom: 0;
                left: 0;
                border-top: 1px solid #ddd;
                border-right: none;
                flex-direction: row;
                justify-content: space-around;
                background-color: #fff;
            }

            .profile-summary {
                display: none;
            }

            .cs-nav a {
                flex-direction: column;
                padding: 5px;
                font-size: 14px;
            }

            .cs-nav a .nav-icon {
                margin-right: 0;
                margin-bottom: 5px;
                color: #333; /* Single color for all icons */
            }

            .logout-container {
                margin-top: 0;
            }

            .logout-btn {
                width: auto;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="left-sidebar">
        <div class="profile-summary">
            <div class="profile-pic">
                <img src="../assets/uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p>@<?php echo htmlspecialchars($username); ?></p>
            </div>
        </div>

        <nav class="cs-nav">
            <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Home</span>
            </a>
            <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">Profile</span>
            </a>
            <a href="my_post.php" class="<?php echo ($current_page == 'my_post.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-text">My post</span>
            </a>
            <a href="notifications.php" class="<?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>">
                <span class="nav-icon">🔔</span>
                <span class="nav-text">Notifications</span>
            </a>
            <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>
        
        <div class="logout-container">
            <a href="logout.php" class="logout-btn">Log out</a>
        </div>
    </div>
</body>
</html>