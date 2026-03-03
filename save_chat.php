<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && !empty($_POST['response'])) {

    $userId = $_SESSION['user_id'];
    $message = $_POST['message'];
    $response = $_POST['response'];

    try {
        $stmt = $pdo->prepare("INSERT INTO chats (user_id, message, response) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $message, $response]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
