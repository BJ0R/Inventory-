<?php
// frontend/user-profile_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Ensure user is logged in to access their profile
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_profile':
            getProfile();
            break;
        case 'update_profile':
            updateProfile();
            break;
        case 'update_password':
            updatePassword();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Profile API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: An internal server error occurred.']);
}

/**
 * Fetches the user's profile data (username, email).
 */
function getProfile() {
    global $pdo;
    
    // Get user ID from session for security
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID from session.']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, username, email
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}

/**
 * Updates the user's profile data (username, email).
 */
function updateProfile() {
    global $pdo;
    
    // Get user ID from session for security
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID from session.']);
        return;
    }
    
    $requiredFields = ['username', 'email'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        return;
    }

    // Check if username or email already exists for other users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists for another account.']);
        return;
    }
    
    // Update profile
    $stmt = $pdo->prepare("
        UPDATE users 
        SET username = ?, email = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$username, $email, $userId])) {
        // Update session username if it changed
        $_SESSION['username'] = $username;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
}

/**
 * Updates the user's password.
 */
function updatePassword() {
    global $pdo;
    
    // Get user ID from session for security
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID from session.']);
        return;
    }
    
    $requiredFields = ['current_password', 'new_password', 'confirm_password'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
        return;
    }
    
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters']);
        return;
    }
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    if ($stmt->execute([$hashedPassword, $userId])) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
}
?>
