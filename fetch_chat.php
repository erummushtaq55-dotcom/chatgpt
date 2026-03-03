<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT message, response FROM chats WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$userId]);
    $chats = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'chats' => $chats]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
