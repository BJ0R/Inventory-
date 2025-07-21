<?php
// frontend/my-request_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_requests':
            getRequests();
            break;
        case 'cancel_request':
            cancelRequest();
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
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    $searchCondition = "WHERE r.user_id = :user_id";
    $params = [':user_id' => $userId];
    
    if (!empty($search)) {
        $searchCondition .= " AND (i.item_name LIKE :search OR r.status LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        $searchCondition
    ");
    $countStmt->execute($params);
    $totalRequests = $countStmt->fetchColumn();
    $totalPages = ceil($totalRequests / $perPage);
    
    // Get requests for current page
    $stmt = $pdo->prepare("
        SELECT r.id, i.item_name, r.quantity_requested, r.status, r.request_date
        FROM item_requests r
        JOIN inventory_items i ON r.item_id = i.id
        $searchCondition
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

function cancelRequest() {
    global $pdo;
    
    $requestId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($requestId <= 0 || $userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        return;
    }
    
    // Check if request belongs to user
    $stmt = $pdo->prepare("SELECT status FROM item_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or not authorized']);
        return;
    }
    
    // Check if request can be cancelled
    if (!in_array($request['status'], ['pending', 'approved'])) {
        echo json_encode(['success' => false, 'message' => 'Request cannot be cancelled in its current status']);
        return;
    }
    
    // Update request status to cancelled
    $stmt = $pdo->prepare("UPDATE item_requests SET status = 'cancelled' WHERE id = ?");
    
    if ($stmt->execute([$requestId])) {
        echo json_encode(['success' => true, 'message' => 'Request cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel request']);
    }
}
?>