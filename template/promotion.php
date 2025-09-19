<?php
/**
 * Promotion Analysis Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-promotion-container">
    <style>
    /* Promotion specific styles - Following overview.php design pattern */
    .csr-promotion-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-promotion-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-promotion-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-promotion-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Main dashboard grid - Following overview.php pattern */
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
    
    .csr-metric-card.quaternary {
        background: #B8860B; /* Gold variant */
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
    
    /* Promotion metrics grid - keep old styles for backward compatibility */
    .csr-promotion-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .csr-promotion-metric-card {
        background: #D2691E; /* Orange theme matching overview */
        color: #fff;
        border-radius: 6px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .csr-promotion-metric-card.secondary {
        background: #FF8C00;
    }
    
    .csr-promotion-metric-card.tertiary {
        background: #CD853F;
    }
    
    .csr-promotion-metric-card.quaternary {
        background: #B8860B;
    }
    
    .csr-promotion-metric-value {
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 5px 0;
        line-height: 1.1;
    }
    
    .csr-promotion-metric-label {
        font-size: 14px;
        color: rgba(255,255,255,0.9);
        margin: 0;
        line-height: 1.3;
    }
    
    /* Latest promotions section */
    .csr-latest-promotions {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .csr-latest-promotions h2 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        text-align: center;
    }
    
    /* Promotion cards grid */
    .csr-promotion-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .csr-promotion-card {
        background: #f8f5f0;
        border-radius: 12px;
        padding: 24px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        min-height: 200px;
    }
    
    .csr-promotion-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .csr-promotion-card.active {
        background: #f8f5f0;
    }
    
    .csr-promotion-card.expired {
        background: #f5f5f5;
        opacity: 0.7;
    }
    
    .csr-promotion-title {
        font-size: 20px;
        font-weight: 700;
        color: #D2691E;
        margin: 0 0 8px 0;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .csr-promotion-description {
        font-size: 13px;
        color: #8B4513;
        margin: 0 0 8px 0;
        text-align: center;
        font-weight: 600;
    }
    
    .csr-promotion-code {
        font-size: 14px;
        color: #8B4513;
        margin: 0 0 8px 0;
        text-align: center;
        font-weight: 600;
    }
    
    .csr-promotion-period-label {
        font-size: 12px;
        color: #8B4513;
        margin: 0 0 4px 0;
        text-align: center;
        font-weight: 600;
    }
    
    .csr-promotion-period {
        font-size: 13px;
        color: #D2691E;
        margin: 0 0 24px 0;
        text-align: center;
        font-weight: 600;
    }
    
    .csr-promotion-stats {
        background: rgba(255,255,255,0.7);
        border-radius: 8px;
        padding: 16px;
        margin-top: auto;
    }
    
    .csr-promotion-stat-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        align-items: center;
    }
    
    .csr-promotion-stat-row:last-child {
        margin-bottom: 0;
    }
    
    .csr-promotion-stat-label {
        color: #666;
        font-weight: 600;
        font-size: 13px;
    }
    
    .csr-promotion-stat-value {
        color: #23282d;
        font-weight: 700;
        font-size: 15px;
    }
    
    .csr-promotion-revenue {
        color: #D2691E !important;
        font-weight: 700 !important;
        font-size: 15px !important;
    }
    
    /* Show more button */
    .csr-show-more-promotions {
        text-align: center;
        margin-top: 20px;
    }
    
    .csr-show-more-btn {
        background: #D2691E;
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .csr-show-more-btn:hover {
        background: #B8530C;
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
    
    .csr-promotion-metric-card.loading .csr-promotion-metric-value {
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
    .csr-no-promotions {
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
        
        .csr-promotion-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .csr-promotion-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .csr-metrics-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .csr-promotion-cards {
            grid-template-columns: 1fr;
        }
        
        .csr-promotion-metrics {
            grid-template-columns: 1fr;
        }
        
        .csr-metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .csr-promotion-container {
            padding: 15px;
        }
    }
    </style>

    <!-- Header -->
    <div class="csr-promotion-header">
        <h1><?php _e( '促銷活動', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Main Dashboard Grid - Following overview.php pattern -->
    <div class="csr-dashboard-grid">
        <div class="csr-left-column">
            <!-- Sales trend chart with coupon usage -->
            <div class="csr-main-chart">
                <div class="csr-chart-header">
                    <h3 class="csr-chart-title"><?php _e( '促銷銷售趨勢', 'catering-sales-report' ); ?></h3>
                </div>
                <div class="csr-chart-canvas">
                    <canvas id="csr-promotion-sales-trend-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="csr-right-column">
            <!-- Promotion metrics grid -->
            <div class="csr-metrics-grid">
                <div class="csr-metric-card loading">
                    <div class="csr-metric-label"><?php _e( '總促銷活動', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-total-promotions">-</div>
                </div>
                
                <div class="csr-metric-card secondary loading">
                    <div class="csr-metric-label"><?php _e( '活躍促銷', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-active-promotions">-</div>
                </div>
                
                <div class="csr-metric-card tertiary loading">
                    <div class="csr-metric-label"><?php _e( '總使用次數', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-total-usage">-</div>
                </div>
                
                <div class="csr-metric-card quaternary loading">
                    <div class="csr-metric-label"><?php _e( '促銷收入', 'catering-sales-report' ); ?></div>
                    <div class="csr-metric-value" id="metric-promotion-revenue">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Promotions -->
    <div class="csr-latest-promotions">
        <h2><?php _e( '最新促銷活動', 'catering-sales-report' ); ?></h2>
        
        <div id="csr-promotion-cards-container">
            <div class="csr-loading-container">
                <div class="csr-loading-spinner"></div>
                <div class="csr-loading-text"><?php _e( '載入促銷資料中...', 'catering-sales-report' ); ?></div>
            </div>
        </div>

        <div class="csr-show-more-promotions" style="display: none;">
            <button class="csr-show-more-btn" id="show-more-promotions">
                <?php _e( '更多', 'catering-sales-report' ); ?>
            </button>
        </div>
    </div>
</div>

<script>
// Promotion page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states
    initializePromotionLoadingStates();
    
    // Load promotion data when page is ready
    loadPromotionData();
    
    // Show more button click handler
    $('#show-more-promotions').on('click', function() {
        showAllPromotions();
    });
});

function initializePromotionLoadingStates() {
    // Add loading states to metric cards
    jQuery('.csr-metric-card').addClass('loading');
    jQuery('.csr-promotion-metric-card').addClass('loading');
    
    // Add loading overlay to sales trend chart
    addChartLoadingOverlay('#csr-promotion-sales-trend-chart');
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

function loadPromotionData() {
    var dateRange = getCurrentDateRange();
    
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'promotion',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updatePromotionMetrics(response.data);
            updatePromotionCards(response.data.coupons || []);
            updatePromotionSalesTrendChart(response.data.sales_trend || []);
        } else {
            showPromotionError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showPromotionError(csr_ajax.strings.api_error);
    })
    .always(function() {
        // Remove loading states
        jQuery('.csr-metric-card').removeClass('loading');
        jQuery('.csr-promotion-metric-card').removeClass('loading');
        removeChartLoadingOverlay('#csr-promotion-sales-trend-chart');
    });
}

function updatePromotionMetrics(data) {
    var coupons = data.coupons || [];
    var totalUsage = 0;
    var totalRevenue = 0;
    var activeCount = 0;
    var currentDate = new Date();
    
    // Calculate metrics from coupon data
    coupons.forEach(function(coupon) {
        totalUsage += parseInt(coupon.usage_count || 0);
        totalRevenue += parseFloat(coupon.total_revenue || 0);
        
        // Check if coupon is currently active
        var expiryDate = coupon.date_expires ? new Date(coupon.date_expires) : null;
        if (!expiryDate || expiryDate > currentDate) {
            activeCount++;
        }
    });
    
    // Update metric displays - using new IDs for dashboard grid
    jQuery('#metric-total-promotions').text(coupons.length);
    jQuery('#metric-active-promotions').text(activeCount);
    jQuery('#metric-total-usage').text(totalUsage);
    jQuery('#metric-promotion-revenue').text(formatCurrency(totalRevenue));
    
    // Also update old IDs for backward compatibility
    jQuery('#total-promotions').text(coupons.length);
    jQuery('#active-promotions').text(activeCount);
    jQuery('#total-usage').text(totalUsage);
    jQuery('#total-revenue').text(formatCurrency(totalRevenue));
}

function updatePromotionCards(coupons) {
    var container = jQuery('#csr-promotion-cards-container');
    
    if (!coupons || coupons.length === 0) {
        container.html('<div class="csr-no-promotions">' + 
                      '<?php _e( "暫無促銷活動資料", "catering-sales-report" ); ?>' + 
                      '</div>');
        return;
    }
    
    // Sort coupons by usage count (descending)
    coupons.sort(function(a, b) {
        return (parseInt(b.usage_count) || 0) - (parseInt(a.usage_count) || 0);
    });
    
    var html = '<div class="csr-promotion-cards">';
    var displayLimit = 6; // Show first 6 promotions
    var currentDate = new Date();
    
    coupons.slice(0, displayLimit).forEach(function(coupon) {
        var isExpired = coupon.date_expires && new Date(coupon.date_expires) < currentDate;
        var cardClass = isExpired ? 'expired' : 'active';
        
        // Format dates
        var startDate = coupon.date_created ? formatDate(coupon.date_created) : '';
        var endDate = coupon.date_expires ? formatDate(coupon.date_expires) : '無期限';
        var dateRange = startDate + (endDate !== '無期限' ? ' - ' + endDate : ' - ' + endDate);
        
        html += '<div class="csr-promotion-card ' + cardClass + '">';
        html += '<div class="csr-promotion-header">';
        html += '<div class="csr-promotion-title">' + escapeHtml(coupon.description || coupon.code || 'N/A') + '</div>';
        html += '<div class="csr-promotion-code">' + escapeHtml(coupon.code || 'N/A') + '</div>';
        html += '</div>';
        html += '<div class="csr-promotion-period">' + dateRange + '</div>';
        html += '<div class="csr-promotion-stats">';
        html += '<div class="csr-promotion-stat-item">';
        html += '<div class="csr-promotion-stat-value">' + (coupon.usage_count || 0) + '</div>';
        html += '<div class="csr-promotion-stat-label">交易數量(單)</div>';
        html += '</div>';
        html += '<div class="csr-promotion-stat-item">';
        html += '<div class="csr-promotion-stat-value csr-promotion-revenue">' + formatCurrency(coupon.total_revenue || 0) + '</div>';
        html += '<div class="csr-promotion-stat-label">總收入</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    html += '</div>';
    
    container.html(html);
    
    // Show "更多" button if there are more than 6 promotions
    if (coupons.length > displayLimit) {
        jQuery('.csr-show-more-promotions').show();
        
        // Store all coupons data for "show more" functionality
        window.allPromotions = coupons;
    } else {
        jQuery('.csr-show-more-promotions').hide();
    }
}

function showAllPromotions() {
    if (!window.allPromotions) return;
    
    var container = jQuery('#csr-promotion-cards-container');
    var html = '<div class="csr-promotion-cards">';
    var currentDate = new Date();
    
    window.allPromotions.forEach(function(coupon) {
        var isExpired = coupon.date_expires && new Date(coupon.date_expires) < currentDate;
        var cardClass = isExpired ? 'expired' : 'active';
        
        // Format dates
        var startDate = coupon.date_created ? formatDate(coupon.date_created) : '';
        var endDate = coupon.date_expires ? formatDate(coupon.date_expires) : '無期限';
        var dateRange = startDate + (endDate !== '無期限' ? ' - ' + endDate : ' - ' + endDate);
        
        html += '<div class="csr-promotion-card ' + cardClass + '">';
        html += '<div class="csr-promotion-header">';
        html += '<div class="csr-promotion-title">' + escapeHtml(coupon.description || coupon.code || 'N/A') + '</div>';
        html += '<div class="csr-promotion-code">' + escapeHtml(coupon.code || 'N/A') + '</div>';
        html += '</div>';
        html += '<div class="csr-promotion-period">' + dateRange + '</div>';
        html += '<div class="csr-promotion-stats">';
        html += '<div class="csr-promotion-stat-item">';
        html += '<div class="csr-promotion-stat-value">' + (coupon.usage_count || 0) + '</div>';
        html += '<div class="csr-promotion-stat-label">交易數量(單)</div>';
        html += '</div>';
        html += '<div class="csr-promotion-stat-item">';
        html += '<div class="csr-promotion-stat-value csr-promotion-revenue">' + formatCurrency(coupon.total_revenue || 0) + '</div>';
        html += '<div class="csr-promotion-stat-label">總收入</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    html += '</div>';
    
    container.html(html);
    
    // Hide the "更多" button
    jQuery('.csr-show-more-promotions').hide();
}

function updatePromotionSalesTrendChart(salesTrend) {
    var ctx = document.getElementById('csr-promotion-sales-trend-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.promotionSalesTrendChart) {
        window.promotionSalesTrendChart.destroy();
    }
    
    if (!salesTrend || salesTrend.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無促銷銷售趨勢數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Prepare chart data
    var labels = [];
    var couponSalesData = [];
    var totalSalesData = [];
    
    salesTrend.forEach(function(monthData) {
        labels.push(monthData.period_label);
        couponSalesData.push(parseFloat(monthData.coupon_sales) || 0);
        totalSalesData.push(parseFloat(monthData.total_sales) || 0);
    });
    
    var chartData = {
        labels: labels,
        datasets: [
            {
                label: '<?php _e( '促銷訂單銷售額', 'catering-sales-report' ); ?>',
                data: couponSalesData,
                borderColor: '#cf6316ff',
                backgroundColor: 'rgba(210, 105, 30, 0.1)',
                tension: 0.1,
                fill: true
            },
            {
                label: '<?php _e( '總銷售額', 'catering-sales-report' ); ?>',
                data: totalSalesData,
                borderColor: '#2ad083ff',
                backgroundColor: 'rgba(64, 216, 43, 0.1)',
                tension: 0.1,
                fill: false
            }
        ]
    };
    
    window.promotionSalesTrendChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += formatCurrency(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: '<?php _e( '月份', 'catering-sales-report' ); ?>'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: '<?php _e( '銷售額 (HKD)', 'catering-sales-report' ); ?>'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function showEmptyChart(ctx, message) {
    var container = ctx.parentElement;
    container.innerHTML = '<div class="csr-loading-container">' +
                         '<div class="csr-loading-text">' + message + '</div>' +
                         '</div>';
}

function showPromotionError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('#csr-promotion-cards-container').html(errorHtml);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'HKD'
    }).format(amount || 0);
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    var date = new Date(dateString);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    
    return day + '/' + month + '/' + year;
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
