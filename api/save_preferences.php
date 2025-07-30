<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/game_functions.php';

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

// Validate background parameter
if (!isset($input['background']) || empty(trim($input['background']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Background parameter is required']);
    exit();
}

$background = trim($input['background']);

// Validate background exists in available backgrounds
$availableBackgrounds = getAvailableBackgrounds();
$validBackground = false;
foreach ($availableBackgrounds as $bg) {
    if ($bg['filename'] === $background) {
        $validBackground = true;
        break;
    }
}

if (!$validBackground) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid background selection']);
    exit();
}

// Update user preferences
$user = $auth->getCurrentUser();
$success = updateUserPreferences($user['id'], $background);

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Preferences updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update preferences']);
}
?>
