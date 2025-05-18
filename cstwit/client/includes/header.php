<?php
// No session_start() here; handled in config/session.php
// Determine if this is an auth page (login or register)
$current_file = basename($_SERVER['PHP_SELF']);
$is_auth_page = ($current_file == 'login.php' || $current_file == 'register.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CStwIT</title>
  <link rel="stylesheet" href="../assets/style.css">
  <!-- Add Font Awesome for eye icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Load JavaScript file containing likePost() function -->
  <script src="../assets/script.js"></script>
  <style>
    .cs-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #f5f5f5;
    padding: 20px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    z-index: 1000; /* Ensure it layers above other elements */
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

.cs-logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cs-logo img {
    width: 30px;
    height: 30px;
}

.cs-logo h1 {
    font-size: 18px;
    color: #d32f2f;
    margin: 0;
    font-weight: 600;
}

.cs-search {
    flex: 0 0 auto;
      ;
}

.cs-search form {
    margin: 0;
  
}

.search-input {
    padding: 6px 12px;
    font-size: 14px;
    margin-left: 100px;
    border: 1px solid #ddd;
    border-radius: 20px;
    background-color: #fff;
    width: 200px;
    box-sizing: border-box;
    outline: none;
}

.search-input::placeholder {
    color: #999;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cs-header {
        padding: 8px 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .cs-logo img {
        width: 25px;
        height: 25px;
    }

    .cs-logo h1 {
        font-size: 16px;
    }

    .search-input {
        width: 150px;
        font-size: 13px;
        padding: 5px 10px;
    }
}

@media (max-width: 480px) {
    .cs-header {
        padding: 8px 10px;
        flex-direction: column;
        align-items: flex-start;
    }

    .cs-logo {
        margin-bottom: 10px;
    }

    .cs-logo img {
        width: 20px;
        height: 20px;
    }

    .cs-logo h1 {
        font-size: 14px;
    }

    .cs-search {
        width: 100%;
    }

    .search-input {
        width: 100%;
        font-size: 12px;
        padding: 6px 10px;
    }
}
    </style>
</head>

<body<?php echo $is_auth_page ? ' class="auth-page"' : ''; ?>>
  <?php if (!$is_auth_page): ?>
  <header class="cs-header">
    <div class="cs-logo">
      <img src="../assets/images/logo.png" alt="CStwIT Logo">
      <h1>Welcome to CStwIT</h1>
    </div>
    <div class="cs-search">
      <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search" aria-label="Search" class="search-input">
      </form>
    </div>
  </header>
  <?php endif; ?>
  <div class="container">