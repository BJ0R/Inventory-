<?php
// frontend/inventory-management_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_inventory':
            getInventory();
            break;
        case 'get_item':
            getItem();
            break;
        case 'add_item':
            addItem();
            break;
        case 'update_item':
            updateItem();
            break;
        case 'withdraw_item':
            withdrawItem();
            break;
        case 'generate_ris_number':
            generateRisNumber();
            break;
        case 'generate_ws_number':
            generateWsNumber();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getInventory() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchCondition = '';
    $params = [];
    
    if (!empty($search)) {
        $searchCondition = "WHERE item_name LIKE :search OR description LIKE :search OR ris_number LIKE :search OR supplier LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_items $searchCondition");
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
    
    // Get items for current page
    $stmt = $pdo->prepare("
        SELECT id, item_name, description, unit, quantity, date_added, ris_number, supplier 
        FROM inventory_items 
        $searchCondition
        ORDER BY date_added DESC, id DESC
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

function getItem() {
    global $pdo;
    
    $itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, item_name, description, unit, quantity, date_added, ris_number, supplier 
        FROM inventory_items 
        WHERE id = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo json_encode(['success' => true, 'data' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
}

function generateRisNumber() {
    global $pdo;
    
    $yearMonth = date('Y-m');
    $prefix = "RIS-$yearMonth-";
    
    // Get the highest existing RIS number for this month
    $stmt = $pdo->prepare("SELECT MAX(ris_number) FROM inventory_items WHERE ris_number LIKE ?");
    $stmt->execute([$prefix . '%']);
    $lastRisNumber = $stmt->fetchColumn();
    
    if ($lastRisNumber) {
        $lastNumber = intval(substr($lastRisNumber, strlen($prefix)));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $risNumber = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
    echo json_encode(['success' => true, 'ris_number' => $risNumber]);
}

function generateWsNumber() {
    global $pdo;
    
    $yearMonth = date('Y-m');
    $prefix = "WS-$yearMonth-";
    
    // Get the highest existing WS number for this month
    $stmt = $pdo->prepare("SELECT MAX(ws_number) FROM inventory_withdrawals WHERE ws_number LIKE ?");
    $stmt->execute([$prefix . '%']);
    $lastWsNumber = $stmt->fetchColumn();
    
    if ($lastWsNumber) {
        $lastNumber = intval(substr($lastWsNumber, strlen($prefix)));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $wsNumber = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
    echo json_encode(['success' => true, 'ws_number' => $wsNumber]);
}

function addItem() {
    global $pdo;
    
    $requiredFields = ['item_name', 'unit', 'quantity'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $itemName = trim($_POST['item_name']);
    $description = trim($_POST['description'] ?? '');
    $unit = trim($_POST['unit']);
    $quantity = floatval($_POST['quantity']);
    $risNumber = trim($_POST['ris_number'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    
    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
        return;
    }
    
    // If RIS number is provided, check if it's unique
    if ($risNumber) {
        $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE ris_number = ?");
        $stmt->execute([$risNumber]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'RIS Number already exists']);
            return;
        }
    }
    
    // Insert new item
    $stmt = $pdo->prepare("
        INSERT INTO inventory_items (item_name, description, unit, quantity, ris_number, supplier) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$itemName, $description, $unit, $quantity, $risNumber, $supplier])) {
        $itemId = $pdo->lastInsertId();
        
        // Log the activity
        logActivity($itemId, 'added', "Item added with quantity: $quantity");
        
        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item']);
    }
}

function updateItem() {
    global $pdo;
    
    $itemId = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        return;
    }
    
    $requiredFields = ['item_name', 'unit', 'quantity'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $itemName = trim($_POST['item_name']);
    $description = trim($_POST['description'] ?? '');
    $unit = trim($_POST['unit']);
    $quantity = floatval($_POST['quantity']);
    $risNumber = trim($_POST['ris_number'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    
    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
        return;
    }
    
    // Check if RIS number is being changed and if the new one is unique
    if ($risNumber) {
        $stmt = $pdo->prepare("SELECT id FROM inventory_items WHERE ris_number = ? AND id != ?");
        $stmt->execute([$risNumber, $itemId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'RIS Number already exists']);
            return;
        }
    }
    
    // Get current quantity for logging
    $stmt = $pdo->prepare("SELECT quantity, ris_number FROM inventory_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentItem) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        return;
    }
    
    $oldQuantity = $currentItem['quantity'];
    $oldRisNumber = $currentItem['ris_number'];
    
    // Update item
    $stmt = $pdo->prepare("
        UPDATE inventory_items 
        SET item_name = ?, description = ?, unit = ?, quantity = ?, ris_number = ?, supplier = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$itemName, $description, $unit, $quantity, $risNumber, $supplier, $itemId])) {
        // Log the activity
        $details = [];
        if ($oldQuantity != $quantity) {
            $details[] = "Quantity changed from $oldQuantity to $quantity";
        }
        if ($oldRisNumber != $risNumber) {
            $details[] = "RIS# changed from $oldRisNumber to $risNumber";
        }
        if (!empty($details)) {
            logActivity($itemId, 'edited', implode(', ', $details));
        }
        
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update item']);
    }
}

function withdrawItem() {
    global $pdo;
    
    $requiredFields = ['item_id', 'ws_number', 'quantity_withdrawn', 'balance', 'date_withdrawn'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $itemId = intval($_POST['item_id']);
    $wsNumber = trim($_POST['ws_number']);
    $quantityWithdrawn = floatval($_POST['quantity_withdrawn']);
    $balance = floatval($_POST['balance']);
    $dateWithdrawn = trim($_POST['date_withdrawn']);
    $remark = trim($_POST['remark'] ?? '');
    
    if ($quantityWithdrawn <= 0) {
        echo json_encode(['success' => false, 'message' => 'Withdrawal quantity must be positive']);
        return;
    }
    
    // Check if WS number is unique
    $stmt = $pdo->prepare("SELECT id FROM inventory_withdrawals WHERE ws_number = ?");
    $stmt->execute([$wsNumber]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'WS Number already exists']);
        return;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert withdrawal record
        $stmt = $pdo->prepare("
            INSERT INTO inventory_withdrawals 
            (item_id, ws_number, quantity_withdrawn, balance, date_withdrawn, remark) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$itemId, $wsNumber, $quantityWithdrawn, $balance, $dateWithdrawn, $remark]);
        
        // Update item quantity
        $stmt = $pdo->prepare("
            UPDATE inventory_items 
            SET quantity = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$balance, $itemId]);
        
        // Commit transaction
        $pdo->commit();
        
        // Log the activity
        logActivity($itemId, 'withdrawn', "Withdrawn: $quantityWithdrawn, New balance: $balance, WS#: $wsNumber");
        
        echo json_encode(['success' => true, 'message' => 'Item withdrawn successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error during withdrawal: ' . $e->getMessage()]);
    }
}

function logActivity($itemId, $action, $details) {
    global $pdo;
    
    $performedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';
    
    $stmt = $pdo->prepare("
        INSERT INTO inventory_activity_log 
        (item_id, action, details, performed_by) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$itemId, $action, $details, $performedBy]);
}