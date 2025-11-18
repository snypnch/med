<?php
$pageTitle = "My Profile";
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isUserLoggedIn()) {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get user information
$result = $db->query("SELECT * FROM regular_users WHERE id = $userId");
$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile
    if (isset($_POST['update_profile'])) {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        
        // Validate input
        $errors = [];
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        
        // Check if email already exists (for another user)
        if (empty($errors)) {
            $emailCheck = $db->query("SELECT id FROM regular_users WHERE email = '{$db->escape($email)}' AND id != $userId");
            if ($emailCheck && $emailCheck->num_rows > 0) {
                $errors[] = "Email is already in use by another account.";
            }
        }
        
        if (empty($errors)) {
            $data = [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName
            ];
            
            if ($db->update('regular_users', $data, "id = $userId")) {
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                logUserActivity($userId, 'update_profile', "Updated profile information");
                $message = "Profile updated successfully.";
                $messageType = "success";
                
                // Refresh user data
                $result = $db->query("SELECT * FROM regular_users WHERE id = $userId");
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
                if ($db->update('regular_users', ['password' => $hashedPassword], "id = $userId")) {
                    logUserActivity($userId, 'change_password', "Password changed");
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

include_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="card-text">Member since <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="list-group mb-4">
                <a href="profile.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
                <a href="favorites.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-heart me-2"></i> My Favorites
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <input type="hidden" name="update_profile" value="1">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
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
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
