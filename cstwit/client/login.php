<?php
// Check if user is already logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header("Location: /CStwIT/client/index.php");
    exit();
}
// Include header
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CStwIT - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Modal Background */
        .modal-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .modal-background.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .register-modal, .forgot-modal {
            background-color: white;
            border-radius: 8px;
            max-width: 500px;
            width: 100%;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .modal-background.active .register-modal,
        .modal-background.active .forgot-modal {
            transform: translateY(0);
        }
        
        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            color: #888;
        }
        
        .modal-close:hover {
            color: #000;
        }

        .error-message {
            color: red;
            margin-top: 10px;
            display: none;
        }

        .success-message {
            color: green;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box cstwit-login">
            <div class="auth-left">
                <h1>CStwIT</h1>
                <p>Join now and never miss an update from your academic circle.</p>
            </div>
            
            <div class="auth-right">
                <div class="auth-header">
                    <div class="logo-icon"></div>
                    <h2>Welcome to CStwIT</h2>
                </div>
                <form action="../api/login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group password-wrapper">
                        <div class="password-container">
                            <input type="password" name="password" id="login-password" placeholder="Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('login-password', this)"></i>
                        </div>
                    </div>
                    <button type="submit" class="auth-button">Log in</button>
                    <div class="auth-links">
                        <a href="#" class="forgot-link" id="open-forgot-modal">Forgot Password?</a>
                    </div>
                    <div class="auth-divider"></div>
                    <a href="#" class="create-account-button" id="open-register-modal">Create new account</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <div class="modal-background" id="register-modal-bg">
        <div class="register-modal">
            <button class="modal-close" id="close-register-modal">×</button>
            <div class="auth-header">
                <div class="logo-icon"></div>
                <h2>Create new account</h2>
            </div>
            <form action="/CStwIT/api/register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group password-wrapper">
                    <div class="password-container">
                        <input type="password" name="password" id="register-password" placeholder="Password" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('register-password', this)"></i>
                    </div>
                </div>
                <button type="submit" class="auth-button">Create new account</button>
                <div class="auth-links">
                    <p>Already have account? <a href="login.php" class="login-link">Log in</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal-background" id="forgot-modal-bg">
        <div class="forgot-modal">
            <button class="modal-close" id="close-forgot-modal">×</button>
            <div class="auth-header">
                <div class="logo-icon"></div>
                <h2>Reset Password</h2>
            </div>
            <div id="email-step">
                <form id="forgot-email-form" class="auth-form">
                    <div class="form-group">
                        <input type="email" name="email" id="forgot-email" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="auth-button">Verify Email</button>
                    <p class="error-message" id="email-error"></p>
                    <p class="success-message" id="email-success"></p>
                </form>
            </div>
            <div id="password-step" style="display: none;">
                <form id="reset-password-form" class="auth-form">
                    <div class="form-group password-wrapper">
                        <div class="password-container">
                            <input type="password" name="new_password" id="new-password" placeholder="New Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('new-password', this)"></i>
                        </div>
                    </div>
                    <div class="form-group password-wrapper">
                        <div class="password-container">
                            <input type="password" name="confirm_password" id="confirm-password" placeholder="Confirm Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm-password', this)"></i>
                        </div>
                    </div>
                    <button type="submit" class="auth-button">Reset Password</button>
                    <p class="error-message" id="password-error"></p>
                    <p class="success-message" id="password-success"></p>
                </form>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(inputId, icon) {
        const passwordInput = document.getElementById(inputId);
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Register Modal Elements
        const openRegisterModalButton = document.getElementById('open-register-modal');
        const closeRegisterModalButton = document.getElementById('close-register-modal');
        const registerModalBackground = document.getElementById('register-modal-bg');

        // Forgot Password Modal Elements
        const openForgotModalButton = document.getElementById('open-forgot-modal');
        const closeForgotModalButton = document.getElementById('close-forgot-modal');
        const forgotModalBackground = document.getElementById('forgot-modal-bg');

        // Login Form Elements
        const loginForm = document.querySelector('.auth-form[action="../api/login.php"]');
        const loginError = document.createElement('p');
        loginError.className = 'error-message';
        loginError.id = 'login-error';
        loginForm.appendChild(loginError);

        // Handle Login Form Submission
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(loginForm);
            // Ensure is_admin is not sent for client login
            formData.delete('is_admin');

            fetch('../api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const errorMessage = document.getElementById('login-error');
                if (data.success) {
                    errorMessage.style.display = 'none';
                    window.location.href = data.redirect;
                } else {
                    errorMessage.textContent = data.message || 'Invalid username or password.';
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                const errorMessage = document.getElementById('login-error');
                errorMessage.textContent = 'An error occurred. Please try again later.';
                errorMessage.style.display = 'block';
            });
        });

        // Open Register Modal
        openRegisterModalButton.addEventListener('click', function(e) {
            e.preventDefault();
            registerModalBackground.style.display = 'flex';
            setTimeout(() => {
                registerModalBackground.classList.add('active');
            }, 10);
        });

        // Close Register Modal
        closeRegisterModalButton.addEventListener('click', function() {
            registerModalBackground.classList.remove('active');
            setTimeout(() => {
                registerModalBackground.style.display = 'none';
            }, 300);
        });

        // Close Register Modal on Background Click
        registerModalBackground.addEventListener('click', function(e) {
            if (e.target === registerModalBackground) {
                registerModalBackground.classList.remove('active');
                setTimeout(() => {
                    registerModalBackground.style.display = 'none';
                }, 300);
            }
        });

        // Open Forgot Password Modal
        openForgotModalButton.addEventListener('click', function(e) {
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

        // Handle Email Verification
        document.getElementById('forgot-email-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('forgot-email').value;
            const emailError = document.getElementById('email-error');
            const emailSuccess = document.getElementById('email-success');

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
</body>
</html>
<?php ?>