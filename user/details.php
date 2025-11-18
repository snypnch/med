<?php
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get medication ID
$medicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get medication details
$medication = getMedicationById($medicationId);

// If medication not found, redirect to search page
if (!$medication) {
    header("Location: search.php?error=medication_not_found");
    exit;
}

// Get prices for this medication
$prices = getMedicationPrices($medicationId);

// Log this view for analytics purposes
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    logMedicationView($medicationId);
}

// Sort prices by lowest first
usort($prices, function($a, $b) {
    return $a['price'] - $b['price'];
});

$pageTitle = $medication['name'] . " " . $medication['dosage'];
include_once 'includes/header.php';

// Check if user has favorited this medication
$isFavorited = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $favResult = $db->query("SELECT id FROM favorites WHERE user_id = $userId AND medication_id = $medicationId");
    $isFavorited = ($favResult && $favResult->num_rows > 0);
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="search.php">Search Results</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo sanitize($medication['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-3">
                <?php echo sanitize($medication['name']); ?> <?php echo sanitize($medication['dosage'] . ' ' . $medication['form']); ?>
                <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <?php if($isFavorited): ?>
                        <span class="badge bg-danger"><i class="fas fa-heart me-1"></i> In Favorites</span>
                    <?php else: ?>
                        <a href="favorites.php?action=add&med_id=<?php echo $medication['id']; ?>" class="btn btn-outline-danger btn-sm ms-2">
                            <i class="fas fa-heart me-1"></i> Add to Favorites
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </h1>
            
            <?php if(!empty($medication['generic_name'])): ?>
                <p class="lead">Generic Name: <?php echo sanitize($medication['generic_name']); ?></p>
            <?php endif; ?>
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(sanitize($medication['description'])); ?></p>
                </div>
            </div>
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Price Comparison</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($prices)): ?>
                        <p class="text-muted">No price information available for this medication.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pharmacy</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($prices as $index => $price): ?>
                                        <tr class="<?php echo $index === 0 ? 'table-success' : ''; ?>">
                                            <td><?php echo sanitize($price['pharmacy_name']); ?></td>
                                            <td><?php echo sanitize($price['address']); ?></td>
                                            <td><?php echo sanitize($price['contact']); ?></td>
                                            <td class="text-end fw-bold">
                                                <?php echo formatPrice($price['price']); ?>
                                                <?php if($index === 0): ?>
                                                    <span class="badge bg-success ms-2">Best Price</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <?php if(!empty($prices)): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Best Price</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="text-center text-primary mb-3"><?php echo formatPrice($prices[0]['price']); ?></h3>
                        <div class="text-center mb-3">
                            <h5><?php echo sanitize($prices[0]['pharmacy_name']); ?></h5>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo sanitize($prices[0]['address']); ?></p>
                            <p><i class="fas fa-phone me-2"></i><?php echo sanitize($prices[0]['contact']); ?></p>
                        </div>
                        
                        <?php if(!empty($prices[0]['latitude']) && !empty($prices[0]['longitude'])): ?>
                            <div class="mb-3">
                                <div id="map" style="height: 200px; width: 100%;"></div>
                            </div>
                            <a href="https://www.openstreetmap.org/directions?from=&to=<?php echo $prices[0]['latitude']; ?>%2C<?php echo $prices[0]['longitude']; ?>" class="btn btn-primary w-100" target="_blank">
                                <i class="fas fa-directions me-2"></i> Get Directions
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Disclaimer</h5>
                </div>
                <div class="card-body">
                    <p class="small">The information provided is for price comparison only and does not constitute medical advice. Prices may change without notice. Always consult a healthcare professional before taking any medication.</p>
                </div>
            </div>
            
            <?php
            // Get other medications with the same generic name for comparison
            $similarMedications = [];
            if(!empty($medication['generic_name'])) {
                $db = Database::getInstance();
                $generic = $db->escape($medication['generic_name']);
                $currentId = (int)$medication['id'];
                
                $result = $db->query("SELECT * FROM medications 
                                    WHERE generic_name = '$generic' 
                                    AND id != $currentId 
                                    LIMIT 5");
                
                while($row = $result->fetch_assoc()) {
                    $similarMedications[] = $row;
                }
            }
            
            if(!empty($similarMedications)):
            ?>
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Similar Medications</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach($similarMedications as $similar): ?>
                            <?php
                            // Get the lowest price for this similar medication
                            $similarPrices = getMedicationPrices($similar['id']);
                            usort($similarPrices, function($a, $b) {
                                return $a['price'] - $b['price'];
                            });
                            $lowestPrice = !empty($similarPrices) ? $similarPrices[0]['price'] : null;
                            ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="details.php?id=<?php echo $similar['id']; ?>" class="text-decoration-none">
                                            <?php echo sanitize($similar['name']); ?> <?php echo sanitize($similar['dosage']); ?>
                                        </a>
                                    </div>
                                    <?php if($lowestPrice): ?>
                                        <span class="badge bg-secondary"><?php echo formatPrice($lowestPrice); ?></span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if(!empty($prices[0]['latitude']) && !empty($prices[0]['longitude'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    .leaflet-container {
        height: 200px;
        width: 100%;
        border-radius: 0.25rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('map')) {
            const map = L.map('map').setView([<?php echo $prices[0]['latitude']; ?>, <?php echo $prices[0]['longitude']; ?>], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            L.marker([<?php echo $prices[0]['latitude']; ?>, <?php echo $prices[0]['longitude']; ?>])
                .addTo(map)
                .bindPopup("<?php echo addslashes($prices[0]['pharmacy_name']); ?>")
                .openPopup();
        }
    });
</script>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
