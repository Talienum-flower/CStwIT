<?php
include 'includes/session.php';
include 'includes/header.php';
include 'includes/left_sidebar.php';
include 'includes/right_sidebar.php';
include '../config/database.php';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$new_password, $user_id]);
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $user_id = $_SESSION['user_id'];
    $reason = $_POST['delete_reason'];
    if ($_POST['delete_reason'] === 'Other' && !empty($_POST['delete_reason_other'])) {
        $reason = $_POST['delete_reason_other'];
    }

    // Delete related data from other tables
    $conn->query("DELETE FROM posts WHERE user_id = $user_id");
    $conn->query("DELETE FROM likes WHERE user_id = $user_id");
    $conn->query("DELETE FROM notifications WHERE user_id = $user_id OR related_user_id = $user_id");
    $conn->query("DELETE FROM follows WHERE follower_id = $user_id OR followed_id = $user_id");
    $conn->query("DELETE FROM comments WHERE user_id = $user_id");

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle report account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_account'])) {
    $reported_user_id = $_POST['report_user'];
    $reason = $_POST['report_reason'];
    if ($_POST['report_reason'] === 'Other' && !empty($_POST['report_reason_other'])) {
        $reason = $_POST['report_reason_other'];
    }
    $reporter_id = $_SESSION['user_id'];

    // Insert into notifications table (as a report)
    $message = "User reported for: $reason";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, related_user_id) VALUES (?, ?, 'report', ?)");
    $stmt->execute([$reported_user_id, $message, $reporter_id]);
}

// Fetch users for the report dropdown
$users_result = $conn->query("SELECT id, username FROM users");
$users = [];
while ($row = $users_result->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CSTwIT</title>
    <style>
        /* Global Styles */
      /* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

body {
  background-color: #121212;
  color: #ffffff;
}

/* Container */
.container {
  max-width: 800px;
  margin: 0 auto;
  background-color: #ffffff;
  min-height: 100vh;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #e6e6e6;
}

.logo {
  display: flex;
  align-items: center;
}

.logo img {
  height: 30px;
  margin-right: 10px;
}

.logo h1 {
  font-size: 18px;
  color: #800000;
  font-weight: bold;
}

.search-bar input {
  padding: 8px 15px;
  border-radius: 25px;
  border: 1px solid #e6e6e6;
  width: 180px;
  font-size: 14px;
}

/* Main Content */
.main-content {
  display: flex;
}

/* Sidebar */
.sidebar {
  width: 200px;
  border-right: 1px solid #e6e6e6;
  padding: 25px 15px;
}

.profile-section {
  text-align: center;
  padding-bottom: 20px;
  border-bottom: 1px solid #e6e6e6;
  margin-bottom: 20px;
}

.profile-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #e6e6e6;
  margin: 0 auto 10px;
}

.profile-name {
  font-weight: bold;
  color: #800000;
  font-size: 16px;
  margin-bottom: 5px;
}

.profile-username {
  color: #666666;
  font-size: 14px;
}

.nav-menu {
  list-style-type: none;
}

.nav-item {
  padding: 12px 5px;
  color: #333333;
  font-size: 15px;
  cursor: pointer;
}

.nav-item.active {
  font-weight: bold;
}

.logout-btn {
  margin-top: 150px;
  padding: 8px 0;
  border: 1px solid #800000;
  color: #800000;
  border-radius: 25px;
  text-align: center;
  font-weight: bold;
  cursor: pointer;
}

/* Settings Area */
.settings-area {
  flex: 1;
  padding: 20px;
  background-color: #f5f5f8;
}

.settings-section {
  margin-bottom: 30px;
}

.settings-section h2 {
  color: #800000;
  font-size: 18px;
  margin-bottom: 15px;
}

.settings-card {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.settings-description {
  color: #666666;
  font-size: 14px;
  margin-bottom: 15px;
}

.settings-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-top: 1px solid #f0f0f0;
  cursor: pointer;
}

.settings-option:first-child {
  border-top: none;
}

.option-text {
  color: #333333;
  font-size: 14px;
}

.option-arrow {
  color: #999999;
}

/* Recommendations */
.recommendations {
  width: 200px;
  padding: 20px;
  border-left: 1px solid #e6e6e6;
}

.recommendations h3 {
  color: #800000;
  font-size: 16px;
  margin-bottom: 15px;
}

.recommendation-item {
  padding: 8px 0;
  color: #333333;
  font-size: 14px;
}
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left sidebar is included from includes/left_sidebar.php -->
        
        <div class="content-area">
            <div class="settings-area">
                <div class="settings-header">
                    <h1>Manage your account</h1>
                </div>

                <div class="section">
                    <h2>Password and Security</h2>
                    <p>Manage your login details and keep your account safe.</p>
                    <div class="option" onclick="document.getElementById('changePasswordModal').style.display='block'">Change password</div>
                </div>

                <div class="section">
                    <h2>Report or delete account</h2>
                    <p>Manage your account settings by deactivating it temporarily or deleting it permanently, with all your data removed and unable to be recovered.</p>
                    <div class="option" onclick="document.getElementById('reportAccountModal').style.display='block'">Report account</div>
                    <div class="option" onclick="document.getElementById('deleteAccountModal').style.display='block'">Delete account</div>
                </div>
            </div>
        </div>
        
        <!-- Right sidebar is included from includes/right_sidebar.php -->
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <h3>Change Password</h3>
            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="change_password">Submit</button>
            </form>
        </div>
    </div>

    <!-- Report Account Modal -->
    <div id="reportAccountModal" class="modal">
        <div class="modal-content">
            <h3>Report Account</h3>
            <form method="POST">
                <select name="report_user" required>
                    <option value="">Select user to report</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="report_reason" id="report_reason" onchange="document.getElementById('report_reason_other').style.display = this.value === 'Other' ? 'block' : 'none'" required>
                    <option value="">Select reason</option>
                    <option value="Inappropriate Content">Inappropriate Content</option>
                    <option value="Spam">Spam</option>
                    <option value="Harassment">Harassment</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" name="report_reason_other" id="report_reason_other" class="custom-reason" placeholder="Please specify your reason">
                <button type="submit" name="report_account">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteAccountModal" class="modal">
        <div class="modal-content">
            <h3>Delete Account</h3>
            <p>Are you sure you want to permanently delete your account? This action cannot be undone.</p>
            <form method="POST">
                <select name="delete_reason" id="delete_reason" onchange="document.getElementById('delete_reason_other').style.display = this.value === 'Other' ? 'block' : 'none'" required>
                    <option value="">Select reason for deletion</option>
                    <option value="Privacy Concerns">Privacy Concerns</option>
                    <option value="Not Using Anymore">Not Using Anymore</option>
                    <option value="Technical Issues">Technical Issues</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" name="delete_reason_other" id="delete_reason_other" class="custom-reason" placeholder="Please specify your reason">
                <button type="submit" name="delete_account">Delete Account</button>
            </form>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>