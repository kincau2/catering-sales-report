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
            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
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
        getCurrentDateRange();
        // Trigger report refresh with new date range
        if (typeof loadReportContent === 'function') {
            var currentReport = $('.csr-nav-item.active').data('report') || 'overview';
            loadReportContent(currentReport);
        }
        
        // Update quick stats
        updateQuickStats(preset, startDate, endDate);
    });
    
    // Load initial quick stats
    <?php if ( $credentials_configured ): ?>
        updateQuickStats('this_month');
    <?php endif; ?>
});


function getCurrentDateRange() {
    // Get current date range from the date picker
    var preset = jQuery('#csr-date-preset').val() || 'this_month';
    
    if (preset === 'custom' ) {
        startDate = jQuery('#csr-start-date').val();
        endDate = jQuery('#csr-end-date').val();
    }
    
    // Calculate date range based on preset
    console.log( new Date() );
    switch (preset) {
        case 'today':
            startDate = new Date();
            endDate = new Date();
            break;
        case 'yesterday':
            startDate = setDate( new Date() - 1);
            endDate = setDate( new Date() - 1);
            break;
        case 'this_week':
            startDate.setDate(startDate.getDate() - startDate.getDay());
            break;
        case 'last_week':
            startDate.setDate(startDate.getDate() - startDate.getDay() - 7);
            endDate.setDate(endDate.getDate() - endDate.getDay() - 1);
            break;
        case 'this_month':
            start.setDate(1);
            break;
        case 'last_month':
            start.setMonth(start.getMonth() - 1);
            start.setDate(1);
            end.setDate(0); // Last day of previous month
            break;
        case 'this_year':
            start.setMonth(0); // January
            start.setDate(1);
            break;
        case 'last_year':
            start.setFullYear(start.getFullYear() - 1);
            start.setMonth(0); // January
            start.setDate(1);
        default:
            start.setDate(1); // Default to this month
    }
    
    return {
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0]
    };
}

function updateQuickStats(preset, startDate, endDate) {
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_quick_stats',
        preset: preset,
        start_date: startDate,
        end_date: endDate,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success) {
            var stats = response.data;
            jQuery('#csr-stat-today-sales').text(stats.today_sales || '--');
            jQuery('#csr-stat-month-sales').text(stats.month_sales || '--');
            jQuery('#csr-stat-total-orders').text(stats.total_orders || '--');
            jQuery('#csr-stat-avg-order').text(stats.avg_order || '--');
        }
    })
    .fail(function() {
        // Keep showing placeholder values on error
        console.log('Failed to load quick stats');
    });
}
</script>