<?php
require_once 'database.php';

function getUserPreferences($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prefs) {
            // Create default preferences if they don't exist
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            return [
                'user_id' => $userId,
                'preferred_background' => 'default.jpg',
                'sound_enabled' => true
            ];
        }
        
        return $prefs;
    } catch(PDOException $e) {
        error_log("Error getting user preferences: " . $e->getMessage());
        return [
            'user_id' => $userId,
            'preferred_background' => 'default.jpg',
            'sound_enabled' => true
        ];
    }
}

function updateUserPreferences($userId, $background, $soundEnabled = null) {
    try {
        $pdo = getDBConnection();
        
        if ($soundEnabled !== null) {
            $stmt = $pdo->prepare("UPDATE user_preferences SET preferred_background = ?, sound_enabled = ? WHERE user_id = ?");
            $stmt->execute([$background, $soundEnabled, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE user_preferences SET preferred_background = ? WHERE user_id = ?");
            $stmt->execute([$background, $userId]);
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating user preferences: " . $e->getMessage());
        return false;
    }
}

function getAvailableBackgrounds() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT filename, display_name FROM background_images WHERE is_enabled = 1 ORDER BY display_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting backgrounds: " . $e->getMessage());
        return [
            ['filename' => 'default.jpg', 'display_name' => 'Default']
        ];
    }
}

function saveGameStats($userId, $moves, $timeSeconds, $backgroundImage) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO game_stats (user_id, moves, time_seconds, background_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $moves, $timeSeconds, $backgroundImage]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log("Error saving game stats: " . $e->getMessage());
        return false;
    }
}

function getLeaderboard($limit = 10) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT u.username, gs.moves, gs.time_seconds, gs.completed_at 
            FROM game_stats gs 
            JOIN users u ON gs.user_id = u.id 
            ORDER BY gs.time_seconds ASC, gs.moves ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting leaderboard: " . $e->getMessage());
        return [];
    }
}

function getUserGameHistory($userId, $limit = 20) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT moves, time_seconds, background_image, completed_at 
            FROM game_stats 
            WHERE user_id = ? 
            ORDER BY completed_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting user history: " . $e->getMessage());
        return [];
    }
}

function getUserStats($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_games,
                MIN(time_seconds) as best_time,
                MIN(moves) as best_moves,
                AVG(time_seconds) as avg_time,
                AVG(moves) as avg_moves
            FROM game_stats 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting user stats: " . $e->getMessage());
        return [
            'total_games' => 0,
            'best_time' => null,
            'best_moves' => null,
            'avg_time' => null,
            'avg_moves' => null
        ];
    }
}
?>
