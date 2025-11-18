<?php require_once __DIR__ . '/../../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>/styles.css">
</head>
<body>
    <header class="bg-primary text-white shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>/user/index.php">
                    <strong><?php echo APP_NAME; ?></strong>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/user/index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/user/about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/user/contact.php">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/user/disclaimer.php">Disclaimer</a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto">
                        <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_username']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/profile.php">My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/favorites.php">My Favorites</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/user/login.php">
                                    <i class="fas fa-sign-in-alt me-1"></i> User Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-light btn-sm px-3 mx-2" href="<?php echo APP_URL; ?>/user/login.php#register">
                                    <i class="fas fa-user-plus me-1"></i> Register
                                </a>
                            </li> -->
                            <li class="nav-item">
                                <!-- <a class="nav-link btn btn-sm btn-outline-light ms-2" href="<?php echo APP_URL; ?>/admin/index.php">
                                    <i class="fas fa-user-shield me-1"></i> Admin Panel
                                </a> -->
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container py-4">
