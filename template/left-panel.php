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
/* Left panel styles - Updated to match design draft */

#csr-left-panel {
    width: 280px;
    color: #fff;
    background: #fff;
    overflow-y: auto;
    flex-shrink: 0;
    position: relative;
}

.csr-panel-header {
    padding: 20px;
    text-align: center;
    background: #fff;
    border-bottom: 1px solid #e0e0e0;
}

.csr-panel-logo {
    max-width: 80px;
    height: auto;
    margin: 0 auto 10px;
    display: block;
}

.csr-panel-header h2 {
    color: #333;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.csr-panel-header .csr-version {
    color: #666;
    font-size: 11px;
    margin-top: 5px;
}

.csr-nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
    background: #fff;
}

.csr-nav-item {
    display: block;
    color: #333;
    text-decoration: none;
    padding: 12px 20px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    font-size: 14px;
    background: #fff;
    margin-bottom: unset;
}

.csr-nav-item:hover {
    background: #f8f8f8;
    color: #D2691E;
}

.csr-nav-item.active {
    background: #D2691E;
    color: #fff;
}

.csr-nav-item.active:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #FF8C00;
}

/* Remove icon and description styles */
.csr-nav-icon {
    display: none;
}

.csr-nav-title {
    font-weight: 500;
    font-size: 14px;
    display: block;
}

.csr-nav-description {
    display: none;
}

/* Date range selector - Updated colors */
.csr-date-range {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #fff;
}

.csr-date-range h3 {
    color: #333;
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
}

.csr-date-range select,
.csr-date-range input {
    width: 100%;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    background: #fff;
    color: #333;
    border-radius: 4px;
    font-size: 13px;
}

.csr-date-range select:focus,
.csr-date-range input:focus {
    border-color: #D2691E;
    outline: none;
    box-shadow: 0 0 0 2px rgba(210, 105, 30, 0.1);
}

.csr-date-range .button {
    width: 100%;
    text-align: center;
    background: #ea8843;
    border:1px solid #ea8843;
    color: #fff;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.csr-date-range .button:hover {
    background: #FFF!important;
    color: #D2691E!important;
    border:1px solid #ea8843!important;
}

/* API status indicator - Updated colors and fixed positioning */
.csr-api-status {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px 20px;
    background: #fff;
    border-top: 1px solid #e0e0e0;
    z-index: 100;
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
    color: #666;
}
</style>

<div class="csr-panel-header">
    <?php
    // Get site logo - try custom logo first, then site icon, then fallback
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo_url = '';
    
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'medium' );
    } elseif ( has_site_icon() ) {
        $logo_url = get_site_icon_url( 128 );
    }
    
    if ( $logo_url ) : ?>
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="csr-panel-logo">
    <?php else : ?>
        <!-- Fallback logo placeholder -->
        <div class="csr-panel-logo" style="width: 80px; height: 80px; background: #D2691E; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px; margin: 0 auto 10px;">
            <?php echo esc_html( substr( get_bloginfo( 'name' ), 0, 2 ) ); ?>
        </div>
    <?php endif; ?>
    
    <h2><?php _e( '銷售報告', 'catering-sales-report' ); ?></h2>
    <div class="csr-version"><?php echo sprintf( __( 'Version %s', 'catering-sales-report' ), CSR_VERSION ); ?></div>
</div>

<!-- Date Range Selector -->
<div class="csr-date-range">
    <h3><?php _e( '日期範圍', 'catering-sales-report' ); ?></h3>
    
    <select id="csr-date-preset">
        <?php foreach ( CSR_Init::get_date_range_options() as $key => $label ): ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, 'this_month' ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
    </select>
    
    <div id="csr-custom-dates" style="display: none;">
        <input type="date" id="csr-start-date" placeholder="<?php _e( '開始日期', 'catering-sales-report' ); ?>">
        <input type="date" id="csr-end-date" placeholder="<?php _e( '結束日期', 'catering-sales-report' ); ?>">
    </div>
    
    <button type="button" class="button" id="csr-apply-date-range">
        <?php _e( '套用', 'catering-sales-report' ); ?>
    </button>
</div>

<!-- Navigation Menu -->
<ul class="csr-nav-menu">
    <?php 
    // Simplified menu items with Chinese labels to match design
    $simplified_menu = array(
        'overview' => '銷售總覽',
        'trend' => '銷售趨勢', 
        'payment' => '付款方式',
        'channel' => '銷售渠道',
        'product-sales' => '產品銷售',
        'promotion' => '促銷活動',
        'region' => '地區銷售',
        'membership' => '會員分析'
    );
    
    foreach ( $simplified_menu as $page_key => $title ): 
        if ( isset( $report_pages[$page_key] ) ): ?>
            <li class="csr-nav-item<?php echo ( $current_page === $page_key ) ? ' active' : ''; ?>" 
                data-report="<?php echo esc_attr( $page_key ); ?>">
                <span class="csr-nav-title"><?php echo esc_html( $title ); ?></span>
            </li>
        <?php endif;
    endforeach; ?>
</ul>

<!-- API Status - Fixed at bottom -->
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
        
        // Trigger report refresh with new date range
        if (typeof loadReportContent === 'function') {
            var currentReport = $('.csr-nav-item.active').data('report') || 'overview';
            loadReportContent(currentReport);
        }
    });
});


function getCurrentDateRange( getFullRange = false ) {
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
            // End should be Sunday of this week, not today
            end = new Date(start);
            end.setDate(end.getDate() + 6); // End of this week (Sunday)
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
            // End should be last day of this month, not today
            if ( getFullRange ) {
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of current month
            } else {
                end = new Date(today); // Up to today
            }
            break;
            
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1); // First day of last month
            end = new Date(today.getFullYear(), today.getMonth(), 0); // Last day of last month
            break;
            
        case 'this_year':
            start = new Date(today.getFullYear(), 0, 1); // January 1st of current year
            if ( getFullRange ) {
                end = new Date(today.getFullYear(), 11, 31); // December 31st of current year
            } else {
                end = new Date(today); // Up to today
            }
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
</script>