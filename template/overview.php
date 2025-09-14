<?php
/**
 * Overview Report Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-overview-container">
    <style>
    /* Overview specific styles */
    .csr-overview-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .csr-overview-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
    }
    
    .csr-overview-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-overview-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    .csr-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csr-metric-card {
        background: #fff;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
    }
    
    .csr-metric-card:hover {
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }
    
    .csr-metric-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 15px;
        background: #0073aa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 24px;
    }
    
    .csr-metric-value {
        font-size: 32px;
        font-weight: 700;
        color: #23282d;
        margin: 0 0 5px 0;
        line-height: 1;
    }
    
    .csr-metric-label {
        font-size: 14px;
        color: #666;
        margin: 0 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .csr-charts-section {
        margin-bottom: 30px;
    }
    
    .csr-chart-container {
        background: #fff;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .csr-chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f1;
    }
    
    .csr-chart-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0;
    }
    
    .csr-chart-filters {
        display: flex;
        gap: 10px;
    }
    
    .csr-chart-filter {
        padding: 6px 12px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 3px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .csr-chart-filter:hover,
    .csr-chart-filter.active {
        background: #0073aa;
        color: #fff;
        border-color: #0073aa;
    }
    
    .csr-chart-canvas {
        height: 400px;
        position: relative;
    }
    
    .csr-recent-orders {
        background: #fff;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .csr-recent-orders h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .csr-orders-table th {
        text-align: left;
        padding: 12px 8px;
        border-bottom: 2px solid #e1e1e1;
        font-weight: 600;
        color: #23282d;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .csr-orders-table td {
        padding: 12px 8px;
        border-bottom: 1px solid #f0f0f1;
        font-size: 14px;
    }
    
    .csr-orders-table tr:hover {
        background: #f9f9f9;
    }
    
    .csr-order-status {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .csr-order-status.completed {
        background: #e8f5e8;
        color: #46b450;
    }
    
    .csr-order-status.processing {
        background: #fff3cd;
        color: #856404;
    }
    
    .csr-order-status.pending {
        background: #f0f0f1;
        color: #666;
    }
    
    .csr-loading-placeholder {
        text-align: center;
        padding: 40px;
        color: #666;
        font-style: italic;
    }
    
    .csr-error-message {
        background: #fff2f2;
        border: 1px solid #dc3232;
        color: #dc3232;
        padding: 15px;
        border-radius: 3px;
        margin: 20px 0;
    }
    </style>

    <div class="csr-overview-header">
        <h1><?php _e( 'Sales Overview', 'catering-sales-report' ); ?></h1>
        <p class="csr-overview-subtitle"><?php _e( 'Key metrics and performance indicators for your WooCommerce store', 'catering-sales-report' ); ?></p>
    </div>

    <!-- Key Metrics -->
    <div class="csr-metrics-grid" id="csr-overview-metrics">
        <div class="csr-metric-card">
            <div class="csr-metric-icon dashicons dashicons-chart-line"></div>
            <div class="csr-metric-value" id="metric-total-revenue">--</div>
            <div class="csr-metric-label"><?php _e( 'Total Revenue', 'catering-sales-report' ); ?></div>
        </div>
        
        <div class="csr-metric-card">
            <div class="csr-metric-icon dashicons dashicons-cart"></div>
            <div class="csr-metric-value" id="metric-total-orders">--</div>
            <div class="csr-metric-label"><?php _e( 'Total Orders', 'catering-sales-report' ); ?></div>
        </div>
        
        <div class="csr-metric-card">
            <div class="csr-metric-icon dashicons dashicons-money"></div>
            <div class="csr-metric-value" id="metric-avg-order">--</div>
            <div class="csr-metric-label"><?php _e( 'Average Order Value', 'catering-sales-report' ); ?></div>
        </div>
        
        <div class="csr-metric-card">
            <div class="csr-metric-icon dashicons dashicons-products"></div>
            <div class="csr-metric-value" id="metric-total-items">--</div>
            <div class="csr-metric-label"><?php _e( 'Items Sold', 'catering-sales-report' ); ?></div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="csr-charts-section">
        <!-- Sales Trend Chart -->
        <div class="csr-chart-container">
            <div class="csr-chart-header">
                <h3 class="csr-chart-title"><?php _e( 'Sales Trend', 'catering-sales-report' ); ?></h3>
            </div>
            <div class="csr-chart-canvas">
                <canvas id="csr-sales-trend-chart"></canvas>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="csr-chart-container">
            <div class="csr-chart-header">
                <h3 class="csr-chart-title"><?php _e( 'Top Selling Products', 'catering-sales-report' ); ?></h3>
            </div>
            <div class="csr-chart-canvas" style="height: 300px;">
                <canvas id="csr-top-products-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="csr-recent-orders">
        <h3><?php _e( 'Recent Orders', 'catering-sales-report' ); ?></h3>
        <div id="csr-recent-orders-content">
            <div class="csr-loading-placeholder">
                <?php _e( 'Loading recent orders...', 'catering-sales-report' ); ?>
            </div>
        </div>
    </div>
</div>

<script>
// Overview page JavaScript
jQuery(document).ready(function($) {
    // Load overview data when page is ready
    loadReportData('overview');
    
});

function loadReportData(reportType) {
    var dateRange = getCurrentDateRange();
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: reportType,
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        console.log(response);
        if (response.success) {
            updateOverviewMetrics(response.data.summary);
            updateRecentOrders(response.data.recent_orders);
            updateSalesTrendChart(response.data.sales_report);
            updateTopProductsChart(response.data.top_products);
        } else {
            showError(response.data.message || csr_ajax.strings.error);
        }
    })
    .fail(function() {
        showError(csr_ajax.strings.api_error);
    });
}

function updateOverviewMetrics(summary) {
    if (!summary) return;
    
    jQuery('#metric-total-revenue').text(formatCurrency(summary.total_revenue || 0));
    jQuery('#metric-total-orders').text(summary.total_orders || 0);
    jQuery('#metric-avg-order').text(formatCurrency(summary.average_order_value || 0));
    jQuery('#metric-total-items').text(summary.total_items || 0);
    
    // Update change indicators (placeholder for now)
    updateChangeIndicator('#metric-revenue-change', 0);
    updateChangeIndicator('#metric-orders-change', 0);
    updateChangeIndicator('#metric-avg-change', 0);
    updateChangeIndicator('#metric-items-change', 0);
}

function updateChangeIndicator(selector, change) {
    var $element = jQuery(selector);
    var className = 'neutral';
    var text = '--';
    
    if (change > 0) {
        className = 'positive';
        text = '+' + change.toFixed(1) + '%';
    } else if (change < 0) {
        className = 'negative';
        text = change.toFixed(1) + '%';
    }
    
    $element.removeClass('positive negative neutral')
             .addClass(className)
             .text(text);
}

function updateRecentOrders(orders) {
    var $container = jQuery('#csr-recent-orders-content');
    
    if (!orders || orders.length === 0) {
        $container.html('<div class="csr-loading-placeholder">' + csr_ajax.strings.no_data + '</div>');
        return;
    }
    
    var html = '<table class="csr-orders-table">';
    html += '<thead><tr>';
    html += '<th>' + '<?php _e( "Order", "catering-sales-report" ); ?>' + '</th>';
    html += '<th>' + '<?php _e( "Customer", "catering-sales-report" ); ?>' + '</th>';
    html += '<th>' + '<?php _e( "Date", "catering-sales-report" ); ?>' + '</th>';
    html += '<th>' + '<?php _e( "Status", "catering-sales-report" ); ?>' + '</th>';
    html += '<th>' + '<?php _e( "Total", "catering-sales-report" ); ?>' + '</th>';
    html += '</tr></thead><tbody>';
    
    orders.forEach(function(order) {
        var customerName = (order.billing && order.billing.first_name) 
            ? order.billing.first_name + ' ' + order.billing.last_name 
            : '<?php _e( "Guest", "catering-sales-report" ); ?>';
        
        var date = new Date(order.date_created).toLocaleDateString();
        var statusClass = order.status.toLowerCase();
        
        html += '<tr>';
        html += '<td>#' + order.number + '</td>';
        html += '<td>' + escapeHtml(customerName) + '</td>';
        html += '<td>' + date + '</td>';
        html += '<td><span class="csr-order-status ' + statusClass + '">' + order.status + '</span></td>';
        html += '<td>' + formatCurrency(order.total) + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    $container.html(html);
}

function updateSalesTrendChart(salesReports) {
    // Chart.js implementation for sales trend
    var ctx = document.getElementById('csr-sales-trend-chart');
    if (!ctx) return;
    
    console.log('Sales reports data:', salesReports);
    
    // Destroy existing chart if it exists
    if (window.salesTrendChart) {
        window.salesTrendChart.destroy();
    }
    
    // Check if we have data
    if (!salesReports || salesReports.length === 0) {
        return;
    }
    
    // Get the sales report data (should be the first item in array)
    var salesReport = salesReports[0];
    if (!salesReport || !salesReport.totals) {
        console.log('No totals data found');
        return;
    }
    
    // Get current date range to determine if we should group by month
    var dateRange;
    try {
        dateRange = getCurrentDateRange();
    } catch (e) {
        // Fallback if function not available
        dateRange = {
            start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            end: new Date().toISOString().split('T')[0]
        };
    }
    
    var startDate = new Date(dateRange.start);
    var endDate = new Date(dateRange.end);
    var daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    
    // If range is more than 90 days, group by month
    var groupByMonth = daysDiff > 90;
    
    var chartData = processChartData(salesReport.totals, groupByMonth);
    
    // Sample data structure - replace with actual data
    var chartDataConfig = {
        labels: chartData.labels,
        datasets: [{
            label: '<?php _e( "Sales", "catering-sales-report" ); ?>',
            data: chartData.values,
            borderColor: '#0073aa',
            backgroundColor: 'rgba(0, 115, 170, 0.1)',
            tension: 0.4,
            fill: true
        }]
    };
    
    window.salesTrendChart = new Chart(ctx, {
        type: 'line',
        data: chartDataConfig,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
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

function processChartData(totals, groupByMonth) {
    if (!totals) return { labels: [], values: [] };
    
    var labels = [];
    var values = [];
    var monthlyData = {};
    
    // Sort dates
    var sortedDates = Object.keys(totals).sort();
    
    if (groupByMonth) {
        // Group by month
        sortedDates.forEach(function(date) {
            var monthKey = date.substring(0, 7); // YYYY-MM format
            
            if (!monthlyData[monthKey]) {
                monthlyData[monthKey] = {
                    sales: 0,
                    orders: 0
                };
            }
            
            monthlyData[monthKey].sales += parseFloat(totals[date].sales || 0);
            monthlyData[monthKey].orders += parseInt(totals[date].orders || 0);
        });
        
        // Convert monthly data to arrays
        Object.keys(monthlyData).sort().forEach(function(monthKey) {
            var date = new Date(monthKey + '-01');
            labels.push(date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' }));
            values.push(monthlyData[monthKey].sales);
        });
    } else {
        // Use daily data
        sortedDates.forEach(function(date) {
            var dateObj = new Date(date);
            labels.push(dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            values.push(parseFloat(totals[date].sales || 0));
        });
    }
    
    return { labels: labels, values: values };
}

function updateTopProductsChart(products) {
    var ctx = document.getElementById('csr-top-products-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.topProductsChart) {
        window.topProductsChart.destroy();
    }
    
    if (!products || products.length === 0) {
        return;
    }
    
    var chartData = {
        labels: products.map(p => p.name || 'Unknown Product'),
        datasets: [{
            data: products.map(p => p.quantity || 0),
            backgroundColor: [
                '#0073aa', '#00a0d2', '#0085ba', '#005a87', '#004b67'
            ]
        }]
    };
    
    window.topProductsChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
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

function showError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('#csr-overview-metrics').html(errorHtml);
}
</script>