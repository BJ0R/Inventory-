<?php
// account.php - Combined login and signup with modern swap UI
session_start();
require_once 'db_connect.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        if ($action === 'login') {
            // Login logic
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password';
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: admin-dashboard.php');
                    } else {
                        header('Location: user-home.php');
                    }
                    exit();
                } else {
                    $error = 'Invalid username or password';
                }
            }
        } elseif ($action === 'signup') {
            // Signup logic
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Please fill in all fields';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                if ($stmt->rowCount() > 0) {
                    $error = 'Username or email already exists';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) 
                                          VALUES (?, ?, ?, 'user', 'pending')");

                    if ($stmt->execute([$username, $email, $hashedPassword])) {
                        $success = 'Registration successful! Your account is pending approval.';
                        $_POST = []; // Clear POST data
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        }
    }
}

$show_signup = false;
if (($error && $action === 'signup') || $success) {
    $show_signup = true;
}

// Prepare form data for repopulation
$form_data = [
    'username' => htmlspecialchars($_POST['username'] ?? ''),
    'email' => htmlspecialchars($_POST['email'] ?? '')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Account | ISPSC Supply Office</title>
    <meta name="description" content="Login or register for ISPSC Supply Office Management System">
    <meta name="robots" content="noindex, nofollow">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../images/logo.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/account.css">
</head>
<body>
    <div class="container <?= $show_signup ? 'active' : '' ?>">
        <div class="form-box login">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" id="loginForm">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <img src="../images/logo.png" alt="ISPSC Logo" class="college-logo" loading="lazy">
                <h1>Sign In</h1>
                
                <?php if ($error && $action === 'login'): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="input-box">
                    <input type="text" name="username" id="loginUsername" placeholder="Username" required
                           aria-label="Username" value="<?= $form_data['username'] ?>" autocomplete="username">
                    <i class='bx bxs-user' aria-hidden="true"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required
                           aria-label="Password" autocomplete="current-password">
                    <i class='bx bxs-lock-alt toggle-password' tabindex="0" aria-label="Toggle password visibility"></i>
                </div>
                <div class="forgot-link">
                    <a href="#" id="forgotPasswordLink" aria-label="Forgot password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn" id="loginBtn">Login</button>
                <p>ISPSC Supply Office Management System</p>
            </form>
        </div>

        <div class="form-box register">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" id="registerForm">
                <input type="hidden" name="action" value="signup">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <img src="../images/logo.png" alt="ISPSC Logo" class="college-logo" loading="lazy">
                <h1>Sign Up</h1>
                
                <?php if ($error && $action === 'signup'): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <div class="input-box">
                    <input type="text" name="username" id="registerUsername" placeholder="Username" required
                           aria-label="Username" value="<?= $form_data['username'] ?>" autocomplete="username">
                    <i class='bx bxs-user' aria-hidden="true"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" id="registerEmail" placeholder="Email" required
                           aria-label="Email" value="<?= $form_data['email'] ?>" autocomplete="email">
                    <i class='bx bxs-envelope' aria-hidden="true"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="registerPassword" placeholder="Password" required
                           aria-label="Password" oninput="checkPasswordStrength(this.value)" autocomplete="new-password">
                    <i class='bx bxs-lock-alt toggle-password' tabindex="0" aria-label="Toggle password visibility"></i>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                </div>
                <button type="submit" class="btn" id="registerBtn">Register</button>
                <p>ISPSC Supply Office Management System</p>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Welcome to ISPSC!</h1>
                <p>Don't have an account? Register to access the Supply Office Management System</p>
                <button type="button" class="btn register-btn" aria-label="Switch to registration form">Register</button>
            </div>

            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account? Login to access your dashboard</p>
                <button type="button" class="btn login-btn" aria-label="Switch to login form">Login</button>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Forgot Password</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>If you've forgotten your password, please contact the ISPSC Supply Office for assistance.</p>
                <div class="contact-info">
                    <p><i class="bi bi-envelope"></i> Email: ISPSCSupplyOffice@gmail.com</p>
                    <p><i class="bi bi-telephone"></i> Phone: (63) 912-345-6789</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.container');
            const registerBtn = document.querySelector('.register-btn');
            const loginBtn = document.querySelector('.login-btn');
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const loginSubmitBtn = document.getElementById('loginBtn');
            const registerSubmitBtn = document.getElementById('registerBtn');
            const forgotPasswordLink = document.getElementById('forgotPasswordLink');
            const modal = document.getElementById('forgotPasswordModal');
            const closeBtn = document.querySelector('.close');
            
            // Prevent viewport scaling on mobile when keyboard appears
            function preventViewportScale() {
                if (window.innerWidth <= 425) {
                    const viewport = document.querySelector('meta[name=viewport]');
                    if (viewport) {
                        viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover');
                    }
                    
                    // Prevent body scroll when keyboard is open
                    document.body.style.position = 'fixed';
                    document.body.style.width = '100%';
                    document.body.style.height = '100%';
                    document.body.style.overflow = 'hidden';
                }
            }
            
            // Handle input focus to prevent keyboard issues on mobile
            function handleInputFocus(e) {
                if (window.innerWidth <= 425) {
                    // Small delay to ensure keyboard is shown
                    setTimeout(() => {
                        // Scroll the focused input into view without affecting the container
                        const input = e.target;
                        const rect = input.getBoundingClientRect();
                        const viewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                        
                        if (rect.bottom > viewportHeight * 0.6) {
                            input.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center',
                                inline: 'nearest'
                            });
                        }
                    }, 300);
                }
            }
            
            // Handle visual viewport changes (keyboard show/hide)
            function handleViewportChange() {
                if (window.visualViewport && window.innerWidth <= 425) {
                    const viewport = window.visualViewport;
                    const container = document.querySelector('.container');
                    
                    // Adjust container when keyboard appears/disappears
                    if (viewport.height < window.innerHeight * 0.75) {
                        // Keyboard is likely open
                        container.style.height = viewport.height + 'px';
                    } else {
                        // Keyboard is likely closed
                        container.style.height = '100vh';
                        container.style.height = '100dvh';
                    }
                }
            }
            
            // Initialize mobile optimizations
            preventViewportScale();
            
            // Add visual viewport listener for modern browsers
            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', handleViewportChange);
            }
            
            // Add input focus listeners
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', handleInputFocus);
                
                // Prevent zoom on iOS by ensuring font-size is at least 16px
                if (/iPad|iPhone|iPod/.test(navigator.userAgent) && window.innerWidth <= 425) {
                    input.style.fontSize = '16px';
                }
            });
            
            // Toggle password visibility
            const togglePassword = function(e) {
                if (e.target.classList.contains('bxs-lock-alt') || e.target.classList.contains('bxs-lock-open-alt')) {
                    const icon = e.target;
                    const input = icon.previousElementSibling || icon.parentElement.querySelector('input[type="password"], input[type="text"]');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bxs-lock-alt');
                        icon.classList.add('bxs-lock-open-alt');
                        icon.setAttribute('aria-label', 'Hide password');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bxs-lock-open-alt');
                        icon.classList.add('bxs-lock-alt');
                        icon.setAttribute('aria-label', 'Show password');
                    }
                }
            };
            
            // Add loading state to forms
            const handleFormSubmit = function(form, submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('loading');
                    submitBtn.textContent = '';
                });
            };
            
            // Modal functionality
            forgotPasswordLink.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'block';
            });
            
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Add event listeners
            registerBtn.addEventListener('click', () => {
                container.classList.add('active');
            });
            
            loginBtn.addEventListener('click', () => {
                container.classList.remove('active');
            });
            
            // Make password icons clickable for toggling visibility
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', togglePassword);
                icon.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        togglePassword(e);
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(alert => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.style.display = 'none';
                        }, 500);
                    });
                }, 5000);
            }
            
            // Add loading states
            handleFormSubmit(loginForm, loginSubmitBtn);
            handleFormSubmit(registerForm, registerSubmitBtn);
            
            // Add error class to inputs with errors
            <?php if ($error): ?>
                const errorInputs = {
                    'login': ['username', 'password'],
                    'signup': ['username', 'email', 'password']
                };
                
                const currentAction = '<?= $action ?>';
                if (errorInputs[currentAction]) {
                    errorInputs[currentAction].forEach(field => {
                        const input = document.querySelector(`#${currentAction}${field.charAt(0).toUpperCase() + field.slice(1)}`);
                        if (input) input.classList.add('error');
                    });
                }
            <?php endif; ?>
        });
        
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;
            
            const width = (strength / 5) * 100;
            strengthBar.style.width = width + '%';
            
            // Change color based on strength
            if (width < 40) {
                strengthBar.style.backgroundColor = '#dc3545'; // Weak
            } else if (width < 70) {
                strengthBar.style.backgroundColor = '#fd7e14'; // Medium
            } else {
                strengthBar.style.backgroundColor = '#28a745'; // Strong
            }
        }
    </script>
</body>
</html>