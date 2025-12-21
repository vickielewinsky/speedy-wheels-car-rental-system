<?php
// contact.php - FULL UPDATED VERSION WITH SELF-CONTAINED NAVBAR AND CONTACT PAGE
require_once __DIR__ . '/src/config/config.php';

$page_title = "Contact Us - Speedy Wheels";

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $success = true;
        $log = date('Y-m-d H:i:s') . " | $name | $email | $subject\n";
        @file_put_contents(__DIR__ . '/contact_log.txt', $log, FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $page_title; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
/* Navbar Styles */
.navbar {
    background: linear-gradient(90deg, #667eea, #764ba2);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.navbar .nav-link {
    color: white !important;
    font-weight: 500;
    margin: 0 10px;
    transition: all 0.3s ease;
}
.navbar .nav-link:hover, .navbar .nav-link.active {
    color: #ffdd57 !important;
    text-shadow: 0 0 5px rgba(255,255,255,0.7);
}
.navbar-brand {
    font-weight: bold;
    font-size: 1.5rem;
    color: white !important;
    text-shadow: 0 0 5px rgba(0,0,0,0.3);
}

/* Hero Banner */
.contact-hero {
    position: relative;
    background: linear-gradient(rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8)),
                url('<?php echo base_url('src/assets/images/hero-car.png'); ?>') center/cover no-repeat;
    height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.contact-hero h1 {
    font-size: 3rem;
    font-weight: bold;
}
.contact-hero p {
    font-size: 1.2rem;
}

/* Contact Page Styles */
.contact-icon { flex-shrink:0; }
.social-icon.facebook { background: #1877F2; color:white; }
.social-icon.twitter { background: #1DA1F2; color:white; }
.social-icon.instagram { background: linear-gradient(45deg,#405DE6,#5851DB,#833AB4,#C13584,#E1306C,#FD1D1D); color:white; }
.social-icon.whatsapp { background:#25D366; color:white; }
.social-icon:hover { transform: translateY(-3px); transition: 0.3s; }
.contact-info-item:hover .contact-icon { transform: scale(1.1); transition: 0.3s; }
.form-floating>label { padding-left:2.5rem; }
.form-floating>.form-control { padding-left:2.5rem; }
.form-floating>.form-control:focus~label { color:#667eea; }
.accordion-button:not(.collapsed) { background-color: rgba(102,126,234,0.1); color:#667eea; box-shadow:none; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
<div class="container">
    <a class="navbar-brand" href="<?php echo base_url('index.php'); ?>">Speedy Wheels</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="<?php echo base_url('index.php'); ?>">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo base_url('src/modules/vehicles/index.php'); ?>">Vehicles</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?php echo base_url('contact.php'); ?>">Contact</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>">Book Now</a></li>
        </ul>
    </div>
</div>
</nav>

<!-- HERO -->
<div class="contact-hero text-center">
    <div>
        <h1>Get in Touch</h1>
        <p>We're here to help with your car rental needs</p>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="container py-5">
<div class="row g-5">

<!-- Left: Contact Form -->
<div class="col-lg-7">
<div class="card shadow-lg border-0">
<div class="card-body p-5">
<h2 class="mb-4 text-primary"><i class="fas fa-paper-plane me-2"></i>Send Us a Message</h2>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>Thank you, <?php echo htmlspecialchars($name); ?>! Your message has been received.
    <?php if($phone): ?> We'll contact you at <?php echo htmlspecialchars($phone); ?><?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php elseif($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST">
<div class="row">
<div class="col-md-6 mb-4">
<div class="form-floating">
<input type="text" class="form-control" name="name" placeholder="Your Name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
<label><i class="fas fa-user me-2"></i>Full Name</label>
</div>
</div>
<div class="col-md-6 mb-4">
<div class="form-floating">
<input type="email" class="form-control" name="email" placeholder="name@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
<label><i class="fas fa-envelope me-2"></i>Email Address</label>
</div>
</div>
</div>
<div class="mb-4">
<div class="form-floating">
<input type="text" class="form-control" name="subject" placeholder="Subject" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
<label><i class="fas fa-tag me-2"></i>Subject</label>
</div>
</div>
<div class="mb-4">
<div class="form-floating">
<textarea class="form-control" name="message" placeholder="Your Message" style="height:150px" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
<label><i class="fas fa-comment-dots me-2"></i>Your Message</label>
</div>
</div>
<div class="mb-4">
<div class="form-floating">
<input type="tel" class="form-control" name="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
<label><i class="fas fa-phone me-2"></i>Phone Number (Optional)</label>
</div>
</div>
<div class="d-grid">
<button type="submit" class="btn btn-primary btn-lg py-3"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
</div>
</form>
</div>
</div>
</div>

<!-- Right: Contact Info & Map -->
<div class="col-lg-5">

<!-- Contact Info -->
<div class="card shadow-sm border-0 mb-4">
<div class="card-body p-4">
<h3 class="text-primary mb-4"><i class="fas fa-info-circle me-2"></i>Contact Information</h3>
<div class="contact-info-item d-flex mb-4">
<div class="contact-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;"><i class="fas fa-map-marker-alt"></i></div>
<div>
<h5 class="fw-bold">Our Location</h5>
<p class="mb-0 text-muted">Mombasa Road, Next to Shell Station<br>Mombasa, Kenya</p>
</div>
</div>
<div class="contact-info-item d-flex mb-4">
<div class="contact-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;"><i class="fas fa-phone"></i></div>
<div>
<h5 class="fw-bold">Call Us</h5>
<p class="mb-0">
<a href="tel:+254799692055" class="text-decoration-none">+254 799 692 055</a><br>
<a href="tel:+254750515354" class="text-decoration-none">+254 750 515 354</a>
</p>
</div>
</div>
<div class="contact-info-item d-flex mb-4">
<div class="contact-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;"><i class="fas fa-envelope"></i></div>
<div>
<h5 class="fw-bold">Email Us</h5>
<p class="mb-0">
<a href="mailto:lewinskyvictoria45@gmail.com" class="text-decoration-none">lewinskyvictoria45@gmail.com</a><br>
<a href="mailto:vickielewinsky@gmail.com" class="text-decoration-none">vickielewinsky@gmail.com</a>
</p>
</div>
</div>
<div class="contact-info-item d-flex">
<div class="contact-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:50px;height:50px;"><i class="fas fa-clock"></i></div>
<div>
<h5 class="fw-bold">Business Hours</h5>
<p class="mb-0 text-muted"><strong>Mon - Fri:</strong> 8:00 AM - 8:00 PM<br><strong>Sat - Sun:</strong> 9:00 AM - 6:00 PM<br><strong>Emergency:</strong> 24/7 Available</p>
</div>
</div>
</div>
</div>

<!-- Social Media -->
<div class="card shadow-sm border-0 mb-4">
<div class="card-body p-4">
<h3 class="text-primary mb-4"><i class="fas fa-share-alt me-2"></i>Follow Us</h3>
<div class="d-flex justify-content-center gap-3">
<a href="#" class="social-icon facebook d-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width:50px;height:50px;"><i class="fab fa-facebook-f"></i></a>
<a href="#" class="social-icon twitter d-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width:50px;height:50px;"><i class="fab fa-twitter"></i></a>
<a href="#" class="social-icon instagram d-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width:50px;height:50px;"><i class="fab fa-instagram"></i></a>
<a href="#" class="social-icon whatsapp d-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width:50px;height:50px;"><i class="fab fa-whatsapp"></i></a>
</div>
</div>
</div>

<!-- Quick Actions -->
<div class="d-grid gap-2">
<a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-primary"><i class="fas fa-home me-2"></i>Back to Home</a>
<a href="<?php echo base_url('src/modules/bookings/create_booking.php'); ?>" class="btn btn-success"><i class="fas fa-calendar-plus me-2"></i>Book a Car Now</a>
</div>

</div>
</div>

<!-- FAQ Section -->
<div class="row mt-5">
<div class="col-12">
<div class="card shadow border-0">
<div class="card-body p-5">
<h2 class="text-center mb-4 text-primary"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h2>
<div class="accordion" id="faqAccordion">
<div class="accordion-item mb-3 border-0">
<h3 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
<i class="fas fa-car me-2 text-primary"></i>What documents do I need to rent a car?
</button>
</h3>
<div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">You'll need a valid driver's license, national ID or passport, and a credit/debit card for the security deposit.</div>
</div>
</div>
<div class="accordion-item mb-3 border-0">
<h3 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
<i class="fas fa-clock me-2 text-primary"></i>What are your operating hours?
</button>
</h3>
<div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">We're open Monday to Friday from 8:00 AM to 8:00 PM, and weekends from 9:00 AM to 6:00 PM. Emergency assistance is available 24/7.</div>
</div>
</div>
<div class="accordion-item border-0">
<h3 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
<i class="fas fa-money-bill-wave me-2 text-primary"></i>What payment methods do you accept?
</button>
</h3>
<div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">We accept MPESA, credit/debit cards (Visa, MasterCard), and cash payments at our office.</div>
</div>
</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
