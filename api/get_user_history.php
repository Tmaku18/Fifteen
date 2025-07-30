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

// Get limit from query parameter (default 20)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$limit = max(1, min($limit, 100)); // Ensure limit is between 1 and 100

try {
    $user = $auth->getCurrentUser();
    $history = getUserGameHistory($user['id'], $limit);
    echo json_encode($history);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch user history']);
}
?>
