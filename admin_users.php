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
            case 'update_user':
                $userId = intval($_POST['user_id']);
                $username = trim($_POST['username']);
                $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
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
                } else if (resetUserPassword($userId, $newPassword)) {
                    $message = 'Password reset successfully.';
                } else {
                    $error = 'Failed to reset password.';
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

// Get all users
$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <h1><i class="fas fa-users"></i> User Management</h1>
            </div>
            <div class="header-right">
                <span class="welcome">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="admin.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to Admin</a>
                <a href="game.php" class="btn btn-primary"><i class="fas fa-gamepad"></i> Game</a>
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
            <!-- Users Table -->
            <div class="users-panel">
                <h2><i class="fas fa-users"></i> All Users (<?php echo count($users); ?>)</h2>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Games Played</th>
                                <th>Best Time</th>
                                <th>Best Moves</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr class="user-row <?php echo $u['is_active'] ? '' : 'inactive'; ?>">
                                    <td><?php echo $u['id']; ?></td>
                                    <td class="username"><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo $u['is_admin'] ? 'admin' : 'user'; ?>">
                                            <?php echo $u['is_admin'] ? 'Admin' : 'User'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $u['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $u['total_games']; ?></td>
                                    <td>
                                        <?php if ($u['best_time']): ?>
                                            <?php 
                                            $minutes = floor($u['best_time'] / 60);
                                            $seconds = $u['best_time'] % 60;
                                            echo "{$minutes}:" . str_pad($seconds, 2, '0', STR_PAD_LEFT);
                                            ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $u['best_moves'] ?: '-'; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                    <td class="actions">
                                        <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $u['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="resetPassword(<?php echo $u['id']; ?>)">
                                            <i class="fas fa-key"></i> Reset
                                        </button>
                                        <?php if (!$u['is_admin']): ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $u['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit User</h3>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form method="POST" id="editUserForm">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-group">
                    <label for="editUsername">Username:</label>
                    <input type="text" id="editUsername" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_admin" id="editIsAdmin">
                        Administrator
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" id="editIsActive">
                        Active Account
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Reset Password</h3>
                <span class="close" onclick="closeModal('resetPasswordModal')">&times;</span>
            </div>
            <form method="POST" id="resetPasswordForm">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                
                <div class="form-group">
                    <label for="newPassword">New Password:</label>
                    <input type="password" id="newPassword" name="new_password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('resetPasswordModal')">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/admin_users.js"></script>

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
