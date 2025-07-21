<?php
// frontend/admin-request_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_requests':
            getRequests();
            break;
        case 'update_request_status':
            updateRequestStatus();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getRequests() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    $searchCondition = '';
    $params = [];
    
    if (!empty($search)) {
        $searchCondition = "AND (i.item_name LIKE :search OR u.username LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status)) {
        $searchCondition .= " AND r.status = :status";
        $params[':status'] = $status;
    }
    
    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        JOIN users u ON r.user_id = u.id
        WHERE 1=1 $searchCondition
    ");
    $countStmt->execute($params);
    $totalRequests = $countStmt->fetchColumn();
    $totalPages = ceil($totalRequests / $perPage);
    
    // Get requests for current page
    $stmt = $pdo->prepare("
        SELECT r.id, i.item_name, u.username, r.quantity_requested, r.status, r.request_date
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        JOIN users u ON r.user_id = u.id
        WHERE 1=1 $searchCondition
        ORDER BY r.request_date DESC
        LIMIT :offset, :perPage
    ");
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'requests' => $requests,
            'totalPages' => $totalPages
        ]
    ]);
}

function updateRequestStatus() {
    global $pdo;
    
    $requestId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    if ($requestId <= 0 || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        return;
    }
    
    // Validate status
    $validStatuses = ['pending', 'approved', 'rejected', 'on_order', 'partially_fulfilled', 'ready_for_pickup', 'completed', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    // Update request status
    $stmt = $pdo->prepare("UPDATE item_requests SET status = ? WHERE id = ?");
    
    if ($stmt->execute([$status, $requestId])) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
}
?>