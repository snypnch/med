<?php
$pageTitle = "Find Affordable Medications";
require_once 'includes/functions.php';
include_once 'includes/header.php';

// Get popular medications for quick links
$popularMedications = getPopularMedications();
?>

<!-- <div class="container py-5"> -->
    <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
        <!-- <div class="row justify-content-center mb-4">
            <div class="col-md-10">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Create an account or log in</strong> to save your favorite medications and get personalized
                        recommendations.
                    </div>
                    <div>
                        <div class="d-flex">
                            <a href="<?php echo APP_URL; ?>/user/login.php" class="btn btn-primary me-2">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                            <a href="<?php echo APP_URL; ?>/user/login.php#register" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div> -->
    <?php endif; ?>

    <div class="row justify-content-center mb-5">
        <div class="col-md-10 text-center">
            <h1 class="display-4 fw-bold text-primary">Mandaue MedCompare</h1>
            <p class="lead">Find the best prices for medications across pharmacies in Mandaue City</p>

            <div class="card shadow mt-4">
                <div class="card-body p-4">
                    <form action="search.php" method="GET" class="search-form">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="q"
                                placeholder="Enter medication name (min. 3 characters)" required minlength="3">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-2"></i> Compare Prices
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="manual_search.php" class="btn btn-link text-decoration-none">
                            <i class="fas fa-sliders-h me-1"></i> Advanced Search Options
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Popular Medications</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($popularMedications as $medication): ?>
                    <div class="col">
                        <div class="card h-100 hover-shadow">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo sanitize($medication['name']); ?></h5>
                                <p class="card-text">
                                    <?php echo sanitize($medication['dosage'] . ' ' . $medication['form']); ?>
                                    <?php if (!empty($medication['generic_name'])): ?>
                                        <br><small class="text-muted">Generic:
                                            <?php echo sanitize($medication['generic_name']); ?></small>
                                    <?php endif; ?>
                                </p>
                                <a href="search.php?q=<?php echo urlencode($medication['name']); ?>"
                                    class="btn btn-outline-primary">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Newly Added Medications Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Newly Added Medications</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach (getNewlyAddedMedications() as $medication): ?>
                    <div class="col">
                        <div class="card h-100 hover-shadow border-primary">
                            <div class="card-header bg-primary text-white">
                                <span class="badge bg-light text-primary float-end">New</span>
                                <h5 class="card-title mb-0"><?php echo sanitize($medication['name']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <?php echo sanitize($medication['dosage'] . ' ' . $medication['form']); ?>
                                    <?php if (!empty($medication['generic_name'])): ?>
                                        <br><small class="text-muted">Generic:
                                            <?php echo sanitize($medication['generic_name']); ?></small>
                                    <?php endif; ?>
                                </p>
                                <a href="search.php?q=<?php echo urlencode($medication['name']); ?>"
                                    class="btn btn-outline-primary">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">How It Works</h2>
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="rounded-circle bg-primary text-white mx-auto mb-4 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-search fa-2x"></i>
                            </div>
                            <h4>1. Search</h4>
                            <p>Enter the name of your medication in the search box</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="rounded-circle bg-primary text-white mx-auto mb-4 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-balance-scale fa-2x"></i>
                            </div>
                            <h4>2. Compare</h4>
                            <p>View and compare prices from different pharmacies in Mandaue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="rounded-circle bg-primary text-white mx-auto mb-4 d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                            <h4>3. Find</h4>
                            <p>Get pharmacy details and directions to purchase your medication</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Categories</h2>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-2">
                <?php foreach (getMedicationCategories() as $category): ?>
                    <div class="col">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo sanitize($category); ?></h6>
                                <a href="search.php?category=<?php echo urlencode($category); ?>"
                                    class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>