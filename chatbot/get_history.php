<?php
// Server-side proxy to fetch chat history for the currently logged-in user.
session_start();
header('Content-Type: application/json; charset=utf-8');

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated', 'message' => 'User not logged in']);
    exit;
}

$nodeUrl = getenv('CHATBOT_API_URL') ?: 'http://localhost:3000/api/history';
$url = $nodeUrl . '?user_id=' . urlencode($user_id);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$errno = curl_errno($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno || !$response || $httpCode >= 400) {
    echo json_encode(['messages' => []]);
    exit;
}

// Forward response (should be JSON with { messages: [...] })
echo $response;
