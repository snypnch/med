/**
 * JavaScript for Mandaue MedCompare Admin Panel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables if available
    initDataTables();
    
    // Initialize form validations
    initFormValidations();
    
    // Setup AJAX for seamless interactions
    setupAjaxInteractions();
    
    // Initialize tooltips and popovers
    initTooltipsAndPopovers();
    
    // Setup confirmation dialogs
    setupConfirmationDialogs();
});

/**
 * Initialize DataTables for better table functionality
 */
function initDataTables() {
    // Check if DataTable is available and the table exists
    if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable').length > 0) {
        $('#dataTable').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });
    }
}

/**
 * Initialize form validations
 */
function initFormValidations() {
    // Get all forms that need validation
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission if invalid
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Setup AJAX for seamless interactions
 */
function setupAjaxInteractions() {
    // Check if jQuery is available
    if (typeof $ === 'undefined') return;
    
    // Setup AJAX global settings
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    
    // Example: Setup AJAX price updates
    $(document).on('click', '.update-price-ajax', function(e) {
        e.preventDefault();
        
        const priceId = $(this).data('price-id');
        const newPrice = $('#price-' + priceId).val();
        
        if (!newPrice || isNaN(newPrice) || newPrice <= 0) {
            alert('Please enter a valid price');
            return;
        }
        
        $.ajax({
            url: 'ajax_handler.php',
            type: 'POST',
            data: {
                action: 'update_price',
                price_id: priceId,
                price: newPrice
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('Price updated successfully!', 'success');
                    
                    // Update UI with new price
                    $('.price-display-' + priceId).text('PHP ' + parseFloat(newPrice).toFixed(2));
                } else {
                    showAlert('Error: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Server error occurred. Please try again.', 'danger');
            }
        });
    });
}

/**
 * Initialize Bootstrap tooltips and popovers
 */
function initTooltipsAndPopovers() {
    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') return;
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Setup confirmation dialogs for dangerous actions
 */
function setupConfirmationDialogs() {
    // Add confirmation to all elements with data-confirm attribute
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

/**
 * Show an alert message in the UI
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Find alert container or create one
    let alertContainer = document.querySelector('.alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert-container position-fixed top-0 end-0 p-3';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    // Add alert to container
    alertContainer.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}
