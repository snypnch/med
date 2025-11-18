<?php
$pageTitle = "Manage Prices";
require_once 'includes/auth.php';
include_once 'includes/header.php';

$db = Database::getInstance();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$medicationId = isset($_GET['medication_id']) ? (int)$_GET['medication_id'] : 0;
$pharmacyId = isset($_GET['pharmacy_id']) ? (int)$_GET['pharmacy_id'] : 0;
$message = '';
$messageType = '';

// Get all medications for dropdown
$medications = [];
$medicationResult = $db->query("SELECT id, name, dosage, form FROM medications ORDER BY name");
if ($medicationResult) {
    while ($row = $medicationResult->fetch_assoc()) {
        $medications[$row['id']] = $row['name'] . ' ' . $row['dosage'] . ' ' . $row['form'];
    }
}

// Get all pharmacies for dropdown
$pharmacies = [];
$pharmacyResult = $db->query("SELECT id, name FROM pharmacies ORDER BY name");
if ($pharmacyResult) {
    while ($row = $pharmacyResult->fetch_assoc()) {
        $pharmacies[$row['id']] = $row['name'];
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $medicationId = isset($_POST['medication_id']) ? (int)$_POST['medication_id'] : 0;
    $pharmacyId = isset($_POST['pharmacy_id']) ? (int)$_POST['pharmacy_id'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'available';
    
    // Validate input
    $errors = [];
    if ($medicationId <= 0) {
        $errors[] = "Please select a medication.";
    }
    if ($pharmacyId <= 0) {
        $errors[] = "Please select a pharmacy.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    if (!in_array($status, ['available', 'out_of_stock'])) {
        $errors[] = "Please select a valid status.";
    }
    
    if (empty($errors)) {
        // Prepare data for database
        $data = [
            'medication_id' => $medicationId,
            'pharmacy_id' => $pharmacyId,
            'price' => $price,
            'status' => $status
        ];
        
        // Check if price already exists
        if ($action === 'add') {
            $existingResult = $db->query("SELECT id FROM medication_prices WHERE medication_id = $medicationId AND pharmacy_id = $pharmacyId");
            if ($existingResult && $existingResult->num_rows > 0) {
                $existingId = $existingResult->fetch_assoc()['id'];
                if ($db->update('medication_prices', $data, "id = $existingId")) {
                    logAdminActivity($_SESSION['admin_id'], 'update_price', "Updated existing price for medication #$medicationId at pharmacy #$pharmacyId");
                    $message = "Price updated successfully.";
                    $messageType = "success";
                    $action = 'list'; // Return to list view
                } else {
                    $message = "Error updating price.";
                    $messageType = "danger";
                }
            } else {
                // Add new price
                if ($db->insert('medication_prices', $data)) {
                    logAdminActivity($_SESSION['admin_id'], 'add_price', "Added price for medication #$medicationId at pharmacy #$pharmacyId");
                    $message = "Price added successfully.";
                    $messageType = "success";
                    $action = 'list'; // Return to list view
                } else {
                    $message = "Error adding price.";
                    $messageType = "danger";
                }
            }
        }
        // Update existing price
        elseif ($action === 'edit' && $id > 0) {
            if ($db->update('medication_prices', $data, "id = $id")) {
                logAdminActivity($_SESSION['admin_id'], 'edit_price', "Updated price ID: $id");
                $message = "Price updated successfully.";
                $messageType = "success";
                $action = 'list'; // Return to list view
            } else {
                $message = "Error updating price.";
                $messageType = "danger";
            }
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "danger";
    }
}

// Delete price
if ($action === 'delete' && $id > 0) {
    // Get price details for logging
    $priceResult = $db->query("
        SELECT mp.*, m.name as medication_name, p.name as pharmacy_name 
        FROM medication_prices mp
        JOIN medications m ON mp.medication_id = m.id
        JOIN pharmacies p ON mp.pharmacy_id = p.id
        WHERE mp.id = $id
    ");
    $priceDetails = $priceResult->fetch_assoc();
    
    if ($db->delete('medication_prices', "id = $id")) {
        logAdminActivity($_SESSION['admin_id'], 'delete_price', "Deleted price for {$priceDetails['medication_name']} at {$priceDetails['pharmacy_name']}");
        $message = "Price deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting price.";
        $messageType = "danger";
    }
    $action = 'list'; // Return to list view
}

// Get price for editing
$price = null;
if ($action === 'edit' && $id > 0) {
    $result = $db->query("SELECT * FROM medication_prices WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $price = $result->fetch_assoc();
        $medicationId = $price['medication_id'];
        $pharmacyId = $price['pharmacy_id'];
    } else {
        $message = "Price not found.";
        $messageType = "danger";
        $action = 'list'; // Return to list view
    }
}

// Get all prices for listing
$prices = [];
if ($action === 'list') {
    $result = $db->query("
        SELECT mp.*, m.name as medication_name, m.dosage, m.form, p.name as pharmacy_name
        FROM medication_prices mp
        JOIN medications m ON mp.medication_id = m.id
        JOIN pharmacies p ON mp.pharmacy_id = p.id
        ORDER BY m.name, p.name
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $prices[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><?php echo $pageTitle; ?></h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Price
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
                    <?php echo $action === 'add' ? 'Add New Price' : 'Edit Price'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=<?php echo $action; ?><?php echo $action === 'edit' ? '&id=' . $id : ''; ?>">
                    <div class="mb-3">
                        <label for="medication_id" class="form-label">Medication <span class="text-danger">*</span></label>
                        <select class="form-select" id="medication_id" name="medication_id" required <?php echo $action === 'edit' ? 'disabled' : ''; ?>>
                            <option value="">Select Medication</option>
                            <?php foreach ($medications as $medId => $medName): ?>
                                <option value="<?php echo $medId; ?>" <?php echo $medicationId == $medId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($medName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="medication_id" value="<?php echo $medicationId; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="pharmacy_id" class="form-label">Pharmacy <span class="text-danger">*</span></label>
                        <select class="form-select" id="pharmacy_id" name="pharmacy_id" required <?php echo $action === 'edit' ? 'disabled' : ''; ?>>
                            <option value="">Select Pharmacy</option>
                            <?php foreach ($pharmacies as $pharmId => $pharmName): ?>
                                <option value="<?php echo $pharmId; ?>" <?php echo $pharmacyId == $pharmId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pharmName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="pharmacy_id" value="<?php echo $pharmacyId; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (PHP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">PHP</span>
                            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required 
                                   value="<?php echo isset($price) ? htmlspecialchars($price['price']) : ''; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" <?php echo (isset($price) && $price['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="out_of_stock" <?php echo (isset($price) && $price['status'] === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $action === 'add' ? 'Add Price' : 'Update Price'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- List All Prices -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Prices</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Pharmacy</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No prices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prices as $p): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($p['medication_name']); ?>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($p['dosage'] . ' ' . $p['form']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($p['pharmacy_name']); ?></td>
                                        <td class="text-end">PHP <?php echo number_format($p['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $p['status'] === 'available' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($p['updated_at'])); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this price entry?');">
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

<?php include_once 'includes/footer.php'; ?>