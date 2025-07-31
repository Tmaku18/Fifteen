<?php
// Database configuration - Using SQLite for simplicity
define('DB_FILE', 'database/sliding_puzzle.db');

// Create connection
function getDBConnection() {
    try {
        // Create database directory if it doesn't exist
        $dbDir = dirname(DB_FILE);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $pdo = new PDO("sqlite:" . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database and tables
function initializeDatabase() {
    try {
        $pdo = getDBConnection();

        // Enable foreign key constraints
        $pdo->exec("PRAGMA foreign_keys = ON");

        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Create game_stats table
        $pdo->exec("CREATE TABLE IF NOT EXISTS game_stats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            moves INTEGER NOT NULL,
            time_seconds INTEGER NOT NULL,
            background_image TEXT NOT NULL,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Create user_preferences table
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_preferences (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            preferred_background TEXT DEFAULT 'default.jpg',
            sound_enabled INTEGER DEFAULT 1,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Create background_images table
        $pdo->exec("CREATE TABLE IF NOT EXISTS background_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT UNIQUE NOT NULL,
            display_name TEXT NOT NULL,
            is_enabled INTEGER DEFAULT 1,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert default background images (only if they don't exist)
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO background_images (filename, display_name) VALUES (?, ?)");
        $stmt->execute(['default.jpg', 'Default Landscape']);
        $stmt->execute(['nature.jpg', 'Nature Scene']);
        $stmt->execute(['abstract.jpg', 'Abstract Pattern']);

        // Add is_active column if it doesn't exist (migration)
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_active INTEGER DEFAULT 1");
        } catch(PDOException $e) {
            // Column already exists, ignore error
        }

        // Create default admin user (password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, password, is_admin, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 1, 1]);

        return true;
    } catch(PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}
?>
