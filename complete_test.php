<?php
require_once 'includes/auth.php';
require_once 'includes/game_functions.php';

session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Leaderboard Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
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
        .test-result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            min-height: 50px;
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
    <h1>🧪 Complete Leaderboard Test</h1>

    <div class="section">
        <h2>🔐 Authentication Status</h2>
        <?php
        if ($auth->isLoggedIn()) {
            $user = $auth->getCurrentUser();
            echo '<p class="success">✓ User is logged in as: ' . htmlspecialchars($user['username']) . '</p>';
        } else {
            echo '<p class="error">✗ User is not logged in</p>';
            echo '<p><a href="login.php">Please login first</a></p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>📊 Current Database Data</h2>
        <?php
        try {
            $leaderboard = getLeaderboard(10);
            echo "<p><strong>Total leaderboard entries:</strong> " . count($leaderboard) . "</p>";
            
            if (count($leaderboard) > 0) {
                echo "<table>";
                echo "<tr><th>Rank</th><th>Username</th><th>Time</th><th>Moves</th><th>Date</th></tr>";
                foreach ($leaderboard as $index => $entry) {
                    $minutes = floor($entry['time_seconds'] / 60);
                    $seconds = $entry['time_seconds'] % 60;
                    $timeStr = sprintf('%d:%02d', $minutes, $seconds);
                    
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td>{$entry['username']}</td>";
                    echo "<td>$timeStr</td>";
                    echo "<td>{$entry['moves']}</td>";
                    echo "<td>" . date('M j, Y H:i', strtotime($entry['completed_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo '<p class="warning">⚠ No leaderboard data found</p>';
                echo '<p><a href="create_demo_data.php">Create demo data</a></p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error getting leaderboard: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>🌐 API Tests</h2>
        
        <button onclick="testLeaderboardAPI()">Test Leaderboard API</button>
        <button onclick="testSaveGameAPI()">Test Save Game API</button>
        <button onclick="clearResults()">Clear Results</button>
        
        <div id="api-results" class="test-result"></div>
    </div>

    <div class="section">
        <h2>🎮 Game Links</h2>
        <p><a href="game.php" target="_blank">Open Game (New Tab)</a> - Complete a puzzle and check if it appears in leaderboard</p>
        <p><a href="test_save_game.php">Manual Save Test</a> - Manually save a game stat</p>
        <p><a href="create_demo_data.php">Create Demo Data</a> - Add sample leaderboard entries</p>
        <p><a href="debug_database.php">Database Debug</a> - Full database diagnostics</p>
    </div>

    <script>
        function clearResults() {
            document.getElementById('api-results').innerHTML = '';
        }

        async function testLeaderboardAPI() {
            const results = document.getElementById('api-results');
            results.innerHTML = '<h4>Testing Leaderboard API...</h4>';
            
            try {
                const response = await fetch('api/get_leaderboard.php');
                const data = await response.json();
                
                let html = `
                    <h4>✅ Leaderboard API Test Results:</h4>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Response Type:</strong> ${Array.isArray(data) ? 'Array' : 'Object'}</p>
                `;
                
                if (data.error) {
                    html += `<p class="error"><strong>Error:</strong> ${data.error}</p>`;
                } else if (Array.isArray(data)) {
                    html += `<p class="success"><strong>Entries Found:</strong> ${data.length}</p>`;
                    if (data.length > 0) {
                        html += '<table><tr><th>Username</th><th>Time (s)</th><th>Moves</th></tr>';
                        data.slice(0, 5).forEach(entry => {
                            html += `<tr><td>${entry.username}</td><td>${entry.time_seconds}</td><td>${entry.moves}</td></tr>`;
                        });
                        html += '</table>';
                    }
                } else {
                    html += `<p class="warning">Unexpected response format</p>`;
                }
                
                html += `<details><summary>Raw Response</summary><pre>${JSON.stringify(data, null, 2)}</pre></details>`;
                results.innerHTML = html;
                
            } catch (error) {
                results.innerHTML = `<h4>❌ Leaderboard API Test Failed:</h4><p class="error">${error.message}</p>`;
            }
        }

        async function testSaveGameAPI() {
            const results = document.getElementById('api-results');
            results.innerHTML = '<h4>Testing Save Game API...</h4>';
            
            try {
                const testData = {
                    moves: Math.floor(Math.random() * 50) + 15,
                    time_seconds: Math.floor(Math.random() * 300) + 60,
                    background_image: 'default.jpg'
                };
                
                const response = await fetch('api/save_game.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                let html = `
                    <h4>✅ Save Game API Test Results:</h4>
                    <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Test Data Sent:</strong> ${testData.moves} moves, ${testData.time_seconds} seconds</p>
                `;
                
                if (data.error) {
                    html += `<p class="error"><strong>Error:</strong> ${data.error}</p>`;
                } else if (data.success) {
                    html += `<p class="success"><strong>Success:</strong> Game saved with ID ${data.game_id}</p>`;
                    html += `<p><button onclick="testLeaderboardAPI()">Refresh Leaderboard</button></p>`;
                }
                
                html += `<details><summary>Raw Response</summary><pre>${JSON.stringify(data, null, 2)}</pre></details>`;
                results.innerHTML = html;
                
            } catch (error) {
                results.innerHTML = `<h4>❌ Save Game API Test Failed:</h4><p class="error">${error.message}</p>`;
            }
        }

        // Auto-test on page load
        window.onload = function() {
            testLeaderboardAPI();
        };
    </script>
</body>
</html>
