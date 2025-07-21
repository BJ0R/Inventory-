<?php
// frontend/available-items_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_items':
            getItems();
            break;
        case 'request_item':
            requestItem();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getItems() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchCondition = '';
    $params = [];
    
    if (!empty($search)) {
        $searchCondition = "WHERE item_name LIKE :search OR description LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_items $searchCondition");
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
    
    // Get items for current page
    $stmt = $pdo->prepare("
        SELECT id, item_name, description 
        FROM inventory_items 
        $searchCondition
        ORDER BY item_name ASC
        LIMIT :offset, :perPage
    ");
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'totalPages' => $totalPages
        ]
    ]);
}

function requestItem() {
    global $pdo;
    
    $requiredFields = ['item_id', 'user_id', 'quantity'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $itemId = intval($_POST['item_id']);
    $userId = intval($_POST['user_id']);
    $quantity = floatval($_POST['quantity']);
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be positive']);
        return;
    }
    
    // Check if item exists
    $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE id = ?");
    $stmt->execute([$itemId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        return;
    }
    
    // Insert request
    $stmt = $pdo->prepare("
        INSERT INTO item_requests 
        (item_id, user_id, quantity_requested) 
        VALUES (?, ?, ?)
    ");
    
    if ($stmt->execute([$itemId, $userId, $quantity])) {
        echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
    }
}
?>