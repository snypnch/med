<?php
$pageTitle = "System Settings";
require_once 'includes/auth.php';

// Check if user has admin privileges
if (!checkPermission('admin')) {
    $_SESSION['error_message'] = "You do not have permission to access system settings.";
    header("Location: dashboard.php");
    exit;
}

include_once 'includes/header.php';

$db = Database::getInstance();
$message = '';
$messageType = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process backup request
if ($action === 'backup') {
    // Create database backup
    $backupFile = 'medcompare_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupPath = __DIR__ . '/../backups/' . $backupFile;
    
    // Create backups directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../backups')) {
        mkdir(__DIR__ . '/../backups', 0755, true);
    }
    
    // Execute the backup command
    $command = sprintf(
        'mysqldump -h %s -u %s %s %s > %s',
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_USER),
        !empty(DB_PASS) ? '-p' . escapeshellarg(DB_PASS) : '',
        escapeshellarg(DB_NAME),
        escapeshellarg($backupPath)
    );
    
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        logAdminActivity($_SESSION['admin_id'], 'database_backup', "Created database backup: $backupFile");
        $message = "Database backup created successfully.";
        $messageType = "success";
    } else {
        $message = "Error creating database backup.";
        $messageType = "danger";
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add user
    if (isset($_POST['add_user'])) {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'admin'; // Changed default from 'manager' to 'admin'
        
        // Validate input
        $errors = [];
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }
        
        // Check if username or email already exists
        $existingResult = $db->query("SELECT * FROM users WHERE username = '{$db->escape($username)}' OR email = '{$db->escape($email)}'");
        if ($existingResult && $existingResult->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        
        if (empty($errors)) {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $data = [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role
            ];
            
            if ($db->insert('users', $data)) {
                logAdminActivity($_SESSION['admin_id'], 'add_user', "Added user: $username");
                $message = "User added successfully.";
                $messageType = "success";
            } else {
                $message = "Error adding user.";
                $messageType = "danger";
            }
        } else {
            $message = implode("<br>", $errors);
            $messageType = "danger";
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        
        // Don't allow deletion of current user
        if ($userId === (int)$_SESSION['admin_id']) {
            $message = "You cannot delete your own account.";
            $messageType = "danger";
        } else {
            // Get username for logging
            $userResult = $db->query("SELECT username FROM users WHERE id = $userId");
            if ($userResult && $userResult->num_rows > 0) {
                $username = $userResult->fetch_assoc()['username'];
                
                if ($db->delete('users', "id = $userId")) {
                    logAdminActivity($_SESSION['admin_id'], 'delete_user', "Deleted user: $username");
                    $message = "User deleted successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error deleting user.";
                    $messageType = "danger";
                }
            } else {
                $message = "User not found.";
                $messageType = "danger";
            }
        }
    }
}

// Get users
$usersResult = $db->query("SELECT * FROM users ORDER BY username");
$users = [];
if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get backup files
$backups = [];
$backupDir = __DIR__ . '/../backups';
if (file_exists($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backupDir . '/' . $file),
                'date' => date('Y-m-d H:i:s', filemtime($backupDir . '/' . $file))
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">System Settings</h1>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-12">
            <!-- Settings Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">User Management</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false">Database Backup</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- User Management Tab -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                            <h5 class="mb-4">Manage System Users</h5>
                            
                            <!-- Add User Form -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Add New User</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <input type="hidden" name="add_user" value="1">
                                            <button type="submit" class="btn btn-primary">Add User</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- User List -->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No users found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <?php if ((int)$user['id'] !== (int)$_SESSION['admin_id']): ?>
                                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <input type="hidden" name="delete_user" value="1">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted">Current User</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Database Backup Tab -->
                        <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                            <h5 class="mb-4">Database Backup Management</h5>
                            
                            <div class="alert alert-info">
                                <p>Regular database backups are essential to prevent data loss. It's recommended to backup your database at least once a week.</p>
                            </div>
                            
                            <a href="?action=backup" class="btn btn-primary mb-4">
                                <i class="fas fa-database me-2"></i> Create New Backup
                            </a>
                            
                            <!-- Backup List -->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Backup File</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($backups)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No backups found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($backups as $backup): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                                    <td><?php echo round($backup['size'] / 1024, 2); ?> KB</td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($backup['date'])); ?></td>
                                                    <td>
                                                        <a href="<?php echo APP_URL; ?>/backups/<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-success" download>
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
