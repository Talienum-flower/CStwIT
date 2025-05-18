<?php
// Include header and database
include 'includes/header.php';
include '../config/database.php';
$users = $conn->query("SELECT * FROM users WHERE role = 'user'")->fetchAll();
?>

<style>
/* Existing Header CSS */


/* Manage Users Styles */
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
  <h2>Manage Users</h2>
  <table>
    <tr>
      <th>Username</th>
      <th>Name</th>
      <th>Email</th>
      <th>Deletion Status</th>
      <th>Created At</th>
      <th>Action</th>
    </tr>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['name']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td>
          <?php
          if ($user['status'] == 'deletion_requested') {
              echo "Pending";
          } elseif ($user['status'] == 'deletion_approved') {
              echo "Approved";
          } else {
              echo $user['status'];
          }
          ?>
        </td>
        <td><?php echo $user['created_at']; ?></td>
        <td>
          <a href="api/admin/ban_user.php?id=<?php echo $user['id']; ?>" class="button">Ban</a>
          <?php if ($user['status'] == 'deletion_requested'): ?>
            <a href="api/admin/approve_deletion.php?id=<?php echo $user['id']; ?>" class="button">Approve Deletion</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php ?>