<?php
$pageTitle = "Dashboard";
require_once 'includes/auth.php';
include_once 'includes/header.php';

// Get database statistics
$db = Database::getInstance();

// Count medications
$medicationsResult = $db->query("SELECT COUNT(*) as count FROM medications");
$medicationsCount = $medicationsResult->fetch_assoc()['count'];

// Count pharmacies
$pharmaciesResult = $db->query("SELECT COUNT(*) as count FROM pharmacies");
$pharmaciesCount = $pharmaciesResult->fetch_assoc()['count'];

// Count prices
$pricesResult = $db->query("SELECT COUNT(*) as count FROM medication_prices");
$pricesCount = $pricesResult->fetch_assoc()['count'];

// Get average price
$avgPriceResult = $db->query("SELECT AVG(price) as avg_price FROM medication_prices");
$avgPrice = $avgPriceResult->fetch_assoc()['avg_price'];

// Get recent activity logs
$activityResult = $db->query("
    SELECT al.*, u.username 
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");

$activities = [];
if ($activityResult) {
    while ($row = $activityResult->fetch_assoc()) {
        $activities[] = $row;
    }
}

// Get recent user activity
$userActivityResult = $db->query("
    SELECT * FROM activity_logs 
    WHERE user_id IN (SELECT id FROM regular_users)
    ORDER BY created_at DESC
    LIMIT 5
");

$userActivities = [];
if ($userActivityResult) {
    while ($row = $userActivityResult->fetch_assoc()) {
        $userActivities[] = $row;
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
    
    <div class="row">
        <!-- Medications Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Medications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $medicationsCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pills fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="medications.php" class="text-primary">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Pharmacies Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pharmacies</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pharmaciesCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="pharmacies.php" class="text-success">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Price Entries Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Price Entries</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pricesCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="prices.php" class="text-info">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Average Price Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Average Price</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">PHP <?php echo number_format($avgPrice, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="prices.php" class="text-warning">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Admin Activity -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <p class="text-center">No recent activity found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($activity['action']); ?>
                                                <?php if (!empty($activity['details'])): ?>
                                                    <span class="text-muted small">
                                                        (<?php echo htmlspecialchars($activity['details']); ?>)
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="medications.php?action=add" class="btn btn-primary btn-block">
                                <i class="fas fa-plus-circle me-2"></i> Add Medication
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="pharmacies.php?action=add" class="btn btn-success btn-block">
                                <i class="fas fa-plus-circle me-2"></i> Add Pharmacy
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="prices.php?action=add" class="btn btn-info btn-block">
                                <i class="fas fa-plus-circle me-2"></i> Add Price Entry
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="settings.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Overview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Overview</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                    </div>
                    <div class="mb-2">
                        <strong>MySQL Version:</strong> 
                        <?php 
                            $versionResult = $db->query("SELECT VERSION() as version");
                            echo $versionResult->fetch_assoc()['version'];
                        ?>
                    </div>
                    <div class="mb-2">
                        <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                    </div>
                    <div>
                        <strong>Last Database Backup:</strong> <span class="text-danger">Never</span>
                        <a href="settings.php?action=backup" class="btn btn-sm btn-outline-primary ms-2">
                            Backup Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent User Activity -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent User Activity</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($userActivities)): ?>
                        <p class="text-center">No recent user activity found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userActivities as $activity): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($activity['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                            <td>
                                                <?php if (!empty($activity['details'])): ?>
                                                    <?php echo htmlspecialchars($activity['details']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No details</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
