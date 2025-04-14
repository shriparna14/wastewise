<?php
session_start();
require 'config.php';

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

$postId = json_decode(file_get_contents('php://input'), true)['post_id'];
$stmt = $pdo->prepare("UPDATE forum SET is_resolved = 1 WHERE id = ?");
$stmt->execute([$postId]);

echo json_encode(['success' => true]);
?>