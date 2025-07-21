<?php
// frontend/user-home_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_dashboard_data':
            getDashboardData();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getDashboardData() {
    global $pdo;
    
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    // Get user information
    $userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Get request statistics
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests
        FROM item_requests
        WHERE user_id = ?
    ");
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent completed requests
    $recentStmt = $pdo->prepare("
        SELECT r.id, i.item_name, r.quantity_requested, r.last_updated as completed_date
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        WHERE r.user_id = ? AND r.status = 'completed'
        ORDER BY r.last_updated DESC
        LIMIT 5
    ");
    $recentStmt->execute([$userId]);
    $recentRequests = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'user' => $user,
            'stats' => $stats,
            'recent_requests' => $recentRequests
        ]
    ]);
}
?>