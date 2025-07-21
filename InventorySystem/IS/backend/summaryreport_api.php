<?php
// summaryreport_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_summary_report':
            getSummaryReport();
            break;
        case 'generate_pdf':
            generatePDF();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getSummaryReport() {
    global $pdo;
    
    $filterType = $_GET['filter_type'] ?? 'all';
    $timeRange = $_GET['time_range'] ?? 'monthly';
    $year = $_GET['year'] ?? date('Y');
    $month = $_GET['month'] ?? date('m');
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    // Build date conditions based on time range
    $dateConditions = '';
    $params = [];
    
    if ($timeRange === 'monthly') {
        $dateConditions = "AND YEAR(date_added) = :year AND MONTH(date_added) = :month";
        $params[':year'] = $year;
        $params[':month'] = $month;
    } elseif ($timeRange === 'yearly') {
        $dateConditions = "AND YEAR(date_added) = :year";
        $params[':year'] = $year;
    } elseif ($timeRange === 'custom' && $startDate && $endDate) {
        $dateConditions = "AND date_added BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }
    
    // Build search condition
    $searchCondition = '';
    if (!empty($search)) {
        $searchCondition = "AND (i.item_name LIKE :search OR i.description LIKE :search OR i.ris_number LIKE :search OR i.supplier LIKE :search OR w.ws_number LIKE :search OR w.remark LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Build query based on filter type
    if ($filterType === 'added') {
        $query = "SELECT 
                    i.id, i.item_name, i.description, i.unit, i.date_added, i.ris_number, i.supplier,
                    NULL as ws_number, NULL as quantity_withdrawn, NULL as balance, NULL as date_withdrawn, NULL as remark
                  FROM inventory_items i
                  WHERE 1=1 $dateConditions $searchCondition
                  ORDER BY i.date_added DESC";
    } elseif ($filterType === 'withdrawn') {
        $query = "SELECT 
                    i.id, i.item_name, i.description, i.unit, i.date_added, i.ris_number, i.supplier,
                    w.ws_number, w.quantity_withdrawn, w.balance, w.date_withdrawn, w.remark
                  FROM inventory_withdrawals w
                  JOIN inventory_items i ON w.item_id = i.id
                  WHERE 1=1 $dateConditions $searchCondition
                  ORDER BY w.date_withdrawn DESC";
    } else { // 'all'
        $query = "SELECT 
                    i.id, i.item_name, i.description, i.unit, i.date_added, i.ris_number, i.supplier,
                    w.ws_number, w.quantity_withdrawn, w.balance, w.date_withdrawn, w.remark
                  FROM inventory_items i
                  LEFT JOIN inventory_withdrawals w ON w.item_id = i.id
                  WHERE 1=1 $dateConditions $searchCondition
                  ORDER BY COALESCE(w.date_withdrawn, i.date_added) DESC";
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM ($query) as total";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
    
    // Get data for current page
    $dataQuery = "$query LIMIT :offset, :perPage";
    $stmt = $pdo->prepare($dataQuery);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems
        ]
    ]);
}

function generatePDF() {
    global $pdo;
    
    $filterType = $_POST['filter_type'] ?? 'all';
    $timeRange = $_POST['time_range'] ?? 'monthly';
    $year = $_POST['year'] ?? date('Y');
    $month = $_POST['month'] ?? date('m');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    
    // Build date conditions based on time range
    $dateConditions = '';
    $params = [];
    
    if ($timeRange === 'monthly') {
        $dateConditions = "AND YEAR(date_added) = :year AND MONTH(date_added) = :month";
        $params[':year'] = $year;
        $params[':month'] = $month;
    } elseif ($timeRange === 'yearly') {
        $dateConditions = "AND YEAR(date_added) = :year";
        $params[':year'] = $year;
    } elseif ($timeRange === 'custom' && $startDate && $endDate) {
        $dateConditions = "AND date_added BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }
    
    // Build query based on filter type
    if ($filterType === 'added') {
        $query = "SELECT 
                    i.id, i.item_name, i.description, i.unit, i.date_added, i.ris_number, i.supplier
                  FROM inventory_items i
                  WHERE 1=1 $dateConditions
                  ORDER BY i.date_added DESC";
    } elseif ($filterType === 'withdrawn') {
        $query = "SELECT 
                    i.item_name, i.description, i.unit, i.ris_number, i.supplier,
                    w.ws_number, w.quantity_withdrawn, w.balance, w.date_withdrawn, w.remark
                  FROM inventory_withdrawals w
                  JOIN inventory_items i ON w.item_id = i.id
                  WHERE 1=1 $dateConditions
                  ORDER BY w.date_withdrawn DESC";
    } else { // 'all'
        $query = "SELECT 
                    i.item_name, i.description, i.unit, i.date_added, i.ris_number, i.supplier,
                    w.ws_number, w.quantity_withdrawn, w.balance, w.date_withdrawn, w.remark
                  FROM inventory_items i
                  LEFT JOIN inventory_withdrawals w ON w.item_id = i.id
                  WHERE 1=1 $dateConditions
                  ORDER BY COALESCE(w.date_withdrawn, i.date_added) DESC";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate HTML for PDF
    $html = generatePDFHtml($items, $filterType, $timeRange, $year, $month, $startDate, $endDate);
    
    // Return HTML for preview
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
}

function generatePDFHtml($items, $filterType, $timeRange, $year, $month, $startDate, $endDate) {
    $title = "Inventory Summary Report - ";
    
    if ($timeRange === 'monthly') {
        $title .= date('F Y', mktime(0, 0, 0, $month, 1, $year));
    } elseif ($timeRange === 'yearly') {
        $title .= $year;
    } else {
        $title .= date('m/d/Y', strtotime($startDate)) . " to " . date('m/d/Y', strtotime($endDate));
    }
    
    $title .= " (" . ucfirst($filterType) . " Items)";
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>'.$title.'</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .report-header { display: flex; align-items: center; margin-bottom: 20px; }
            .report-header img { height: 80px; margin-left: 20px; }
            .report-title { text-align: center; flex-grow: 1; }
            .report-title h1 { font-size: 18px; margin-right: 100px; }
            .report-title p { font-size: 14px; margin-right: 100px; }
            
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="report-header">
            <img src="../images/logo.png" alt="ISPSC Logo">
            <div class="report-title">
                <h1>ILOCOS SUR POLYTECHNIC STATE COLLEGE</h1>
                <p>Supply Office</p>
            </div>
        </div>
        
        <h1 style="text-align: center; font-size: 18px; margin-bottom: 20px;">'.$title.'</h1>
        
        <table>
            <thead>
                <tr>';
    
    if ($filterType === 'added') {
        $html .= '
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Date Added</th>
                    <th>RIS Number</th>
                    <th>Supplier</th>';
    } elseif ($filterType === 'withdrawn') {
        $html .= '
                    <th>Item Name</th>
                    <th>RIS Number</th>
                    <th>Supplier</th>
                    <th>WS Number</th>
                    <th>Quantity Withdrawn</th>
                    <th>Balance</th>
                    <th>Date Withdrawn</th>
                    <th>Remarks</th>';
    } else {
        $html .= '
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Date Added</th>
                    <th>RIS Number</th>
                    <th>Supplier</th>
                    <th>WS Number</th>
                    <th>Quantity Withdrawn</th>
                    <th>Balance</th>
                    <th>Date Withdrawn</th>
                    <th>Remarks</th>';
    }
    
    $html .= '
                </tr>
            </thead>
            <tbody>';
    
    foreach ($items as $item) {
        $html .= '<tr>';
        
        if ($filterType === 'added') {
            $html .= '
                <td>'.htmlspecialchars($item['item_name']).'</td>
                <td>'.htmlspecialchars($item['description']).'</td>
                <td>'.htmlspecialchars($item['unit']).'</td>
                <td>'.date('m/d/Y H:i', strtotime($item['date_added'])).'</td>
                <td>'.htmlspecialchars($item['ris_number']).'</td>
                <td>'.htmlspecialchars($item['supplier']).'</td>';
        } elseif ($filterType === 'withdrawn') {
            $html .= '
                <td>'.htmlspecialchars($item['item_name']).'</td>
                <td>'.htmlspecialchars($item['ris_number']).'</td>
                <td>'.htmlspecialchars($item['supplier']).'</td>
                <td>'.htmlspecialchars($item['ws_number']).'</td>
                <td>'.$item['quantity_withdrawn'].'</td>
                <td>'.$item['balance'].'</td>
                <td>'.date('m/d/Y H:i', strtotime($item['date_withdrawn'])).'</td>
                <td>'.htmlspecialchars($item['remark']).'</td>';
        } else {
            $html .= '
                <td>'.htmlspecialchars($item['item_name']).'</td>
                <td>'.htmlspecialchars($item['description']).'</td>
                <td>'.htmlspecialchars($item['unit']).'</td>
                <td>'.date('m/d/Y H:i', strtotime($item['date_added'])).'</td>
                <td>'.htmlspecialchars($item['ris_number']).'</td>
                <td>'.htmlspecialchars($item['supplier']).'</td>
                <td>'.htmlspecialchars($item['ws_number'] ?? '-').'</td>
                <td>'.($item['quantity_withdrawn'] ?? '-').'</td>
                <td>'.($item['balance'] ?? '-').'</td>
                <td>'.($item['date_withdrawn'] ? date('m/d/Y H:i', strtotime($item['date_withdrawn'])) : '-').'</td>
                <td>'.htmlspecialchars($item['remark'] ?? '-').'</td>';
        }
        
        $html .= '</tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        <div class="footer">
            Generated on '.date('m/d/Y H:i').'
        </div>
    </body>
    </html>';
    
    return $html;
}