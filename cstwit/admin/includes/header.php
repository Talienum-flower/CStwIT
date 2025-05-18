

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SimplePost Admin</title>
  
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

body {
  display: flex;
  min-height: 100vh;
}

/* Navigation sidebar */
nav {
  background-color: #8b0000; /* Deep red color */
  width: 220px;
  display: flex;
  flex-direction: column;
  color: white;
  padding-top: 20px;
  position: fixed;
  min-height: 100vh;
  background-image: url("../assets/owl-pattern.png"); /* Add owl pattern background */
  background-repeat: repeat;
  background-size: contain;
}

/* Logo and title */
nav:after {
  content: "CStwIT Admin";
  font-size: 18px;
  font-weight: bold;
  color: white;
  position: absolute;
  top: 75px;
  left: 45px;
  text-align: center;
}

/* Horizontal separator line */
nav::before {
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
  content: "";
  display: block;
  width: 90%;
  margin: 120px auto 20px;
}

/* Navigation links */
nav a {
  color: white;
  text-decoration: none;
  padding: 15px 20px;
  display: flex;
  align-items: center;
  border-left: 5px solid transparent;
  transition: all 0.3s ease;
  margin: 10px 0;
  font-size: 14px;
}

nav a i {
  margin-right: 10px;
  font-size: 16px;
}

nav a:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.logout-btn {
  background-color: white;
  color: #8b0000;
  width: 160px; /* Fixed width to ensure proper centering */
  text-align: center;
  margin: 0 auto; /* Centers the button horizontally */
  border-radius: 20px;
  padding: 10px 0;
  font-size: 14px;
  border: none;
  cursor: pointer;
  position: absolute;
  bottom: 20px; /* Positions it at the bottom */
  left: 50%; /* Moves the left edge to the center of the nav */
  transform: translateX(-50%); /* Shifts it back by half its width to center */
}
/* Main content area */
.container {
  flex: 1;
  padding: 20px;
  background-color: #f4f4f4;
}
  </style>

</head>
<body>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_posts.php">Manage Posts</a>
   <a href="logout.php" class="logout-btn" style="display: block; text-align: center;">Logout</a>

</div>
  </nav>
  <div class="container">