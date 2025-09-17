<?php
/**
 * Regional Analysis Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-region-container">
    <style>
    /* Region specific styles - Following overview.php design pattern */
    .csr-region-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-region-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-region-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-region-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Main dashboard grid - Following overview.php pattern */
    .csr-dashboard-grid {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        align-items: start;
        min-height: 400px;
    }
    
    .csr-left-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
        height: 100%;
            width: calc(66.6666% - 6.6667px);
    }
    
    .csr-right-column {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 20px;
        height: 100%;

    }
    
    /* Hong Kong Map widget */
    .csr-main-map {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: 400px;
    }
    
    .csr-map-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f1;
        padding-bottom: 15px;
    }
    
    .csr-map-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0;
    }
    
    .csr-map-container {
        height: 320px;
        position: relative;
    }
    
    .csr-hk-map {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        opacity: 0.9;
        transition: opacity 0.3s ease;
    }
    
    .csr-hk-map:hover {
        opacity: 1;
    }
    
    /* Regional metrics grid - single column layout with region-specific colors */
    .csr-metrics-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        height: 100%;
        align-content: start;
    }
    
    .csr-metric-card {
        color: #fff;
        border-radius: 6px;
        padding: 20px;
        text-align: left;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        min-height: 97px;
    }
    
    /* Region-specific colors matching chart */
    .csr-hk-island-card {
        background: #a59fff; /* Hong Kong Island - Purple */
    }
    
    .csr-kowloon-card {
        background: #00e69a; /* Kowloon - Green */
    }
    
    .csr-nt-card {
        background: #ffbf4e; /* New Territories - Yellow */
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
    
    /* Regional trend chart */
    .csr-region-trend-chart {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 30px;
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
    
    /* District details grid */
    .csr-district-widgets {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csr-district-widget {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .csr-district-title {
        font-size: 16px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 15px 0;
        text-align: center;
        padding-bottom: 10px;
    }
    
    .csr-district-content {
        text-align: center;
    }
    
    /* District statistics list */
    .csr-district-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .csr-district-item {
        display: flex;
        flex-direction: column;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f1;
        font-size: 14px;
    }
    
    .csr-district-item:last-child {
        border-bottom: none;
    }
    
    .csr-district-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    
    .csr-district-name {
        color: #23282d;
        font-weight: 600;
    }
    
    .csr-district-sales {
        color: #D2691E;
        font-weight: 600;
    }
    
    .csr-district-stats {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        font-size: 12px;
        color: #666;
    }
    
    .csr-district-orders, .csr-district-customers {
        color: #666;
    }
    
    /* Loading states */
    .csr-loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60px;
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
    
    .csr-error-message {
        background: #fff2f2;
        border: 1px solid #dc3232;
        color: #dc3232;
        padding: 15px;
        border-radius: 3px;
        margin: 20px 0;
        text-align: center;
    }
    
    /* Empty state */
    .csr-no-data {
        text-align: center;
        padding: 40px;
        color: #666;
        font-style: italic;
        background: #f9f9f9;
        border-radius: 6px;
        border: 2px dashed #ddd;
    }
    
    /* Responsive design */
    @media (max-width: 1200px) {
        .csr-dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .csr-metrics-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .csr-district-widgets {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .csr-metrics-grid {
            grid-template-columns: 1fr;
        }
        
        .csr-district-widgets {
            grid-template-columns: 1fr;
        }
        
        .csr-region-container {
            padding: 15px;
        }
    }
    </style>

    <!-- Header -->
    <div class="csr-region-header">
        <h1><?php _e( '地區分析', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="csr-dashboard-grid">
        <div class="csr-left-column">
            <!-- Regional Sales Trend Chart -->
            <div class="csr-main-map">
                <div class="csr-map-header">
                    <h3 class="csr-map-title"><?php _e( '地區銷售趨勢', 'catering-sales-report' ); ?></h3>
                </div>
                <div class="csr-map-container">
                    <canvas id="csr-regional-trend-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="csr-right-column">
            <!-- Regional metrics grid -->
            <div class="csr-metrics-grid">
                <div class="csr-metric-card csr-hk-island-card loading">
                    <div class="csr-metric-label"><?php _e( '香港島銷售額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-hk-island-sales">-</div>
                    <div class="csr-metric-comparison" id="metric-hk-island-details">-</div>
                </div>
                
                <div class="csr-metric-card csr-kowloon-card loading">
                    <div class="csr-metric-label"><?php _e( '九龍銷售額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-kowloon-sales">-</div>
                    <div class="csr-metric-comparison" id="metric-kowloon-details">-</div>
                </div>
                
                <div class="csr-metric-card csr-nt-card loading">
                    <div class="csr-metric-label"><?php _e( '新界銷售額', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-nt-sales">-</div>
                    <div class="csr-metric-comparison" id="metric-nt-details">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- District Details Widgets -->
    <div class="csr-district-widgets">
        <!-- Hong Kong Island Districts -->
        <div class="csr-district-widget">
            <h3 class="csr-district-title" style="border-bottom: 2px solid #a59fff;"><?php _e( '香港島', 'catering-sales-report' ); ?></h3>
            <div class="csr-district-content">
                <ul class="csr-district-list" id="hk-island-districts">
                    <li class="csr-loading-container">
                        <div class="csr-loading-spinner"></div>
                        <div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Kowloon Districts -->
        <div class="csr-district-widget">
            <h3 class="csr-district-title" style="border-bottom: 2px solid #00e69a;"><?php _e( '九龍', 'catering-sales-report' ); ?></h3>
            <div class="csr-district-content">
                <ul class="csr-district-list" id="kowloon-districts">
                    <li class="csr-loading-container">
                        <div class="csr-loading-spinner"></div>
                        <div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- New Territories Districts -->
        <div class="csr-district-widget">
            <h3 class="csr-district-title" style="border-bottom: 2px solid #ffbf4e;"><?php _e( '新界', 'catering-sales-report' ); ?></h3>
            <div class="csr-district-content">
                <ul class="csr-district-list" id="nt-districts">
                    <li class="csr-loading-container">
                        <div class="csr-loading-spinner"></div>
                        <div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Region page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states
    initializeRegionLoadingStates();
    
    // Load region data when page is ready
    loadRegionData();
});

function initializeRegionLoadingStates() {
    // Add loading states to metric cards
    jQuery('.csr-metric-card').addClass('loading');
    
    // Add loading overlay to regional trend chart in the main dashboard
    addChartLoadingOverlay('#csr-regional-trend-chart');
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

function loadRegionData() {
    var dateRange = getCurrentDateRange();
    
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'region',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updateRegionalMetrics(response.data.regional_summary || {});
            updateRegionalTrendChart(response.data.monthly_trends || []);
            updateDistrictDetails(response.data.district_details || {});
        } else {
            showRegionError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showRegionError(csr_ajax.strings.api_error);
    })
    .always(function() {
        // Remove loading states
        jQuery('.csr-metric-card').removeClass('loading');
        removeChartLoadingOverlay('#csr-regional-trend-chart');
    });
}

function updateRegionalMetrics(regionalSummary) {
    // Update regional sales metrics
    var hkIslandData = regionalSummary['香港島'] || {sales: 0, orders: 0, customers: 0};
    var kowloonData = regionalSummary['九龍'] || {sales: 0, orders: 0, customers: 0};
    var ntData = regionalSummary['新界'] || {sales: 0, orders: 0, customers: 0};
    
    // Update sales amounts
    jQuery('#metric-hk-island-sales').text(formatCurrency(hkIslandData.sales));
    jQuery('#metric-kowloon-sales').text(formatCurrency(kowloonData.sales));
    jQuery('#metric-nt-sales').text(formatCurrency(ntData.sales));
    
    // Update order and customer counts
    jQuery('#metric-hk-island-details').text(hkIslandData.orders + ' 筆訂單 • ' + hkIslandData.customers + ' 位客戶');
    jQuery('#metric-kowloon-details').text(kowloonData.orders + ' 筆訂單 • ' + kowloonData.customers + ' 位客戶');
    jQuery('#metric-nt-details').text(ntData.orders + ' 筆訂單 • ' + ntData.customers + ' 位客戶');
}

function updateRegionalTrendChart(monthlyTrends) {
    var ctx = document.getElementById('csr-regional-trend-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.regionalTrendChart) {
        window.regionalTrendChart.destroy();
    }
    
    if (!monthlyTrends || monthlyTrends.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無地區趨勢數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Prepare chart data
    var labels = monthlyTrends.map(function(month) {
        return month.month_label || month.month;
    });
    
    var datasets = [
        {
            label: '<?php _e( '香港島', 'catering-sales-report' ); ?>',
            data: monthlyTrends.map(function(month) { return month['香港島'] || 0; }),
            borderColor: '#a59fff',
            backgroundColor: 'rgba(165, 159, 255, 0.1)',
            tension: 0.1,
            fill: false,
            borderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7
        },
        {
            label: '<?php _e( '九龍', 'catering-sales-report' ); ?>',
            data: monthlyTrends.map(function(month) { return month['九龍'] || 0; }),
            borderColor: '#00e69a',
            backgroundColor: 'rgba(0, 230, 154, 0.1)',
            tension: 0.1,
            fill: false,
            borderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7
        },
        {
            label: '<?php _e( '新界', 'catering-sales-report' ); ?>',
            data: monthlyTrends.map(function(month) { return month['新界'] || 0; }),
            borderColor: '#ffbf4e',
            backgroundColor: 'rgba(255, 191, 78, 0.1)',
            tension: 0.1,
            fill: false,
            borderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7
        }
    ];
    
    window.regionalTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                        }
                    }
                },
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function updateDistrictDetails(districtDetails) {
    // Hong Kong Island districts
    var hkIslandDistricts = ['中西區', '灣仔區', '東區', '南區'];
    updateDistrictList('#hk-island-districts', hkIslandDistricts, districtDetails);
    
    // Kowloon districts
    var kowloonDistricts = ['油尖旺區', '深水埗區', '九龍城區', '黃大仙區', '觀塘區'];
    updateDistrictList('#kowloon-districts', kowloonDistricts, districtDetails);
    
    // New Territories districts
    var ntDistricts = ['離島區', '荃灣區', '屯門區', '元朗區', '北區', '大埔區', '沙田區', '西貢區', '葵青區'];
    updateDistrictList('#nt-districts', ntDistricts, districtDetails);
}

function updateDistrictList(selector, districts, districtDetails) {
    var districtData = [];
    
    // Collect district data for sorting
    districts.forEach(function(district) {
        var sales = 0;
        var orders = 0;
        var customers = 0;
        
        if (districtDetails[district]) {
            sales = districtDetails[district].sales;
            orders = districtDetails[district].orders;
            customers = districtDetails[district].customers;
        }
        
        districtData.push({
            name: district,
            sales: sales,
            orders: orders,
            customers: customers
        });
    });
    
    // Sort by sales amount (descending)
    districtData.sort(function(a, b) {
        return b.sales - a.sales;
    });
    
    var html = '';
    districtData.forEach(function(district) {
        html += '<li class="csr-district-item">';
        html += '  <div class="csr-district-header">';
        html += '    <span class="csr-district-name">' + escapeHtml(district.name) + '</span>';
        html += '    <span class="csr-district-sales">' + formatCurrency(district.sales) + '</span>';
        html += '  </div>';
        html += '  <div class="csr-district-stats">';
        html += '    <span class="csr-district-orders">' + district.orders + ' 筆訂單</span>';
        html += '    <span class="csr-district-customers">' + district.customers + ' 位客戶</span>';
        html += '  </div>';
        html += '</li>';
    });
    
    if (html === '') {
        html = '<li class="csr-no-data"><?php _e( "暫無地區數據", "catering-sales-report" ); ?></li>';
    }
    
    jQuery(selector).html(html);
}

function showEmptyChart(ctx, message) {
    var container = ctx.parentElement;
    container.innerHTML = '<div class="csr-loading-container">' +
                         '<div class="csr-loading-text">' + message + '</div>' +
                         '</div>';
}

function showRegionError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-dashboard-grid').html(errorHtml);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HKD'
    }).format(amount || 0);
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
