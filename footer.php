
<!-- footer section -->
<footer class="footer mt-5 py-4 text-white" style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d);">
    <div class="container">
        <div class="row">

            <!-- brand info -->
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold">
                    <i class="bi bi-shop me-2" style="color:#e8813a;"></i>FoodieHub
                </h5>
                <p class="text-muted small">
                    A complete restaurant management system built with PHP and MySQL for university project.
                </p>
            </div>

            <!-- quick links -->
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold mb-3" style="color:#e8813a;">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="../customer/index.php" class="text-muted text-decoration-none"><i class="bi bi-chevron-right"></i> Home</a></li>
                    <li><a href="../customer/menu.php" class="text-muted text-decoration-none"><i class="bi bi-chevron-right"></i> Menu</a></li>
                    <li><a href="../customer/cart.php" class="text-muted text-decoration-none"><i class="bi bi-chevron-right"></i> Cart</a></li>
                    <li><a href="../auth/login.php" class="text-muted text-decoration-none"><i class="bi bi-chevron-right"></i> Admin Panel</a></li>
                </ul>
            </div>

            <!-- contact -->
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold mb-3" style="color:#e8813a;">Contact</h6>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-geo-alt me-2"></i>Lahore, Pakistan</li>
                    <li><i class="bi bi-telephone me-2"></i>0300-1234567</li>
                    <li><i class="bi bi-envelope me-2"></i>info@foodiehub.com</li>
                    <li><i class="bi bi-clock me-2"></i>Open: 11am - 11pm</li>
                </ul>
            </div>

        </div>

        <hr style="border-color:#444;">

        <div class="text-center small text-muted">
            <p class="mb-0">
                &copy; 2025 FoodieHub Restaurant Management System. University Project.
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo isset($base_url) ? $base_url : '../'; ?>js/script.js"></script>

</body>
</html>
