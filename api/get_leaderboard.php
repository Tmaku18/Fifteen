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

// Get limit from query parameter (default 10)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$limit = max(1, min($limit, 50)); // Ensure limit is between 1 and 50

try {
    $leaderboard = getLeaderboard($limit);
    echo json_encode($leaderboard);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch leaderboard']);
}
?>
