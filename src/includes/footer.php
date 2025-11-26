<?php
// src/includes/footer.php
?>

<footer class="bg-dark text-light py-4 mt-5">
  <div class="container">
    <!-- Contact Info - Centered Above Buttons -->
    <div class="row mb-3">
      <div class="col-12 text-center">
        <div class="contact-info">
          <p class="mb-2">
            <i class="fas fa-phone-alt me-2"></i>+254 799 692 055
          </p>
          <p class="mb-3">
            <i class="fas fa-envelope me-2"></i>lewinskyvictoria45@gmail.com
          </p>
        </div>
      </div>
    </div>

    <!-- Social Media Buttons - Centered Below Contact Info -->
    <div class="row mb-4">
      <div class="col-12 text-center">
        <div class="social-links">
          <!-- WhatsApp -->
          <a href="https://wa.me/254712345678" class="btn btn-success btn-sm mx-1 mb-2" target="_blank" title="Contact on WhatsApp">
            <i class="fab fa-whatsapp"></i> WhatsApp
          </a>
          
          <!-- GitHub -->
          <a href="https://github.com/vickielewinsky" class="btn btn-outline-light btn-sm mx-1 mb-2" target="_blank" title="View GitHub Profile">
            <i class="fab fa-github"></i> GitHub
          </a>
          
          <!-- LinkedIn -->
          <a href="https://www.linkedin.com/in/vickie-lewinsky-038474291" class="btn btn-primary btn-sm mx-1 mb-2" target="_blank" title="View LinkedIn Profile">
            <i class="fab fa-linkedin"></i> LinkedIn
          </a>
          
          <!-- Gmail -->
          <a href="mailto:lewinskyvictoria45@gmail.com" class="btn btn-danger btn-sm mx-1 mb-2" title="Send Email">
            <i class="fas fa-envelope"></i> Gmail
          </a>
        </div>
      </div>
    </div>

    <!-- Copyright Info - Centered Below Buttons -->
    <div class="row">
      <div class="col-12 text-center">
        <p class="mb-1">&copy; <?php echo date('Y'); ?> <strong>Speedy Wheels Car Rental System</strong></p>
        <p class="small mb-2">Developed by <strong>Lewinsky Victoria Wesonga</strong> | Technical University of Mombasa</p>
      </div>
    </div>

    <!-- Quick Links - Centered at Bottom -->
    <div class="row mt-3">
      <div class="col-12 text-center">
        <div class="quick-links">
          <a href="<?php echo base_url('index.php'); ?>" class="text-muted small mx-2">Home</a>
          <a href="<?php echo base_url('src/modules/vehicles/index.php'); ?>" class="text-muted small mx-2">Vehicles</a>
          <a href="<?php echo base_url('src/modules/bookings/index.php'); ?>" class="text-muted small mx-2">Bookings</a>
          <a href="<?php echo base_url('src/modules/auth/login.php'); ?>" class="text-muted small mx-2">Login</a>
          <a href="<?php echo base_url('src/modules/auth/register.php'); ?>" class="text-muted small mx-2">Register</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Add scroll effect to navbar
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Mobile menu close on click
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        if (navbarCollapse.classList.contains('show')) {
            navbarToggler.click();
        }
    });
});

// Social media button hover effects
document.querySelectorAll('.social-links a').forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.transition = 'all 0.3s ease';
    });
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

</body>
</html>