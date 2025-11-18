</main>
    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase"><?php echo APP_NAME; ?></h5>
                    <p>
                        Helping residents of Mandaue City find the best prices for their medications.
                        Compare prices across local pharmacies easily and quickly.
                    </p>
                </div>
                <div class="col-lg-6 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Links</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="<?php echo APP_URL; ?>/user/about.php" class="text-dark">About Us</a></li>
                        <li><a href="<?php echo APP_URL; ?>/user/contact.php" class="text-dark">Contact Us</a></li>
                        <li><a href="<?php echo APP_URL; ?>/user/disclaimer.php" class="text-dark">Disclaimer</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?php echo date('Y'); ?> <?php echo APP_NAME; ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo JS_PATH; ?>/scripts.js"></script>
</body>
</html>
