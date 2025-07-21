<?php
// frontend/dashboard_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_dashboard_stats':
            getDashboardStats();
            break;
        case 'get_low_stock_data':
            getLowStockData();
            break;
        case 'get_item_analysis_data':
            getItemAnalysisData();
            break;
        case 'get_recent_activities':
            getRecentActivities();
            break;
        case 'get_pending_requests':
            getPendingRequests();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getDashboardStats() {
    global $pdo;
    
    // Get total inventory items
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventory_items");
    $totalItems = $stmt->fetchColumn();
    
    // Get total withdrawals
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventory_withdrawals");
    $totalWithdrawals = $stmt->fetchColumn();
    
    // Get pending requests
    $stmt = $pdo->query("SELECT COUNT(*) FROM item_requests WHERE status = 'pending'");
    $pendingRequests = $stmt->fetchColumn();
    
    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    // Calculate trends (simplified - in a real app you'd compare with previous period)
    $itemsTrend = rand(0, 10);
    $withdrawalsTrend = rand(0, 15);
    $requestsTrend = rand(-3, 3);
    $usersTrend = rand(0, 8);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_items' => $totalItems,
            'items_trend' => $itemsTrend,
            'total_withdrawals' => $totalWithdrawals,
            'withdrawals_trend' => $withdrawalsTrend,
            'pending_requests' => $pendingRequests,
            'requests_trend' => $requestsTrend,
            'total_users' => $totalUsers,
            'users_trend' => $usersTrend
        ]
    ]);
}

function getLowStockData() {
    global $pdo;
    
    $threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 5;
    
    $stmt = $pdo->prepare("
        SELECT item_name, quantity 
        FROM inventory_items 
        WHERE quantity < ? 
        ORDER BY quantity ASC 
        LIMIT 6
    ");
    $stmt->execute([$threshold]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $quantities = [];
    foreach ($items as $item) {
        $labels[] = $item['item_name'];
        $quantities[] = $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'quantities' => $quantities
        ]
    ]);
}

function getItemAnalysisData() {
    global $pdo;
    
    $days = isset($_GET['days']) ? intval($_GET['days']) : 7;
    $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'stock';
    
    // Determine sort order
    $orderBy = '';
    switch ($sortBy) {
        case 'withdrawal':
            $orderBy = 'total_withdrawn DESC';
            break;
        case 'name':
            $orderBy = 'i.item_name ASC';
            break;
        default:
            $orderBy = 'i.quantity ASC';
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.item_name,
            i.quantity,
            i.unit,
            COALESCE(SUM(w.quantity_withdrawn), 0) as total_withdrawn
        FROM inventory_items i
        LEFT JOIN inventory_withdrawals w ON i.id = w.item_id 
            AND w.date_withdrawn >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY i.id, i.item_name, i.quantity, i.unit
        ORDER BY $orderBy
        LIMIT 10
    ");
    $stmt->execute([$days]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $stockLevels = [];
    $withdrawnAmounts = [];
    foreach ($items as $item) {
        $labels[] = $item['item_name'];
        $stockLevels[] = $item['quantity'];
        $withdrawnAmounts[] = $item['total_withdrawn'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'stock_levels' => $stockLevels,
            'withdrawn_amounts' => $withdrawnAmounts
        ]
    ]);
}

function getRecentActivities() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT l.*, i.item_name 
        FROM inventory_activity_log l
        LEFT JOIN inventory_items i ON l.item_id = i.id
        ORDER BY l.performed_at DESC 
        LIMIT 5
    ");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $activities
    ]);
}

function getPendingRequests() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT r.*, i.item_name, i.unit, u.username 
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        JOIN users u ON r.user_id = u.id
        WHERE r.status = 'pending'
        ORDER BY r.request_date DESC 
        LIMIT 5
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $requests
    ]);
}