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
    /* Overview specific styles - Following design draft color scheme */
    .csr-overview-container {
        padding: 30px;
        /* max-width: 1400px; */
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-overview-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
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
    
    /* Main dashboard grid */
    .csr-dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csr-left-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .csr-right-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    /* Sales trend chart - large widget */
    .csr-main-chart {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: 400px;
    }
    
    .csr-chart-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f1;
        padding-bottom: 15px;
    }
    
    .csr-chart-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0;
    }
    
    .csr-chart-canvas {
        height: 320px;
        position: relative;
    }
    
    /* Metrics grid - orange themed like design */
    .csr-metrics-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .csr-metric-card {
        background: #D2691E; /* Orange theme from design */
        color: #fff;
        border-radius: 6px;
        padding: 20px;
        text-align: left;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        min-height: 120px;
    }
    
    .csr-metric-card.secondary {
        background: #FF8C00; /* Darker orange variant */
    }
    
    .csr-metric-card.tertiary {
        background: #CD853F; /* Brown-orange variant */
    }
    
    .csr-metric-value {
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 5px 0;
        line-height: 1.1;
    }
    
    .csr-metric-label {
        font-size: 14px;
        color: rgba(255,255,255,0.9);
        margin: 0 0 5px 0;
        line-height: 1.3;
    }
    
    .csr-metric-comparison {
        font-size: 12px;
        color: rgba(255,255,255,0.8);
        margin-top: 10px;
    }
    
    .csr-metric-change {
        font-weight: 600;
        color: rgba(255,255,255,0.95);
    }
    
    /* Secondary widgets */
    .csr-secondary-widgets {
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
        font-size: 16px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 15px 0;
        text-align: center;
    }
    
    .csr-widget-content {
        text-align: center;
    }
    
    /* Top products pie chart - colorful design */
    .csr-top-products-chart {
        height: 300px;
        position: relative;
    }
    
    /* Recent orders table */
    .csr-recent-orders {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        grid-column: span 2;
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
    
    .csr-order-status.pending,
    .csr-order-status.on-hold {
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
    
    /* Regional and stats lists */
    .csr-stat-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .csr-stat-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f1;
        font-size: 14px;
    }
    
    .csr-stat-item:last-child {
        border-bottom: none;
    }
    
    .csr-stat-name {
        color: #23282d;
        font-weight: 500;
    }
    
    .csr-stat-value {
        color: #D2691E;
        font-weight: 600;
    }
    
    /* Loading spinner animation */
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
    
    .csr-loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60px;
        flex-direction: column;
        gap: 10px;
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
    
    /* Widget loading states */
    .csr-widget.loading {
        position: relative;
    }
    
    .csr-metric-card.loading .csr-metric-value,
    .csr-metric-card.loading .csr-metric-comparison {
        opacity: 0.3;
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
        z-index: 10;
    }
    
    /* Responsive design */
    @media (max-width: 1200px) {
        .csr-dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .csr-secondary-widgets {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .csr-metrics-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <div class="csr-overview-header">
        <h1><?php _e( '銷售總覽', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="csr-dashboard-grid">
        <!-- Left Column: Sales Trend Chart -->
        <div class="csr-left-column">
            <div class="csr-main-chart">
                <div class="csr-chart-header">
                    <h3 class="csr-chart-title"><?php _e( '銷售趨勢', 'catering-sales-report' ); ?></h3>
                </div>
                <div class="csr-chart-canvas">
                    <canvas id="csr-sales-trend-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column: Key Metrics -->
        <div class="csr-right-column">
            <div class="csr-metrics-grid">

                <!-- 總營業額 (keeping this as a summary) -->
                <div class="csr-metric-card">
                    <div class="csr-metric-label"><?php _e( '總營業額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-total-revenue">HK$--</div>
                    <div class="csr-metric-comparison">
                        <div><?php _e( '總訂單', 'catering-sales-report' ); ?>: <span id="metric-total-orders">--</span></div>
                        <div><?php _e( '平均訂單金額', 'catering-sales-report' ); ?>: <span id="metric-avg-order">HK$--</span></div>
                    </div>
                </div>

                <!-- 本月銷售額 vs 上月銷售額 -->
                <div class="csr-metric-card">
                    <div class="csr-metric-label"><?php _e( '本月銷售額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-current-month-sales">HK$--</div>
                    <div class="csr-metric-comparison">
                        <span><?php _e( '上月銷售額', 'catering-sales-report' ); ?>: </span>
                        <span class="csr-metric-change" id="metric-last-month-sales">HK$--</span>
                    </div>
                </div>

                <!-- 過去30/60/90日平均銷售額 -->
                <div class="csr-metric-card tertiary">
                    <div class="csr-metric-label"><?php _e( '過去30日平均銷售額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-avg-30days">HK$--</div>
                    <div class="csr-metric-comparison">
                        <div><?php _e( '60日平均', 'catering-sales-report' ); ?>: <span id="metric-avg-60days">HK$--</span></div>
                        <div><?php _e( '90日平均', 'catering-sales-report' ); ?>: <span id="metric-avg-90days">HK$--</span></div>
                    </div>
                </div>

                <!-- 本月新增顧客 vs 上月新增顧客 -->
                <div class="csr-metric-card secondary">
                    <div class="csr-metric-label"><?php _e( '本月新增顧客', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-current-month-customers">--</div>
                    <div class="csr-metric-comparison">
                        <span><?php _e( '上月新增顧客', 'catering-sales-report' ); ?>: </span>
                        <span class="csr-metric-change" id="metric-last-month-customers">--</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Secondary Widgets -->
    <div class="csr-secondary-widgets">
        <!-- 最高銷售地區 -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '最高銷售地區', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <ul class="csr-stat-list" id="csr-top-regions">
                    <!-- Loading state will be inserted here -->
                </ul>
            </div>
        </div>

        <!-- 最高銷售產品 -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '最高銷售產品', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <div class="csr-top-products-chart">
                    <canvas id="csr-top-products-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Views Widgets -->
    <div class="csr-secondary-widgets">
        <!-- 最高瀏覽次數 -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '最高瀏覽次數', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <ul class="csr-stat-list" id="csr-top-viewed-products">
                    <!-- Loading state will be inserted here -->
                </ul>
            </div>
        </div>

        <!-- 瀏覽次數趨勢 -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '瀏覽次數趨勢', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <div class="csr-page-views-chart" style="height: 250px;">
                    <canvas id="csr-page-views-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Overview page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states for all widgets
    initializeLoadingStates();
    
    // Load overview data when page is ready
    loadReportData('overview');
    
});

function initializeLoadingStates() {
    // Add loading states to all widgets
    showLoadingState('#csr-top-regions');
    showLoadingState('#csr-top-viewed-products');
    
    // Add loading overlay to charts
    addChartLoadingOverlay('#csr-sales-trend-chart');
    addChartLoadingOverlay('#csr-top-products-chart');
    addChartLoadingOverlay('#csr-page-views-chart');
    
    // Add loading class to metric cards
    jQuery('.csr-metric-card').addClass('loading');
}

function showLoadingState(selector) {
    var loadingHtml = '<div class="csr-loading-container">' +
                     '<div class="csr-loading-spinner"></div>' +
                     '</div>';
    jQuery(selector).html(loadingHtml);
}

function addChartLoadingOverlay(selector) {
    var $container = jQuery(selector).parent();
    var loadingOverlay = '<div class="csr-chart-loading">' +
                        '<div class="csr-loading-spinner"></div>' +
                        '</div>';
    $container.css('position', 'relative').append(loadingOverlay);
}

function removeChartLoadingOverlay(selector) {
    jQuery(selector).parent().find('.csr-chart-loading').remove();
}

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
        if (response.success) {
            // Remove loading states
            jQuery('.csr-metric-card').removeClass('loading');
            removeChartLoadingOverlay('#csr-sales-trend-chart');
            removeChartLoadingOverlay('#csr-top-products-chart');
            removeChartLoadingOverlay('#csr-page-views-chart');
            
            updateOverviewMetrics(response.data.sales_report);
            updateMonthlyComparison(response.data.monthly_comparison);
            updateAverageSales(response.data.average_sales);
            updateSalesTrendChart(response.data.sales_report);
            updateTopProductsChart(response.data.top_products);
            updateTopRegions(response.data.top_regions);
            updateTopViewedProducts(response.data.page_views);
            updatePageViewsChart(response.data.page_views);
        } else {
            showError(response.data.message || csr_ajax.strings.error);
        }
    })
    .fail(function() {
        // Remove loading states on error
        jQuery('.csr-metric-card').removeClass('loading');
        removeChartLoadingOverlay('#csr-sales-trend-chart');
        removeChartLoadingOverlay('#csr-top-products-chart');
        removeChartLoadingOverlay('#csr-page-views-chart');
        
        showError(csr_ajax.strings.api_error);
    });
}

function updateOverviewMetrics(sales_report) {
    if (!sales_report) return;
    var sales_report = sales_report[0];
    jQuery('#metric-total-revenue').text(formatCurrency(sales_report.total_sales || 0));
    jQuery('#metric-total-orders').text(sales_report.total_orders || 0);
    jQuery('#metric-avg-order').text(formatCurrency(sales_report.average_sales || 0));
}

function updateMonthlyComparison(comparison) {
    if (!comparison) return;
    
    // Current vs Last Month Sales
    jQuery('#metric-current-month-sales').text(formatCurrency(comparison.current_month_sales || 0));
    jQuery('#metric-last-month-sales').text(formatCurrency(comparison.last_month_sales || 0));
    
    // Current vs Last Month Customers
    jQuery('#metric-current-month-customers').text(comparison.current_month_customers || 0);
    jQuery('#metric-last-month-customers').text(comparison.last_month_customers || 0);
}

function updateAverageSales(averages) {
    if (!averages) return;
    
    jQuery('#metric-avg-30days').text(formatCurrency(averages.avg_30_days || 0));
    jQuery('#metric-avg-60days').text(formatCurrency(averages.avg_60_days || 0));
    jQuery('#metric-avg-90days').text(formatCurrency(averages.avg_90_days || 0));
}

function updateTopRegions(topRegions) {
    if (!topRegions || topRegions.length === 0) {
        jQuery('#csr-top-regions').html('<div class="csr-loading-container"><span style="color: #666;">No data available</span></div>');
        return;
    }
    
    var html = '';
    topRegions.forEach(function(region) {
        html += '<li class="csr-stat-item">';
        html += '<span class="csr-stat-name">' + escapeHtml(region.city) + '</span>';
        html += '<span class="csr-stat-value">' + formatCurrency(region.amount) + '</span>';
        html += '</li>';
    });
    
    if (html === '') {
        html = '<div class="csr-loading-container"><span style="color: #666;">No regional data available</span></div>';
    }
    
    jQuery('#csr-top-regions').html(html);
}

function updateTopViewedProducts(pageViewData) {
    if (!pageViewData || !pageViewData.top_products || pageViewData.top_products.length === 0) {
        jQuery('#csr-top-viewed-products').html('<div class="csr-loading-container"><span style="color: #666;">No page view data available</span></div>');
        return;
    }
    
    var html = '';
    pageViewData.top_products.forEach(function(product) {
        html += '<li class="csr-stat-item">';
        html += '<span class="csr-stat-name">' + escapeHtml(product.name) + '</span>';
        html += '<span class="csr-stat-value">' + product.views + ' views</span>';
        html += '</li>';
    });
    
    jQuery('#csr-top-viewed-products').html(html);
}

function updatePageViewsChart(pageViewData) {
    var ctx = document.getElementById('csr-page-views-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.pageViewsChart) {
        window.pageViewsChart.destroy();
    }
    
    if (!pageViewData || !pageViewData.daily_trends || pageViewData.daily_trends.length === 0) {
        return;
    }
    
    // Process data for chart
    var labels = [];
    var data = [];
    
    pageViewData.daily_trends.forEach(function(day) {
        var date = new Date(day.view_date);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        data.push(parseInt(day.views || 0));
    });
    
    var chartData = {
        labels: labels,
        datasets: [{
            label: '<?php _e( "Page Views", "catering-sales-report" ); ?>',
            data: data,
            borderColor: '#FF8C00',
            backgroundColor: 'rgba(255, 140, 0, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#FF8C00',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    };
    
    window.pageViewsChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
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
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function showError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-dashboard-grid').html(errorHtml);
}

function updateSalesTrendChart(salesReports) {
    // Chart.js implementation for sales trend
    var ctx = document.getElementById('csr-sales-trend-chart');
    if (!ctx) return;
    
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
    
    // Colorful palette instead of just blue variants
    var colorfulPalette = [
        '#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
        '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4', 
        '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000', 
        '#aaffc3', '#808000', '#ffd8b1', '#000075', '#a9a9a9', 
        '#000000'
    ];
    
    var chartData = {
        labels: products.map(p => p.name || 'Unknown Product'),
        datasets: [{
            data: products.map(p => p.quantity || 0),
            backgroundColor: colorfulPalette.slice(0, products.length),
            borderWidth: 2,
            borderColor: '#fff'
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
                    position: 'right',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}



function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HKD',
        maximumFractionDigits: 0
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