<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/game_functions.php';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

// Validate required fields
$required_fields = ['moves', 'time_seconds', 'background_image'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// Validate data types and ranges
$moves = intval($input['moves']);
$timeSeconds = intval($input['time_seconds']);
$backgroundImage = trim($input['background_image']);

if ($moves < 0 || $moves > 10000) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid moves count']);
    exit();
}

if ($timeSeconds < 0 || $timeSeconds > 86400) { // Max 24 hours
    http_response_code(400);
    echo json_encode(['error' => 'Invalid time']);
    exit();
}

if (empty($backgroundImage) || strlen($backgroundImage) > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid background image']);
    exit();
}

// Save game stats
$user = $auth->getCurrentUser();
$gameId = saveGameStats($user['id'], $moves, $timeSeconds, $backgroundImage);

if ($gameId) {
    echo json_encode([
        'success' => true,
        'game_id' => $gameId,
        'message' => 'Game stats saved successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save game stats']);
}
?>
