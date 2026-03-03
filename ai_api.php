<?php
// Ensure no output before this
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

session_start();

// Check Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}

// Check Auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Check Input
if (empty($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Empty Message']);
    exit;
}

$message = $_POST['message'];

// --- LOCAL KNOWLEDGE BASE (No API Key Required) ---
$lowerMessage = strtolower(trim($message));
$localResponses = [
    'what is chatgpt' => "🤖 **ChatGPT** is an advanced AI language model developed by OpenAI. It uses deep learning to generate human-like text, answer questions, write code, and assist with various creative tasks based on the prompts you provide.",
    'chatgpt' => "🤖 **ChatGPT** is an advanced AI language model developed by OpenAI. It uses deep learning to generate human-like text, answer questions, write code, and assist with various creative tasks based on the prompts you provide.",
    'what is ai' => "🧠 **Artificial Intelligence (AI)** is the simulation of human intelligence by machines, especially computer systems. It involves learning (the acquisition of information and rules for using it), reasoning (using rules to reach conclusions), and self-correction.",
    'ai' => "🧠 **Artificial Intelligence (AI)** is the simulation of human intelligence by machines, especially computer systems. It involves learning (the acquisition of information and rules for using it), reasoning (using rules to reach conclusions), and self-correction.",
    'what is computer' => "💻 **A Computer** is an electronic device that manipulates information, or data. It has the ability to store, retrieve, and process data. It consists of hardware (physical parts) and software (instructions) that work together to perform tasks.",
    'computer' => "💻 **A Computer** is an electronic device that manipulates information, or data. It has the ability to store, retrieve, and process data. It consists of hardware (physical parts) and software (instructions) that work together to perform tasks.",
    'what is data' => "📊 **Data** is a collection of facts, such as numbers, words, measurements, observations or just descriptions of things. In computing, data is information that has been translated into a form that is efficient for movement or processing.",
    'computer and data' => "💻📊 **Computers and Data**: A computer is an electronic device designed to process **data**. Data represents raw facts or figures which the computer's CPU processes to produce meaningful information. Without data, a computer has nothing to work with; and without a computer, data can be hard to process at scale.",
    'data' => "📊 **Data** is a collection of facts, such as numbers, words, measurements, observations or just descriptions of things. In computing, data is information that has been translated into a form that is efficient for movement or processing."
];

// Check for matches (flexible match)
foreach ($localResponses as $key => $response) {
    if (strpos($lowerMessage, $key) !== false) {
        echo json_encode(['status' => 'success', 'reply' => $response]);
        exit;
    }
}
// ---------------------------------------------------

// Check for API Key in Session or Environment
// We default to the provided key, but allow session override
$defaultKey = 'sk-2FnceBc4YbreJUd1TY-2cw';
$apiKey = isset($_SESSION['openai_api_key']) && !empty($_SESSION['openai_api_key']) ? $_SESSION['openai_api_key'] : $defaultKey;

// If no key found, return specific status to trigger UI prompt
if (empty($apiKey) || $apiKey === 'YOUR_OPENAI_API_KEY_HERE') {
    echo json_encode([
        'status' => 'error',
        'code' => 'MISSING_API_KEY',
        'message' => 'OpenAI API Key is missing. Please enter it in Settings.'
    ]);
    exit;
}

$systemPrompt = "You are a highly intelligent, premium AI assistant. Your interface is a Cyberpunk Neon style. Respond with helpful, professional, and concise answers. Format code blocks nicely.";

// Real API Call
$url = 'https://api.openai.com/v1/chat/completions';
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message]
    ],
    'temperature' => 0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    // Graceful fallback for connection errors
    $fallbackReply = "⚠️ **Network Error**: I couldn't reach the AI server. (Detail: $err)";
    echo json_encode(['status' => 'success', 'reply' => $fallbackReply]);
    exit;
}

$decoded = json_decode($response, true);

// Success
if ($httpCode === 200 && isset($decoded['choices'][0]['message']['content'])) {
    echo json_encode(['status' => 'success', 'reply' => $decoded['choices'][0]['message']['content']]);
}
// Handle Quota Exceeded (429) or Invalid Key (401) gracefully
elseif ($httpCode === 429 || $httpCode === 401) {
    $errorType = ($httpCode === 429) ? "Quota Exceeded" : "Invalid API Key";

    // Fallback "Offline Mode" response so the app doesn't look broken
    $demoReply = "⚠️ **$errorType (Error $httpCode)**\n\n" .
        "Your OpenAI API key has ran out of credits or is invalid. I am unable to generate a new AI response.\n\n" .
        "💡 *To fix this: Go to platform.openai.com > Billing and add credits.*\n\n" .
        "_(Since I can't think right now, I'm just a simple echo bot until you fix the key!)_";

    echo json_encode(['status' => 'success', 'reply' => $demoReply]);
} else {
    $apiError = $decoded['error']['message'] ?? 'Unknown API Error';
    $fallbackReply = "⚠️ **AI Server Error ($httpCode)**: $apiError";
    echo json_encode(['status' => 'success', 'reply' => $fallbackReply]);
}
?>
