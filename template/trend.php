<?php
/**
 * Trend Analysis Report Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-trend-container">
    <style>
    /* Trend specific styles - Following design draft layout */
    .csr-trend-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-trend-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-trend-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-trend-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Main trend widgets */
    .csr-trend-widgets {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .csr-trend-widget {
        background: #fff;
        border-radius: 6px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .csr-widget-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f1;
        padding-bottom: 15px;
    }
    
    .csr-widget-title {
        font-size: 18px;
        font-weight: 600;
        color: #23282d;
        margin: 0;
    }
    
    /* Period comparison chart */
    .csr-period-comparison-chart {
        height: 400px;
        position: relative;
    }
    
    /* Year-over-year chart */
    .csr-yearly-chart {
        height: 450px;
        position: relative;
    }
    
    .csr-chart-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 15px;
        font-size: 14px;
    }
    
    .csr-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .csr-legend-color {
        width: 16px;
        height: 3px;
        border-radius: 2px;
    }
    
    .csr-legend-solid {
        background: #0073aa;
    }
    
    .csr-legend-dashed {
        background: #e08646ff;
        background-image: repeating-linear-gradient(
            to right,
            #e08646ff,
            #e08646ff 4px,
            transparent 4px,
            transparent 8px
        );
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
        min-height: 200px;
        flex-direction: column;
        gap: 10px;
    }
    
    @keyframes csr-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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
    
    /* Responsive design */
    @media (max-width: 768px) {
        .csr-trend-container {
            padding: 15px;
        }
        
        .csr-period-comparison-chart,
        .csr-yearly-chart {
            height: 300px;
        }
    }
    </style>

    <div class="csr-trend-header">
        <h1><?php _e( '銷售趨勢', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Trend Analysis Widgets -->
    <div class="csr-trend-widgets">
        <!-- Period Comparison Chart -->
        <div class="csr-trend-widget">
            <div class="csr-widget-header">
                <h3 class="csr-widget-title"><?php _e( '期間比較分析', 'catering-sales-report' ); ?></h3>
            </div>
            <div class="csr-chart-legend">
                <div class="csr-legend-item">
                    <div class="csr-legend-color csr-legend-solid"></div>
                    <span><?php _e( '當前期間', 'catering-sales-report' ); ?></span>
                </div>
                <div class="csr-legend-item">
                    <div class="csr-legend-color csr-legend-dashed"></div>
                    <span><?php _e( '對比期間', 'catering-sales-report' ); ?></span>
                </div>
            </div>
            <div class="csr-period-comparison-chart">
                <canvas id="csr-period-comparison-chart"></canvas>
            </div>
        </div>

        <!-- Year-over-Year Bar Chart -->
        <div class="csr-trend-widget">
            <div class="csr-widget-header">
                <h3 class="csr-widget-title"><?php _e( '近三年月度銷售對比', 'catering-sales-report' ); ?></h3>
            </div>
            <div class="csr-yearly-chart">
                <canvas id="csr-yearly-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Trend page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states
    initializeTrendLoadingStates();
    
    // Load trend data when page is ready
    loadTrendData();
});

function initializeTrendLoadingStates() {
    // Add loading overlay to charts
    addChartLoadingOverlay('#csr-period-comparison-chart');
    addChartLoadingOverlay('#csr-yearly-chart');
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

function loadTrendData() {
    var dateRange = getCurrentDateRange(true); // Get full year range if "this_year" is selected
    console.log('Loading trend data for range:', dateRange);
    // Load period comparison data
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'trend',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        console.log('Trend data response:', response);
        if (response.success) {
            // Remove loading states
            removeChartLoadingOverlay('#csr-period-comparison-chart');
            removeChartLoadingOverlay('#csr-yearly-chart');
            
            // Update charts
            updatePeriodComparisonChart(response.data);
            updateYearlyChart(response.data);
        } else {
            showTrendError(response.data.message || csr_ajax.strings.error);
        }
    })
    .fail(function() {
        // Remove loading states on error
        removeChartLoadingOverlay('#csr-period-comparison-chart');
        removeChartLoadingOverlay('#csr-yearly-chart');
        
        showTrendError(csr_ajax.strings.api_error);
    });
}

function updatePeriodComparisonChart(data) {
    var ctx = document.getElementById('csr-period-comparison-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.periodComparisonChart) {
        window.periodComparisonChart.destroy();
    }
    
    if (!data.period_comparison) {
        console.log('No period_comparison data found');
        return;
    }
    
    var comparisonData = data.period_comparison;
    console.log('Period comparison data:', comparisonData);
    
    // Create date vs date labels using actual API data
    var labels = [];
    
    // Check if we have the totals data with date keys
    if (comparisonData.current_totals && comparisonData.comparison_totals) {
        var currentDates = Object.keys(comparisonData.current_totals);
        var comparisonDates = Object.keys(comparisonData.comparison_totals);
        
        // Create labels using the actual date keys from API
        for (var i = 0; i < Math.min(currentDates.length, comparisonDates.length); i++) {
            var currentDate = currentDates[i];
            var comparisonDate = comparisonDates[i];
            labels.push(currentDate + ' vs ' + comparisonDate);
        }
    } else {
        // Fallback: calculate dates manually if totals data not available
        var dateRange = getCurrentDateRange();
        var currentStart = new Date(dateRange.start);
        var currentEnd = new Date(dateRange.end);
        var periodLength = Math.ceil((currentEnd - currentStart) / (1000 * 60 * 60 * 24)) + 1;
        
        var comparisonEnd = new Date(currentStart);
        comparisonEnd.setDate(comparisonEnd.getDate() - 1);
        var comparisonStart = new Date(comparisonEnd);
        comparisonStart.setDate(comparisonStart.getDate() - (periodLength - 1));
        
        for (var i = 0; i < comparisonData.current_period.length; i++) {
            var currentDate = new Date(currentStart);
            currentDate.setDate(currentDate.getDate() + i);
            
            var comparisonDate = new Date(comparisonStart);
            comparisonDate.setDate(comparisonDate.getDate() + i);
            
            var currentDateStr = currentDate.getFullYear() + '-' + 
                                String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(currentDate.getDate()).padStart(2, '0');
                                
            var comparisonDateStr = comparisonDate.getFullYear() + '-' + 
                                   String(comparisonDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                   String(comparisonDate.getDate()).padStart(2, '0');
            
            labels.push(currentDateStr + ' vs ' + comparisonDateStr);
        }
    }
    
    var chartData = {
        labels: labels,
        datasets: [
            {
                label: '<?php _e( "當前期間", "catering-sales-report" ); ?>',
                data: comparisonData.current_period || [],
                borderColor: '#0073aa',
                backgroundColor: 'rgba(210, 105, 30, 0.1)',
                tension: 0.4,
                fill: false,
                pointBackgroundColor: '#0073aa',
                pointBorderColor: '#0073aa',
                pointBorderWidth: 2,
                borderWidth: 3
            },
            {
                label: '<?php _e( "對比期間", "catering-sales-report" ); ?>',
                data: comparisonData.comparison_period || [],
                borderColor: '#e08646ff',
                backgroundColor: 'rgba(255, 140, 0, 0.1)',
                tension: 0.4,
                fill: false,
                pointBackgroundColor: '#e08646ff',
                pointBorderColor: '#e08646ff',
                pointBorderWidth: 2,
                borderWidth: 3,
                borderDash: [10, 5]
            }
        ]
    };
    
    window.periodComparisonChart = new Chart(ctx, {
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
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '<?php _e( "日期對比", "catering-sales-report" ); ?>'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function updateYearlyChart(data) {
    var ctx = document.getElementById('csr-yearly-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.yearlyChart) {
        window.yearlyChart.destroy();
    }
    
    if (!data.yearly_comparison) {
        return;
    }
    
    var yearlyData = data.yearly_comparison;
    var currentYear = new Date().getFullYear();
    var years = [currentYear - 2, currentYear - 1, currentYear];
    
    // Month labels in Chinese
    var monthLabels = [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月'
    ];
    
    // Color palette for years
    var yearColors = [
        '#9C27B0', // Purple for oldest year
        '#FF8C00', // Orange for middle year  
        '#D2691E', // Dark orange for current year
        '#4CAF50', // Green for future if needed
        '#2196F3'  // Blue for future if needed
    ];
    
    var datasets = [];
    years.forEach(function(year, index) {
        if (yearlyData[year]) {
            datasets.push({
                label: year.toString(),
                data: yearlyData[year],
                backgroundColor: yearColors[index],
                borderColor: yearColors[index],
                borderWidth: 1
            });
        }
    });
    
    var chartData = {
        labels: monthLabels,
        datasets: datasets
    };
    
    window.yearlyChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    },
                    title: {
                        display: true,
                        text: '<?php _e( "銷售額", "catering-sales-report" ); ?>'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '<?php _e( "月份", "catering-sales-report" ); ?>'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function showTrendError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-trend-widgets').html(errorHtml);
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