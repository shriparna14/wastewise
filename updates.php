<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

require 'config.php';

$lastEventId = $_SERVER['HTTP_LAST_EVENT_ID'] ?? 0;

while (true) {
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE id > ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$lastEventId]);
    $complaint = $stmt->fetch();

    if ($complaint) {
        echo "data: " . json_encode([
            'type' => 'complaint_update',
            'id' => $complaint['id'],
            'status' => $complaint['status']
        ]) . "\n\n";
        $lastEventId = $complaint['id'];
        ob_flush();
        flush();
    }
    
    sleep(5);
}
?>