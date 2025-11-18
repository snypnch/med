<?php
require_once 'includes/functions.php';

// Get search query
$searchQuery = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Prevent very short searches that would return too many results
if (strlen($searchQuery) < 3 && empty($category)) {
    $searchTooShort = true;
    $medications = [];
    $pageTitle = "Search";
} else {
    $searchTooShort = false;

    // Sort options
    $sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'price_low';

    // Filter options
    $selectedTypes = isset($_GET['type']) ? $_GET['type'] : ['brand', 'generic'];
    $selectedPharmacies = isset($_GET['pharmacy']) ? array_map('intval', $_GET['pharmacy']) : [];
    $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 100;

    // If we have a search query, search for medications
    if (!empty($searchQuery)) {
        $medications = searchMedications($searchQuery);
        $pageTitle = "Results for: " . $searchQuery;
    } elseif (!empty($category)) {
        // In a real app, we'd search by category in the database
        // For this prototype, we're simplifying
        $medications = getAllMedications();
        $pageTitle = "Category: " . $category;
    } else {
        // If no search term or category, show all medications
        $medications = getAllMedications();
        $pageTitle = "All Medications";
    }
}

// Get the pharmacies for filtering
$pharmacies = getAllPharmacies();

include_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search Results</li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <!-- Enhanced Search Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="search.php" method="GET" class="d-flex flex-column flex-md-row gap-2">
                <input type="text" class="form-control" name="q" placeholder="Enter medication name (min. 3 characters)"
                    value="<?php echo htmlspecialchars($searchQuery); ?>" required minlength="3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                <a href="manual_search.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sliders-h me-1"></i> Advanced Search
                </a>
            </form>
        </div>
    </div>

    <?php if ($searchTooShort): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> Please enter at least 3 characters to search for medications.
            <p class="mt-2 mb-0">
                Try our <a href="manual_search.php" class="alert-link">Advanced Search</a> for more options.
            </p>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form action="search.php" method="GET" id="filterForm">
                            <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="q" value="<?php echo $searchQuery; ?>">
                            <?php endif; ?>
                            <?php if (!empty($category)): ?>
                                <input type="hidden" name="category" value="<?php echo $category; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Sort By</label>
                                <select name="sort" class="form-select"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Lowest
                                        Price</option>
                                    <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>
                                        Highest Price</option>
                                    <option value="pharmacy" <?php echo $sortBy == 'pharmacy' ? 'selected' : ''; ?>>Pharmacy
                                        Name</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Brand/Generic</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="brand"
                                        id="brandCheck" <?php echo in_array('brand', $selectedTypes) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="brandCheck">Brand</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="type[]" value="generic"
                                        id="genericCheck" <?php echo in_array('generic', $selectedTypes) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="genericCheck">Generic</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pharmacy</label>
                                <?php foreach ($pharmacies as $pharmacy): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pharmacy[]"
                                            value="<?php echo $pharmacy['id']; ?>" id="pharmacy<?php echo $pharmacy['id']; ?>"
                                            <?php echo empty($selectedPharmacies) || in_array($pharmacy['id'], $selectedPharmacies) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="pharmacy<?php echo $pharmacy['id']; ?>">
                                            <?php echo sanitize($pharmacy['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label for="priceRange" class="form-label fw-bold">Maximum Price: PHP <span
                                        id="priceValue"><?php echo $maxPrice; ?></span></label>
                                <input type="range" class="form-range" min="0" max="1000" step="10"
                                    value="<?php echo $maxPrice; ?>" id="priceRange" name="max_price">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">PHP 0</span>
                                    <span class="small text-muted">PHP 1000</span>
                                </div>
                                <div class="mt-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Custom Price: PHP</span>
                                        <input type="number" class="form-control" id="customPrice" min="0" step="1"
                                            value="<?php echo $maxPrice; ?>">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="applyCustomPrice">Apply</button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mb-3">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                                <a href="search.php<?php echo !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''; ?>"
                                    class="btn btn-outline-secondary" id="resetFilters">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="col-md-9">
                <?php if (empty($medications)): ?>
                    <div class="alert alert-info">
                        No medications found matching your search criteria. Please try a different search term.
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <span class="text-muted"><?php echo count($medications); ?> results found</span>
                    </div>

                    <?php
                    $filteredMedications = [];
                    $initialCount = count($medications);
                    $afterPriceCount = 0;
                    $afterPharmacyCount = 0;
                    $afterTypeCount = 0;
                    $noPricesCount = 0;

                    foreach ($medications as $medication):
                        // Get prices for this medication
                        $prices = getMedicationPrices($medication['id']);

                        // If no prices available, count and skip
                        if (empty($prices)) {
                            $noPricesCount++;
                            continue;
                        }

                        // Apply pharmacy filter
                        if (!empty($selectedPharmacies)) {
                            $filteredPrices = [];
                            foreach ($prices as $price) {
                                if (in_array($price['pharmacy_id'], $selectedPharmacies)) {
                                    $filteredPrices[] = $price;
                                }
                            }
                            $prices = $filteredPrices;

                            // Skip if no matching pharmacies
                            if (empty($prices))
                                continue;
                        }
                        $afterPharmacyCount++;

                        // Apply price filter only if a maximum price is set
                        if ($maxPrice > 0) {
                            $filteredPrices = [];
                            foreach ($prices as $price) {
                                if ($price['price'] <= $maxPrice) {
                                    $filteredPrices[] = $price;
                                }
                            }
                            $prices = $filteredPrices;

                            // Skip if no prices match the max price filter
                            if (empty($prices))
                                continue;
                        }
                        $afterPriceCount++;

                        // Apply type filter (brand/generic)
                        $isGeneric = !empty($medication['generic_name']);
                        if (
                            (!in_array('generic', $selectedTypes) && $isGeneric) ||
                            (!in_array('brand', $selectedTypes) && !$isGeneric)
                        ) {
                            continue;
                        }
                        $afterTypeCount++;

                        // Sort prices based on selected sorting
                        if ($sortBy == 'price_high') {
                            usort($prices, function ($a, $b) {
                                return $b['price'] - $a['price'];
                            });
                        } else if ($sortBy == 'pharmacy') {
                            usort($prices, function ($a, $b) {
                                return strcmp($a['pharmacy_name'], $b['pharmacy_name']);
                            });
                        } else {
                            // Default: price_low
                            usort($prices, function ($a, $b) {
                                return $a['price'] - $b['price'];
                            });
                        }

                        // Add medication with filtered prices to results
                        $medication['filtered_prices'] = $prices;
                        $filteredMedications[] = $medication;
                    endforeach;

                    // Sort medications by the lowest price of each medication
                    usort($filteredMedications, function ($a, $b) {
                        $priceA = !empty($a['filtered_prices']) ? $a['filtered_prices'][0]['price'] : PHP_INT_MAX;
                        $priceB = !empty($b['filtered_prices']) ? $b['filtered_prices'][0]['price'] : PHP_INT_MAX;
                        return $priceA - $priceB;
                    });

                    if (empty($filteredMedications)):
                        ?>
                        <div class="alert alert-info">
                            <p>No medications found matching your filter criteria. Please try different filters.</p>

                            <?php if ($initialCount == 0): ?>
                                <p><strong>Suggestion:</strong> Try a different search term. No medications matched
                                    "<?php echo $searchQuery; ?>".</p>
                            <?php elseif ($noPricesCount == $initialCount): ?>
                                <p><strong>Suggestion:</strong> The medications found have no price information available yet.</p>
                            <?php elseif ($afterPharmacyCount == 0 && !empty($selectedPharmacies)): ?>
                                <p><strong>Suggestion:</strong> Try selecting different pharmacies.</p>
                            <?php elseif ($afterPriceCount == 0 && $afterPharmacyCount > 0): ?>
                                <p><strong>Suggestion:</strong> Try increasing your maximum price or removing the price filter.</p>
                            <?php elseif ($afterTypeCount == 0 && $afterPriceCount > 0): ?>
                                <p><strong>Suggestion:</strong> Try selecting both "Brand" and "Generic" medication types.</p>
                            <?php endif; ?>

                            <a href="search.php<?php echo !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''; ?>"
                                class="btn btn-outline-primary mt-2">
                                <i class="fas fa-redo me-1"></i> Reset Filters
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filteredMedications as $medication):
                            $prices = $medication['filtered_prices'];
                            $lowestPrice = !empty($prices) ? $prices[0]['price'] : null;
                            ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h4 class="card-title"><?php echo sanitize($medication['name']); ?>
                                                <?php echo sanitize($medication['dosage'] . ' ' . $medication['form']); ?></h4>
                                            <?php if (!empty($medication['generic_name'])): ?>
                                                <p class="text-muted">Generic Name: <?php echo sanitize($medication['generic_name']); ?></p>
                                            <?php endif; ?>
                                            <p><?php echo nl2br(sanitize($medication['description'])); ?></p>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <?php if ($lowestPrice): ?>
                                                <h5 class="text-primary mb-3">From <?php echo formatPrice($lowestPrice); ?></h5>
                                                <p class="text-muted">Available at <?php echo count($prices); ?> pharmacies</p>
                                            <?php else: ?>
                                                <p class="text-muted">Price information not available</p>
                                            <?php endif; ?>
                                            <a href="details.php?id=<?php echo $medication['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-info-circle me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-3">
    <h6 class="mb-3 fw-bold text-primary">Available at:</h6>
    <ul class="list-group shadow-sm rounded-3">
        <?php foreach($prices as $price): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="fas fa-store text-primary me-2"></i>
                    <span class="fw-semibold text-dark"><?php echo sanitize($price['pharmacy_name']); ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-<?php echo $price['status'] === 'available' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $price['status'])); ?>
                    </span>
                    <span class="badge bg-primary rounded-pill">
                        <?php echo formatPrice($price['price']); ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Update price range value display
    if (document.getElementById('priceRange')) {
        document.getElementById('priceRange').addEventListener('input', function () {
            document.getElementById('priceValue').textContent = this.value;
            document.getElementById('customPrice').value = this.value;
        });

        // Custom price input handler
        document.getElementById('customPrice').addEventListener('input', function () {
            const priceRange = document.getElementById('priceRange');
            const customValue = parseInt(this.value);

            if (customValue >= 0) {
                document.getElementById('priceValue').textContent = customValue;

                // Update range slider if value is within its limits
                if (customValue <= 1000) {
                    priceRange.value = customValue;
                }
            }
        });

        // Apply custom price button handler
        document.getElementById('applyCustomPrice').addEventListener('click', function () {
            document.getElementById('filterForm').submit();
        });
    }
</script>

<?php include_once 'includes/footer.php'; ?>