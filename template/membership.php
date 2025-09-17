<?php
/**
 * Membership Analysis Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-membership-container">
    <style>
    /* Membership specific styles - Following overview.php design pattern */
    .csr-membership-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-membership-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-membership-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-membership-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Main dashboard grid - Full width chart + bottom row */
    .csr-dashboard-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .csr-top-row {
        width: 100%;
    }
    
    .csr-bottom-row {
        display: grid;
        grid-template-columns: 2fr 3fr;
        gap: 20px;
        align-items: start;
    }
    
    .csr-left-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
        height: 100%;
    }
    
    .csr-right-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
        height: 100%;
    }
    
    /* User trends bar chart */
    .csr-user-trends-chart {
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
    
    /* Geographic distribution widget - Region format */
    .csr-geographic-widget {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: auto;
    }
    
    .csr-widget-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 5px 0;
        text-align: left;
    }
    
    .csr-widget-subtitle {
        font-size: 14px;
        color: #666;
        margin: 0 0 15px 0;
        text-align: left;
    }
    
    .csr-region-list {
        margin: 0;
        padding: 0;
    }
    
    .csr-region-item {
        display: flex;
        flex-direction: column;
        padding: 12px 0;
        font-size: 14px;
        gap: 8px;
    }
    
    .csr-region-item:last-child {
        border-bottom: none;
    }
    
    .csr-region-name {
        color: #23282d;
        font-weight: 500;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }
    
    .csr-region-stats {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .csr-region-count {
        color: #666;
        font-weight: 400;
        font-size: 13px;
        min-width: 80px;
    }
    
    .csr-progress-bar {
        flex: 1;
        height: 8px;
        background-color: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }
    
    .csr-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    .csr-progress-fill.region-hk {
        background-color: #8b5cf6;
    }
    
    .csr-progress-fill.region-kl {
        background-color: #10b981;
    }
    
    .csr-progress-fill.region-nt {
        background-color: #f59e0b;
    }
    
    /* Monthly table widget */
    .csr-monthly-table-widget {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: auto;
    }
    
    .csr-monthly-table {
        width: 100%;
        overflow-x: auto;
    }
    
    .csr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: transparent;
    }
    
    .csr-table th {
        background: transparent;
        padding: 8px 6px;
        text-align: center;
        font-weight: 600;
        border: none;
        color: #23282d;
        white-space: nowrap;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .csr-table td {
        padding: 8px 6px;
        text-align: center;
        border: none;
        color: #666;
        background: transparent;
    }
    
    .csr-table tbody tr {
        background: transparent;
    }
    
    .csr-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .csr-month-cell {
        font-weight: 500;
        color: #23282d;
        text-align: left;
    }
    
    .csr-positive {
        color: #00a32a;
    }
    
    .csr-negative {
        color: #d63638;
    }
    
    .csr-status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }
    
    /* Bottom analytics grid */
    .csr-analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .csr-analytics-card {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .csr-analytics-title {
        font-size: 16px;
        font-weight: 600;
        color: #23282d;
        margin: 0 0 15px 0;
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f1;
    }
    
    .csr-analytics-content {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .csr-metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f9f9f9;
    }
    
    .csr-metric-row:last-child {
        border-bottom: none;
    }
    
    .csr-metric-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }
    
    .csr-metric-value {
        font-size: 16px;
        font-weight: 600;
        color: #D2691E;
    }
    
    .csr-metric-large {
        font-size: 24px;
        font-weight: 700;
        color: #23282d;
        text-align: center;
        margin: 10px 0;
    }
    
    .csr-metric-trend {
        font-size: 12px;
        color: #666;
        text-align: center;
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
    
    .csr-analytics-card.loading .csr-metric-value {
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
        .csr-bottom-row {
            grid-template-columns: 1fr;
        }
        
        .csr-analytics-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .csr-bottom-row {
            grid-template-columns: 1fr;
        }
        
        .csr-membership-container {
            padding: 15px;
        }
        
        .csr-user-trends-chart,
        .csr-geographic-widget,
        .csr-monthly-table-widget {
            height: auto;
        }
        
        .csr-chart-canvas {
            height: 220px;
        }
        
        .csr-table {
            font-size: 11px;
        }
        
        .csr-table th,
        .csr-table td {
            padding: 4px;
        }
        
        .csr-region-stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        
        .csr-progress-bar {
            width: 100%;
        }
    }
    </style>

    <!-- Header -->
    <div class="csr-membership-header">
        <h1><?php _e( '會員數據庫', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="csr-dashboard-grid">
        <!-- Top Row - Full Width User Trends Chart -->
        <div class="csr-top-row">
            <div class="csr-user-trends-chart">
                <div class="csr-chart-header">
                    <h3 class="csr-chart-title"><?php _e( '會員數據庫', 'catering-sales-report' ); ?></h3>
                </div>
                <div class="csr-chart-canvas">
                    <canvas id="csr-user-trends-chart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Bottom Row - Geographic Distribution and Monthly Table -->
        <div class="csr-bottom-row">
            <div class="csr-left-column">
                <!-- Geographic Distribution (Regions) -->
                <div class="csr-geographic-widget">
                    <h3 class="csr-widget-title"><?php _e( '會員分佈', 'catering-sales-report' ); ?></h3>
                    <div class="csr-widget-subtitle"><?php echo '截至 ' . date('d-m-Y'); ?></div>
                    <div class="csr-region-list" id="geographic-distribution">
                        <div class="csr-loading-container">
                            <div class="csr-loading-spinner"></div>
                            <div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="csr-right-column">
                <!-- Monthly Table -->
                <div class="csr-monthly-table-widget">
                    <h3 class="csr-widget-title"><?php _e( '月度數據表', 'catering-sales-report' ); ?></h3>
                    <div class="csr-monthly-table" id="monthly-table">
                        <div class="csr-loading-container">
                            <div class="csr-loading-spinner"></div>
                            <div class="csr-loading-text"><?php _e( '載入中...', 'catering-sales-report' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
// Membership page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states
    initializeMembershipLoadingStates();
    
    // Load membership data when page is ready
    loadMembershipData();
});

function initializeMembershipLoadingStates() {
    // Add loading overlay to user trends chart
    addChartLoadingOverlay('#csr-user-trends-chart');
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

function loadMembershipData() {
    var dateRange = getCurrentDateRange();
    
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'membership',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updateUserTrendsChart(response.data.user_trends || []);
            updateGeographicDistribution(response.data.geographic_distribution || {});
            updateMonthlyTable(response.data.monthly_analytics || [], response.data.summary || {});
        } else {
            showMembershipError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showMembershipError(csr_ajax.strings.api_error);
    })
    .always(function() {
        // Remove loading states
        removeChartLoadingOverlay('#csr-user-trends-chart');
    });
}

function updateUserTrendsChart(userTrends) {
    var ctx = document.getElementById('csr-user-trends-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.userTrendsChart) {
        window.userTrendsChart.destroy();
    }
    
    if (!userTrends || userTrends.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無會員趨勢數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Prepare chart data
    var labels = userTrends.map(function(month) {
        return month.month_label || month.month;
    });
    
    var datasets = [
        {
            label: '<?php _e( '新會員', 'catering-sales-report' ); ?>',
            data: userTrends.map(function(month) { return month.new_users || 0; }),
            backgroundColor: '#00e69a',
            borderColor: '#00e69a',
            borderWidth: 1
        },
        {
            label: '<?php _e( '活躍會員', 'catering-sales-report' ); ?>',
            data: userTrends.map(function(month) { return month.active_users || 0; }),
            backgroundColor: '#ffbf4e',
            borderColor: '#ffbf4e',
            borderWidth: 1
        },
        {
            label: '<?php _e( '總會員人數', 'catering-sales-report' ); ?>',
            data: userTrends.map(function(month) { return month.total_users || 0; }),
            backgroundColor: '#a59fff',
            borderColor: '#a59fff',
            borderWidth: 1
        }
    ];
    
    window.userTrendsChart = new Chart(ctx, {
        type: 'bar',
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
                    stacked: false
                },
                x: {
                    stacked: false
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function updateGeographicDistribution(geographicData) {
    var html = '';
    
    if (Object.keys(geographicData).length === 0) {
        html = '<div class="csr-no-data"><?php _e( "暫無地理分佈數據", "catering-sales-report" ); ?></div>';
    } else {
        // Define Hong Kong regions mapping
        var regionMapping = {
            '香港島': ['中西區', '東區', '南區', '灣仔區'],
            '九龍': ['九龍城區', '觀塘區', '深水埗區', '黃大仙區', '油尖旺區'],
            '新界': ['離島區', '葵青區', '北區', '西貢區', '沙田區', '屯門區', '大埔區', '荃灣區', '元朗區']
        };
        
        // Calculate region totals
        var regionTotals = {
            '香港島': 0,
            '九龍': 0,
            '新界': 0
        };
        
        var totalUsers = 0;
        
        // Group districts by regions and calculate totals
        Object.keys(geographicData).forEach(function(district) {
            var count = geographicData[district].user_count || 0;
            totalUsers += count;
            
            // Find which region this district belongs to
            Object.keys(regionMapping).forEach(function(region) {
                if (regionMapping[region].includes(district)) {
                    regionTotals[region] += count;
                }
            });
        });
        
        // Define region classes for progress bars
        var regionClasses = {
            '香港島': 'region-hk',
            '九龍': 'region-kl', 
            '新界': 'region-nt'
        };
        
        // Display regions in progress bar format
        Object.keys(regionTotals).forEach(function(region) {
            var count = regionTotals[region];
            var percentage = totalUsers > 0 ? (count / totalUsers) * 100 : 0;
            var regionClass = regionClasses[region] || 'region-hk';
            
            html += '<div class="csr-region-item">';
            html += '  <div class="csr-region-name">' + region + '<span class="csr-region-count">' + count + ' / ' + totalUsers + '人</span> </div>';
            html += '  <div class="csr-region-stats">';
            html += '    <div class="csr-progress-bar">';
            html += '      <div class="csr-progress-fill ' + regionClass + '" style="width: ' + percentage + '%"></div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
        });
    }
    
    jQuery('#geographic-distribution').html(html);
}

function updateMonthlyTable(monthlyData, summary) {
    var html = '';
    
    if (!monthlyData || monthlyData.length === 0) {
        html = '<div class="csr-no-data"><?php _e( "暫無月度數據", "catering-sales-report" ); ?></div>';
    } else {
        // Create table header
        html += '<table class="csr-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th></th>';
        html += '<th>總單量</th>';
        html += '<th>人均消費</th>';
        html += '<th>90日內重複消費</th>';
        html += '<th>重複消費比例</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        // Sort monthly data by date (most recent first)
        var sortedData = monthlyData.sort(function(a, b) {
            return new Date(b.month + '-01') - new Date(a.month + '-01');
        });
        
        sortedData.forEach(function(monthData) {
            var monthLabel = formatMonthLabel(monthData.month);
            var totalOrders = monthData.total_orders || 0;
            var avgSpending = monthData.avg_spending_per_customer || 0;
            var repeatCustomers = monthData.repeat_customers_90d || 0;
            var repeatRate = monthData.repeat_purchase_rate || 0;
            
            html += '<tr>';
            html += '<td class="csr-month-cell">';
            html += monthLabel;
            html += '</td>';
            html += '<td>' + formatNumber(totalOrders) + '</td>';
            html += '<td>HK$' + formatNumber(avgSpending) + '</td>';
            html += '<td>' + formatNumber(repeatCustomers) + '</td>';
            html += '<td>' + repeatRate + '%</td>';
            html += '</tr>';
        });
        
        html += '</tbody>';
        html += '</table>';
    }
    
    jQuery('#monthly-table').html(html);
}

function formatMonthLabel(monthString) {
    if (!monthString) return '';
    var date = new Date(monthString + '-01');
    var months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
    return date.getFullYear() + '年' + months[date.getMonth()];
}

function showEmptyChart(ctx, message) {
    var container = ctx.parentElement;
    container.innerHTML = '<div class="csr-loading-container">' +
                         '<div class="csr-loading-text">' + message + '</div>' +
                         '</div>';
}

function showMembershipError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-dashboard-grid').html(errorHtml);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HKD'
    }).format(amount || 0);
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-US').format(number || 0);
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
