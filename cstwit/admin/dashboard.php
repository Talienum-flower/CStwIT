<?php
// Include header and database
include 'includes/header.php';
include '../config/database.php';

// Total users and posts
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$post_count = $conn->query("SELECT COUNT(*) FROM posts")->fetchColumn();

// Total reports
$report_count = $conn->query("SELECT COUNT(*) FROM reports")->fetchColumn();

// Recent Activity
$new_users = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$deletion_requests = $conn->query("SELECT id, username, email, created_at FROM users WHERE status = 'deletion_requested' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$user_reports = $conn->query("SELECT r.id, r.user_id, r.reason, r.created_at, u.username FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
$deleted_posts = $conn->query("SELECT id, user_id, content, created_at FROM posts WHERE status = 'deleted' ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<style>
body {
  display: flex;
  min-height: 100vh;
  background-color: #f5f7fa;
  font-family: Arial, sans-serif;
  margin: 0;
}

.container {
  margin-left: 150px;
  padding: 20px;
  width: calc(100% - 220px);
  background-color: #f5f7fa;
}

h2 {
  color: #8b0000;
  font-size: 24px;
  margin-bottom: 20px;
}

.metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.metric-card {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 20px;
  text-align: center;
  transition: transform 0.2s;
}

.metric-card:hover {
  transform: translateY(-5px);
}

.metric-card p:first-child {
  color: #8b0000;
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 10px;
}

.metric-card p:last-child {
  color: #333;
  font-size: 28px;
  font-weight: bold;
}

h3 {
  color: #8b0000;
  font-size: 20px;
  margin-bottom: 15px;
}

.activity-section {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.activity-accordion {
  width: 100%;
}

.activity-accordion h4 {
  color: #8b0000;
  font-size: 16px;
  margin: 10px 0;
  cursor: pointer;
  padding: 10px;
  background-color: #f8f9fa;
  border-radius: 5px;
}

.activity-accordion h4.active {
  background-color: #e0e0e0;
}

.activity-content {
  display: none;
  padding: 10px 0;
}

.activity-content.active {
  display: block;
}

.activity-content p {
  margin: 5px 0;
  font-size: 14px;
  color: #555;
}

.activity-content a {
  color: #8b0000;
  text-decoration: none;
}

.activity-content a:hover {
  text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    margin-left: 0;
    width: 100%;
    padding: 10px;
  }
  .metrics {
    grid-template-columns: 1fr;
  }
  .metric-card {
    margin-bottom: 15px;
  }
  .activity-section {
    margin-top: 15px;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 20px;
  }
  .metric-card p:last-child {
    font-size: 22px;
  }
  .activity-accordion h4 {
    font-size: 14px;
  }
  .activity-content p {
    font-size: 12px;
  }
}
</style>

<div class="container">
  <h2>Admin Dashboard</h2>
  <div class="metrics">
    <div class="metric-card">
      <p>Total Users</p>
      <p><?php echo number_format($user_count); ?></p>
    </div>
    <div class="metric-card">
      <p>Total Posts</p>
      <p><?php echo number_format($post_count); ?></p>
    </div>
    <div class="metric-card">
      <p>Total Reports</p>
      <p><?php echo $report_count; ?></p>
    </div>
  </div>

  <h3>Recent Activity</h3>
  <div class="activity-section">
    <div class="activity-accordion">
      <h4 class="active">New Registered Users</h4>
      <div class="activity-content active">
        <?php foreach ($new_users as $user): ?>
          <p><?php echo $user['username'] . ' (' . $user['email'] . ') - ' . $user['created_at']; ?></p>
        <?php endforeach; ?>
      </div>

      <h4>Users Requesting Deletion</h4>
      <div class="activity-content">
        <?php foreach ($deletion_requests as $user): ?>
          <p><?php echo $user['username'] . ' (' . $user['email'] . ') - ' . $user['created_at']; ?></p>
        <?php endforeach; ?>
      </div>

      <h4>Users Who Reported Others</h4>
      <div class="activity-content">
        <?php foreach ($user_reports as $report): ?>
          <p><a href="view_report.php?id=<?php echo $report['id']; ?>"><?php echo $user['username'] . ' - Reason: ' . $report['reason'] . ' - ' . $report['created_at']; ?></a></p>
        <?php endforeach; ?>
      </div>

      <h4>Recently Deleted Posts</h4>
      <div class="activity-content">
        <?php foreach ($deleted_posts as $post): ?>
          <p><?php echo 'Post ID: ' . $post['id'] . ' by User ID: ' . $post['user_id'] . ' - ' . $post['content'] . ' - ' . $post['created_at']; ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const headers = document.querySelectorAll('.activity-accordion h4');
  headers.forEach(header => {
    header.addEventContentsListener('click', () => {
      const content = header.nextElementSibling;
      const isActive = header.classList.contains('active');
      
      headers.forEach(h => h.classList.remove('active'));
      document.querySelectorAll('.activity-content').forEach(c => c.classList.remove('active'));
      
      if (!isActive) {
        header.classList.add('active');
        content.classList.add('active');
      }
    });
  });
});
</script>
<?php ?>