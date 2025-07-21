<?php
// frontend/user-management_api.php
session_start();
require_once '../db_connect.php'; // Adjust the path as necessary

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            getUsers();
            break;
        case 'get_user':
            getUser();
            break;
        case 'add_user':
            addUser();
            break;
        case 'update_user':
            updateUser();
            break;
        case 'delete_user':
            deleteUser();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred.']);
}

function getUsers() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchCondition = '';
    $params = [];
    
    if (!empty($search)) {
        $searchCondition = "WHERE username LIKE :search OR email LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users " . $searchCondition);
    $countStmt->execute($params);
    $totalUsers = $countStmt->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);

    $stmt = $pdo->prepare("SELECT id, username, email, role, status FROM users " . $searchCondition . " LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => ['users' => $users, 'totalPages' => $totalPages, 'currentPage' => $page]]);
}

function getUser() {
    global $pdo;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, username, email, role, status FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
}

function addUser() {
    global $pdo;

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'pending';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username, Email, and Password are required.']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or Email already exists.']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (:username, :email, :password, :role, :status)");
    if ($stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role,
        ':status' => $status
    ])) {
        echo json_encode(['success' => true, 'message' => 'User added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user.']);
    }
}

function updateUser() {
    global $pdo;

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'pending';

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username and Email are required.']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id != :id");
    $stmt->execute([':username' => $username, ':email' => $email, ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or Email already exists for another user.']);
        exit();
    }

    $sql = "UPDATE users SET username = :username, email = :email, role = :role, status = :status";
    $params = [
        ':username' => $username,
        ':email' => $email,
        ':role' => $role,
        ':status' => $status,
        ':id' => $id
    ];

    if (!empty($password)) {
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
            exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = :password";
        $params[':password'] = $hashedPassword;
    }

    $sql .= " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update user.']);
    }
}

function deleteUser() {
    global $pdo;

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }

    // Check if user is admin before deletion
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    if ($user['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete admin user.']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }
}
?>