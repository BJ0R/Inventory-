<?php
// frontend/activity-log_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_activities':
            getActivities();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getActivities() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterAction = isset($_GET['filter_action']) ? trim($_GET['filter_action']) : '';
    
    $searchCondition = '';
    $params = [];
    
    // Build search conditions
    $conditions = [];
    
    if (!empty($search)) {
        $conditions[] = "(details LIKE :search OR performed_by LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($filterAction)) {
        $conditions[] = "action = :action";
        $params[':action'] = $filterAction;
    }
    
    if (!empty($conditions)) {
        $searchCondition = "WHERE " . implode(" AND ", $conditions);
    }
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_activity_log $searchCondition");
    $countStmt->execute($params);
    $totalActivities = $countStmt->fetchColumn();
    $totalPages = ceil($totalActivities / $perPage);
    
    // Get activities for current page
    $stmt = $pdo->prepare("
        SELECT id, action, details, performed_by, performed_at 
        FROM inventory_activity_log 
        $searchCondition
        ORDER BY performed_at DESC, id DESC
        LIMIT :offset, :perPage
    ");
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'activities' => $activities,
            'totalPages' => $totalPages
        ]
    ]);
}
?>