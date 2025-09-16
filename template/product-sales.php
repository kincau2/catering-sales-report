<?php
/**
 * Product Sales Analysis Report Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-product-sales-container">
    <style>
    /* Product sales page specific styles - Following design draft color scheme */
    .csr-product-sales-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-product-sales-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-product-sales-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-product-sales-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Product sales widgets grid */
    .csr-product-sales-widgets {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
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
        display: flex;
        gap: 20px;
        flex-direction: row;
        flex-wrap: nowrap;
    }
    
    .csr-chart-section {
        display: flex;
        flex-direction: column;
        height: 350px; /* Fixed height like overview.php */
        max-height: 350px;
        overflow: hidden;
    }
    
    /* Pie chart section - 1/3 width */
    .csr-chart-section:first-child {
        flex: 0 0 33.333%; /* Take exactly 1/3 of the width */
        max-width: 33.333%;
    }
    
    /* Line chart section - 2/3 width */
    .csr-chart-section:last-child {
        flex: 1; /* Take remaining width (2/3) */
        min-width: 0; /* Allow flexbox to shrink if needed */
    }

    .csr-product-pie-chart{
        max-width: 500px;
    }
    
    .csr-chart-title {
        font-size: 14px;
        font-weight: 500;
        color: #666;
        margin-bottom: 10px;
        text-align: center;
        flex-shrink: 0; /* Prevent title from shrinking */
        height: auto;
    }
    
    /* Product sales charts */
    .csr-product-pie-chart,
    .csr-product-line-chart {
        position: relative;
        height: 280px; /* Fixed height instead of flex: 1 */
        max-height: 280px;
    }
    
    .csr-quantity-pie-chart,
    .csr-quantity-line-chart {
        position: relative;
        height: 280px; /* Fixed height instead of flex: 1 */
        max-height: 280px;
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
    @media (max-width: 1024px) {
        .csr-widget-content {
            flex-direction: column;
            height: auto; /* Allow natural height on smaller screens */
        }
        
        /* Reset flex properties for mobile */
        .csr-chart-section:first-child,
        .csr-chart-section:last-child {
            flex: none;
            max-width: none;
            min-width: none;
        }
        
        .csr-chart-section {
            height: 320px; /* Fixed height for mobile */
            max-height: 320px;
        }
        
        .csr-product-pie-chart,
        .csr-product-line-chart,
        .csr-quantity-pie-chart,
        .csr-quantity-line-chart {
            height: 250px;
            max-height: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .csr-product-sales-container {
            padding: 15px;
        }
        
        .csr-chart-section {
            height: 270px; /* Smaller height for mobile */
            max-height: 270px;
        }
        
        .csr-product-pie-chart,
        .csr-product-line-chart,
        .csr-quantity-pie-chart,
        .csr-quantity-line-chart {
            height: 200px;
            max-height: 200px;
        }
    }
    </style>

    <div class="csr-product-sales-header">
        <h1><?php _e( '產品銷售', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Product Sales Analysis Widgets -->
    <div class="csr-product-sales-widgets">
        <!-- Top Products by Sales Amount -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '產品銷售額分析', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <div class="csr-chart-section">
                    <div class="csr-chart-title"><?php _e( '產品銷售額比例', 'catering-sales-report' ); ?></div>
                    <canvas id="csr-product-sales-pie-chart" class="csr-product-pie-chart"></canvas>
                </div>
                <div class="csr-chart-section" style="flex: 1;">
                    <div class="csr-chart-title"><?php _e( '產品銷售額趨勢', 'catering-sales-report' ); ?></div>
                    <canvas id="csr-product-sales-line-chart" class="csr-product-line-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products by Quantity Sold -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '產品銷售量分析', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <div class="csr-chart-section">
                    <div class="csr-chart-title"><?php _e( '產品銷售量比例', 'catering-sales-report' ); ?></div>
                    <canvas id="csr-product-quantity-pie-chart" class="csr-quantity-pie-chart"></canvas>
                </div>
                <div class="csr-chart-section" style="flex: 1;">
                    <div class="csr-chart-title"><?php _e( '產品銷售量趨勢', 'catering-sales-report' ); ?></div>
                    <canvas id="csr-product-quantity-line-chart" class="csr-quantity-line-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Product sales page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states for all widgets
    initializeProductSalesLoadingStates();
    
    // Load product sales data when page is ready
    loadProductSalesData();
});

function initializeProductSalesLoadingStates() {
    // Add loading overlay to charts
    addChartLoadingOverlay('#csr-product-sales-pie-chart');
    addChartLoadingOverlay('#csr-product-sales-line-chart');
    addChartLoadingOverlay('#csr-product-quantity-pie-chart');
    addChartLoadingOverlay('#csr-product-quantity-line-chart');
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

function loadProductSalesData() {
    var dateRange = getCurrentDateRange();
    
    // Load product sales data
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'product-sales',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updateProductSalesCharts(response.data);
            updateProductQuantityCharts(response.data);
        } else {
            showProductSalesError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showProductSalesError(csr_ajax.strings.api_error);
    })
    .always(function() {
        removeChartLoadingOverlay('#csr-product-sales-pie-chart');
        removeChartLoadingOverlay('#csr-product-sales-line-chart');
        removeChartLoadingOverlay('#csr-product-quantity-pie-chart');
        removeChartLoadingOverlay('#csr-product-quantity-line-chart');
    });
}

function updateProductSalesCharts(data) {
    updateProductSalesPieChart(data.top_products_by_sales || []);
    updateProductSalesLineChart(data.sales_trends || []);
}

function updateProductQuantityCharts(data) {
    updateProductQuantityPieChart(data.top_products_by_quantity || []);
    updateProductQuantityLineChart(data.quantity_trends || []);
}

function updateProductSalesPieChart(products) {
    var ctx = document.getElementById('csr-product-sales-pie-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.productSalesPieChart) {
        window.productSalesPieChart.destroy();
    }
    
    if (!products || products.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無產品銷售數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Convert to chart data
    var labels = [];
    var data = [];
    var colors = [];
    
    products.forEach(function(product, index) {
        labels.push(product.name || '未知產品');
        data.push(parseFloat(product.total_sales || 0));
        colors.push(getColorForLabel(product.name || '未知產品', index));
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
    
    window.productSalesPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var product = context.label;
                            var amount = formatCurrency(context.parsed);
                            var percentage = ((context.parsed / data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                            return product + ': ' + amount + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            layout: {
                padding: 0
            }
        }
    });
}

function updateProductSalesLineChart(trends) {
    var ctx = document.getElementById('csr-product-sales-line-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.productSalesLineChart) {
        window.productSalesLineChart.destroy();
    }
    
    // Generate labels from actual trend data
    var labels = [];
    if (trends && trends.length > 0) {
        trends.forEach(function(monthData) {
            labels.push(monthData.month_label || monthData.month);
        });
    } else {
        // Fallback: generate sample labels
        var currentDate = new Date();
        for (var i = 11; i >= 0; i--) {
            var date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            var monthLabel = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
            labels.push(monthLabel);
        }
        // Generate sample data if no trends provided
        trends = generateSampleProductTrendData();
    }
    
    // Process trend data for line chart
    var datasets = [];
    var products = new Set();
    
    // Extract all products
    trends.forEach(function(monthData) {
        if (monthData.products) {
            Object.keys(monthData.products).forEach(function(product) {
                products.add(product);
            });
        }
    });
    
    var productIndex = 0;
    products.forEach(function(product) {
        var data = labels.map(function(label, index) {
            var monthData = trends[index];
            return monthData && monthData.products && monthData.products[product] 
                ? monthData.products[product] 
                : 0;
        });
        
        var color = getColorForLabel(product, productIndex);
        datasets.push({
            label: product,
            data: data,
            borderColor: color,
            backgroundColor: color + '20',
            borderWidth: 2,
            fill: false,
            tension: 0.1
        });
        productIndex++;
    });
    
    window.productSalesLineChart = new Chart(ctx, {
        type: 'line',
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
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
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
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            layout: {
                padding: 0
            }
        }
    });
}

function updateProductQuantityPieChart(products) {
    var ctx = document.getElementById('csr-product-quantity-pie-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.productQuantityPieChart) {
        window.productQuantityPieChart.destroy();
    }
    
    if (!products || products.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無產品銷售量數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Convert to chart data
    var labels = [];
    var data = [];
    var colors = [];
    
    products.forEach(function(product, index) {
        labels.push(product.name || '未知產品');
        data.push(parseInt(product.quantity || 0));
        colors.push(getColorForLabel(product.name || '未知產品', index));
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
    
    window.productQuantityPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var product = context.label;
                            var quantity = context.parsed.toLocaleString();
                            var percentage = ((context.parsed / data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                            return product + ': ' + quantity + ' 件 (' + percentage + '%)';
                        }
                    }
                }
            },
            layout: {
                padding: 0
            }
        }
    });
}

function updateProductQuantityLineChart(trends) {
    var ctx = document.getElementById('csr-product-quantity-line-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.productQuantityLineChart) {
        window.productQuantityLineChart.destroy();
    }
    
    // Generate labels from actual trend data
    var labels = [];
    if (trends && trends.length > 0) {
        trends.forEach(function(monthData) {
            labels.push(monthData.month_label || monthData.month);
        });
    } else {
        // Fallback: generate sample labels
        var currentDate = new Date();
        for (var i = 11; i >= 0; i--) {
            var date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            var monthLabel = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
            labels.push(monthLabel);
        }
        // Generate sample data if no trends provided
        trends = generateSampleQuantityTrendData();
    }
    
    // Process trend data for line chart
    var datasets = [];
    var products = new Set();
    
    // Extract all products
    trends.forEach(function(monthData) {
        if (monthData.products) {
            Object.keys(monthData.products).forEach(function(product) {
                products.add(product);
            });
        }
    });
    
    var productIndex = 0;
    products.forEach(function(product) {
        var data = labels.map(function(label, index) {
            var monthData = trends[index];
            return monthData && monthData.products && monthData.products[product] 
                ? monthData.products[product] 
                : 0;
        });
        
        var color = getColorForLabel(product, productIndex);
        datasets.push({
            label: product,
            data: data,
            borderColor: color,
            backgroundColor: color + '20',
            borderWidth: 2,
            fill: false,
            tension: 0.1
        });
        productIndex++;
    });
    
    window.productQuantityLineChart = new Chart(ctx, {
        type: 'line',
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
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' 件';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' 件';
                        }
                    }
                }
            },
            layout: {
                padding: 0
            }
        }
    });
}

// Centralized color mapping for consistent colors across charts
function getColorForLabel(label, index) {
    // Define specific colors for known products - can be expanded
    var colorMapping = {
        // Common product types
        '套餐A': '#3498db',      // Blue
        '套餐B': '#e74c3c',      // Red  
        '套餐C': '#2ecc71',      // Green
        '單品': '#f39c12',       // Orange
        '飲料': '#9b59b6',       // Purple
        '甜品': '#1abc9c',       // Turquoise
        '小食': '#e67e22',       // Dark Orange
        '湯品': '#34495e'        // Dark Blue Grey
    };
    
    // Return specific color if mapped, otherwise use default colors
    if (colorMapping[label]) {
        return colorMapping[label];
    }
    
    // Default color palette for unmapped labels
    var defaultColors = [
        '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', 
        '#1abc9c', '#e67e22', '#34495e', '#f1c40f', '#e91e63',
        '#95a5a6', '#16a085', '#27ae60', '#2980b9', '#8e44ad'
    ];
    
    return defaultColors[index % defaultColors.length];
}

function generateSampleProductTrendData() {
    // Generate sample data for last 12 months
    var sampleData = [];
    var products = ['套餐A', '套餐B', '單品'];
    
    for (var i = 0; i < 12; i++) {
        var monthData = {
            products: {}
        };
        
        products.forEach(function(product) {
            monthData.products[product] = Math.random() * 50000 + 10000;
        });
        
        sampleData.push(monthData);
    }
    
    return sampleData;
}

function generateSampleQuantityTrendData() {
    // Generate sample data for last 12 months
    var sampleData = [];
    var products = ['套餐A', '套餐B', '單品'];
    
    for (var i = 0; i < 12; i++) {
        var monthData = {
            products: {}
        };
        
        products.forEach(function(product) {
            monthData.products[product] = Math.floor(Math.random() * 500 + 100);
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

function showProductSalesError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-product-sales-widgets').html(errorHtml);
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
</script>
