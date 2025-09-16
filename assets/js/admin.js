/*
 * Catering Sales Report - Admin JavaScript
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Global variables
    window.csrCharts = {};
    window.csrData = {};
    
    // Initialize when document is ready
    $(document).ready(function() {
        initializePlugin();
    });
    
    /**
     * Initialize plugin functionality
     */
    function initializePlugin() {
        // Initialize date pickers
        initializeDatePickers();
        
        // Initialize tooltips
        initializeTooltips();
        
        // Initialize keyboard navigation
        initializeKeyboardNavigation();
        
        // Initialize auto-refresh - disabled to prevent unexpected widget reloads
        // initializeAutoRefresh();
    }
    
    /**
     * Initialize date picker components
     */
    function initializeDatePickers() {
        if ($.fn.datepicker) {
            $('.csr-date-input').datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: new Date(),
                changeMonth: true,
                changeYear: true
            });
        }
    }
    
    /**
     * Initialize tooltip functionality
     */
    function initializeTooltips() {
        $('[data-tooltip]').each(function() {
            var $element = $(this);
            var tooltipText = $element.data('tooltip');
            
            $element.on('mouseenter', function() {
                showTooltip($element, tooltipText);
            }).on('mouseleave', function() {
                hideTooltip();
            });
        });
    }
    
    /**
     * Show tooltip
     */
    function showTooltip($element, text) {
        var $tooltip = $('<div class="csr-tooltip">' + text + '</div>');
        $('body').append($tooltip);
        
        var offset = $element.offset();
        $tooltip.css({
            position: 'absolute',
            top: offset.top - $tooltip.outerHeight() - 5,
            left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2),
            zIndex: 999999,
            background: '#333',
            color: '#fff',
            padding: '5px 10px',
            borderRadius: '3px',
            fontSize: '12px',
            whiteSpace: 'nowrap'
        });
    }
    
    /**
     * Hide tooltip
     */
    function hideTooltip() {
        $('.csr-tooltip').remove();
    }
    
    /**
     * Initialize keyboard navigation
     */
    function initializeKeyboardNavigation() {
        // Navigate between report sections with arrow keys
        $(document).on('keydown', function(e) {
            if (e.target.tagName.toLowerCase() === 'input' || 
                e.target.tagName.toLowerCase() === 'textarea') {
                return;
            }
            
            var $activeNav = $('.csr-nav-item.active');
            var $navItems = $('.csr-nav-item');
            var currentIndex = $navItems.index($activeNav);
            
            switch(e.keyCode) {
                case 38: // Up arrow
                    e.preventDefault();
                    if (currentIndex > 0) {
                        $navItems.eq(currentIndex - 1).click();
                    }
                    break;
                case 40: // Down arrow
                    e.preventDefault();
                    if (currentIndex < $navItems.length - 1) {
                        $navItems.eq(currentIndex + 1).click();
                    }
                    break;
                case 27: // Escape
                    e.preventDefault();
                    if (typeof closeDashboard === 'function') {
                        closeDashboard();
                    }
                    break;
            }
        });
    }
    
    /**
     * Initialize auto-refresh functionality
     */
    function initializeAutoRefresh() {
        // Auto-refresh disabled to prevent unexpected widget reloads
        // Uncomment the following code if you want to enable auto-refresh:
        /*
        setInterval(function() {
            if ($('#csr-dashboard-overlay').is(':visible')) {
                refreshCurrentReport();
            }
        }, 300000); // 5 minutes
        */
    }
    
    /**
     * Refresh current report data
     */
    function refreshCurrentReport() {
        var currentReport = $('.csr-nav-item.active').data('report');
        if (currentReport && typeof loadReportContent === 'function') {
            loadReportContent(currentReport);
        }
    }
    
    /**
     * Utility functions
     */
    window.csrUtils = {
        
        /**
         * Format number with thousands separator
         */
        formatNumber: function(num) {
            return new Intl.NumberFormat().format(num);
        },
        
        /**
         * Format currency
         */
        formatCurrency: function(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },
        
        /**
         * Format percentage
         */
        formatPercentage: function(value, decimals = 1) {
            return (value * 100).toFixed(decimals) + '%';
        },
        
        /**
         * Format date
         */
        formatDate: function(date, options = {}) {
            var defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return new Date(date).toLocaleDateString('en-US', 
                Object.assign(defaultOptions, options));
        },
        
        /**
         * Debounce function
         */
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        /**
         * Throttle function
         */
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() {
                        inThrottle = false;
                    }, limit);
                }
            };
        },
        
        /**
         * Show loading state
         */
        showLoading: function(selector) {
            $(selector).html('<div class="csr-loading">' + 
                (csr_ajax.strings.loading || 'Loading...') + '</div>');
        },
        
        /**
         * Show error message
         */
        showError: function(selector, message) {
            $(selector).html('<div class="csr-error">' + message + '</div>');
        },
        
        /**
         * Show success message
         */
        showSuccess: function(selector, message) {
            $(selector).html('<div class="csr-success">' + message + '</div>');
        },
        
        /**
         * Generate random color
         */
        generateColor: function(opacity = 1) {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + opacity + ')';
        },
        
        /**
         * Get color palette
         */
        getColorPalette: function() {
            return [
                '#0073aa', '#00a0d2', '#0085ba', '#005a87', '#004b67',
                '#46b450', '#00d084', '#008a00', '#005d00', '#003f00',
                '#dc3232', '#ff6b6b', '#e85d5d', '#b71c1c', '#8e0000',
                '#ffb900', '#ffc107', '#ff9800', '#ff6f00', '#e65100'
            ];
        }
    };
    
    /**
     * Chart utilities
     */
    window.csrChartUtils = {
        
        /**
         * Default chart options
         */
        getDefaultOptions: function() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            };
        },
        
        /**
         * Create line chart
         */
        createLineChart: function(ctx, data, options = {}) {
            return new Chart(ctx, {
                type: 'line',
                data: data,
                options: Object.assign(this.getDefaultOptions(), options)
            });
        },
        
        /**
         * Create bar chart
         */
        createBarChart: function(ctx, data, options = {}) {
            return new Chart(ctx, {
                type: 'bar',
                data: data,
                options: Object.assign(this.getDefaultOptions(), options)
            });
        },
        
        /**
         * Create pie chart
         */
        createPieChart: function(ctx, data, options = {}) {
            var defaultPieOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            };
            
            return new Chart(ctx, {
                type: 'pie',
                data: data,
                options: Object.assign(defaultPieOptions, options)
            });
        },
        
        /**
         * Create doughnut chart
         */
        createDoughnutChart: function(ctx, data, options = {}) {
            var defaultDoughnutOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            };
            
            return new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: Object.assign(defaultDoughnutOptions, options)
            });
        }
    };
    
    // Export functions for global access
    window.csrPlugin = {
        initializePlugin: initializePlugin,
        refreshCurrentReport: refreshCurrentReport
    };
    
})(jQuery);
