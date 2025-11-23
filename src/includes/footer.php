<?php
// src/includes/footer.php
?>
</main>

<footer class="bg-dark text-light py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; <?php echo date('Y'); ?> <strong>Speedy Wheels Car Rental System</strong></p>
    <p class="small mb-2">Developed by <strong>Lewinsky Victoria Wesonga</strong> | Technical University of Mombasa</p>
    <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-light btn-sm"><i class="fas fa-home"></i> Home</a>
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
</script>

</body>
</html>