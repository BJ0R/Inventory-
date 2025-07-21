<?php
// frontend/user-profile.php
session_start();
require_once 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php'); // Redirect to login page if not logged in
    exit();
}
$currentUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/user-profile.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar Navigation -->
        <div id="nav-container">
            <?php include 'components/user-nav.php'; ?>
        </div>

        <!-- Header -->
        <div id="header-container">
            <?php include 'components/header.html'; ?>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-4">My Profile</h2>

                <div class="alert" id="alertMessage"></div>

                <div class="profile-card">
                    <div class="profile-header">
                        <div>
                            <div class="profile-title">Profile Information</div>
                            <div>Update your account's profile information</div>
                        </div>
                        <!-- Profile initials based on username now -->
                        <div class="profile-avatar" id="profileInitials"></div>
                    </div>

                    <form id="profileForm">
                        <input type="hidden" id="userId" name="id" value="<?php echo $currentUserId; ?>">
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>

                <div class="profile-card mt-4">
                    <div class="profile-header">
                        <div>
                            <div class="profile-title">Update Password</div>
                            <div>Ensure your account is using a long, random password to stay secure</div>
                        </div>
                    </div>

                    <form id="passwordForm">
                        <input type="hidden" id="passwordUserId" name="id" value="<?php echo $currentUserId; ?>">
                        
                        <div class="form-group">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                <i class="bi bi-eye-fill password-toggle-icon" id="toggleCurrentPassword"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="8">
                                <i class="bi bi-eye-fill password-toggle-icon" id="toggleNewPassword"></i>
                            </div>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="8">
                                <i class="bi bi-eye-fill password-toggle-icon" id="toggleConfirmPassword"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div id="footer-container">
            <?php include 'components/footer.html'; ?>
        </div>
           <!-- botleg -->
            <?php include 'chatbot.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const currentUserId = <?php echo $currentUserId; ?>;
            
            // Load user profile data
            loadUserProfile(currentUserId);

            // Password toggle functionality
            $('#toggleCurrentPassword').click(function() {
                togglePasswordVisibility('currentPassword', $(this));
            });

            $('#toggleNewPassword').click(function() {
                togglePasswordVisibility('newPassword', $(this));
            });

            $('#toggleConfirmPassword').click(function() {
                togglePasswordVisibility('confirmPassword', $(this));
            });

            // Profile form submission
            $('#profileForm').submit(function(e) {
                e.preventDefault();
                updateProfile();
            });

            // Password form submission
            $('#passwordForm').submit(function(e) {
                e.preventDefault();
                updatePassword();
            });

            // Function to toggle password visibility
            function togglePasswordVisibility(inputId, icon) {
                const input = $('#' + inputId);
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                }
            }

            // Function to load user profile
            function loadUserProfile(userId) {
                $.ajax({
                    url: 'backend/user-profile_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_profile',
                        id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const user = response.data;
                            $('#username').val(user.username);
                            $('#email').val(user.email);
                            
                            // Set profile initials using the username
                            const initials = getUsernameInitial(user.username);
                            $('#profileInitials').text(initials);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading profile: ' + error, 'danger');
                    }
                });
            }

            // Function to update profile
            function updateProfile() {
                const formData = $('#profileForm').serialize();
                
                $.ajax({
                    url: 'backend/user-profile_api.php',
                    type: 'POST',
                    data: formData + '&action=update_profile',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert('Profile updated successfully!', 'success');
                            
                            // Update profile initials based on the new username
                            const newUsername = $('#username').val();
                            const initials = getUsernameInitial(newUsername);
                            $('#profileInitials').text(initials);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error updating profile: ' + error, 'danger');
                    }
                });
            }

            // Function to update password
            function updatePassword() {
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();
                
                if (newPassword !== confirmPassword) {
                    showAlert('New password and confirmation do not match', 'danger');
                    return;
                }
                
                if (newPassword.length < 8) {
                    showAlert('Password must be at least 8 characters', 'danger');
                    return;
                }
                
                const formData = $('#passwordForm').serialize();
                
                $.ajax({
                    url: 'backend/user-profile_api.php',
                    type: 'POST',
                    data: formData + '&action=update_password',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert('Password updated successfully!', 'success');
                            $('#passwordForm')[0].reset(); // Clear password fields
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error updating password: ' + error, 'danger');
                    }
                });
            }

            // Helper function to get initial from username
            function getUsernameInitial(username) {
                return username ? username.charAt(0).toUpperCase() : 'U'; // Return 'U' for unknown/empty username
            }

            // Function to show alert message
            function showAlert(message, type) {
                const alert = $('#alertMessage');
                alert.removeClass('alert-success alert-danger');
                alert.addClass(`alert-${type}`);
                alert.text(message);
                alert.fadeIn();
                
                setTimeout(() => {
                    alert.fadeOut();
                }, 5000);
            }
        });
    </script>
</body>
</html>
