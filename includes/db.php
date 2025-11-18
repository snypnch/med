<?php
require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset('utf8');
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        
        $escapedFields = array_map(function($field) {
            return "`" . $field . "`";
        }, $fields);
        
        $escapedValues = array_map(function($value) {
            if ($value === NULL) return "NULL";
            return "'" . $this->escape($value) . "'";
        }, $values);
        
        $sql = "INSERT INTO `" . $table . "` (" . implode(", ", $escapedFields) . ") VALUES (" . implode(", ", $escapedValues) . ")";
        
        if ($this->query($sql)) {
            return $this->connection->insert_id;
        }
        
        return false;
    }
    
    public function update($table, $data, $where) {
        $setParts = [];
        foreach ($data as $field => $value) {
            if ($value === NULL) {
                $setParts[] = "`" . $field . "` = NULL";
            } else {
                $setParts[] = "`" . $field . "` = '" . $this->escape($value) . "'";
            }
        }
        
        $sql = "UPDATE `" . $table . "` SET " . implode(", ", $setParts) . " WHERE " . $where;
        
        return $this->query($sql);
    }
    
    public function delete($table, $where) {
        $sql = "DELETE FROM `" . $table . "` WHERE " . $where;
        return $this->query($sql);
    }
}

// Create database tables if they don't exist
function ensureDatabaseSetup() {
    $db = Database::getInstance();
    
    // Create medications table
    $db->query("CREATE TABLE IF NOT EXISTS `medications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `generic_name` varchar(255) DEFAULT NULL,
        `dosage` varchar(100) NOT NULL,
        `form` varchar(100) NOT NULL,
        `description` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create pharmacies table
    $db->query("CREATE TABLE IF NOT EXISTS `pharmacies` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `address` text NOT NULL,
        `contact` varchar(100) DEFAULT NULL,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create medication_prices table
    $db->query("CREATE TABLE IF NOT EXISTS `medication_prices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `medication_id` int(11) NOT NULL,
        `pharmacy_id` int(11) NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `medication_id` (`medication_id`),
        KEY `pharmacy_id` (`pharmacy_id`),
        CONSTRAINT `medication_prices_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
        CONSTRAINT `medication_prices_ibfk_2` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create users table for admin access
    $db->query("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `email` varchar(100) NOT NULL,
        `role` enum('admin') NOT NULL DEFAULT 'admin',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create activity_logs table
    $db->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(50) NOT NULL,
        `details` text,
        `ip_address` varchar(50) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create regular users table
    $db->query("CREATE TABLE IF NOT EXISTS `regular_users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `email` varchar(100) NOT NULL,
        `first_name` varchar(50) DEFAULT NULL,
        `last_name` varchar(50) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Create favorites table for users to save medications
    $db->query("CREATE TABLE IF NOT EXISTS `favorites` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `medication_id` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `medication_id` (`medication_id`),
        CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `regular_users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Insert default admin user if not exists
    $result = $db->query("SELECT COUNT(*) as count FROM `users` WHERE username = 'admin'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert default admin with password 'admin123'
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query("INSERT INTO `users` (username, password, email, role) 
                   VALUES ('admin', '$hashedPassword', 
                   'admin@medcompare.ph', 'admin')");
    }
    
    // Insert a sample regular user if none exists
    $result = $db->query("SELECT COUNT(*) as count FROM `regular_users`");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert default user with password 'user123'
        $db->query("INSERT INTO `regular_users` (username, password, email, first_name, last_name) 
                   VALUES ('user', '" . password_hash('user123', PASSWORD_DEFAULT) . "', 
                   'user@medcompare.ph', 'Demo', 'User')");
    }
    
    // Insert some sample data if tables are empty
    $result = $db->query("SELECT COUNT(*) as count FROM `medications`");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert sample medications
        $db->query("INSERT INTO `medications` (name, generic_name, dosage, form, description) VALUES 
            ('Paracetamol', 'Acetaminophen', '500mg', 'Tablet', 'Used to treat mild to moderate pain and reduce fever'),
            ('Biogesic', 'Paracetamol', '500mg', 'Tablet', 'Brand name for Paracetamol'),
            ('Amoxicillin', 'Amoxicillin', '500mg', 'Capsule', 'Antibiotic used to treat bacterial infections'),
            ('Alaxan FR', 'Ibuprofen + Paracetamol', '200mg/325mg', 'Tablet', 'Pain reliever and anti-inflammatory')");
            
        // Insert sample pharmacies
        $db->query("INSERT INTO `pharmacies` (name, address, contact, latitude, longitude) VALUES 
            ('Rose Pharmacy', 'Downtown Mandaue, Cebu', '032-123-4567', 10.3212, 123.9335),
            ('Mercury Drug', 'Mandaue City Public Market', '032-234-5678', 10.3287, 123.9425),
            ('Generics Pharmacy', 'Banilad, Mandaue City', '032-345-6789', 10.3354, 123.9210),
            ('Watsons Pharmacy', 'Pacific Mall, Mandaue City', '032-456-7890', 10.3176, 123.9300)");
            
        // Insert sample prices
        $db->query("INSERT INTO `medication_prices` (medication_id, pharmacy_id, price) VALUES 
            (1, 1, 15.00), (1, 2, 12.50), (1, 3, 10.00), (1, 4, 14.75),
            (2, 1, 22.50), (2, 2, 20.00), (2, 3, 18.75), (2, 4, 21.25),
            (3, 1, 45.00), (3, 2, 42.50), (3, 3, 40.00), (3, 4, 44.75),
            (4, 1, 35.25), (4, 2, 32.50), (4, 3, 30.00), (4, 4, 33.75)");
    }
}

// Call the setup function to ensure the database is properly initialized
ensureDatabaseSetup();
?>
