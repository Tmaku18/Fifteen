<?php
require_once 'includes/auth.php';
require_once 'includes/game_functions.php';

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Leaderboard - Sliding Puzzle</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
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
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        #leaderboard-result, #history-result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            min-height: 50px;
        }
    </style>
</head>
<body>
    <h1>🧪 Leaderboard Test</h1>

    <div class="section">
        <h2>🔐 Authentication Status</h2>
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
            echo '<p class="error">✗ User is not logged in</p>';
            echo '<p><a href="login.php">Go to Login Page</a></p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>📊 Direct Database Query</h2>
        <?php
        try {
            $leaderboard = getLeaderboard(10);
            echo "<p><strong>Leaderboard Records Found:</strong> " . count($leaderboard) . "</p>";
            
            if (count($leaderboard) > 0) {
                echo "<table>";
                echo "<tr><th>Username</th><th>Time (s)</th><th>Moves</th><th>Date</th></tr>";
                foreach ($leaderboard as $entry) {
                    echo "<tr>";
                    echo "<td>{$entry['username']}</td>";
                    echo "<td>{$entry['time_seconds']}</td>";
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

    <div class="section">
        <h2>🌐 API Test</h2>
        <p>Test the leaderboard API endpoint directly:</p>
        
        <button onclick="testLeaderboardAPI()">Test Leaderboard API</button>
        <button onclick="testUserHistoryAPI()">Test User History API</button>
        <button onclick="testSaveGameAPI()">Test Save Game API</button>
        
        <div id="leaderboard-result"></div>
        <div id="history-result"></div>
    </div>

    <div class="section">
        <h2>🎮 Quick Actions</h2>
        <p><a href="game.php">Go to Game</a></p>
        <p><a href="login.php">Login Page</a></p>
        <p><a href="create_demo_data.php">Create Demo Data</a></p>
        <p><a href="debug_database.php">Database Debug</a></p>
    </div>

    <script>
        async function testLeaderboardAPI() {
            const result = document.getElementById('leaderboard-result');
            result.innerHTML = 'Testing leaderboard API...';
            
            try {
                const response = await fetch('api/get_leaderboard.php');
                const data = await response.json();
                
                result.innerHTML = `
                    <h4>Leaderboard API Response:</h4>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                result.innerHTML = `<p class="error">Error: ${error.message}</p>`;
            }
        }

        async function testUserHistoryAPI() {
            const result = document.getElementById('history-result');
            result.innerHTML = 'Testing user history API...';
            
            try {
                const response = await fetch('api/get_user_history.php');
                const data = await response.json();
                
                result.innerHTML = `
                    <h4>User History API Response:</h4>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                result.innerHTML = `<p class="error">Error: ${error.message}</p>`;
            }
        }

        async function testSaveGameAPI() {
            const result = document.getElementById('history-result');
            result.innerHTML = 'Testing save game API...';
            
            try {
                const response = await fetch('api/save_game.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        moves: 25,
                        time_seconds: 120,
                        background_image: 'default.jpg'
                    })
                });
                const data = await response.json();
                
                result.innerHTML = `
                    <h4>Save Game API Response:</h4>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                result.innerHTML = `<p class="error">Error: ${error.message}</p>`;
            }
        }
    </script>
</body>
</html>
