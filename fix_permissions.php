<?php
// Fix database permissions and test functionality

echo "<h1>🔧 Fixing Database Permissions</h1>";

// Fix directory permissions
$dbDir = 'database';
$dbFile = 'database/sliding_puzzle.db';

echo "<h2>📁 Fixing Directory Permissions</h2>";

if (!is_dir($dbDir)) {
    if (mkdir($dbDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Created database directory</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create database directory</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Database directory already exists</p>";
}

// Set directory permissions
if (chmod($dbDir, 0777)) {
    echo "<p style='color: green;'>✓ Set directory permissions to 777</p>";
} else {
    echo "<p style='color: orange;'>⚠ Could not change directory permissions</p>";
}

// Set file permissions if database exists
if (file_exists($dbFile)) {
    if (chmod($dbFile, 0666)) {
        echo "<p style='color: green;'>✓ Set database file permissions to 666</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not change database file permissions</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Database file will be created with proper permissions</p>";
}

echo "<h2>🔄 Re-initializing Database</h2>";

// Include and run setup
require_once 'includes/database.php';

if (initializeDatabase()) {
    echo "<p style='color: green;'>✓ Database initialized successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Database initialization failed</p>";
}

echo "<h2>🧪 Testing Database Operations</h2>";

try {
    $pdo = getDBConnection();
    
    // Test user creation
    $testUsername = 'testuser_' . time();
    $testPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    if ($stmt->execute([$testUsername, $testPassword, 'test@example.com'])) {
        $userId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✓ Test user created successfully (ID: $userId)</p>";
        
        // Create user preferences
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        echo "<p style='color: green;'>✓ User preferences created</p>";
        
        // Test game stats creation
        $stmt = $pdo->prepare("INSERT INTO game_stats (user_id, moves, time_seconds, background_image) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$userId, 25, 120, 'default.jpg'])) {
            echo "<p style='color: green;'>✓ Test game stats created</p>";
        }
        
        // Clean up test data
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "<p style='color: blue;'>ℹ Test data cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to create test user</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database test failed: " . $e->getMessage() . "</p>";
}

echo "<h2>📊 Current Database Status</h2>";

try {
    $pdo = getDBConnection();
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "<p><strong>Total Users:</strong> $userCount</p>";
    
    // Count games
    $stmt = $pdo->query("SELECT COUNT(*) FROM game_stats");
    $gameCount = $stmt->fetchColumn();
    echo "<p><strong>Total Games:</strong> $gameCount</p>";
    
    // Count background images
    $stmt = $pdo->query("SELECT COUNT(*) FROM background_images");
    $imageCount = $stmt->fetchColumn();
    echo "<p><strong>Background Images:</strong> $imageCount</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error getting database status: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Next Steps</h2>";
echo "<ul>";
echo "<li><a href='login.php'>Test user registration</a></li>";
echo "<li><a href='create_demo_data.php'>Create demo data</a></li>";
echo "<li><a href='game.php'>Test game functionality</a></li>";
echo "<li><a href='debug_database.php'>Run full database debug</a></li>";
echo "</ul>";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Database Permissions</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        h1, h2 { color: #333; }
        p { margin: 8px 0; }
        ul { margin: 10px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>
