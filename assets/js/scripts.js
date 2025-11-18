/**
 * Main JavaScript for Mandaue MedCompare User Interface
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search autocomplete
    initSearchAutocomplete();
    
    // Initialize price range slider if it exists
    initPriceRangeSlider();
    
    // Initialize filter functionality
    initFilterFunctionality();
    
    // Add back-to-top button functionality
    initBackToTop();
});

/**
 * Initialize search autocomplete functionality
 */
function initSearchAutocomplete() {
    const searchInput = document.querySelector('input[name="q"]');
    if (!searchInput) return;
    
    // Sample popular medications for autocomplete
    const popularMedications = [
        'Paracetamol',
        'Amoxicillin',
        'Biogesic',
        'Alaxan FR',
        'Neozep',
        'Bioflu',
        'Solmux',
        'Cetirizine',
        'Mefenamic Acid',
        'Decolgen'
    ];
    
    // Create and append the autocomplete container
    const autocompleteContainer = document.createElement('div');
    autocompleteContainer.className = 'autocomplete-items d-none';
    autocompleteContainer.style.position = 'absolute';
    autocompleteContainer.style.zIndex = '999';
    autocompleteContainer.style.width = searchInput.offsetWidth + 'px';
    autocompleteContainer.style.maxHeight = '200px';
    autocompleteContainer.style.overflowY = 'auto';
    autocompleteContainer.style.backgroundColor = '#fff';
    autocompleteContainer.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    autocompleteContainer.style.borderRadius = '0 0 0.5rem 0.5rem';
    searchInput.parentNode.style.position = 'relative';
    searchInput.parentNode.appendChild(autocompleteContainer);
    
    // Handle input event - only start showing suggestions after 3 characters
    searchInput.addEventListener('input', function() {
        const value = this.value.trim().toLowerCase();
        autocompleteContainer.innerHTML = '';
        
        if (value.length < 3) {
            autocompleteContainer.classList.add('d-none');
            return;
        }
        
        const matches = popularMedications.filter(med => 
            med.toLowerCase().includes(value)
        );
        
        if (matches.length > 0) {
            autocompleteContainer.classList.remove('d-none');
            matches.forEach(match => {
                const item = document.createElement('div');
                item.style.padding = '10px';
                item.style.cursor = 'pointer';
                item.style.borderBottom = '1px solid #e9ecef';
                
                // Highlight the matching part
                const matchIndex = match.toLowerCase().indexOf(value);
                const highlighted = 
                    match.substring(0, matchIndex) + 
                    '<strong>' + match.substring(matchIndex, matchIndex + value.length) + '</strong>' +
                    match.substring(matchIndex + value.length);
                
                item.innerHTML = highlighted;
                
                item.addEventListener('click', function() {
                    searchInput.value = match;
                    autocompleteContainer.classList.add('d-none');
                    // Optional: Submit the form automatically
                    searchInput.closest('form').submit();
                });
                
                item.addEventListener('mouseover', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                
                item.addEventListener('mouseout', function() {
                    this.style.backgroundColor = '#fff';
                });
                
                autocompleteContainer.appendChild(item);
            });
        } else {
            autocompleteContainer.classList.add('d-none');
        }
    });
    
    // Close autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput) {
            autocompleteContainer.classList.add('d-none');
        }
    });
}

/**
 * Initialize price range slider with live update of displayed value
 */
function initPriceRangeSlider() {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = this.value;
        });
    }
}

/**
 * Initialize filter functionality for the search results page
 */
function initFilterFunctionality() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    
    // Auto-submit form when filter options change (except price range which needs explicit apply)
    const autoSubmitElements = filterForm.querySelectorAll('select, input[type="checkbox"]');
    autoSubmitElements.forEach(element => {
        element.addEventListener('change', function() {
            // Don't auto-submit if all checkboxes of a type are unchecked
            if (element.type === 'checkbox') {
                const checkboxName = element.name;
                const checkboxes = filterForm.querySelectorAll(`input[name="${checkboxName}"]`);
                let anyChecked = false;
                
                checkboxes.forEach(cb => {
                    if (cb.checked) anyChecked = true;
                });
                
                // If none are checked, check this one to avoid no selections
                if (!anyChecked) {
                    element.checked = true;
                }
            }
            
            filterForm.submit();
        });
    });
    
    // Advanced price range handler
    const priceRange = document.getElementById('priceRange');
    const customPrice = document.getElementById('customPrice');
    
    if (priceRange && customPrice) {
        // Sync custom price with range
        customPrice.addEventListener('change', function() {
            const value = parseInt(this.value);
            if (!isNaN(value) && value >= 0) {
                // Update priceValue span
                const priceValue = document.getElementById('priceValue');
                if (priceValue) priceValue.textContent = value;
                
                // Update range if within bounds
                if (value <= parseInt(priceRange.max)) {
                    priceRange.value = value;
                }
            }
        });
    }
    
    // Reset button functionality
    const resetButton = document.getElementById('resetFilters');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the search query if it exists
            const searchParams = new URLSearchParams(window.location.search);
            const query = searchParams.get('q');
            
            // Redirect to the search page with just the query
            if (query) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query);
            } else {
                window.location.href = 'search.php';
            }
        });
    }
}

/**
 * Initialize back-to-top button functionality
 */
function initBackToTop() {
    // Create back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'btn btn-primary back-to-top-btn';
    backToTopBtn.style.position = 'fixed';
    backToTopBtn.style.bottom = '20px';
    backToTopBtn.style.right = '20px';
    backToTopBtn.style.display = 'none';
    backToTopBtn.style.borderRadius = '50%';
    backToTopBtn.style.width = '40px';
    backToTopBtn.style.height = '40px';
    backToTopBtn.style.padding = '0';
    backToTopBtn.style.zIndex = '99';
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });
    
    // Scroll to top when clicked
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}
