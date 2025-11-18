<?php
$pageTitle = "Manage Pharmacies";
require_once 'includes/auth.php';
include_once 'includes/header.php';

$db = Database::getInstance();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$messageType = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : null;
    
    // Convert empty coordinates to null
    if (empty($latitude)) $latitude = null;
    if (empty($longitude)) $longitude = null;
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Pharmacy name is required.";
    }
    if (empty($address)) {
        $errors[] = "Address is required.";
    }
    
    if (empty($errors)) {
        // Prepare data for database
        $data = [
            'name' => $name,
            'address' => $address,
            'contact' => $contact,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        
        // Add new pharmacy
        if ($action === 'add') {
            if ($db->insert('pharmacies', $data)) {
                logAdminActivity($_SESSION['admin_id'], 'add_pharmacy', "Added pharmacy: $name");
                $message = "Pharmacy added successfully.";
                $messageType = "success";
                $action = 'list'; // Return to list view
            } else {
                $message = "Error adding pharmacy.";
                $messageType = "danger";
            }
        }
        // Update existing pharmacy
        elseif ($action === 'edit' && $id > 0) {
            if ($db->update('pharmacies', $data, "id = $id")) {
                logAdminActivity($_SESSION['admin_id'], 'edit_pharmacy', "Updated pharmacy ID: $id");
                $message = "Pharmacy updated successfully.";
                $messageType = "success";
                $action = 'list'; // Return to list view
            } else {
                $message = "Error updating pharmacy.";
                $messageType = "danger";
            }
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "danger";
    }
}

// Delete pharmacy
if ($action === 'delete' && $id > 0) {
    // Get pharmacy name for logging
    $pharmacyResult = $db->query("SELECT name FROM pharmacies WHERE id = $id");
    $pharmacyName = $pharmacyResult->fetch_assoc()['name'];
    
    if ($db->delete('pharmacies', "id = $id")) {
        logAdminActivity($_SESSION['admin_id'], 'delete_pharmacy', "Deleted pharmacy: $pharmacyName (ID: $id)");
        $message = "Pharmacy deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting pharmacy.";
        $messageType = "danger";
    }
    $action = 'list'; // Return to list view
}

// Get pharmacy for editing
$pharmacy = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    $result = $db->query("SELECT * FROM pharmacies WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $pharmacy = $result->fetch_assoc();
    } else {
        $message = "Pharmacy not found.";
        $messageType = "danger";
        $action = 'list'; // Return to list view
    }
}

// Get all pharmacies for listing
$pharmacies = [];
if ($action === 'list') {
    $result = $db->query("SELECT * FROM pharmacies ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pharmacies[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><?php echo $pageTitle; ?></h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Pharmacy
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $action === 'add' ? 'Add New Pharmacy' : 'Edit Pharmacy'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=<?php echo $action; ?><?php echo $action === 'edit' ? '&id=' . $id : ''; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Pharmacy Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo isset($pharmacy) ? htmlspecialchars($pharmacy['name']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" required><?php echo isset($pharmacy) ? htmlspecialchars($pharmacy['address']) : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact" name="contact" 
                               value="<?php echo isset($pharmacy) ? htmlspecialchars($pharmacy['contact']) : ''; ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" 
                                   value="<?php echo isset($pharmacy) ? htmlspecialchars($pharmacy['latitude']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" 
                                   value="<?php echo isset($pharmacy) ? htmlspecialchars($pharmacy['longitude']) : ''; ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $action === 'add' ? 'Add Pharmacy' : 'Update Pharmacy'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($action === 'view' && $pharmacy): ?>
        <!-- View Pharmacy Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pharmacy Details</h6>
                <div>
                    <a href="?action=edit&id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="?action=delete&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Are you sure you want to delete this pharmacy?');">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Pharmacy Name:</strong> <?php echo htmlspecialchars($pharmacy['name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($pharmacy['address']); ?></p>
                        <p><strong>Contact:</strong> <?php echo !empty($pharmacy['contact']) ? htmlspecialchars($pharmacy['contact']) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Latitude:</strong> <?php echo !empty($pharmacy['latitude']) ? htmlspecialchars($pharmacy['latitude']) : 'N/A'; ?></p>
                        <p><strong>Longitude:</strong> <?php echo !empty($pharmacy['longitude']) ? htmlspecialchars($pharmacy['longitude']) : 'N/A'; ?></p>
                        <p><strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($pharmacy['updated_at'])); ?></p>
                    </div>
                </div>
                
                <!-- Map if coordinates are available -->
                <?php if (!empty($pharmacy['latitude']) && !empty($pharmacy['longitude'])): ?>
                    <div class="mt-3">
                        <h6>Location Map:</h6>
                        <div id="map" style="height: 300px; width: 100%;"></div>
                        <div class="mt-2">
                            <a href="https://www.openstreetmap.org/directions?from=&to=<?php echo $pharmacy['latitude']; ?>%2C<?php echo $pharmacy['longitude']; ?>" 
                               class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-directions me-1"></i> Get Directions
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Medication Prices at this Pharmacy -->
                <?php
                $priceResult = $db->query("
                    SELECT mp.*, m.name as medication_name, m.dosage, m.form
                    FROM medication_prices mp
                    JOIN medications m ON mp.medication_id = m.id
                    WHERE mp.pharmacy_id = $id
                    ORDER BY m.name ASC
                ");
                
                $prices = [];
                if ($priceResult) {
                    while ($row = $priceResult->fetch_assoc()) {
                        $prices[] = $row;
                    }
                }
                ?>
                
                <div class="mt-4">
                    <h6>Medications Available:</h6>
                    <?php if (empty($prices)): ?>
                        <p class="text-muted">No medications listed for this pharmacy yet.</p>
                        <a href="prices.php?action=add&pharmacy_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Medication Price
                        </a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Medication</th>
                                        <th>Dosage</th>
                                        <th>Price</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prices as $price): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($price['medication_name']); ?></td>
                                            <td><?php echo htmlspecialchars($price['dosage'] . ' ' . $price['form']); ?></td>
                                            <td>PHP <?php echo number_format($price['price'], 2); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($price['updated_at'])); ?></td>
                                            <td>
                                                <a href="prices.php?action=edit&id=<?php echo $price['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="prices.php?action=delete&id=<?php echo $price['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this price entry?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="prices.php?action=add&pharmacy_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Medication Price
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer">
                <a href="?action=list" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    <?php else: ?>
        <!-- List All Pharmacies -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Pharmacies</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pharmacies)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No pharmacies found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pharmacies as $pharmacy): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                        <td><?php echo htmlspecialchars($pharmacy['address']); ?></td>
                                        <td><?php echo !empty($pharmacy['contact']) ? htmlspecialchars($pharmacy['contact']) : 'N/A'; ?></td>
                                        <td>
                                            <a href="?action=view&id=<?php echo $pharmacy['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?php echo $pharmacy['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $pharmacy['id']; ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this pharmacy?');">
                                                <i class="fas fa-trash"></i>
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
    <?php endif; ?>
</div>

<?php 
// Only include Leaflet map scripts when viewing a pharmacy with coordinates
if (isset($pharmacy) && !empty($pharmacy['latitude']) && !empty($pharmacy['longitude'])): 
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    .leaflet-container {
        height: 300px;
        width: 100%;
        border-radius: 0.25rem;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('map')) {
            const map = L.map('map').setView([<?php echo $pharmacy['latitude']; ?>, <?php echo $pharmacy['longitude']; ?>], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            L.marker([<?php echo $pharmacy['latitude']; ?>, <?php echo $pharmacy['longitude']; ?>])
                .addTo(map)
                .bindPopup("<?php echo addslashes($pharmacy['name']); ?>")
                .openPopup();
        }
    });
</script>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
