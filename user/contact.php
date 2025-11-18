<?php
$pageTitle = "Contact Us";
require_once 'includes/functions.php';
include_once 'includes/header.php';

$message = '';
$messageType = '';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $messageContent = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if (empty($messageContent)) {
        $errors[] = "Message is required.";
    }
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For this prototype, we'll just show a success message
        
        $message = "Thank you for contacting us! We will get back to you soon.";
        $messageType = "success";
        
        // Clear form fields
        $name = $email = $subject = $messageContent = '';
    } else {
        $message = implode("<br>", $errors);
        $messageType = "danger";
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="mb-4">Contact Us</h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-5">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Get in Touch</h2>
                    <p class="mb-4">We value your feedback, questions, and suggestions. Please use the form below to get in touch with our team.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($messageContent) ? htmlspecialchars($messageContent) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Contact Information</h2>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h5>Address</h5>
                            <p>
                                Mandaue MedCompare<br>
                                123 A.S. Fortuna Street<br>
                                Mandaue City, Cebu<br>
                                Philippines 6014
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Contact Details</h5>
                            <p>
                                <strong>Email:</strong> info@mandauemedcompare.ph<br>
                                <strong>Phone:</strong> (032) 123-4567<br>
                                <strong>Working Hours:</strong> Monday to Friday, 9:00 AM - 5:00 PM
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Follow Us</h5>
                        <div class="d-flex">
                            <a href="#" class="me-3 text-decoration-none">
                                <i class="fab fa-facebook fa-2x text-primary"></i>
                            </a>
                            <a href="#" class="me-3 text-decoration-none">
                                <i class="fab fa-twitter fa-2x text-info"></i>
                            </a>
                            <a href="#" class="me-3 text-decoration-none">
                                <i class="fab fa-instagram fa-2x text-danger"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
