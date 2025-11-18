<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/utilities.php';

// Get all medications
function getAllMedications() {
    $db = Database::getInstance();
    $result = $db->query("SELECT * FROM medications ORDER BY name");
    
    $medications = [];
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
    
    return $medications;
}

// Search medications by name
function searchMedications($term) {
    $db = Database::getInstance();
    $term = $db->escape($term);
    
    // Use a more flexible search to find medications
    $sql = "SELECT * FROM medications 
            WHERE name LIKE '%$term%' 
            OR generic_name LIKE '%$term%' 
            OR SOUNDEX(name) = SOUNDEX('$term')
            OR SOUNDEX(generic_name) = SOUNDEX('$term')
            ORDER BY 
                CASE 
                    WHEN name LIKE '$term%' THEN 1
                    WHEN generic_name LIKE '$term%' THEN 2
                    WHEN name LIKE '%$term%' THEN 3
                    WHEN generic_name LIKE '%$term%' THEN 4
                    ELSE 5
                END";
    
    $result = $db->query($sql);
    
    $medications = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $medications[] = $row;
        }
    }
    
    return $medications;
}

// Get medication by ID
function getMedicationById($id) {
    $db = Database::getInstance();
    $id = (int)$id;
    
    $result = $db->query("SELECT * FROM medications WHERE id = $id");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get popular medications (most often searched or viewed)
 */
function getPopularMedications($limit = 5) {
    $db = Database::getInstance();
    
    // Get recently added medications first, then fall back to basic query
    $result = $db->query("
        SELECT m.* FROM medications m
        LEFT JOIN medication_prices mp ON m.id = mp.medication_id
        GROUP BY m.id
        ORDER BY m.created_at DESC, COUNT(mp.id) DESC
        LIMIT $limit
    ");
    
    $medications = [];
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
    
    return $medications;
}

// Get all pharmacies
function getAllPharmacies() {
    $db = Database::getInstance();
    $result = $db->query("SELECT * FROM pharmacies ORDER BY name");
    
    $pharmacies = [];
    while ($row = $result->fetch_assoc()) {
        $pharmacies[] = $row;
    }
    
    return $pharmacies;
}

// Get pharmacy by ID
function getPharmacyById($id) {
    $db = Database::getInstance();
    $id = (int)$id;
    
    $result = $db->query("SELECT * FROM pharmacies WHERE id = $id");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get prices for a specific medication
function getMedicationPrices($medicationId) {
    $db = Database::getInstance();
    $medicationId = (int)$medicationId;
    
    // Check if medication exists first
    $medCheck = $db->query("SELECT id FROM medications WHERE id = $medicationId");
    if (!$medCheck || $medCheck->num_rows == 0) {
        return [];
    }
    
    $sql = "SELECT mp.*, p.name as pharmacy_name, p.address, p.contact, p.latitude, p.longitude, mp.pharmacy_id 
            FROM medication_prices mp 
            JOIN pharmacies p ON mp.pharmacy_id = p.id 
            WHERE mp.medication_id = $medicationId 
            ORDER BY mp.price";
    
    $result = $db->query($sql);
    
    $prices = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Ensure price is properly formatted as a float
            $row['price'] = (float)$row['price'];
            $prices[] = $row;
        }
    }
    
    return $prices;
}

/**
 * Get newly added medications
 */
function getNewlyAddedMedications($limit = 3) {
    $db = Database::getInstance();
    $result = $db->query("
        SELECT m.*, COUNT(mp.id) as price_count 
        FROM medications m
        LEFT JOIN medication_prices mp ON m.id = mp.medication_id
        GROUP BY m.id
        HAVING price_count > 0
        ORDER BY m.created_at DESC
        LIMIT $limit
    ");
    
    $medications = [];
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
    
    return $medications;
}

/**
 * Get medication categories
 */
function getMedicationCategories() {
    return [
        'Pain Relievers',
        'Antibiotics',
        'Vitamins',
        'Gastrointestinal',
        'Allergy',
        'Heart & Blood Pressure'
    ];
}

/**
 * Log user viewing a medication
 */
function logMedicationView($medicationId) {
    // Only log if user is logged in
    if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    return logUserActivity($userId, 'view_medication', "Viewed medication #$medicationId");
}

/**
 * Log user activity
 */
function logUserActivity($userId, $action, $details = '') {
    $db = Database::getInstance();
    $data = [
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ];
    
    return $db->insert('activity_logs', $data);
}
?>
