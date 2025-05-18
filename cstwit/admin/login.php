<?php
// Include header (you might need to modify header.php to include the CSS)
?>

<!-- Add this in your header.php or add it here if you don't want to modify header.php -->
<link rel="stylesheet" href="assets/css/login-style.css">
<style>
/* Reset and Base Styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background-color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100%;
    margin: 0;
}

/* Main Container */
.auth-container, .login-container {
    display: flex;
    width: 80%;
    max-width: 900px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    background-color: #fff;
}

/* Left Section */
.login-info, .auth-left {
    flex: 1;
    background-color: white;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-info h1, .auth-left h1 {
    color: #800000;
    font-size: 42px;
    font-weight: bold;
    margin-bottom: 20px;
}

.login-info p, .auth-left p {
    color: #333;
    font-size: 16px;
    line-height: 1.5;
}

/* Right Section */
.login-form, .auth-right {
    flex: 1;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-left: 1px solid #eee;
    background-color: white;
}

.login-form-container {
    width: 100%;
    max-width: 400px;
    padding: 40px;
    background-color: white;
    border-radius: 8px;
}

.admin-icon, .logo-icon {
    text-align: center;
    margin-bottom: 20px;
}

.admin-icon img {
    width: 40px;
    height: 40px;
}

.logo-icon {
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><circle cx="12" cy="9" r="2" fill="%23800000"/><path d="M12 14c-2.33 0-4.32 1.45-5.12 3.5h10.24c-.8-2.05-2.79-3.5-5.12-3.5z" fill="%23800000"/></svg>') no-repeat center;
    height: 40px;
    width: 40px;
    margin: 0 auto 10px;
}

.admin-text {
    color: #800000;
    font-weight: bold;
    margin-top: 5px;
    margin-bottom: 20px;
}

.form-group, .auth-form .form-group {
    width: 100%;
    margin-bottom: 15px;
}

input[type="text"],
input[type="password"],
input[type="email"] {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    background-color: #fff;
}

input:focus {
    outline: none;
    border-color: #800000;
}

.login-button, .auth-button, form .button {
    width: 100%;
    padding: 12px;
    background-color: #8B0000;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    background: linear-gradient(to right, #8B0000, #D2691E);
}

.login-button:hover, .auth-button:hover, form .button:hover {
    background: linear-gradient(to right, #e04518, #c02505);
}

.forgot-password, .auth-links {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
    color: #666;
    cursor: pointer;
}

/* Forgot Password Modal Styles */
.modal-background, .account-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
}

.modal-background.active {
    opacity: 1;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    width: 400px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.close-modal, .modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    line-height: 1;
}

.modal-title {
    font-size: 20px;
    margin-bottom: 20px;
    color: #333;
    font-weight: 500;
}

.modal-step {
    margin-bottom: 15px;
}

.error-message {
    color: #d9534f;
    font-size: 14px;
    margin-top: 10px;
    display: none;
}

.success-message {
    color: #5cb85c;
    font-size: 14px;
    margin-top: 10px;
    display: none;
}

.modal-button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(to right, #f05123, #d03010);
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.3s;
}

.modal-button:hover {
    background: linear-gradient(to right, #e04518, #c02505);
}

/* Inline Error Message for Login Form */
.login-error {
    color: #d9534f;
    font-size: 14px;
    margin-top: 10px;
    text-align: center;
    display: none;
}

/* Style fixes for PHP integration */
form label {
    display: block;
    margin-bottom: 5px;
    color: #666;
}

h2 {
    display: none; /* Hide the default Admin Login heading since we're using the icon */
}

.auth-divider {
    height: 1px;
    background-color: #eee;
    margin: 20px 0;
}

.create-account-button {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: #f8d7cd;
    color: #800000;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

.create-account-button:hover {
    background-color: #f6c4b5;
}

.forgot-link, .login-link {
    color: #800000;
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover, .login-link:hover {
    text-decoration: underline;
}

/* Password toggle styling */
.password-wrapper, .password-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
    cursor: pointer;
}

/* Media queries for responsiveness */
@media (max-width: 991px) {
    .auth-container, .login-container {
        flex-direction: column;
        max-width: 450px;
    }
    
    .login-info, .auth-left {
        display: none; /* Hide the left section on mobile */
    }
    
    .login-form, .auth-right {
        border-left: none;
        border-radius: 8px;
        width: 100%;
    }
}

@media (max-width: 767px) {
    .auth-container, .login-container {
        width: 90%;
        padding: 10px;
    }
}

/* Override existing sidebar styles when on auth pages */
body.auth-page nav {
    display: none;
}

body.auth-page .container {
    margin-left: 0;
    max-width: 100%;
    padding: 0;
}
</style>

<div class="login-container">
    <div class="login-info">
        <h1>CStwIT</h1>
        <p>Log in now and stay in control of your system and administrative tools.</p>
    </div>
    
    <div class="login-form">
        <div class="login-form-container">
            <div class="admin-icon">
                <img src="assets/images/admin-icon.png" alt="Admin Icon">
                <p class="admin-text">CStwIT Admin</p>
            </div>
            
            <form id="login-form" method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Admin Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <input type="hidden" name="is_admin" value="1">
                <button type="submit" class="button">Login</button>
                <div id="login-error" class="login-error"></div>
            </form>
            
            <div class="forgot-password">
                <a href="#" id="forgot-password-link" class="forgot-link">Forgot Password?</a>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-background" id="forgot-modal-background">
    <div class="modal-content">
        <span class="close-modal" id="close-forgot-modal">×</span>
        <h3 class="modal-title">Reset Password</h3>
        
        <div id="email-step" class="modal-step">
            <form id="forgot-email-form">
                <div class="form-group">
                    <label for="forgot-email">Email Address</label>
                    <input type="email" id="forgot-email" placeholder="Enter your email" required>
                </div>
                <div id="email-error" class="error-message"></div>
                <div id="email-success" class="success-message"></div>
                <button type="submit" class="modal-button">Verify Email</button>
            </form>
        </div>
        
        <div id="password-step" class="modal-step" style="display: none;">
            <form id="reset-password-form">
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" placeholder="Confirm new password" required>
                </div>
                <div id="password-error" class="error-message"></div>
                <div id="password-success" class="success-message"></div>
                <button type="submit" class="modal-button">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Handling Login and Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements for Login Form
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');

    // Elements for Forgot Password Modal
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const forgotModalBackground = document.getElementById('forgot-modal-background');
    const closeForgotModalButton = document.getElementById('close-forgot-modal');

    // Handle Login Form Submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const formData = new FormData(loginForm);
        
        fetch('../api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to the appropriate page on successful login
                window.location.href = data.redirect;
            } else {
                // Show inline error message
                loginError.textContent = data.message;
                loginError.style.display = 'block';
            }
        })
        .catch(error => {
            // Show inline error message for network or server errors
            loginError.textContent = 'An error occurred. Please try again later.';
            loginError.style.display = 'block';
        });
    });

    // Clear error message when user starts typing
    loginForm.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            loginError.style.display = 'none';
        });
    });

    // Open Forgot Password Modal
    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        forgotModalBackground.style.display = 'flex';
        setTimeout(() => {
            forgotModalBackground.classList.add('active');
        }, 10);
    });

    // Close Forgot Password Modal
    closeForgotModalButton.addEventListener('click', function() {
        forgotModalBackground.classList.remove('active');
        setTimeout(() => {
            forgotModalBackground.style.display = 'none';
            // Reset forms and messages
            document.getElementById('forgot-email-form').reset();
            document.getElementById('reset-password-form').reset();
            document.getElementById('email-step').style.display = 'block';
            document.getElementById('password-step').style.display = 'none';
            document.getElementById('email-error').style.display = 'none';
            document.getElementById('email-success').style.display = 'none';
            document.getElementById('password-error').style.display = 'none';
            document.getElementById('password-success').style.display = 'none';
        }, 300);
    });

    // Close Forgot Password Modal on Background Click
    forgotModalBackground.addEventListener('click', function(e) {
        if (e.target === forgotModalBackground) {
            forgotModalBackground.classList.remove('active');
            setTimeout(() => {
                forgotModalBackground.style.display = 'none';
                // Reset forms and messages
                document.getElementById('forgot-email-form').reset();
                document.getElementById('reset-password-form').reset();
                document.getElementById('email-step').style.display = 'block';
                document.getElementById('password-step').style.display = 'none';
                document.getElementById('email-error').style.display = 'none';
                document.getElementById('email-success').style.display = 'none';
                document.getElementById('password-error').style.display = 'none';
                document.getElementById('password-success').style.display = 'none';
            }, 300);
        }
    });

    // Handle Email Verification for Forgot Password
    document.getElementById('forgot-email-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('forgot-email').value;
        const emailError = document.getElementById('email-error');
        const emailSuccess = document.getElementById('email-success');

        // Validate email ends with @gmail.com
        if (!email.toLowerCase().endsWith('@gmail.com')) {
            emailSuccess.style.display = 'none';
            emailError.textContent = 'Please use a Gmail address (e.g., example@gmail.com).';
            emailError.style.display = 'block';
            return;
        }

        fetch('/CStwIT/api/check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                emailError.style.display = 'none';
                emailSuccess.textContent = 'Email verified! Please enter your new password.';
                emailSuccess.style.display = 'block';
                document.getElementById('email-step').style.display = 'none';
                document.getElementById('password-step').style.display = 'block';
            } else {
                emailSuccess.style.display = 'none';
                emailError.textContent = 'Email not found. Please try again.';
                emailError.style.display = 'block';
            }
        })
        .catch(error => {
            emailSuccess.style.display = 'none';
            emailError.textContent = 'An error occurred. Please try again later.';
            emailError.style.display = 'block';
        });
    });

    // Handle Password Reset
    document.getElementById('reset-password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('forgot-email').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const passwordError = document.getElementById('password-error');
        const passwordSuccess = document.getElementById('password-success');

        if (newPassword !== confirmPassword) {
            passwordSuccess.style.display = 'none';
            passwordError.textContent = 'Passwords do not match.';
            passwordError.style.display = 'block';
            return;
        }

        fetch('/CStwIT/api/reset_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email) + '&new_password=' + encodeURIComponent(newPassword)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                passwordError.style.display = 'none';
                passwordSuccess.textContent = 'Password reset successfully! You can now log in.';
                passwordSuccess.style.display = 'block';
                setTimeout(() => {
                    forgotModalBackground.classList.remove('active');
                    setTimeout(() => {
                        forgotModalBackground.style.display = 'none';
                        document.getElementById('forgot-email-form').reset();
                        document.getElementById('reset-password-form').reset();
                        document.getElementById('email-step').style.display = 'block';
                        document.getElementById('password-step').style.display = 'none';
                        passwordSuccess.style.display = 'none';
                    }, 300);
                }, 2000);
            } else {
                passwordSuccess.style.display = 'none';
                passwordError.textContent = data.message || 'An error occurred. Please try again.';
                passwordError.style.display = 'block';
            }
        })
        .catch(error => {
            passwordSuccess.style.display = 'none';
            passwordError.textContent = 'An error occurred. Please try again later.';
            passwordError.style.display = 'block';
        });
    });
});
</script>

<?php
?>