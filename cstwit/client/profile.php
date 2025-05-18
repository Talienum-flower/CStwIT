<?php
// File: client/profile.php
include 'includes/session.php';
include 'includes/header.php';
include 'includes/left_sidebar.php';
include 'includes/right_sidebar.php';
include '../config/database.php';

// Determine which profile to show
$profileId = isset($_GET['user_id']) && is_numeric($_GET['user_id'])
    ? (int)$_GET['user_id']
    : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);

if ($profileId === null) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: login.php");
    exit();
}

// Fetch the user's data
try {
    $stmt = $conn->prepare("SELECT id, username, email, profile_pic, name, bio FROM users WHERE id = ?");
    $stmt->execute([$profileId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: login.php");
        exit();
    }

    // Fetch the number of posts for this user
    $stmtPosts = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmtPosts->execute([$profileId]);
    $postCount = $stmtPosts->fetchColumn();

    // Fetch the list of users this profile is following
    $stmtFollowing = $conn->prepare("SELECT followed_id, followed_username FROM follows WHERE follower_id = ?");
    $stmtFollowing->execute([$profileId]);
    $followingList = $stmtFollowing->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the list of this profile's followers
    $stmtFollowers = $conn->prepare("SELECT follower_id, follower_username FROM follows WHERE followed_id = ?");
    $stmtFollowers->execute([$profileId]);
    $followersList = $stmtFollowers->fetchAll(PDO::FETCH_ASSOC);

    // Check if the current user is following this profile (only if viewing another user's profile)
    $isFollowing = false;
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $profileId) {
        $stmtFollow = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmtFollow->execute([$_SESSION['user_id'], $profileId]);
        $isFollowing = $stmtFollow->fetchColumn() > 0;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching profile: " . htmlspecialchars($e->getMessage());
    header("Location: login.php");
    exit();
}
?>

<style>
    <style>
/* Main container for profile content */
.main-content {
    margin: 0 auto;
    padding: 80px 20px 20px; /* Account for fixed header height (approx. 60px + 20px padding) */
    max-width: 00px; /* Constrain content width between sidebars */
    min-height: calc(100vh - 80px); /* Ensure content fills viewport minus header */
    display: flex;
    justify-content: center;
    box-sizing: border-box;
}

/* Profile header container */
.profile-header {
    background-color: #fff;
    border-radius: 12px;
    padding: 0 auto 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    text-align: center;
    font-family: Arial, sans-serif;
    margin top: 100px;
}

/* Post count */
.profile-header .post-count {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
    display: block;
}

/* Profile title */
.profile-header h2 {
    font-size: 24px;
    color: #d32f2f; 
    margin: 0 0 15px;
    font-weight: 600;
}

/* Profile picture */
.profile-header .profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
    margin: 0 auto 15px;
    display: block;
}

/* Profile details */
.profile-header p {
    font-size: 16px;
    color: #333;
    margin: 8px 0;
    text-align: left;
    padding: 0 20px;
}

.profile-header p strong {
    color: #d32f2f; /* Match left sidebar accent */
    font-weight: 600;
}

/* Edit/Follow/Unfollow buttons */
.profile-header .button,
.profile-header .btn {
    display: inline-block;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border-radius: 20px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    margin: 10px 5px;
}

.profile-header .button {
    background-color: #d32f2f; /* Match left sidebar logout button */
    color: #fff;
}

.profile-header .button:hover {
    background-color: #b71c1c; /* Darker red on hover */
}

.profile-header .btn-primary {
    background-color: #1e90ff; /* Match right sidebar follow button */
    color: #fff;
    border: none;
}

.profile-header .btn-primary:hover {
    background-color: #187bcd; /* Match right sidebar follow button hover */
}

.profile-header .btn-danger {
    background-color: #e0e0e0; /* Match right sidebar followed state */
    color: #333;
    border: none;
}

.profile-header .btn-danger:hover {
    background-color: #d0d0d0; /* Match right sidebar followed hover */
}

/* Tab buttons */
.tab-buttons {
    display: flex;
    justify-content: center;
    margin: 15px 0;
    border-bottom: 1px solid #ddd;
}

.tab {
    padding: 10px 20px;
    font-size: 16px;
    color: #666;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s ease;
}

.tab:hover {
    color: #d32f2f;
}

.tab.active {
    color: #d32f2f;
    border-bottom: 2px solid #d32f2f;
}

/* Follow lists */
.follow-list {
    display: none;
    padding: 0 20px;
    text-align: left;
}

.follow-list.active {
    display: block;
}

.follow-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.follow-item a {
    font-size: 14px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
}

.follow-item a:hover {
    color: #d32f2f;
}

.follow-button {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 20px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.follow-button:not(.following) {
    background-color: #1e90ff;
    color: #fff;
}

.follow-button.following {
    background-color: #e0e0e0;
    color: #333;
}

.follow-button:not(.following):hover {
    background-color: #187bcd;
}

.follow-button.following:hover {
    background-color: #d0d0d0;
}

/* Edit Profile Modal */
.modal.fade {
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-dialog-centered {
    max-width: 500px;
}

.profile-edit-modal {
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    align-items: center;
}

.modal-logo {
    display: flex;
    align-items: center;
}

.modal-title {
    font-size: 18px;
    color: #333;
    font-weight: 600;
}

.close {
    font-size: 24px;
    color: #666;
    opacity: 1;
    text-shadow: none;
}

.close:hover {
    color: #d32f2f;
}

.modal-body {
    padding: 20px;
}

.profile-pic-upload {
    text-align: center;
    margin-bottom: 20px;
}

.profile-pic-container {
    position: relative;
    display: inline-block;
}

.edit-profile-pic {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
}

.profile-pic-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
    cursor: pointer;
}

.profile-pic-container:hover .profile-pic-overlay {
    opacity: 1;
}

.profile-pic-overlay span {
    color: #fff;
    font-size: 12px;
    text-align: center;
    padding: 0 10px;
}

.hidden-file-input {
    display: none;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
    outline: none;
}

.form-control:focus {
    border-color: #d32f2f;
    box-shadow: 0 0 5px rgba(211, 47, 47, 0.3);
}

.save-button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #d32f2f;
    color: #fff;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.save-button:hover {
    background-color: #b71c1c;
}

/* Responsive Design */
@media (max-width: 1280px) {
    .main-content {
        max-width: 500px;
        padding: 80px 15px 15px;
    }

    .profile-header {
        padding: 15px;
    }

    .profile-header .profile-pic {
        width: 100px;
        height: 100px;
    }

    .profile-header h2 {
        font-size: 20px;
    }

    .profile-header p {
        font-size: 14px;
    }
}

@media (max-width: 1024px) {
    .main-content {
        max-width: 450px;
    }

    .profile-header .profile-pic {
        width: 90px;
        height: 90px;
    }

    .tab {
        font-size: 14px;
        padding: 8px 15px;
    }

    .follow-item {
        font-size: 13px;
    }

    .follow-button {
        padding: 5px 10px;
        font-size: 12px;
    }
}

@media (max-width: 768px) {
    .main-content {
        max-width: 100%;
        padding: 70px 10px 80px; /* Adjust for bottom sidebars */
        margin: 0;
    }

    .profile-header {
        border-radius: 0;
        box-shadow: none;
        padding: 10px;
    }

    .profile-header .profile-pic {
        width: 80px;
        height: 80px;
    }

    .profile-header h2 {
        font-size: 18px;
    }

    .profile-header p {
        font-size: 13px;
        padding: 0 10px;
    }

    .button, .btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .tab-buttons {
        margin: 10px 0;
    }

    .tab {
        font-size: 13px;
        padding: 6px 12px;
    }

    .follow-list {
        padding: 0 10px;
    }

    .follow-item {
        padding: 8px 0;
    }

    .modal-dialog-centered {
        max-width: 90%;
    }

    .edit-profile-pic {
        width: 60px;
        height: 60px;
    }

    .form-control {
        font-size: 13px;
        padding: 6px 10px;
    }

    .save-button {
        padding: 8px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 60px 5px 70px;
    }

    .profile-header .profile-pic {
        width: 70px;
        height: 70px;
    }

    .profile-header h2 {
        font-size: 16px;
    }

    .profile-header p {
        font-size: 12px;
        padding: 0 5px;
    }

    .button, .btn {
        padding: 5px 10px;
        font-size: 12px;
    }

    .tab {
        font-size: 12px;
        padding: 5px 10px;
    }

    .follow-item {
        font-size: 12px;
    }

    .follow-button {
        padding: 4px 8px;
        font-size: 11px;
    }

    .edit-profile-pic {
        width: 50px;
        height: 50px;
    }

    .form-control {
        font-size: 12px;
        padding: 5px 8px;
    }

    .save-button {
        padding: 6px;
        font-size: 12px;
    }
}
</style>
    </style>

    <div class="profile-header">
        <span class="post-count"><?php echo $postCount; ?> post<?php echo $postCount != 1 ? 's' : ''; ?></span>
        <h2>Profile</h2>
        <img src="../assets/uploads/<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.jpg'); ?>" class="profile-pic" alt="Profile Picture">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? 'Not set'); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio'] ?? 'Not set'); ?></p>
        <?php if ($profileId === (int)$_SESSION['user_id']): ?>
            <!-- Edit button for own profile -->
            <a href="#" class="button" data-toggle="modal" data-target="#editProfileModal">Edit Profile</a>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <!-- Follow/Unfollow button for other profiles -->
            <form action="../api/follow_user.php" method="POST" style="display:inline;">
                <input type="hidden" name="followed_id" value="<?php echo $profileId; ?>">
                <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow' : 'follow'; ?>">
                <button type="submit" class="btn btn-<?php echo $isFollowing ? 'danger' : 'primary'; ?>">
                    <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                </button>
            </form>
        <?php endif; ?>

        <!-- Following/Followers Tabs -->
        <div class="tab-buttons">
            <button class="tab active" data-target="following-list">Following</button>
            <button class="tab" data-target="followers-list">Followers</button>
        </div>

        <!-- Following List -->
        <div class="follow-list active" id="following-list">
            <?php if (empty($followingList)): ?>
                <p>No users followed.</p>
            <?php else: ?>
                <?php foreach ($followingList as $followed): ?>
                    <div class="follow-item">
                        <a href="profile.php?user_id=<?php echo htmlspecialchars($followed['followed_id']); ?>">
                            <?php echo htmlspecialchars($followed['followed_username']); ?>
                        </a>
                        <?php if ($profileId !== (int)$_SESSION['user_id']): ?>
                            <form action="../api/follow_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="followed_id" value="<?php echo htmlspecialchars($followed['followed_id']); ?>">
                                <input type="hidden" name="action" value="<?php
                                    $isFollowingUser = false;
                                    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
                                    $stmtCheck->execute([$_SESSION['user_id'], $followed['followed_id']]);
                                    $isFollowingUser = $stmtCheck->fetchColumn() > 0;
                                    echo $isFollowingUser ? 'unfollow' : 'follow';
                                ?>">
                                <button type="submit" class="follow-button <?php echo $isFollowingUser ? 'following' : ''; ?>">
                                    <?php echo $isFollowingUser ? 'Following' : 'Follow'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Followers List -->
        <div class="follow-list" id="followers-list">
            <?php if (empty($followersList)): ?>
                <p>No followers.</p>
            <?php else: ?>
                <?php foreach ($followersList as $follower): ?>
                    <div class="follow-item">
                        <a href="profile.php?user_id=<?php echo htmlspecialchars($follower['follower_id']); ?>">
                            <?php echo htmlspecialchars($follower['follower_username']); ?>
                        </a>
                        <?php if ($profileId !== (int)$_SESSION['user_id']): ?>
                            <form action="../api/follow_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="followed_id" value="<?php echo htmlspecialchars($follower['follower_id']); ?>">
                                <input type="hidden" name="action" value="<?php
                                    $isFollowingUser = false;
                                    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
                                    $stmtCheck->execute([$_SESSION['user_id'], $follower['follower_id']]);
                                    $isFollowingUser = $stmtCheck->fetchColumn() > 0;
                                    echo $isFollowingUser ? 'unfollow' : 'follow';
                                ?>">
                                <button type="submit" class="follow-button <?php echo $isFollowingUser ? 'following' : ''; ?>">
                                    <?php echo $isFollowingUser ? 'Following' : 'Follow'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content profile-edit-modal">
            <div class="modal-header">
                <div class="modal-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="#800000"/>
                    </svg>
                </div>
                <h5 class="modal-title" id="editProfileModalLabel">Personal Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../api/update_profile.php" method="POST" enctype="multipart/form-data" id="profileForm">
                    <div class="profile-pic-upload">
                        <div class="profile-pic-container">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.jpg'); ?>" class="edit-profile-pic" alt="Profile Picture">
                            <div class="profile-pic-overlay">
                                <span>Add profile picture</span>
                            </div>
                        </div>
                        <input type="file" name="profile_pic" id="profile_pic_input" class="hidden-file-input">
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="username" placeholder="Username" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="bio" placeholder="Bio" class="form-control">
                    </div>
                    
                    <!-- Hidden fields to maintain existing functionality -->
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    
                    <button type="submit" class="save-button">Save</button>
                </form>
            </div>
        </div>
    </div>

<script>
    // Trigger file input when clicking on profile picture overlay
    document.addEventListener('DOMContentLoaded', function() {
        const picOverlay = document.querySelector('.profile-pic-overlay');
        const fileInput = document.getElementById('profile_pic_input');
        
        if (picOverlay && fileInput) {
            picOverlay.addEventListener('click', function() {
                fileInput.click();
            });
        }
        
        // Preview selected image
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.edit-profile-pic').src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // Tab switching logic for Following/Followers
        const tabButtons = document.querySelectorAll('.tab');
        const followLists = document.querySelectorAll('.follow-list');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and lists
                tabButtons.forEach(btn => btn.classList.remove('active'));
                followLists.forEach(list => list.classList.remove('active'));

                // Add active class to clicked button and corresponding list
                this.classList.add('active');
                const targetList = document.getElementById(this.getAttribute('data-target'));
                targetList.classList.add('active');
            });
        });
    });
</script>

<?php  ?>