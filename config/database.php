<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sliding_puzzle');

// Create connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database and tables
function initializeDatabase() {
    try {
        // First connect without database to create it
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_admin BOOLEAN DEFAULT FALSE
        )");
        
        // Create game_stats table
        $pdo->exec("CREATE TABLE IF NOT EXISTS game_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            moves INT NOT NULL,
            time_seconds INT NOT NULL,
            background_image VARCHAR(100),
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create user_preferences table
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            preferred_background VARCHAR(100) DEFAULT 'default.jpg',
            sound_enabled BOOLEAN DEFAULT TRUE,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create background_images table for admin management
        $pdo->exec("CREATE TABLE IF NOT EXISTS background_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(100) NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            is_enabled BOOLEAN DEFAULT TRUE,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default background images
        $pdo->exec("INSERT IGNORE INTO background_images (filename, display_name) VALUES 
            ('default.jpg', 'Default Landscape'),
            ('nature.jpg', 'Nature Scene'),
            ('abstract.jpg', 'Abstract Pattern')");
        
        // Create default admin user (password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT IGNORE INTO users (username, password, is_admin) VALUES 
            ('admin', '$adminPassword', TRUE)");
        
        return true;
    } catch(PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}
?>
