// Housing Management System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeComponents();
    
    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-success') || 
                alert.classList.contains('alert-info')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize confirmation dialogs
    initializeConfirmationDialogs();
    
    // Initialize search functionality
    initializeSearchFunctionality();
});

function initializeComponents() {
    // Initialize Bootstrap components
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

function initializeFormValidation() {
    // Custom form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Password confirmation validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(function(input) {
        if (input.name === 'password_confirmation') {
            input.addEventListener('blur', function() {
                const password = document.querySelector('input[name="password"]');
                if (password && input.value !== password.value) {
                    input.setCustomValidity('Passwords do not match');
                } else {
                    input.setCustomValidity('');
                }
            });
        }
    });
}

function initializeDataTables() {
    // Add search functionality to tables
    const tables = document.querySelectorAll('table.table');
    tables.forEach(function(table) {
        const searchInput = table.parentElement.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTable(table, this.value);
            });
        }
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function initializeTooltips() {
    // Custom tooltip initialization
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(function(element) {
        if (!element.hasAttribute('data-bs-toggle')) {
            element.setAttribute('data-bs-toggle', 'tooltip');
            new bootstrap.Tooltip(element);
        }
    });
}

function initializeConfirmationDialogs() {
    // Add confirmation dialogs to dangerous actions
    const dangerousButtons = document.querySelectorAll('.btn-danger, .btn-outline-danger');
    dangerousButtons.forEach(function(button) {
        if (button.type === 'submit' && !button.hasAttribute('onclick')) {
            button.addEventListener('click', function(event) {
                const action = this.closest('form').action;
                let message = 'Are you sure you want to perform this action?';
                
                if (action.includes('delete')) {
                    message = 'Are you sure you want to delete this item? This action cannot be undone.';
                } else if (action.includes('remove')) {
                    message = 'Are you sure you want to remove this item?';
                }
                
                if (!confirm(message)) {
                    event.preventDefault();
                }
            });
        }
    });
}

function initializeSearchFunctionality() {
    // Global search functionality
    const searchInputs = document.querySelectorAll('.global-search');
    searchInputs.forEach(function(input) {
        let debounceTimer;
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    });
}

function performSearch(query) {
    // Implement global search functionality
    console.log('Searching for:', query);
    // This would typically make an AJAX request to search endpoint
}

// Utility functions
function showLoadingState(button) {
    button.disabled = true;
    button.classList.add('loading');
    const originalText = button.textContent;
    button.setAttribute('data-original-text', originalText);
    button.textContent = 'Loading...';
}

function hideLoadingState(button) {
    button.disabled = false;
    button.classList.remove('loading');
    const originalText = button.getAttribute('data-original-text');
    if (originalText) {
        button.textContent = originalText;
    }
}

function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        const alert = new bootstrap.Alert(alertDiv);
        alert.close();
    }, 5000);
}

// Form helper functions
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Export functions for use in other scripts
window.HousingManagement = {
    showLoadingState,
    hideLoadingState,
    showNotification,
    validateEmail,
    validatePhone,
    formatCurrency,
    formatDate
};

// Handle dynamic content loading
function loadDynamicContent(url, targetElement) {
    showLoadingState(targetElement);
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            targetElement.innerHTML = html;
            hideLoadingState(targetElement);
            // Re-initialize components for new content
            initializeComponents();
        })
        .catch(error => {
            console.error('Error loading content:', error);
            hideLoadingState(targetElement);
            showNotification('Error loading content', 'danger');
        });
}

// Auto-refresh functionality for real-time updates
function startAutoRefresh(interval = 30000) {
    setInterval(() => {
        // Refresh dashboard statistics
        const statsCards = document.querySelectorAll('.stats-card');
        statsCards.forEach(card => {
            const url = card.getAttribute('data-refresh-url');
            if (url) {
                loadDynamicContent(url, card);
            }
        });
    }, interval);
}

// Initialize auto-refresh on dashboard
if (window.location.pathname === '/') {
    startAutoRefresh();
}

// Handle print functionality
function printReport() {
    window.print();
}

// Handle export functionality
function exportData(format, data) {
    let content, filename, mimeType;
    
    switch (format) {
        case 'csv':
            content = convertToCSV(data);
            filename = 'housing-data.csv';
            mimeType = 'text/csv';
            break;
        case 'json':
            content = JSON.stringify(data, null, 2);
            filename = 'housing-data.json';
            mimeType = 'application/json';
            break;
        default:
            return;
    }
    
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

function convertToCSV(data) {
    if (!data || data.length === 0) return '';
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => 
            headers.map(header => 
                `"${row[header] || ''}"`
            ).join(',')
        )
    ].join('\n');
    
    return csvContent;
}

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    // Ctrl+/ for search
    if (event.ctrlKey && event.key === '/') {
        event.preventDefault();
        const searchInput = document.querySelector('.global-search');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to close modals
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }
});