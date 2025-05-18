<?php
// Include header and database
include 'includes/header.php';
include '../config/database.php';
// Fetch reported posts with reporter and owner details
$reported_posts = $conn->query("
    SELECT r.id AS report_id, r.post_id, r.user_id AS reporter_id, r.reason, r.created_at AS report_date,
           u1.name AS reporter_name,
           u2.name AS owner_name
    FROM reports r
    JOIN users u1 ON r.user_id = u1.id
    JOIN posts p ON r.post_id = p.id
    JOIN users u2 ON p.user_id = u2.id
")->fetchAll();
?>

<style>

/* Manage Reported Posts Styles */
body {
  background-color: #f5f6fa;
}

.container {
  margin-left: 150px;
  padding: 20px;
  width: calc(100% - 220px);
  background-color: #f5f6fa;
}

h2 {
  color: #8b0000;
  font-size: 24px;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  font-size: 14px;
}

th {
  background-color: #8b0000;
  color: white;
  font-weight: bold;
  text-transform: uppercase;
}

tr:nth-child(even) {
  background-color: #f8f9fa;
}

tr:hover {
  background-color: #e9ecef;
}

td {
  color: #333;
  border-bottom: 1px solid #dee2e6;
}

.button {
  display: inline-block;
  padding: 8px 12px;
  margin: 2px;
  background-color: #8b0000;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  font-size: 12px;
  transition: background-color 0.3s;
}

.button:hover {
  background-color: #700000;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    margin-left: 0;
    width: 100%;
    padding: 10px;
  }

  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }

  th, td {
    min-width: 120px;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 20px;
  }

  th, td {
    font-size: 12px;
    padding: 8px 10px;
  }

  .button {
    padding: 6px 10px;
    font-size: 10px;
  }
}
</style>

<div class="container">
  <h2>Manage Reported Posts</h2>
  <table>
    <tr>
      <th>Reporter Name</th>
      <th>Post Owner Name</th>
      <th>Reason</th>
      <th>Report Date</th>
      <th>Action</th>
    </tr>
    <?php foreach ($reported_posts as $report): ?>
      <tr>
        <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
        <td><?php echo htmlspecialchars($report['owner_name']); ?></td>
        <td><?php echo htmlspecialchars($report['reason']); ?></td>
        <td><?php echo $report['report_date']; ?></td>
        <td>
          <a href="view_report.php?id=<?php echo $report['post_id']; ?>" class="button">View Post</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php ?>