<?php
require_once 'includes/database.php';

// Create demo users and game data
function createDemoData() {
    try {
        $pdo = getDBConnection();
        
        // Demo users data
        $demoUsers = [
            ['username' => 'alice', 'password' => 'password123', 'email' => 'alice@example.com'],
            ['username' => 'bob', 'password' => 'password123', 'email' => 'bob@example.com'],
            ['username' => 'charlie', 'password' => 'password123', 'email' => 'charlie@example.com'],
            ['username' => 'diana', 'password' => 'password123', 'email' => 'diana@example.com'],
            ['username' => 'eve', 'password' => 'password123', 'email' => 'eve@example.com']
        ];
        
        $userIds = [];
        
        // Create demo users
        foreach ($demoUsers as $user) {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$user['username']]);
            if ($stmt->fetch()) {
                echo "<p>User {$user['username']} already exists, skipping...</p>";
                continue;
            }
            
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$user['username'], $hashedPassword, $user['email']]);
            $userId = $pdo->lastInsertId();
            $userIds[] = $userId;
            
            // Create user preferences
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            
            echo "<p style='color: green;'>✓ Created user: {$user['username']}</p>";
        }
        
        // Get all user IDs (including existing ones)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE is_admin = 0");
        $stmt->execute();
        $allUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Create demo game stats
        $backgrounds = ['default.jpg', 'nature.jpg', 'abstract.jpg', 'ocean.jpg', 'mountains.jpg'];
        $gameCount = 0;
        
        foreach ($allUserIds as $userId) {
            // Create 3-8 games per user
            $numGames = rand(3, 8);
            
            for ($i = 0; $i < $numGames; $i++) {
                $moves = rand(15, 80);
                $timeSeconds = rand(45, 300);
                $background = $backgrounds[array_rand($backgrounds)];
                
                $stmt = $pdo->prepare("INSERT INTO game_stats (user_id, moves, time_seconds, background_image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, $moves, $timeSeconds, $background]);
                $gameCount++;
            }
        }
        
        echo "<p style='color: green;'>✓ Created $gameCount demo game records</p>";
        echo "<p><strong>Demo data created successfully!</strong></p>";
        
        // Show some statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
        $stmt->execute();
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_games FROM game_stats");
        $stmt->execute();
        $totalGames = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];
        
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>Database Statistics:</h3>";
        echo "<p><strong>Total Users:</strong> $totalUsers</p>";
        echo "<p><strong>Total Games:</strong> $totalGames</p>";
        echo "</div>";
        
        return true;
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Error creating demo data: " . $e->getMessage() . "</p>";
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Demo Data - Sliding Puzzle</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Create Demo Data</h1>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="results">
                <?php createDemoData(); ?>
            </div>
        <?php else: ?>
            <p>This will create demo users and game statistics for testing the leaderboard and user management features.</p>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <h3>Demo Users to be created:</h3>
                <ul>
                    <li><strong>alice</strong> (password: password123)</li>
                    <li><strong>bob</strong> (password: password123)</li>
                    <li><strong>charlie</strong> (password: password123)</li>
                    <li><strong>diana</strong> (password: password123)</li>
                    <li><strong>eve</strong> (password: password123)</li>
                </ul>
                <p>Each user will have 3-8 random game records with varying completion times and move counts.</p>
            </div>
            
            <form method="POST" style="text-align: center;">
                <button type="submit" class="btn btn-success">
                    🚀 Create Demo Data
                </button>
            </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="game.php" class="btn">🎮 Go to Game</a>
            <a href="admin.php" class="btn">⚙️ Admin Panel</a>
            <a href="login.php" class="btn">🔐 Login Page</a>
        </div>
    </div>
</body>
</html>
