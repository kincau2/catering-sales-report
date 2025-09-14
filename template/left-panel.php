<?php
/**
 * Left Panel Template - Navigation for Sales Report Dashboard
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

$current_page = isset( $current_page ) ? $current_page : CSR_Init::get_current_page();
$report_pages = isset( $report_pages ) ? $report_pages : CSR_Init::get_report_pages();
?>

<style>
/* Left panel styles */
.csr-panel-header {
    padding: 20px;
    border-bottom: 1px solid #32373c;
    background: #191e23;
}

.csr-panel-header h2 {
    color: #fff;
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.csr-panel-header .csr-version {
    color: #a0a5aa;
    font-size: 12px;
    margin-top: 5px;
}

.csr-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
}

.csr-nav-item {
    display: block;
    color: #a0a5aa;
    text-decoration: none;
    padding: 15px 20px;
    border-bottom: 1px solid #32373c;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}

.csr-nav-item:hover {
    background: #32373c;
    color: #00b9eb;
}

.csr-nav-item.active {
    background: #0073aa;
    color: #fff;
}

.csr-nav-item.active:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #00b9eb;
}

.csr-nav-icon {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    vertical-align: top;
    display: inline-block;
}

.csr-nav-icon:before {
    font-family: dashicons;
    font-size: 20px;
    line-height: 1;
}

.csr-nav-title {
    font-weight: 500;
    font-size: 14px;
    display: block;
}

.csr-nav-description {
    font-size: 12px;
    color: #8c8f94;
    margin-top: 3px;
    line-height: 1.4;
    display: block;
}

.csr-nav-item:hover .csr-nav-description {
    color: #a0a5aa;
}

.csr-nav-item.active .csr-nav-description {
    color: #e1f5fe;
}

/* Date range selector */
.csr-date-range {
    padding: 20px;
    border-bottom: 1px solid #32373c;
    background: #191e23;
}

.csr-date-range h3 {
    color: #fff;
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
}

.csr-date-range select,
.csr-date-range input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #32373c;
    background: #32373c;
    color: #fff;
    border-radius: 3px;
    font-size: 13px;
}

.csr-date-range .button {
    width: 100%;
    text-align: center;
    background: #0073aa;
    border-color: #0073aa;
    color: #fff;
    padding: 8px;
    font-size: 13px;
}

.csr-date-range .button:hover {
    background: #005a87;
    border-color: #005a87;
}

/* Quick stats */
.csr-quick-stats {
    padding: 20px;
    background: #191e23;
}

.csr-quick-stats h3 {
    color: #fff;
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
}

.csr-stat-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #32373c;
    color: #a0a5aa;
    font-size: 13px;
}

.csr-stat-item:last-child {
    border-bottom: none;
}

.csr-stat-value {
    color: #00b9eb;
    font-weight: 600;
}

/* API status indicator */
.csr-api-status {
    padding: 15px 20px;
    background: #191e23;
    border-top: 1px solid #32373c;
}

.csr-api-status-indicator {
    display: flex;
    align-items: center;
    font-size: 12px;
}

.csr-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.csr-status-dot.connected {
    background: #46b450;
}

.csr-status-dot.disconnected {
    background: #dc3232;
}

.csr-status-text {
    color: #a0a5aa;
}
</style>

<div class="csr-panel-header">
    <h2><?php _e( 'Sales Reports', 'catering-sales-report' ); ?></h2>
    <div class="csr-version"><?php echo sprintf( __( 'Version %s', 'catering-sales-report' ), CSR_VERSION ); ?></div>
</div>

<!-- Date Range Selector -->
<div class="csr-date-range">
    <h3><?php _e( 'Date Range', 'catering-sales-report' ); ?></h3>
    
    <select id="csr-date-preset">
        <?php foreach ( CSR_Init::get_date_range_options() as $key => $label ): ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, 'this_month' ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
    </select>
    
    <div id="csr-custom-dates" style="display: none;">
        <input type="date" id="csr-start-date" placeholder="<?php _e( 'Start Date', 'catering-sales-report' ); ?>">
        <input type="date" id="csr-end-date" placeholder="<?php _e( 'End Date', 'catering-sales-report' ); ?>">
    </div>
    
    <button type="button" class="button" id="csr-apply-date-range">
        <?php _e( 'Apply', 'catering-sales-report' ); ?>
    </button>
</div>

<!-- Navigation Menu -->
<ul class="csr-nav-menu">
    <?php foreach ( $report_pages as $page_key => $page_data ): ?>
        <li class="csr-nav-item<?php echo ( $current_page === $page_key ) ? ' active' : ''; ?>" 
            data-report="<?php echo esc_attr( $page_key ); ?>">
            <span class="csr-nav-icon dashicons <?php echo esc_attr( $page_data['icon'] ); ?>"></span>
            <span class="csr-nav-title"><?php echo esc_html( $page_data['title'] ); ?></span>
            <span class="csr-nav-description"><?php echo esc_html( $page_data['description'] ); ?></span>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Quick Stats -->
<div class="csr-quick-stats">
    <h3><?php _e( 'Quick Stats', 'catering-sales-report' ); ?></h3>
    <div id="csr-quick-stats-content">
        <div class="csr-stat-item">
            <span><?php _e( 'Today\'s Sales', 'catering-sales-report' ); ?></span>
            <span class="csr-stat-value" id="csr-stat-today-sales">--</span>
        </div>
        <div class="csr-stat-item">
            <span><?php _e( 'This Month', 'catering-sales-report' ); ?></span>
            <span class="csr-stat-value" id="csr-stat-month-sales">--</span>
        </div>
        <div class="csr-stat-item">
            <span><?php _e( 'Total Orders', 'catering-sales-report' ); ?></span>
            <span class="csr-stat-value" id="csr-stat-total-orders">--</span>
        </div>
        <div class="csr-stat-item">
            <span><?php _e( 'Avg. Order Value', 'catering-sales-report' ); ?></span>
            <span class="csr-stat-value" id="csr-stat-avg-order">--</span>
        </div>
    </div>
</div>

<!-- API Status -->
<div class="csr-api-status">
    <div class="csr-api-status-indicator">
        <span class="csr-status-dot <?php echo $credentials_configured ? 'connected' : 'disconnected'; ?>"></span>
        <span class="csr-status-text">
            <?php echo $credentials_configured 
                ? __( 'API Connected', 'catering-sales-report' ) 
                : __( 'API Not Configured', 'catering-sales-report' ); ?>
        </span>
    </div>
</div>

<script>
jQuery(document).ready(function($) {

    var startDate, endDate;
    // Date range selector functionality
    $('#csr-date-preset').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#csr-custom-dates').show();
        } else {
            $('#csr-custom-dates').hide();
        }
    });
    
    // Apply date range
    $('#csr-apply-date-range').on('click', function() {
        var preset = $('#csr-date-preset').val();
        
        // Get the current date range and assign to global variables
        getCurrentDateRange();
        
        // Trigger report refresh with new date range
        if (typeof loadReportContent === 'function') {
            var currentReport = $('.csr-nav-item.active').data('report') || 'overview';
            loadReportContent(currentReport);
        }
        
        // Update quick stats
        // updateQuickStats(preset, startDate, endDate);
    });
    
    // Load initial quick stats
    <?php if ( $credentials_configured ): ?>
        // updateQuickStats('this_month');
    <?php endif; ?>
});


function getCurrentDateRange() {
    // Get current date range from the date picker
    var preset = jQuery('#csr-date-preset').val() || 'this_month';
    
    if (preset === 'custom') {
        var customStart = jQuery('#csr-start-date').val();
        var customEnd = jQuery('#csr-end-date').val();
        
        // Only use custom dates if both are provided
        if (customStart && customEnd) {
            startDate = customStart;
            endDate = customEnd;
            return { start: startDate, end: endDate };
        } else {
            // If custom is selected but no dates provided, fall back to this_month
            preset = 'this_month';
        }
    }
    
    // Calculate date range based on preset
    var today = new Date();
    var start = new Date();
    var end = new Date();
    
    switch (preset) {
        case 'today':
            start = new Date(today);
            end = new Date(today);
            break;
            
        case 'yesterday':
            start = new Date(today);
            start.setDate(start.getDate() - 1);
            end = new Date(start);
            break;
            
        case 'this_week':
            // Calculate start of week (Monday-based week)
            start = new Date(today);
            var dayOfWeek = start.getDay(); // 0 = Sunday, 1 = Monday, etc.
            var daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Make Monday the start
            start.setDate(start.getDate() - daysToSubtract);
            end = new Date(today);
            break;
            
        case 'last_week':
            // Calculate last week (Monday to Sunday)
            start = new Date(today);
            var dayOfWeek = start.getDay();
            var daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
            start.setDate(start.getDate() - daysToSubtract - 7); // Start of last week
            end = new Date(start);
            end.setDate(end.getDate() + 6); // End of last week (Sunday)
            break;
            
        case 'this_month':
            start = new Date(today.getFullYear(), today.getMonth(), 1); // First day of current month
            end = new Date(today);
            break;
            
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1); // First day of last month
            end = new Date(today.getFullYear(), today.getMonth(), 0); // Last day of last month
            break;
            
        case 'this_quarter':
            var quarterStartMonth = Math.floor(today.getMonth() / 3) * 3;
            start = new Date(today.getFullYear(), quarterStartMonth, 1);
            end = new Date(today);
            break;
            
        case 'last_quarter':
            var lastQuarterStartMonth = Math.floor(today.getMonth() / 3) * 3 - 3;
            if (lastQuarterStartMonth < 0) {
                lastQuarterStartMonth = 9;
                start = new Date(today.getFullYear() - 1, lastQuarterStartMonth, 1);
                end = new Date(today.getFullYear(), 0, 0); // Last day of December previous year
            } else {
                start = new Date(today.getFullYear(), lastQuarterStartMonth, 1);
                end = new Date(today.getFullYear(), lastQuarterStartMonth + 3, 0); // Last day of quarter
            }
            break;
            
        case 'this_year':
            start = new Date(today.getFullYear(), 0, 1); // January 1st of current year
            end = new Date(today);
            break;
            
        case 'last_year':
            start = new Date(today.getFullYear() - 1, 0, 1); // January 1st of last year
            end = new Date(today.getFullYear() - 1, 11, 31); // December 31st of last year
            break;
            
        default:
            // Default to this month
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today);
    }
    
    // Format dates as YYYY-MM-DD
    startDate = start.getFullYear() + '-' + 
                String(start.getMonth() + 1).padStart(2, '0') + '-' + 
                String(start.getDate()).padStart(2, '0');
                
    endDate = end.getFullYear() + '-' + 
              String(end.getMonth() + 1).padStart(2, '0') + '-' + 
              String(end.getDate()).padStart(2, '0');

    return { start: startDate, end: endDate };
}

// function updateQuickStats(preset, startDate, endDate) {
//     jQuery.post(csr_ajax.ajax_url, {
//         action: 'csr_get_quick_stats',
//         preset: preset,
//         start_date: startDate,
//         end_date: endDate,
//         nonce: csr_ajax.nonce
//     })
//     .done(function(response) {
//         if (response.success) {
//             var stats = response.data;
//             jQuery('#csr-stat-today-sales').text(stats.today_sales || '--');
//             jQuery('#csr-stat-month-sales').text(stats.month_sales || '--');
//             jQuery('#csr-stat-total-orders').text(stats.total_orders || '--');
//             jQuery('#csr-stat-avg-order').text(stats.avg_order || '--');
//         }
//     })
//     .fail(function() {
//         // Keep showing placeholder values on error
//         console.log('Failed to load quick stats');
//     });
// }
</script>