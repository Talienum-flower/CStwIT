<?php
// Include header and database
include 'includes/header.php';
include '../config/database.php';

// Check if post_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_posts.php");
    exit;
}

$post_id = $_GET['id'];

// Fetch post details
$post = $conn->prepare("
    SELECT p.id, p.content, p.created_at, u.name AS owner_name, u.id AS user_id
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$post->execute([$post_id]);
$post_data = $post->fetch();

if (!$post_data) {
    header("Location: manage_posts.php");
    exit;
}

// Fetch report details
$reports = $conn->prepare("
    SELECT r.id, r.reason, r.created_at AS report_date, u.name AS reporter_name
    FROM reports r
    JOIN users u ON r.user_id = u.id
    WHERE r.post_id = ?
");
$reports->execute([$post_id]);
$report_data = $reports->fetchAll();

// Fetch counts
$like_count = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$like_count->execute([$post_id]);
$like_count = $like_count->fetchColumn();

$comment_count = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
$comment_count->execute([$post_id]);
$comment_count = $comment_count->fetchColumn();

// Fixed: Use PHP's count() function on the fetched report_data array
$report_count = count($report_data);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        // Notify reporter (simulated)
        foreach ($report_data as $report) {
            // In a real system, this would send a notification
            // For now, we'll just update the report status
            $stmt = $conn->prepare("UPDATE reports SET status = 'approved' WHERE id = ?");
            $stmt->execute([$report['id']]);
        }
        header("Location: manage_posts.php");
        exit;
    } elseif (isset($_POST['delete'])) {
        // Delete post
        $stmt = $conn->prepare("UPDATE posts SET status = 'deleted' WHERE id = ?");
        $stmt->execute([$post_id]);
        header("Location: manage_posts.php");
        exit;
    } elseif (isset($_POST['warn'])) {
        // Send warning notification (simulated)
        // In a real system, this would send a notification to the post owner
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $post_data['user_id'],
            "Your post has been reported for inappropriate content. Please review our community guidelines."
        ]);
        header("Location: manage_posts.php");
        exit;
    }
}
?>

<style>

/* View Reported Post Styles */
body {
  background-color: #f5f6fa;
}

.container {
  margin-left: 150px;
  padding: 20px;
  width: calc(100% - 220px);
  background-color: #f5f6fa;
}

h2, h3 {
  color: #8b0000;
  margin-bottom: 15px;
}

h2 {
  font-size: 24px;
}

h3 {
  font-size: 18px;
}

.back-btn {
  display: inline-block;
  padding: 8px 15px;
  background-color: #8b0000;
  color: white;
  text-decoration: none;
  border-radius: 20px;
  font-size: 14px;
  transition: background-color 0.3s;
  margin-bottom: 20px;
}

.back-btn:hover {
  background-color: #700000;
}

.post-details, .report-details {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 15px;
  margin-bottom: 20px;
}

.flagged-content {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 15px;
  margin-bottom: 20px;
  border-left: 4px solid #8b0000;
}

.flagged-content h3 {
  margin-top: 0;
  color: #8b0000;
}

p {
  margin: 5px 0;
  font-size: 14px;
  color: #333;
}

strong {
  color: #555;
}

.report-item {
  background-color: #f8f9fa;
  border-radius: 5px;
  padding: 10px;
  margin-bottom: 10px;
}

.action-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.action-btn {
  padding: 8px 15px;
  background-color: #8b0000;
  color: white;
  border: none;
  border-radius: 20px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.action-btn:hover {
  background-color: #700000;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    margin-left: 0;
    width: 100%;
    padding: 10px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
    margin-bottom: 10px;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 20px;
  }

  h3 {
    font-size: 16px;
  }

  p, .report-item {
    font-size: 12px;
  }

  .back-btn, .action-btn {
    font-size: 12px;
    padding: 6px 12px;
  }

  .post-details, .flagged-content, .report-details {
    padding: 10px;
  }
}
</style>

<div class="container">
    <h2>View Reported Post</h2>
    <a href="manage_posts.php" class="back-btn">Back to List</a>

    <div class="post-details">
        <h3>Post Details</h3>
        <p><strong>Content:</strong> <?php echo htmlspecialchars($post_data['content']); ?></p>
        <p><strong>Posted by:</strong> <?php echo htmlspecialchars($post_data['owner_name']); ?></p>
        <p><strong>Posted on:</strong> <?php echo $post_data['created_at']; ?></p>
    </div>

    <div class="flagged-content">
        <h3>Flagged Content</h3>
        <p>This post has been flagged for review due to potential violation of community guidelines.</p>
    </div>

    <div class="post-details">
        <h3>Post Statistics</h3>
        <p><strong>Likes:</strong> <?php echo $like_count; ?></p>
        <p><strong>Comments:</strong> <?php echo $comment_count; ?></p>
        <p><strong>Reports:</strong> <?php echo $report_count; ?></p>
    </div>

    <div class="report-details">
        <h3>Report Details</h3>
        <?php foreach ($report_data as $report): ?>
            <div class="report-item">
                <p><strong>Reported by:</strong> <?php echo htmlspecialchars($report['reporter_name']); ?></p>
                <p><strong>Reason:</strong> <?php echo htmlspecialchars($report['reason']); ?></p>
                <p><strong>Reported on:</strong> <?php echo $report['report_date']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="action-buttons">
        <form method="POST" style="margin: 0;">
            <button type="submit" name="approve" class="action-btn">Approve Post</button>
        </form>
        <form method="POST" style="margin: 0;">
            <button type="submit" name="delete" class="action-btn">Delete Post</button>
        </form>
        <form method="POST" style="margin: 0;">
            <button type="submit" name="warn" class="action-btn">Warn User</button>
        </form>
    </div>
</div>
<?php ?>