<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/game_functions.php';

// Start session for debugging
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Debug - Sliding Puzzle</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <h1>🔍 Database Debug Information</h1>

    <div class="debug-section">
        <h2>📁 File System Check</h2>
        <?php
        $dbFile = 'database/sliding_puzzle.db';
        $dbDir = dirname($dbFile);
        
        echo "<p><strong>Database Directory:</strong> " . realpath($dbDir) . "</p>";
        echo "<p><strong>Database File:</strong> " . realpath($dbFile) . "</p>";
        echo "<p><strong>Directory Exists:</strong> " . (is_dir($dbDir) ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . "</p>";
        echo "<p><strong>Directory Writable:</strong> " . (is_writable($dbDir) ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . "</p>";
        echo "<p><strong>Database File Exists:</strong> " . (file_exists($dbFile) ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . "</p>";
        if (file_exists($dbFile)) {
            echo "<p><strong>Database File Writable:</strong> " . (is_writable($dbFile) ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . "</p>";
            echo "<p><strong>Database File Size:</strong> " . filesize($dbFile) . " bytes</p>";
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>🔗 Database Connection Test</h2>
        <?php
        try {
            $pdo = getDBConnection();
            echo '<p class="success">✓ Database connection successful</p>';
            
            // Test a simple query
            $stmt = $pdo->query("SELECT sqlite_version()");
            $version = $stmt->fetchColumn();
            echo "<p><strong>SQLite Version:</strong> $version</p>";
            
        } catch (Exception $e) {
            echo '<p class="error">✗ Database connection failed: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>📊 Table Structure Check</h2>
        <?php
        try {
            $pdo = getDBConnection();
            $tables = ['users', 'game_stats', 'user_preferences', 'background_images'];
            
            foreach ($tables as $table) {
                echo "<h3>Table: $table</h3>";
                
                // Check if table exists
                $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
                $stmt->execute([$table]);
                if ($stmt->fetch()) {
                    echo '<p class="success">✓ Table exists</p>';
                    
                    // Get table info
                    $stmt = $pdo->query("PRAGMA table_info($table)");
                    $columns = $stmt->fetchAll();
                    
                    echo "<table>";
                    echo "<tr><th>Column</th><th>Type</th><th>Not Null</th><th>Default</th><th>Primary Key</th></tr>";
                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td>{$col['name']}</td>";
                        echo "<td>{$col['type']}</td>";
                        echo "<td>" . ($col['notnull'] ? 'Yes' : 'No') . "</td>";
                        echo "<td>{$col['dflt_value']}</td>";
                        echo "<td>" . ($col['pk'] ? 'Yes' : 'No') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Count records
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    echo "<p><strong>Record Count:</strong> $count</p>";
                    
                } else {
                    echo '<p class="error">✗ Table does not exist</p>';
                }
            }
        } catch (Exception $e) {
            echo '<p class="error">Error checking tables: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>👤 Session & Authentication Check</h2>
        <?php
        echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
        echo "<p><strong>Session Data:</strong></p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        if ($auth->isLoggedIn()) {
            echo '<p class="success">✓ User is logged in</p>';
            $user = $auth->getCurrentUser();
            echo "<p><strong>Current User:</strong> {$user['username']} (ID: {$user['id']})</p>";
            echo "<p><strong>Is Admin:</strong> " . ($user['is_admin'] ? 'Yes' : 'No') . "</p>";
        } else {
            echo '<p class="warning">⚠ User is not logged in</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>🏆 Leaderboard Test</h2>
        <?php
        try {
            $leaderboard = getLeaderboard(5);
            echo "<p><strong>Leaderboard Records Found:</strong> " . count($leaderboard) . "</p>";
            
            if (count($leaderboard) > 0) {
                echo "<table>";
                echo "<tr><th>Username</th><th>Time</th><th>Moves</th><th>Date</th></tr>";
                foreach ($leaderboard as $entry) {
                    echo "<tr>";
                    echo "<td>{$entry['username']}</td>";
                    echo "<td>{$entry['time_seconds']}s</td>";
                    echo "<td>{$entry['moves']}</td>";
                    echo "<td>{$entry['completed_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo '<p class="warning">⚠ No leaderboard data found</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error getting leaderboard: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>📈 User History Test</h2>
        <?php
        if ($auth->isLoggedIn()) {
            try {
                $user = $auth->getCurrentUser();
                $history = getUserGameHistory($user['id'], 5);
                echo "<p><strong>User History Records Found:</strong> " . count($history) . "</p>";
                
                if (count($history) > 0) {
                    echo "<table>";
                    echo "<tr><th>Time</th><th>Moves</th><th>Background</th><th>Date</th></tr>";
                    foreach ($history as $entry) {
                        echo "<tr>";
                        echo "<td>{$entry['time_seconds']}s</td>";
                        echo "<td>{$entry['moves']}</td>";
                        echo "<td>{$entry['background_image']}</td>";
                        echo "<td>{$entry['completed_at']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo '<p class="warning">⚠ No user history found</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error getting user history: ' . $e->getMessage() . '</p>';
            }
        } else {
            echo '<p class="info">ℹ User must be logged in to check history</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>🖼️ Background Images Test</h2>
        <?php
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM background_images ORDER BY id");
            $images = $stmt->fetchAll();
            
            echo "<p><strong>Background Images Found:</strong> " . count($images) . "</p>";
            
            if (count($images) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Filename</th><th>Display Name</th><th>Enabled</th><th>File Exists</th></tr>";
                foreach ($images as $image) {
                    $fileExists = file_exists("images/backgrounds/{$image['filename']}");
                    echo "<tr>";
                    echo "<td>{$image['id']}</td>";
                    echo "<td>{$image['filename']}</td>";
                    echo "<td>{$image['display_name']}</td>";
                    echo "<td>" . ($image['is_enabled'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . ($fileExists ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo '<p class="error">Error checking background images: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>🔧 Quick Actions</h2>
        <p><a href="setup.php" style="color: #007bff;">🔄 Re-run Setup</a></p>
        <p><a href="create_demo_data.php" style="color: #007bff;">🎮 Create Demo Data</a></p>
        <p><a href="login.php" style="color: #007bff;">🔐 Login Page</a></p>
        <p><a href="game.php" style="color: #007bff;">🎯 Game Page</a></p>
        <p><a href="admin.php" style="color: #007bff;">⚙️ Admin Panel</a></p>
    </div>
</body>
</html>
