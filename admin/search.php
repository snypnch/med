<?php
$pageTitle = "Search Results";
require_once 'includes/auth.php';
include_once 'includes/header.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$db = Database::getInstance();

// Initialize results arrays
$medications = [];
$pharmacies = [];
$prices = [];

if (!empty($searchQuery)) {
    // Escape search query
    $query = $db->escape($searchQuery);
    
    // Search medications
    $medicationResult = $db->query("
        SELECT * FROM medications 
        WHERE name LIKE '%$query%' 
        OR generic_name LIKE '%$query%' 
        OR dosage LIKE '%$query%'
        OR description LIKE '%$query%'
        ORDER BY name
        LIMIT 20
    ");
    
    if ($medicationResult) {
        while ($row = $medicationResult->fetch_assoc()) {
            $medications[] = $row;
        }
    }
    
    // Search pharmacies
    $pharmacyResult = $db->query("
        SELECT * FROM pharmacies 
        WHERE name LIKE '%$query%' 
        OR address LIKE '%$query%' 
        OR contact LIKE '%$query%'
        ORDER BY name
        LIMIT 20
    ");
    
    if ($pharmacyResult) {
        while ($row = $pharmacyResult->fetch_assoc()) {
            $pharmacies[] = $row;
        }
    }
    
    // Search prices (medications with specific price points)
    $priceResult = $db->query("
        SELECT mp.*, m.name as medication_name, m.dosage, m.form, p.name as pharmacy_name
        FROM medication_prices mp
        JOIN medications m ON mp.medication_id = m.id
        JOIN pharmacies p ON mp.pharmacy_id = p.id
        WHERE m.name LIKE '%$query%' 
        OR m.generic_name LIKE '%$query%' 
        OR p.name LIKE '%$query%'
        ORDER BY mp.price ASC
        LIMIT 20
    ");
    
    if ($priceResult) {
        while ($row = $priceResult->fetch_assoc()) {
            $prices[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Search Results: "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
    
    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="search.php" method="GET" class="mb-0">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" placeholder="Search medications, pharmacies, or prices..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($searchQuery)): ?>
        <div class="alert alert-info">
            Please enter a search term to find medications, pharmacies, or prices.
        </div>
    <?php elseif (empty($medications) && empty($pharmacies) && empty($prices)): ?>
        <div class="alert alert-warning">
            No results found for "<?php echo htmlspecialchars($searchQuery); ?>". Please try a different search term.
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Medications Results -->
            <?php if (!empty($medications)): ?>
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Medications (<?php echo count($medications); ?>)</h6>
                            <a href="medications.php" class="btn btn-sm btn-primary">View All Medications</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Generic Name</th>
                                            <th>Dosage</th>
                                            <th>Form</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medications as $med): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($med['name']); ?></td>
                                                <td><?php echo !empty($med['generic_name']) ? htmlspecialchars($med['generic_name']) : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                                <td><?php echo htmlspecialchars($med['form']); ?></td>
                                                <td>
                                                    <a href="medications.php?action=view&id=<?php echo $med['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="medications.php?action=edit&id=<?php echo $med['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Pharmacies Results -->
            <?php if (!empty($pharmacies)): ?>
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Pharmacies (<?php echo count($pharmacies); ?>)</h6>
                            <a href="pharmacies.php" class="btn btn-sm btn-primary">View All Pharmacies</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Address</th>
                                            <th>Contact</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pharmacies as $pharmacy): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                                <td><?php echo htmlspecialchars($pharmacy['address']); ?></td>
                                                <td><?php echo !empty($pharmacy['contact']) ? htmlspecialchars($pharmacy['contact']) : 'N/A'; ?></td>
                                                <td>
                                                    <a href="pharmacies.php?action=view&id=<?php echo $pharmacy['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="pharmacies.php?action=edit&id=<?php echo $pharmacy['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Prices Results -->
            <?php if (!empty($prices)): ?>
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Prices (<?php echo count($prices); ?>)</h6>
                            <a href="prices.php" class="btn btn-sm btn-primary">View All Prices</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Medication</th>
                                            <th>Pharmacy</th>
                                            <th>Price</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prices as $price): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($price['medication_name']); ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($price['dosage'] . ' ' . $price['form']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($price['pharmacy_name']); ?></td>
                                                <td class="text-end">PHP <?php echo number_format($price['price'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($price['updated_at'])); ?></td>
                                                <td>
                                                    <a href="prices.php?action=edit&id=<?php echo $price['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>
