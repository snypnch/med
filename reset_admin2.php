<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

require '../vendor/autoload.php'; // PHPMailer installed via Composer
require_once '../includes/config.php';
require_once '../includes/db.php';

$db = Database::getInstance();

// Delete admin user if it exists
$db->query("DELETE FROM users WHERE username = 'admin'");

// Generate random secure password
function generateRandomPassword($length = 12) {
    return bin2hex(random_bytes($length / 2)); 
    // Example output: "a4c9e2f8b3d1"
}

$adminUsername = 'admin';
$adminPassword = generateRandomPassword(12); // random 12-character password
$adminEmail    = 'jneil_024@hotmail.com';
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Insert fresh admin account
$result = $db->query("INSERT INTO users (username, password, email, role) 
                      VALUES ('$adminUsername', '$hashedPassword', '$adminEmail', 'admin')");

if ($result) {
    // ✅ Log credentials to a local file (server-side only)
    $logFile = __DIR__ . "/admin_reset.log"; 
    $logEntry = "[" . date("Y-m-d H:i:s") . "] Username: {$adminUsername}, Password: {$adminPassword}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Setup PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';   // Change if using another SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jonelreyes290@gmail.com';   // Replace with your sender email
        $mail->Password   = 'yubgdjkgpsfudatb';      // Use app password, not real one
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('jneil_024@hotmail.com', 'MedCompare System');
        $mail->addAddress($adminEmail, 'Admin');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Admin Account Reset';
        $mail->Body    = "
            <h3>Admin account has been reset successfully.</h3>
            <p><b>Username:</b> {$adminUsername}</p>
            <p><b>Password:</b> {$adminPassword}</p>
            <br>
            <a href='http://yourdomain.com/index.php'>Go to login page</a>
        ";

        $mail->send();
        echo "✅ Admin account has been reset. New login details sent to {$adminEmail}.";
    } catch (Exception $e) {
        echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}<br>";
        echo "⚠️ Check admin_reset.log for the credentials.";
    }
} else {
    echo "❌ Error resetting admin account. Please check database connection.";
}
?>
