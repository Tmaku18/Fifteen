<?php
require_once 'includes/auth.php';
require_once 'includes/game_functions.php';

session_start();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    echo "<p>Please <a href='login.php'>login</a> first to test game saving.</p>";
    exit();
}

$user = $auth->getCurrentUser();
$message = '';

// Handle form submission
if ($_POST) {
    $moves = intval($_POST['moves']);
    $timeSeconds = intval($_POST['time_seconds']);
    $backgroundImage = $_POST['background_image'];
    
    $gameId = saveGameStats($user['id'], $moves, $timeSeconds, $backgroundImage);
    
    if ($gameId) {
        $message = "<p style='color: green;'>✓ Game stats saved successfully! Game ID: $gameId</p>";
    } else {
        $message = "<p style='color: red;'>✗ Failed to save game stats</p>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Save Game</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 20px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .stats {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>🎮 Test Save Game Stats</h1>
    
    <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($user['username']); ?> (ID: <?php echo $user['id']; ?>)</p>
    
    <?php echo $message; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="moves">Number of Moves:</label>
            <input type="number" id="moves" name="moves" value="25" min="1" max="1000" required>
        </div>
        
        <div class="form-group">
            <label for="time_seconds">Time (seconds):</label>
            <input type="number" id="time_seconds" name="time_seconds" value="120" min="1" max="3600" required>
        </div>
        
        <div class="form-group">
            <label for="background_image">Background Image:</label>
            <select id="background_image" name="background_image" required>
                <option value="default.jpg">Default</option>
                <option value="nature.jpg">Nature</option>
                <option value="abstract.jpg">Abstract</option>
                <option value="space.jpg">Space</option>
            </select>
        </div>
        
        <button type="submit">Save Test Game</button>
    </form>
    
    <div class="stats">
        <h3>📊 Current Leaderboard</h3>
        <?php
        try {
            $leaderboard = getLeaderboard(5);
            if (count($leaderboard) > 0) {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f8f9fa;'><th style='padding: 8px; border: 1px solid #ddd;'>Username</th><th style='padding: 8px; border: 1px solid #ddd;'>Time</th><th style='padding: 8px; border: 1px solid #ddd;'>Moves</th><th style='padding: 8px; border: 1px solid #ddd;'>Date</th></tr>";
                foreach ($leaderboard as $entry) {
                    $minutes = floor($entry['time_seconds'] / 60);
                    $seconds = $entry['time_seconds'] % 60;
                    $timeStr = sprintf('%d:%02d', $minutes, $seconds);
                    
                    echo "<tr>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$entry['username']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd;'>$timeStr</td>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$entry['moves']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . date('M j, Y', strtotime($entry['completed_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No games in leaderboard yet.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error loading leaderboard: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <p><a href="game.php">← Back to Game</a></p>
    <p><a href="test_leaderboard.php">Test Leaderboard APIs</a></p>
</body>
</html>
