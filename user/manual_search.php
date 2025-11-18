<?php
$pageTitle = "Advanced Search";
require_once 'includes/functions.php';
include_once 'includes/header.php';

// Get all medications for filtering
$allMedications = getAllMedications();

// Get medication categories
$categories = getMedicationCategories();

// Get all pharmacies for filtering
$pharmacies = getAllPharmacies();

// Initialize search results
$results = [];
$searched = false;
$noResults = false;

// Process search form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    
    // Collect search parameters
    $medicationName = isset($_POST['medication_name']) ? sanitize($_POST['medication_name']) : '';
    $genericName = isset($_POST['generic_name']) ? sanitize($_POST['generic_name']) : '';
    $category = isset($_POST['category']) ? sanitize($_POST['category']) : '';
    $selectedPharmacies = isset($_POST['pharmacies']) ? $_POST['pharmacies'] : [];
    $priceMin = isset($_POST['price_min']) ? (float)$_POST['price_min'] : 0;
    $priceMax = isset($_POST['price_max']) ? (float)$_POST['price_max'] : 1000;
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if (!empty($medicationName)) {
        $conditions[] = "m.name LIKE ?";
        $params[] = "%$medicationName%";
    }
    
    if (!empty($genericName)) {
        $conditions[] = "m.generic_name LIKE ?";
        $params[] = "%$genericName%";
    }
    
    // In a real app, we would filter by category correctly
    // For now, we'll just simulate it by matching the category name in description
    if (!empty($category)) {
        $conditions[] = "m.description LIKE ?";
        $params[] = "%$category%";
    }
    
    // Filter by price range only if we're getting prices
    $havingClause = "";
    if ($priceMin > 0 || $priceMax < 1000) {
        $havingClause = "HAVING MIN(mp.price) >= $priceMin AND MIN(mp.price) <= $priceMax";
    }
    
    // Filter by selected pharmacies
    $pharmacyCondition = "";
    if (!empty($selectedPharmacies)) {
        $pharmacyIds = array_map('intval', $selectedPharmacies);
        $pharmacyCondition = "AND mp.pharmacy_id IN (" . implode(',', $pharmacyIds) . ")";
    }
    
    // Build the full query
    $db = Database::getInstance();
    
    $sql = "SELECT m.*, MIN(mp.price) as min_price, COUNT(DISTINCT mp.pharmacy_id) as pharmacy_count 
            FROM medications m
            LEFT JOIN medication_prices mp ON m.id = mp.medication_id ";
    
    if (!empty($pharmacyCondition)) {
        $sql .= "AND mp.pharmacy_id IN (" . implode(',', array_map('intval', $selectedPharmacies)) . ") ";
    }
    
    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    }
    
    $sql .= "GROUP BY m.id ";
    
    if (!empty($havingClause)) {
        $sql .= $havingClause . " ";
    }
    
    $sql .= "ORDER BY min_price ASC";
    
    // Execute the query (simplified for this example)
    // In a real app with prepared statements, this would be different
    foreach ($params as $i => $param) {
        $sql = preg_replace('/\?/', "'" . $db->escape($param) . "'", $sql, 1);
    }
    
    $result = $db->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    
    $noResults = empty($results);
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Advanced Search</li>
        </ol>
    </nav>
    
    <h1 class="mb-4">Advanced Medication Search</h1>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Search Criteria</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="medication_name" class="form-label">Medication Name</label>
                            <input type="text" class="form-control" id="medication_name" name="medication_name" 
                                   value="<?php echo isset($medicationName) ? htmlspecialchars($medicationName) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="generic_name" class="form-label">Generic Name</label>
                            <input type="text" class="form-control" id="generic_name" name="generic_name"
                                   value="<?php echo isset($genericName) ? htmlspecialchars($genericName) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Any Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo (isset($category) && $category === $cat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Price Range (PHP)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_min" placeholder="Min" min="0" 
                                           value="<?php echo isset($priceMin) ? $priceMin : ''; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_max" placeholder="Max" min="0"
                                           value="<?php echo isset($priceMax) ? $priceMax : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Available at Pharmacies</label>
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pharmacies[]" 
                                           value="<?php echo $pharmacy['id']; ?>" id="pharmacy<?php echo $pharmacy['id']; ?>"
                                           <?php echo (isset($selectedPharmacies) && in_array($pharmacy['id'], $selectedPharmacies)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="pharmacy<?php echo $pharmacy['id']; ?>">
                                        <?php echo sanitize($pharmacy['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> Search Medications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <?php if ($searched): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Search Results</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($noResults): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No medications found matching your search criteria.
                                <p class="mt-2 mb-0">
                                    Try adjusting your search parameters or <a href="index.php" class="alert-link">browse all medications</a>.
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-3"><?php echo count($results); ?> medications found</p>
                            
                            <div class="list-group">
                                <?php foreach ($results as $medication): ?>
                                    <a href="details.php?id=<?php echo $medication['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo sanitize($medication['name']); ?> <?php echo sanitize($medication['dosage'] . ' ' . $medication['form']); ?></h5>
                                            <?php if (isset($medication['min_price']) && $medication['min_price']): ?>
                                                <span class="badge bg-primary rounded-pill">From <?php echo formatPrice($medication['min_price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($medication['generic_name'])): ?>
                                            <p class="mb-1 text-muted">Generic: <?php echo sanitize($medication['generic_name']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">
                                                Available at <?php echo $medication['pharmacy_count']; ?> pharmacies
                                            </small>
                                            <span class="btn btn-sm btn-outline-primary">View Details</span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h3>Advanced Medication Search</h3>
                        <p class="lead text-muted">
                            Use the search form to find medications based on specific criteria.
                        </p>
                        <p class="mb-0">
                            You can search by medication name, generic name, category, price range, or available pharmacies.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
