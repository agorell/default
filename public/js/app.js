// Housing Management System - Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar on click outside
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const searchTarget = this.dataset.target;
            const items = document.querySelectorAll(searchTarget);
            
            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
    
    // Table sorting
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const index = this.cellIndex;
            const isNumeric = this.dataset.type === 'numeric';
            const isDate = this.dataset.type === 'date';
            
            // Toggle sort direction
            const currentDirection = this.dataset.sort || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            this.dataset.sort = newDirection;
            
            // Sort rows
            rows.sort(function(a, b) {
                const aValue = a.cells[index].textContent.trim();
                const bValue = b.cells[index].textContent.trim();
                
                let comparison = 0;
                
                if (isNumeric) {
                    comparison = parseFloat(aValue) - parseFloat(bValue);
                } else if (isDate) {
                    comparison = new Date(aValue) - new Date(bValue);
                } else {
                    comparison = aValue.localeCompare(bValue);
                }
                
                return newDirection === 'desc' ? -comparison : comparison;
            });
            
            // Update table
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
            
            // Update sort indicators
            sortableHeaders.forEach(function(h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add(newDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        });
    });
    
    // Auto-refresh for dashboard
    if (window.location.pathname === '/') {
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 300000); // 5 minutes
    }
    
    // Theme switching (if implemented)
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-theme');
            localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
        });
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-theme');
        }
    }
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const toastContainer = document.querySelector('.toast-container') || document.body;
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Export functions for global use
window.HousingManagement = {
    formatCurrency,
    formatDate,
    showToast
};