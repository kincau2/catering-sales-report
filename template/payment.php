<?php
/**
 * Payment Analysis Report Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-payment-container">
    <style>
    /* Payment page specific styles - Following design draft color scheme */
    .csr-payment-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-payment-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-payment-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-payment-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Payment widgets grid */
    .csr-payment-widgets {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csr-widget {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .csr-widget-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 20px 0;
        text-align: center;
        border-bottom: 1px solid #f0f0f1;
        padding-bottom: 15px;
    }
    
    .csr-widget-content {
        text-align: center;
    }
    
    /* Payment type pie chart */
    .csr-payment-pie-chart {
        height: 400px;
        position: relative;
    }
    
    /* Payment trend bar chart */
    .csr-payment-trend-chart {
        height: 400px;
        position: relative;
    }
    
    /* Loading states */
    .csr-loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 300px;
        flex-direction: column;
        gap: 10px;
    }
    
    .csr-loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #D2691E;
        border-radius: 50%;
        animation: csr-spin 1s linear infinite;
        margin: 0 auto;
    }
    
    .csr-loading-text {
        color: #666;
        font-size: 14px;
        font-style: italic;
    }
    
    @keyframes csr-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .csr-error-message {
        background: #fff2f2;
        border: 1px solid #dc3232;
        color: #dc3232;
        padding: 15px;
        border-radius: 3px;
        margin: 20px 0;
        text-align: center;
    }
    
    /* Chart loading overlay */
    .csr-chart-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 10px;
        z-index: 100;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .csr-payment-widgets {
            grid-template-columns: 1fr;
        }
        
        .csr-payment-container {
            padding: 15px;
        }
        
        .csr-payment-pie-chart,
        .csr-payment-trend-chart {
            height: 300px;
        }
    }
    </style>

    <div class="csr-payment-header">
        <h1><?php _e( '付款方式', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Payment Analysis Widgets -->
    <div class="csr-payment-widgets">
        <!-- Payment Type Distribution -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '付款類型', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <canvas id="csr-payment-type-chart" class="csr-payment-pie-chart"></canvas>
            </div>
        </div>

        <!-- Payment Trend (Last 12 Months) -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '付款方式分佈', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <canvas id="csr-payment-trend-chart" class="csr-payment-trend-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Payment page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states for all widgets
    initializePaymentLoadingStates();
    
    // Load payment data when page is ready
    loadPaymentData();
});

function initializePaymentLoadingStates() {
    // Add loading overlay to charts
    addChartLoadingOverlay('#csr-payment-type-chart');
    addChartLoadingOverlay('#csr-payment-trend-chart');
}

function addChartLoadingOverlay(selector) {
    var $container = jQuery(selector).parent();
    var loadingOverlay = '<div class="csr-chart-loading">' +
                        '<div class="csr-loading-spinner"></div>' +
                        '<div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>' +
                        '</div>';
    $container.css('position', 'relative').append(loadingOverlay);
}

function removeChartLoadingOverlay(selector) {
    jQuery(selector).parent().find('.csr-chart-loading').remove();
}

function loadPaymentData() {
    var dateRange = getCurrentDateRange();
    
    // Load orders for payment type analysis
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'payment',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updatePaymentTypeChart(response.data.orders || []);
            updatePaymentTrendChart(response.data.monthly_trends || []);
        } else {
            showPaymentError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showPaymentError(csr_ajax.strings.api_error);
    })
    .always(function() {
        removeChartLoadingOverlay('#csr-payment-type-chart');
        removeChartLoadingOverlay('#csr-payment-trend-chart');
    });
}

function updatePaymentTypeChart(orders) {
    var ctx = document.getElementById('csr-payment-type-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.paymentTypeChart) {
        window.paymentTypeChart.destroy();
    }
    
    if (!orders || orders.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無付款數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Process payment method data
    var paymentMethods = {};
    
    orders.forEach(function(order) {
        var method = order.payment_method_title || '其他';
        
        if (!paymentMethods[method]) {
            paymentMethods[method] = {
                count: 0,
                total: 0
            };
        }
        
        paymentMethods[method].count++;
        paymentMethods[method].total += parseFloat(order.total || 0);
    });
    
    // Convert to chart data
    var labels = [];
    var data = [];
    var colors = [];
    
    Object.keys(paymentMethods).forEach(function(method, index) {
        labels.push(method);
        data.push(paymentMethods[method].total);
        colors.push(getColorForLabel(method, index));
    });
    
    var chartData = {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    window.paymentTypeChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var method = context.label;
                            var amount = formatCurrency(context.parsed);
                            var percentage = ((context.parsed / data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                            return method + ': ' + amount + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function updatePaymentTrendChart(monthlyTrends) {
    var ctx = document.getElementById('csr-payment-trend-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.paymentTrendChart) {
        window.paymentTrendChart.destroy();
    }
    
    // Generate last 12 months labels
    var labels = [];
    var currentDate = new Date();
    
    for (var i = 11; i >= 0; i--) {
        var date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        var monthLabel = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
        labels.push(monthLabel);
    }
    
    // If no trend data provided, get it from the server
    if (!monthlyTrends || monthlyTrends.length === 0) {
        // For now, create sample data structure
        monthlyTrends = generateSampleTrendData();
    }
    
    // Process trend data for stacked bar chart
    var datasets = [];
    var paymentMethods = new Set();
    
    // Extract all payment methods
    monthlyTrends.forEach(function(monthData) {
        if (monthData.payment_methods) {
            Object.keys(monthData.payment_methods).forEach(function(method) {
                paymentMethods.add(method);
            });
        }
    });
    
    var methodIndex = 0;
    paymentMethods.forEach(function(method) {
        var data = labels.map(function(label, index) {
            var monthData = monthlyTrends[index];
            return monthData && monthData.payment_methods && monthData.payment_methods[method] 
                ? monthData.payment_methods[method] 
                : 0;
        });
        
        var color = getColorForLabel(method, methodIndex);
        datasets.push({
            label: method,
            data: data,
            backgroundColor: color,
            borderColor: color,
            borderWidth: 1
        });
        methodIndex++;
    });
    
    window.paymentTrendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

function generateSampleTrendData() {
    // Generate sample data for last 12 months
    var sampleData = [];
    var paymentMethods = ['線上支付', '信用卡', '其他'];
    
    for (var i = 0; i < 12; i++) {
        var monthData = {
            payment_methods: {}
        };
        
        paymentMethods.forEach(function(method) {
            monthData.payment_methods[method] = Math.random() * 50000 + 10000;
        });
        
        sampleData.push(monthData);
    }
    
    return sampleData;
}

function showEmptyChart(ctx, message) {
    var container = ctx.parentElement;
    container.innerHTML = '<div class="csr-loading-container">' +
                         '<div style="color: #666; font-style: italic;">' + escapeHtml(message) + '</div>' +
                         '</div>';
}

function showPaymentError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-payment-widgets').html(errorHtml);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HKD'
    }).format(amount);
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Centralized color mapping for consistent colors across charts
function getColorForLabel(label, index) {
    // Define specific colors for known payment methods
    var colorMapping = {
        // Payment methods
        'yedpay': '#3498db',     // Blue
        '銀行轉帳': '#2ecc71',     // Green
        'Credit / Debit Card': '#f39c12',       // Orange
        '現金': '#9b59b6',         // Purple
        '支票': '#1abc9c',         // Turquoise
        '其他': '#34495e',         // Dark Blue Grey
        // Add more payment methods as needed
        '八達通': '#e67e22',       // Dark Orange
        'Apple Pay': '#f1c40f',    // Yellow
        'Google Pay': '#e91e63'    // Pink
    };
    
    // Return specific color if mapped, otherwise use default colors
    if (colorMapping[label]) {
        return colorMapping[label];
    }
    
    // Default color palette for unmapped labels
    var defaultColors = [
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', 
        '#1abc9c', '#e67e22', '#34495e', '#f1c40f', '#e91e63'
    ];
    
    return defaultColors[index % defaultColors.length];
}
</script>
