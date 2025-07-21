<?php
// frontend/sse-notifications.php
session_start();
require_once 'db_connect.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Get user ID from query string
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId <= 0) {
    die("Invalid user ID");
}

// Set a high execution time limit for SSE
set_time_limit(0);

// Function to send SSE message
function sendSseMessage($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Close the session to allow other requests
session_write_close();

// Track the last event ID the client received
$lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? intval($_SERVER['HTTP_LAST_EVENT_ID']) : 0;

// Send a ping message to test connection
sendSseMessage('ping', ['time' => date('Y-m-d H:i:s')]);

// Main loop to check for updates
while (true) {
    // Check for new notifications
    $stmt = $pdo->prepare("
        SELECT id, title, message, related_id, related_type, created_at
        FROM notifications
        WHERE user_id = ? AND id > ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId, $lastEventId]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notification) {
        sendSseMessage('notification', $notification);
        $lastEventId = $notification['id'];
    }
    
    // Check for request status updates
    $stmt = $pdo->prepare("
        SELECT id, status, last_updated
        FROM item_requests
        WHERE user_id = ? AND id > ? AND last_updated >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY last_updated DESC
        LIMIT 1
    ");
    $stmt->execute([$userId, $lastEventId]);
    $requestUpdate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($requestUpdate) {
        sendSseMessage('request_update', [
            'request_id' => $requestUpdate['id'],
            'user_id' => $userId,
            'new_status' => $requestUpdate['status'],
            'updated_at' => $requestUpdate['last_updated']
    ]);
        $lastEventId = $requestUpdate['id'];
    }
    
    // Sleep for 1 second before checking again
    sleep(1);
    
    // Check if client is still connected
    if (connection_aborted()) {
        break;
    }
}
?>