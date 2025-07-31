<?php
require_once 'includes/auth.php';
require_once 'includes/game_functions.php';

session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Leaderboard Test</title>
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
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover { background: #0056b3; }
        .result { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>🎯 Final Leaderboard Test</h1>

    <div class="section">
        <h2>📊 Current Leaderboard (Direct Database)</h2>
        <?php
        try {
            $leaderboard = getLeaderboard(10);
            echo "<p><strong>Total entries:</strong> " . count($leaderboard) . "</p>";
            
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
                    echo "<td>" . date('M j H:i', strtotime($entry['completed_at'])) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo '<p class="error">No leaderboard data found</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>🌐 API Test</h2>
        <button onclick="testAPI()">Test Leaderboard API</button>
        <button onclick="testSaveAPI()">Test Save Game API</button>
        <div id="api-result" class="result"></div>
    </div>

    <div class="section">
        <h2>🎮 Game Test</h2>
        <p><strong>Instructions:</strong></p>
        <ol>
            <li>Click "Open Game" below</li>
            <li>Complete a puzzle (you can cheat by manually moving tiles to almost-solved position)</li>
            <li>Check if your score appears in the leaderboard</li>
            <li>Come back here and click "Test Leaderboard API" to verify</li>
        </ol>
        <p><a href="game.php" target="_blank" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Open Game (New Tab)</a></p>
    </div>

    <script>
        async function testAPI() {
            const result = document.getElementById('api-result');
            result.innerHTML = '<h4>Testing Leaderboard API...</h4>';
            
            try {
                const response = await fetch('api/get_leaderboard.php');
                const data = await response.json();
                
                let html = `<h4>✅ Leaderboard API Results:</h4>`;
                html += `<p><strong>Status:</strong> ${response.status} ${response.statusText}</p>`;
                
                if (Array.isArray(data)) {
                    html += `<p class="success"><strong>Entries Found:</strong> ${data.length}</p>`;
                    if (data.length > 0) {
                        html += '<table><tr><th>Username</th><th>Time (s)</th><th>Moves</th></tr>';
                        data.slice(0, 5).forEach(entry => {
                            html += `<tr><td>${entry.username}</td><td>${entry.time_seconds}</td><td>${entry.moves}</td></tr>`;
                        });
                        html += '</table>';
                    }
                } else if (data.error) {
                    html += `<p class="error">Error: ${data.error}</p>`;
                } else {
                    html += `<p class="error">Unexpected response format</p>`;
                }
                
                result.innerHTML = html;
                
            } catch (error) {
                result.innerHTML = `<h4>❌ API Test Failed:</h4><p class="error">${error.message}</p>`;
            }
        }

        async function testSaveAPI() {
            const result = document.getElementById('api-result');
            result.innerHTML = '<h4>Testing Save Game API...</h4>';
            
            try {
                const testData = {
                    moves: Math.floor(Math.random() * 50) + 15,
                    time_seconds: Math.floor(Math.random() * 300) + 60,
                    background_image: 'default.jpg'
                };
                
                const response = await fetch('api/save_game.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(testData)
                });
                
                const data = await response.json();
                
                let html = `<h4>Save Game API Results:</h4>`;
                html += `<p><strong>Status:</strong> ${response.status} ${response.statusText}</p>`;
                html += `<p><strong>Test Data:</strong> ${testData.moves} moves, ${testData.time_seconds} seconds</p>`;
                
                if (data.success) {
                    html += `<p class="success">✅ Game saved successfully! ID: ${data.game_id}</p>`;
                    html += `<p><button onclick="testAPI()">Refresh Leaderboard</button></p>`;
                } else if (data.error) {
                    html += `<p class="error">❌ Error: ${data.error}</p>`;
                }
                
                result.innerHTML = html;
                
            } catch (error) {
                result.innerHTML = `<h4>❌ Save API Test Failed:</h4><p class="error">${error.message}</p>`;
            }
        }

        // Auto-test on page load
        window.onload = function() {
            testAPI();
        };
    </script>
</body>
</html>
