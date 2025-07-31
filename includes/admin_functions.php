<?php
require_once 'database.php';

function handleImageUpload() {
    if (!isset($_FILES['image_file']) || !isset($_POST['display_name'])) {
        return ['success' => false, 'message' => 'Missing required fields.'];
    }
    
    $displayName = trim($_POST['display_name']);
    $file = $_FILES['image_file'];
    
    // Validate display name
    if (empty($displayName) || strlen($displayName) > 100) {
        return ['success' => false, 'message' => 'Display name must be between 1 and 100 characters.'];
    }
    
    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed.'];
    }
    
    // Check file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size must be less than 2MB.'];
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG and PNG files are allowed.'];
    }
    
    // Generate unique filename
    $extension = $mimeType === 'image/jpeg' ? '.jpg' : '.png';
    $filename = uniqid('bg_') . $extension;
    $uploadPath = 'images/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }
    
    // Resize image to 400x400 if needed
    if (!resizeImage($uploadPath, 400, 400)) {
        unlink($uploadPath);
        return ['success' => false, 'message' => 'Failed to process image.'];
    }
    
    // Save to database
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO background_images (filename, display_name) VALUES (?, ?)");
        $stmt->execute([$filename, $displayName]);
        
        return ['success' => true, 'message' => 'Image uploaded successfully.'];
    } catch(PDOException $e) {
        unlink($uploadPath);
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred.'];
    }
}

function resizeImage($imagePath, $width, $height) {
    try {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return false;
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) return false;
        
        // Create destination image
        $destImage = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefill($destImage, 0, 0, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        
        // Save resized image
        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($destImage, $imagePath, 90);
                break;
            case 'image/png':
                $success = imagepng($destImage, $imagePath, 9);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $success;
    } catch (Exception $e) {
        error_log("Image resize error: " . $e->getMessage());
        return false;
    }
}

function getAllBackgroundImages() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM background_images ORDER BY uploaded_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting background images: " . $e->getMessage());
        return [];
    }
}

function toggleImageStatus($imageId, $enabled) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE background_images SET is_enabled = ? WHERE id = ?");
        return $stmt->execute([$enabled, $imageId]);
    } catch(PDOException $e) {
        error_log("Error toggling image status: " . $e->getMessage());
        return false;
    }
}

function deleteBackgroundImage($imageId) {
    try {
        $pdo = getDBConnection();
        
        // Get image info
        $stmt = $pdo->prepare("SELECT filename FROM background_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$image) {
            return ['success' => false, 'message' => 'Image not found.'];
        }
        
        // Don't allow deletion of default image
        if ($image['filename'] === 'default.jpg') {
            return ['success' => false, 'message' => 'Cannot delete default image.'];
        }
        
        // Check if image is being used by any users
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_preferences WHERE preferred_background = ?");
        $stmt->execute([$image['filename']]);
        $usage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usage['count'] > 0) {
            // Update users to use default background
            $stmt = $pdo->prepare("UPDATE user_preferences SET preferred_background = 'default.jpg' WHERE preferred_background = ?");
            $stmt->execute([$image['filename']]);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM background_images WHERE id = ?");
        $stmt->execute([$imageId]);
        
        // Delete file
        $filePath = 'images/' . $image['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        return ['success' => true, 'message' => 'Image deleted successfully.'];
    } catch(PDOException $e) {
        error_log("Error deleting image: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred.'];
    }
}

function getGameStatistics() {
    try {
        $pdo = getDBConnection();

        // Get total users
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
        $stmt->execute();
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

        // Get total games
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_games FROM game_stats");
        $stmt->execute();
        $totalGames = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];

        // Get average time and moves
        $stmt = $pdo->prepare("SELECT AVG(time_seconds) as avg_time, AVG(moves) as avg_moves FROM game_stats");
        $stmt->execute();
        $averages = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_users' => $totalUsers,
            'total_games' => $totalGames,
            'avg_time' => $averages['avg_time'] ? round($averages['avg_time']) : 0,
            'avg_moves' => $averages['avg_moves'] ? round($averages['avg_moves']) : 0
        ];
    } catch(PDOException $e) {
        error_log("Error getting game statistics: " . $e->getMessage());
        return [
            'total_users' => 0,
            'total_games' => 0,
            'avg_time' => 0,
            'avg_moves' => 0
        ];
    }
}

// User Management Functions
function getAllUsers() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT u.*,
                   COUNT(gs.id) as total_games,
                   MIN(gs.time_seconds) as best_time,
                   MIN(gs.moves) as best_moves
            FROM users u
            LEFT JOIN game_stats gs ON u.id = gs.user_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

function updateUser($userId, $username, $isAdmin, $isActive) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET username = ?, is_admin = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([$username, $isAdmin ? 1 : 0, $isActive ? 1 : 0, $userId]);
    } catch(PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

function resetUserPassword($userId, $newPassword) {
    try {
        $pdo = getDBConnection();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    } catch(PDOException $e) {
        error_log("Error resetting password: " . $e->getMessage());
        return false;
    }
}

function deleteUser($userId) {
    try {
        $pdo = getDBConnection();

        // Don't allow deletion of admin users
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['is_admin']) {
            return ['success' => false, 'message' => 'Cannot delete admin users.'];
        }

        // Delete user (cascading will handle related records)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        return ['success' => true, 'message' => 'User deleted successfully.'];
    } catch(PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred.'];
    }
}
?>
