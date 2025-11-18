<?php
$pageTitle = "Manage Medications";
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
    $genericName = isset($_POST['generic_name']) ? trim($_POST['generic_name']) : '';
    $dosage = isset($_POST['dosage']) ? trim($_POST['dosage']) : '';
    $form = isset($_POST['form']) ? trim($_POST['form']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Medication name is required.";
    }
    if (empty($dosage)) {
        $errors[] = "Dosage is required.";
    }
    if (empty($form)) {
        $errors[] = "Form is required.";
    }
    
    if (empty($errors)) {
        // Prepare data for database
        $data = [
            'name' => $name,
            'generic_name' => $genericName,
            'dosage' => $dosage,
            'form' => $form,
            'description' => $description
        ];
        
        // Add new medication
        if ($action === 'add') {
            if ($db->insert('medications', $data)) {
                logAdminActivity($_SESSION['admin_id'], 'add_medication', "Added medication: $name");
                $message = "Medication added successfully.";
                $messageType = "success";
                $action = 'list'; // Return to list view
            } else {
                $message = "Error adding medication.";
                $messageType = "danger";
            }
        }
        // Update existing medication
        elseif ($action === 'edit' && $id > 0) {
            if ($db->update('medications', $data, "id = $id")) {
                logAdminActivity($_SESSION['admin_id'], 'edit_medication', "Updated medication ID: $id");
                $message = "Medication updated successfully.";
                $messageType = "success";
                $action = 'list'; // Return to list view
            } else {
                $message = "Error updating medication.";
                $messageType = "danger";
            }
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "danger";
    }
}

// Delete medication
if ($action === 'delete' && $id > 0) {
    // Get medication name for logging
    $medicationResult = $db->query("SELECT name FROM medications WHERE id = $id");
    $medicationName = $medicationResult->fetch_assoc()['name'];
    
    if ($db->delete('medications', "id = $id")) {
        logAdminActivity($_SESSION['admin_id'], 'delete_medication', "Deleted medication: $medicationName (ID: $id)");
        $message = "Medication deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting medication.";
        $messageType = "danger";
    }
    $action = 'list'; // Return to list view
}

// Get medication for editing
$medication = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    $result = $db->query("SELECT * FROM medications WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $medication = $result->fetch_assoc();
    } else {
        $message = "Medication not found.";
        $messageType = "danger";
        $action = 'list'; // Return to list view
    }
}

// Get all medications for listing
$medications = [];
if ($action === 'list') {
    $result = $db->query("SELECT * FROM medications ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $medications[] = $row;
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><?php echo $pageTitle; ?></h1>
        <?php if ($action === 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Medication
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
                    <?php echo $action === 'add' ? 'Add New Medication' : 'Edit Medication'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?action=<?php echo $action; ?><?php echo $action === 'edit' ? '&id=' . $id : ''; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Medication Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   value="<?php echo isset($medication) ? htmlspecialchars($medication['name']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="generic_name" class="form-label">Generic Name</label>
                            <input type="text" class="form-control" id="generic_name" name="generic_name" 
                                   value="<?php echo isset($medication) ? htmlspecialchars($medication['generic_name']) : ''; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dosage" class="form-label">Dosage <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dosage" name="dosage" required 
                                   value="<?php echo isset($medication) ? htmlspecialchars($medication['dosage']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="form" class="form-label">Form <span class="text-danger">*</span></label>
                            <select class="form-select" id="form" name="form" required>
                                <option value="">Select Form</option>
                                <option value="Tablet" <?php echo isset($medication) && $medication['form'] === 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                                <option value="Capsule" <?php echo isset($medication) && $medication['form'] === 'Capsule' ? 'selected' : ''; ?>>Capsule</option>
                                <option value="Syrup" <?php echo isset($medication) && $medication['form'] === 'Syrup' ? 'selected' : ''; ?>>Syrup</option>
                                <option value="Injection" <?php echo isset($medication) && $medication['form'] === 'Injection' ? 'selected' : ''; ?>>Injection</option>
                                <option value="Cream" <?php echo isset($medication) && $medication['form'] === 'Cream' ? 'selected' : ''; ?>>Cream</option>
                                <option value="Ointment" <?php echo isset($medication) && $medication['form'] === 'Ointment' ? 'selected' : ''; ?>>Ointment</option>
                                <option value="Drops" <?php echo isset($medication) && $medication['form'] === 'Drops' ? 'selected' : ''; ?>>Drops</option>
                                <option value="Inhaler" <?php echo isset($medication) && $medication['form'] === 'Inhaler' ? 'selected' : ''; ?>>Inhaler</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($medication) ? htmlspecialchars($medication['description']) : ''; ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="?action=list" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $action === 'add' ? 'Add Medication' : 'Update Medication'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($action === 'view' && $medication): ?>
        <!-- View Medication Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Medication Details</h6>
                <div>
                    <a href="?action=edit&id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="?action=delete&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Are you sure you want to delete this medication?');">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Medication Name:</strong> <?php echo htmlspecialchars($medication['name']); ?></p>
                        <p><strong>Generic Name:</strong> <?php echo !empty($medication['generic_name']) ? htmlspecialchars($medication['generic_name']) : 'N/A'; ?></p>
                        <p><strong>Dosage:</strong> <?php echo htmlspecialchars($medication['dosage']); ?></p>
                        <p><strong>Form:</strong> <?php echo htmlspecialchars($medication['form']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($medication['created_at'])); ?></p>
                        <p><strong>Updated At:</strong> <?php echo date('M d, Y H:i', strtotime($medication['updated_at'])); ?></p>
                        <!-- View link to see how it appears to users -->
                        <p><strong>View on site:</strong> 
                            <a href="<?php echo APP_URL; ?>/user/search.php?q=<?php echo urlencode($medication['name']); ?>" 
                               target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i> View as User
                            </a>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($medication['description'])): ?>
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p><?php echo nl2br(htmlspecialchars($medication['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Price Information -->
                <?php
                $priceResult = $db->query("
                    SELECT mp.*, p.name as pharmacy_name
                    FROM medication_prices mp
                    JOIN pharmacies p ON mp.pharmacy_id = p.id
                    WHERE mp.medication_id = $id
                    ORDER BY mp.price ASC
                ");
                
                $prices = [];
                if ($priceResult) {
                    while ($row = $priceResult->fetch_assoc()) {
                        $prices[] = $row;
                    }
                }
                ?>
                
                <div class="mt-4">
                    <h6>Price Information:</h6>
                    <?php if (empty($prices)): ?>
                        <p class="text-muted">No price information available for this medication.</p>
                        <a href="prices.php?action=add&medication_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Price
                        </a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Pharmacy</th>
                                        <th>Price</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prices as $price): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($price['pharmacy_name']); ?></td>
                                            <td>PHP <?php echo number_format($price['price'], 2); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($price['updated_at'])); ?></td>
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
                        <a href="prices.php?action=add&medication_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Price
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer">
                <a href="?action=list" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    <?php else: ?>
        <!-- List All Medications -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Medications</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                            <?php if (empty($medications)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No medications found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medications as $med): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                                        <td><?php echo !empty($med['generic_name']) ? htmlspecialchars($med['generic_name']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($med['form']); ?></td>
                                        <td>
                                            <a href="?action=view&id=<?php echo $med['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?php echo $med['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $med['id']; ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this medication?');">
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
