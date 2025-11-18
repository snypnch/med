<?php
$pageTitle = "Admin Login";
require_once '../includes/config.php';
require_once '../includes/db.php';

// Clear previous session data
// Don't call session_start() again since it's already called in config.php
session_unset();
session_destroy();

// Start a new session
session_start();

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Direct database query for login to avoid potential issues
        $db = Database::getInstance();
        $username = $db->escape($username);
        
        $result = $db->query("SELECT * FROM users WHERE username = '$username'");
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Log activity
                $data = [
                    'user_id' => $user['id'],
                    'action' => 'login',
                    'details' => 'User logged in',
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];
                $db->insert('activity_logs', $data);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Username not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header text-center bg-primary text-white">
                    <h4 class="my-2"><?php echo APP_NAME; ?> Admin Login</h4>
                </div>
                <div class="card-body p-5">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" value="admin" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" value="admin123" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                <a href="javascript:void(0);" 
   onclick="resetAdmin()" 
   class="text-decoration-none">
   Reset Admin Account
</a>
                    <br>
                    <a href="<?php echo APP_URL; ?>/user/index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Return to Public Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetAdmin() {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will reset the admin account and send new credentials via email.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call reset_admin.php with AJAX
            fetch("reset_admin.php")
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Done!',
                        text: data,
                        icon: 'success'
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong: ' + error,
                        icon: 'error'
                    });
                });
        }
    });
}
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
