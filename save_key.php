<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
    $key = trim($_POST['api_key']);
    $_SESSION['openai_api_key'] = $key;
    echo json_encode(['status' => 'success', 'message' => 'API Key saved securely in session']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
