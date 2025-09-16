<?php
/**
 * Sales Channel Analysis Report Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-channel-container">
    <style>
    /* Channel page specific styles - Following design draft color scheme */
    .csr-channel-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-channel-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-channel-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-channel-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Channel widgets grid */
    .csr-channel-widgets {
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
    
    /* Channel type pie chart */
    .csr-channel-pie-chart {
        height: 400px;
        position: relative;
    }
    
    /* Channel trend bar chart */
    .csr-channel-trend-chart {
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
        .csr-channel-widgets {
            grid-template-columns: 1fr;
        }
        
        .csr-channel-container {
            padding: 15px;
        }
        
        .csr-channel-pie-chart,
        .csr-channel-trend-chart {
            height: 300px;
        }
    }
    </style>

    <div class="csr-channel-header">
        <h1><?php _e( '銷售渠道', 'catering-sales-report' ); ?></h1>
    </div>

    <!-- Channel Analysis Widgets -->
    <div class="csr-channel-widgets">
        <!-- Channel Type Distribution -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '渠道比例', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <canvas id="csr-channel-type-chart" class="csr-channel-pie-chart"></canvas>
            </div>
        </div>

        <!-- Channel Trend (Last 12 Months) -->
        <div class="csr-widget">
            <h3 class="csr-widget-title"><?php _e( '銷售渠道分佈', 'catering-sales-report' ); ?></h3>
            <div class="csr-widget-content">
                <canvas id="csr-channel-trend-chart" class="csr-channel-trend-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Channel page JavaScript
jQuery(document).ready(function($) {
    // Initialize loading states for all widgets
    initializeChannelLoadingStates();
    
    // Load channel data when page is ready
    loadChannelData();
});

function initializeChannelLoadingStates() {
    // Add loading overlay to charts
    addChartLoadingOverlay('#csr-channel-type-chart');
    addChartLoadingOverlay('#csr-channel-trend-chart');
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

function loadChannelData() {
    var dateRange = getCurrentDateRange();
    
    // Load orders for channel analysis
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'channel',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success && response.data) {
            updateChannelTypeChart(response.data.orders || []);
            updateChannelTrendChart(response.data.monthly_trends || []);
        } else {
            showChannelError(response.data || csr_ajax.strings.api_error);
        }
    })
    .fail(function() {
        showChannelError(csr_ajax.strings.api_error);
    })
    .always(function() {
        removeChartLoadingOverlay('#csr-channel-type-chart');
        removeChartLoadingOverlay('#csr-channel-trend-chart');
    });
}

function updateChannelTypeChart(orders) {
    var ctx = document.getElementById('csr-channel-type-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.channelTypeChart) {
        window.channelTypeChart.destroy();
    }
    
    if (!orders || orders.length === 0) {
        showEmptyChart(ctx, '<?php _e( '暫無渠道數據', 'catering-sales-report' ); ?>');
        return;
    }
    
    // Process channel data
    var channels = {};
    
    orders.forEach(function(order) {
        var channel = getChannelLabel(order.created_via);
        
        if (!channels[channel]) {
            channels[channel] = {
                count: 0,
                total: 0
            };
        }
        
        channels[channel].count++;
        channels[channel].total += parseFloat(order.total || 0);
    });
    
    // Convert to chart data
    var labels = [];
    var data = [];
    var colors = [];
    
    Object.keys(channels).forEach(function(channel, index) {
        labels.push(channel);
        data.push(channels[channel].total);
        colors.push(getColorForLabel(channel, index));
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
    
    window.channelTypeChart = new Chart(ctx, {
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
                            var channel = context.label;
                            var amount = formatCurrency(context.parsed);
                            var percentage = ((context.parsed / data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                            return channel + ': ' + amount + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

function updateChannelTrendChart(monthlyTrends) {
    var ctx = document.getElementById('csr-channel-trend-chart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.channelTrendChart) {
        window.channelTrendChart.destroy();
    }
    
    // Generate last 12 months labels
    var labels = [];
    var currentDate = new Date();
    
    for (var i = 11; i >= 0; i--) {
        var date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        var monthLabel = date.getFullYear() + '年' + (date.getMonth() + 1) + '月';
        labels.push(monthLabel);
    }
    
    // If no trend data provided, generate sample data
    if (!monthlyTrends || monthlyTrends.length === 0) {
        monthlyTrends = generateSampleChannelTrendData();
    }
    
    // Process trend data for stacked bar chart
    var datasets = [];
    var channels = new Set();
    
    // Extract all channels
    monthlyTrends.forEach(function(monthData) {
        if (monthData.channels) {
            Object.keys(monthData.channels).forEach(function(channel) {
                channels.add(channel);
            });
        }
    });
    
    var channelIndex = 0;
    channels.forEach(function(channel) {
        var data = labels.map(function(label, index) {
            var monthData = monthlyTrends[index];
            return monthData && monthData.channels && monthData.channels[channel] 
                ? monthData.channels[channel] 
                : 0;
        });
        
        var color = getColorForLabel(channel, channelIndex);
        datasets.push({
            label: channel,
            data: data,
            backgroundColor: color,
            borderColor: color,
            borderWidth: 1
        });
        channelIndex++;
    });
    
    window.channelTrendChart = new Chart(ctx, {
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

function getChannelLabel(createdVia) {
    // Map created_via values to Chinese labels
    switch(createdVia) {
        case 'admin':
            return '線下/活動銷售';
        case 'checkout':
            return '網頁銷售';
        default:
            return '其他渠道';
    }
}

function generateSampleChannelTrendData() {
    // Generate sample data for last 12 months
    var sampleData = [];
    var channels = ['網頁銷售', '線下/活動銷售'];
    
    for (var i = 0; i < 12; i++) {
        var monthData = {
            channels: {}
        };
        
        channels.forEach(function(channel) {
            monthData.channels[channel] = Math.random() * 80000 + 20000;
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

function showChannelError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('.csr-channel-widgets').html(errorHtml);
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
    // Define specific colors for known channels
    var colorMapping = {
        // Channel types
        '網頁銷售': '#3498db',      // Blue - Online sales
        '線下/活動銷售': '#e74c3c', // Red - Offline/Event sales  
        '其他渠道': '#2ecc71',      // Green - Other channels
        // Add more channels as needed
        'API銷售': '#f39c12',       // Orange
        '批量訂單': '#9b59b6',      // Purple
        '手動訂單': '#1abc9c'       // Turquoise
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
