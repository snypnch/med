<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer via Composer
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json'); // ensure JSON response

$db = Database::getInstance();

// 1. Delete old admin user
$db->query("DELETE FROM users WHERE username = 'admin'");

// 2. Generate random secure password
function generateRandomPassword($length = 12) {
    return bin2hex(random_bytes($length / 2)); 
}

$adminUsername   = 'admin';
$adminPassword   = generateRandomPassword(12);
$adminEmail      = 'mmedcompare@gmail.com';
$hashedPassword  = password_hash($adminPassword, PASSWORD_DEFAULT);

// 3. Insert fresh admin account
$result = $db->query("INSERT INTO users (username, password, email, role) 
                      VALUES ('$adminUsername', '$hashedPassword', '$adminEmail', 'admin')");

if ($result) {
    // Log credentials to file
    $logFile = __DIR__ . "/admin_reset.log"; 
    $logEntry = "[" . date("Y-m-d H:i:s") . "] Username: {$adminUsername}, Password: {$adminPassword}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // 4. Setup PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mandauemedcompare@gmail.com';   // Gmail address
        $mail->Password   = 'ifedftapkhwftgrg';    
        // $mail->Password   = 'yubgdjkgpsfudatb';          // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('jonelreyes290@gmail.com', 'MedCompare System');
        $mail->addAddress($adminEmail, 'Admin');

        $mail->isHTML(true);
        $mail->Subject = 'Admin Account Reset';
        $mail->Body    = "
            <h3>Admin account has been reset successfully.</h3>
            <p><b>Username:</b> {$adminUsername}</p>
            <p><b>Password:</b> {$adminPassword}</p>
            <br>
            <a href='http://localhost/med/index.php'>Go to login page</a>
        ";

        $mail->send();

        echo "success, sent to {$adminEmail}";
        
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Email could not be sent. Error: {$mail->ErrorInfo}. Credentials logged in admin_reset.log"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: failed to reset admin account."
    ]);
}
