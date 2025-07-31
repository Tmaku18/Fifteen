<?php
require_once 'includes/auth.php';
require_once 'includes/admin_functions.php';

// Require admin access
$auth->requireAdmin();
$user = $auth->getCurrentUser();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'upload_image':
                $result = handleImageUpload();
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'toggle_image':
                $imageId = intval($_POST['image_id']);
                $enabled = isset($_POST['enabled']) ? 1 : 0;
                if (toggleImageStatus($imageId, $enabled)) {
                    $message = 'Image status updated successfully.';
                } else {
                    $error = 'Failed to update image status.';
                }
                break;
                
            case 'delete_image':
                $imageId = intval($_POST['image_id']);
                $result = deleteBackgroundImage($imageId);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;

            case 'create_user':
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $email = trim($_POST['email']);
                $isAdmin = isset($_POST['is_admin']);

                if (strlen($username) < 3) {
                    $error = 'Username must be at least 3 characters long.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } else {
                    // Check if username already exists
                    $pdo = getDBConnection();
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'Username already exists.';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, ?)");
                        if ($stmt->execute([$username, $hashedPassword, $email, $isAdmin ? 1 : 0])) {
                            $userId = $pdo->lastInsertId();
                            // Create user preferences
                            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)");
                            $stmt->execute([$userId]);
                            $message = 'User created successfully.';
                        } else {
                            $error = 'Failed to create user.';
                        }
                    }
                }
                break;

            case 'update_user':
                $userId = intval($_POST['user_id']);
                $username = trim($_POST['username']);
                $isAdmin = isset($_POST['is_admin']);
                $isActive = isset($_POST['is_active']);

                if (updateUser($userId, $username, $isAdmin, $isActive)) {
                    $message = 'User updated successfully.';
                } else {
                    $error = 'Failed to update user.';
                }
                break;

            case 'reset_password':
                $userId = intval($_POST['user_id']);
                $newPassword = $_POST['new_password'];

                if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                } else {
                    if (resetUserPassword($userId, $newPassword)) {
                        $message = 'Password reset successfully.';
                    } else {
                        $error = 'Failed to reset password.';
                    }
                }
                break;

            case 'delete_user':
                $userId = intval($_POST['user_id']);
                $result = deleteUser($userId);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get all background images
$backgroundImages = getAllBackgroundImages();
$gameStats = getGameStatistics();
$allUsers = getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sliding Puzzle</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <h1><i class="fas fa-cog"></i> Admin Panel</h1>
            </div>
            <div class="header-right">
                <span class="welcome">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="admin_users.php" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
                <a href="game.php" class="btn btn-primary"><i class="fas fa-gamepad"></i> Back to Game</a>
                <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="admin-content">
            <!-- Quick Navigation -->
            <div class="nav-panel">
                <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
                <div class="nav-grid">
                    <a href="admin_users.php" class="nav-card">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="nav-info">
                            <h3>User Management</h3>
                            <p>View, edit, and manage user accounts</p>
                        </div>
                    </a>
                    <a href="#images-panel" class="nav-card" onclick="document.getElementById('images-panel').scrollIntoView()">
                        <div class="nav-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="nav-info">
                            <h3>Background Images</h3>
                            <p>Upload and manage puzzle backgrounds</p>
                        </div>
                    </a>
                    <a href="game.php" class="nav-card">
                        <div class="nav-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div class="nav-info">
                            <h3>Play Game</h3>
                            <p>Test the sliding puzzle game</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Game Statistics -->
            <div class="stats-panel">
                <h2><i class="fas fa-chart-bar"></i> Game Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $gameStats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $gameStats['total_games']; ?></h3>
                            <p>Games Played</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $gameStats['avg_time']; ?>s</h3>
                            <p>Average Time</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $gameStats['avg_moves']; ?></h3>
                            <p>Average Moves</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="upload-panel">
                <h2><i class="fas fa-upload"></i> Upload Background Image</h2>
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="action" value="upload_image">
                    <div class="form-group">
                        <label for="display_name">Display Name:</label>
                        <input type="text" id="display_name" name="display_name" required>
                    </div>
                    <div class="form-group">
                        <label for="image_file">Image File (JPG, PNG, max 2MB):</label>
                        <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Image
                    </button>
                </form>
            </div>

            <!-- Background Images Management -->
            <div class="images-panel" id="images-panel">
                <h2><i class="fas fa-images"></i> Manage Background Images</h2>
                <div class="images-grid">
                    <?php foreach ($backgroundImages as $image): ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <img src="images/<?php echo htmlspecialchars($image['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['display_name']); ?>">
                                <div class="image-overlay">
                                    <span class="image-status <?php echo $image['is_enabled'] ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $image['is_enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="image-info">
                                <h3><?php echo htmlspecialchars($image['display_name']); ?></h3>
                                <p><?php echo htmlspecialchars($image['filename']); ?></p>
                                <p class="upload-date">
                                    Uploaded: <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                </p>
                            </div>
                            <div class="image-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_image">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <?php if (!$image['is_enabled']): ?>
                                        <input type="hidden" name="enabled" value="1">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-eye"></i> Enable
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-eye-slash"></i> Disable
                                        </button>
                                    <?php endif; ?>
                                </form>
                                
                                <?php if ($image['filename'] !== 'default.jpg'): ?>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="action" value="delete_image">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- User Management -->
            <div class="user-management-panel">
                <h2><i class="fas fa-users"></i> User Management</h2>

                <!-- Create New User -->
                <div class="create-user-section">
                    <h3><i class="fas fa-user-plus"></i> Create New User</h3>
                    <form method="POST" class="user-form">
                        <input type="hidden" name="action" value="create_user">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_username">Username:</label>
                                <input type="text" id="new_username" name="username" required minlength="3">
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password:</label>
                                <input type="password" id="new_password" name="password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="new_email">Email (optional):</label>
                                <input type="email" id="new_email" name="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_admin"> Admin User
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create User
                        </button>
                    </form>
                </div>

                <!-- Users List -->
                <div class="users-list">
                    <h3><i class="fas fa-list"></i> All Users</h3>
                    <div class="users-grid">
                        <?php foreach ($allUsers as $userItem): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <h4><?php echo htmlspecialchars($userItem['username']); ?></h4>
                                    <div class="user-details">
                                        <span class="user-role <?php echo $userItem['is_admin'] ? 'admin' : 'user'; ?>">
                                            <i class="fas fa-<?php echo $userItem['is_admin'] ? 'crown' : 'user'; ?>"></i>
                                            <?php echo $userItem['is_admin'] ? 'Admin' : 'User'; ?>
                                        </span>
                                        <span class="user-status <?php echo $userItem['is_active'] ? 'active' : 'inactive'; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo $userItem['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <div class="user-stats">
                                        <span><i class="fas fa-gamepad"></i> <?php echo $userItem['total_games']; ?> games</span>
                                        <?php if ($userItem['best_time']): ?>
                                            <span><i class="fas fa-trophy"></i> Best: <?php echo $userItem['best_time']; ?>s</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-meta">
                                        <small>Joined: <?php echo date('M j, Y', strtotime($userItem['created_at'])); ?></small>
                                        <?php if ($userItem['email']): ?>
                                            <small>Email: <?php echo htmlspecialchars($userItem['email']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="user-actions">
                                    <!-- Edit User Form -->
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="update_user">
                                        <input type="hidden" name="user_id" value="<?php echo $userItem['id']; ?>">
                                        <input type="text" name="username" value="<?php echo htmlspecialchars($userItem['username']); ?>"
                                               class="inline-input" required>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_admin" <?php echo $userItem['is_admin'] ? 'checked' : ''; ?>> Admin
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_active" <?php echo $userItem['is_active'] ? 'checked' : ''; ?>> Active
                                        </label>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i> Update
                                        </button>
                                    </form>

                                    <!-- Reset Password Form -->
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?php echo $userItem['id']; ?>">
                                        <input type="password" name="new_password" placeholder="New password"
                                               class="inline-input" required minlength="6">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-key"></i> Reset Password
                                        </button>
                                    </form>

                                    <!-- Delete User -->
                                    <?php if (!$userItem['is_admin'] || $userItem['id'] != $user['id']): ?>
                                        <form method="POST" class="inline-form"
                                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $userItem['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- W3C Validator Links -->
    <div class="w3c-links">
        <a href="https://validator.w3.org/check/referer">
            <img src="https://www.w3.org/Icons/valid-xhtml11" alt="Valid XHTML 1.1" />
        </a>
        <a href="https://jigsaw.w3.org/css-validator/check/referer">
            <img src="https://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS" />
        </a>
    </div>
</body>
</html>
