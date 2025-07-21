<?php
// frontend/backup_api.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'create_backup':
            createBackup();
            break;
        case 'get_backups':
            getBackups();
            break;
        case 'download_backup':
            downloadBackup();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function createBackup() {
    // Define backup directory (create if doesn't exist)
    $backupDir = __DIR__ . '/../backups/';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Define backup filename with timestamp
    $backupFile = $backupDir . 'ispsc_inventory_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Get all tables
    $tables = [];
    $result = $GLOBALS['pdo']->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Create backup file
    $output = '';
    foreach ($tables as $table) {
        // Table structure
        $output .= "--\n-- Table structure for table `$table`\n--\n";
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $result = $GLOBALS['pdo']->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        $output .= $row[1] . ";\n\n";
        
        // Table data
        $output .= "--\n-- Dumping data for table `$table`\n--\n";
        $result = $GLOBALS['pdo']->query("SELECT * FROM `$table`");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $output .= "INSERT INTO `$table` VALUES(";
            $values = [];
            foreach ($row as $value) {
                $values[] = is_null($value) ? 'NULL' : $GLOBALS['pdo']->quote($value);
            }
            $output .= implode(',', $values) . ");\n";
        }
        $output .= "\n";
    }
    
    // Save to file
    file_put_contents($backupFile, $output);
    
    // Compress the file (optional)
    // You could add gzip compression here if desired
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Backup created successfully',
        'data' => [
            'filename' => basename($backupFile),
            'size' => formatSizeUnits(filesize($backupFile)),
            'created_at' => date('Y-m-d H:i:s', filemtime($backupFile)),
            'tables' => $tables
        ]
    ]);
}

function getBackups() {
    $backupDir = __DIR__ . '/../backups/';
    $backups = [];
    
    if (file_exists($backupDir)) {
        $files = glob($backupDir . '*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => formatSizeUnits(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => $backups
    ]);
}

function downloadBackup() {
    $backupDir = __DIR__ . '/../backups/';
    $file = $_GET['file'] ?? '';
    
    if (empty($file) || !preg_match('/^ispsc_inventory_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $file)) {
        die('Invalid backup file');
    }
    
    $filePath = $backupDir . $file;
    
    if (!file_exists($filePath)) {
        die('Backup file not found');
    }
    
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

// Helper function to format file size
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
?>