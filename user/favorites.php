<?php
$pageTitle = "My Favorites";
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

// Handle add to favorites
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['med_id'])) {
    $medId = (int)$_GET['med_id'];
    
    // Check if medication exists
    $medCheck = $db->query("SELECT id FROM medications WHERE id = $medId");
    if ($medCheck && $medCheck->num_rows > 0) {
        // Check if already in favorites
        $favCheck = $db->query("SELECT id FROM favorites WHERE user_id = $userId AND medication_id = $medId");
        if ($favCheck && $favCheck->num_rows > 0) {
            $message = "This medication is already in your favorites.";
            $messageType = "info";
        } else {
            // Add to favorites
            $data = [
                'user_id' => $userId,
                'medication_id' => $medId
            ];
            
            if ($db->insert('favorites', $data)) {
                logUserActivity($userId, 'add_favorite', "Added medication #$medId to favorites");
                $message = "Medication added to favorites successfully.";
                $messageType = "success";
            } else {
                $message = "Error adding to favorites.";
                $messageType = "danger";
            }
        }
    } else {
        $message = "Medication not found.";
        $messageType = "danger";
    }
}

// Handle remove from favorites
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $favId = (int)$_GET['id'];
    
    // Check if favorite belongs to user
    $favCheck = $db->query("SELECT medication_id FROM favorites WHERE id = $favId AND user_id = $userId");
    if ($favCheck && $favCheck->num_rows > 0) {
        $medId = $favCheck->fetch_assoc()['medication_id'];
        
        if ($db->delete('favorites', "id = $favId")) {
            logUserActivity($userId, 'remove_favorite', "Removed medication #$medId from favorites");
            $message = "Medication removed from favorites.";
            $messageType = "success";
        } else {
            $message = "Error removing from favorites.";
            $messageType = "danger";
        }
    } else {
        $message = "Favorite not found or you don't have permission to remove it.";
        $messageType = "danger";
    }
}

// Get user's favorites
$favorites = [];
$result = $db->query("
    SELECT f.*, m.name, m.generic_name, m.dosage, m.form, m.description 
    FROM favorites f
    JOIN medications m ON f.medication_id = m.id
    WHERE f.user_id = $userId
    ORDER BY f.created_at DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
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
                    <h5 class="card-title"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($_SESSION['user_username']); ?></p>
                </div>
            </div>
            
            <div class="list-group mb-4">
                <a href="profile.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
                <a href="favorites.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-heart me-2"></i> My Favorites
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="col-lg-9">
            <h2 class="mb-4">My Favorite Medications</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($favorites)): ?>
                <div class="alert alert-info">
                    <p>You don't have any favorite medications yet.</p>
                    <p>When browsing medications, click on "Add to Favorites" to save them here for easy access.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Find Medications
                    </a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($favorites as $favorite): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title"><?php echo htmlspecialchars($favorite['name']); ?></h5>
                                        <a href="?action=remove&id=<?php echo $favorite['id']; ?>" class="text-danger" 
                                           onclick="return confirm('Remove this medication from favorites?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php echo htmlspecialchars($favorite['dosage'] . ' ' . $favorite['form']); ?>
                                    </h6>
                                    <?php if (!empty($favorite['generic_name'])): ?>
                                        <p class="card-text"><small>Generic Name: <?php echo htmlspecialchars($favorite['generic_name']); ?></small></p>
                                    <?php endif; ?>
                                    <p class="card-text">
                                        <?php 
                                        $desc = $favorite['description'];
                                        echo strlen($desc) > 100 ? htmlspecialchars(substr($desc, 0, 100)) . '...' : htmlspecialchars($desc); 
                                        ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent text-end">
                                    <a href="details.php?id=<?php echo $favorite['medication_id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-info-circle me-1"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
