<?php
$pageTitle = "User Profile";
require_once 'includes/auth.php';
include_once 'includes/header.php';

$db = Database::getInstance();
$userId = $_SESSION['admin_id'];
$message = '';
$messageType = '';

// Get user information
$result = $db->query("SELECT * FROM users WHERE id = $userId");
$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile
    if (isset($_POST['update_profile'])) {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        // Validate input
        $errors = [];
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        
        if (empty($errors)) {
            if ($db->update('users', ['email' => $email], "id = $userId")) {
                logAdminActivity($userId, 'update_profile', "Updated profile information");
                $message = "Profile updated successfully.";
                $messageType = "success";
                
                // Refresh user data
                $result = $db->query("SELECT * FROM users WHERE id = $userId");
                $user = $result->fetch_assoc();
            } else {
                $message = "Error updating profile.";
                $messageType = "danger";
            }
        } else {
            $message = implode("<br>", $errors);
            $messageType = "danger";
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate input
        $errors = [];
        if (empty($currentPassword)) {
            $errors[] = "Current password is required.";
        }
        if (empty($newPassword)) {
            $errors[] = "New password is required.";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        }
        
        if (empty($errors)) {
            // Verify current password
            if (password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($db->update('users', ['password' => $hashedPassword], "id = $userId")) {
                    logAdminActivity($userId, 'change_password', "Password changed");
                    $message = "Password changed successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error changing password.";
                    $messageType = "danger";
                }
            } else {
                $message = "Current password is incorrect.";
                $messageType = "danger";
            }
        } else {
            $message = implode("<br>", $errors);
            $messageType = "danger";
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">User Profile</h1>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-6">
            <!-- Profile Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" readonly>
                        </div>
                        <input type="hidden" name="update_profile" value="1">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <!-- Change Password -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Password must be at least 6 characters long.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <input type="hidden" name="change_password" value="1">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <?php
                    $activityResult = $db->query("
                        SELECT * FROM activity_logs 
                        WHERE user_id = $userId 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    
                    $activities = [];
                    if ($activityResult) {
                        while ($row = $activityResult->fetch_assoc()) {
                            $activities[] = $row;
                        }
                    }
                    ?>
                    
                    <?php if (empty($activities)): ?>
                        <p class="text-center">No recent activity found.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($activities as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <?php if (!empty($activity['details'])): ?>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($activity['details']); ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">IP: <?php echo htmlspecialchars($activity['ip_address']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>