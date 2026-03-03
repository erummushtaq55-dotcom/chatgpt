<?php
require_once 'config.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$message = $data['message'] ?? '';
// In a real app, you'd send the conversation history too
// For this clone, we'll send just the last message for simplicity, or a few

if (empty($message)) {
    echo json_encode(['reply' => 'I didn\'t hear anything.']);
    exit;
}

// Prepare OpenAI API Request
$url = 'https://api.openai.com/v1/chat/completions';
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENAI_API_KEY
];

$body = [
    'model' => 'gpt-3.5-turbo', // Or gpt-4
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful AI assistant.'],
        ['role' => 'user', 'content' => $message]
    ]
];

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code !== 200) {
    // If API fails or key is invalid, return a mock response for the demo
    echo json_encode(['reply' => "I'm sorry, I cannot connect to the AI brain right now (API Key invalid or limit reached). But I am a functional clone! You said: " . htmlspecialchars($message)]);
} else {
    $result = json_decode($response, true);
    $reply = $result['choices'][0]['message']['content'] ?? 'No response generated.';
    echo json_encode(['reply' => $reply]);
}

curl_close($ch);
?>
