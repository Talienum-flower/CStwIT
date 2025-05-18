<?php
// File: client/notifications.php
include_once 'includes/session.php';
include_once 'includes/header.php';
include 'includes/left_sidebar.php';
include 'includes/right_sidebar.php';
include_once '../api/fetch_notifications.php';
?>

<style>
/* Notification Section Styling */
main {
    margin: 80px 320px 0 270px; /* top right bottom left */
    padding: 20px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    min-height: calc(100vh - 100px);
}

h2 {
    font-size: 24px;
    font-weight: 600;
    color: #d32f2f;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    padding: 10px;
    margin: 0 auto 10px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    width: 60%;
    max-width: 400px;
    transform: translateX(-10%); /* Move left by 20% of its own width */
}
.container{
    margin-top: 75px;  
}
/* Notification Items */
.notification {
    display: flex;
    flex-direction: column;
    background-color: #fff;
    padding: 10px;
    margin: 0 auto 10px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    width: 60%;
    max-width: 400px;
    transform: translateX(-10%); /* Move left by 20% of its own width */
}

.notification p {
    font-size: 15px;
    color: #333;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.notification p small {
    font-size: 13px;
    color: #666;
    margin-left: 10px;
}

.notification small {
    font-size: 12px;
    color: #999;
    margin-bottom: 8px;
}

.notification a {
    align-self: flex-end;
    font-size: 14px;
    color: #d32f2f;
    text-decoration: none;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background-color 0.2s ease;
}

.notification a:hover {
    background-color: #ffebee;
}

/* Right Sidebar "Who to follow" section */
.right-sidebar {
    width: 300px;
    position: fixed;
    right: 20px;
    top: 80px;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.right-sidebar h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

/* Responsive Adjustments */
@media (max-width: 1200px) {
    main {
        margin-right: 270px;
    }
}

@media (max-width: 992px) {
    main {
        margin-left: 20px;
        margin-right: 20px;
    }
    
    .right-sidebar {
        position: static;
        width: auto;
        margin-top: 20px;
    }
}

@media (max-width: 768px) {
    main {
        margin: 70px 15px 0 15px;
        padding: 15px;
    }
    
    h2 {
        font-size: 20px;
    }
    
    .notification {
        padding: 12px;
    }
    
    .notification p {
        font-size: 14px;
    }
    
    .notification a {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    main {
        margin: 60px 10px 0 10px;
        padding: 50px;
    }
    
    .notification {
        padding: 50px;
    }
    
    .notification p {
        font-size: 50px;
    }
    
    .notification a {
        font-size: 12px;
        padding: 4px 8px;
    }   
}
    </style>



<div >
    <h2>Notifications</h2>
    <?php if (isset($notifications) && is_array($notifications) && !empty($notifications)): ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification">
                <p>
                    <?php echo htmlspecialchars($notification['message']); ?>
                    <small>(<?php echo htmlspecialchars($notification['type']); ?>)</small>
                </p>
                <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                <a href="post.php?id=<?php echo htmlspecialchars($notification['related_post_id']); ?>">View Post</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>
</div>
<?php ?>