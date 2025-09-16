<?php
/**
 * Dashboard Template - Main Full Screen Interface
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<style>
/* Full screen overlay styles */
#csr-dashboard-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f1f1f1;
    z-index: 999999;
    overflow: hidden;
}

#csr-dashboard {
    display: flex;
    height: 100vh;
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
}

#csr-content-area > div {
    min-height: calc(100vh - 60px);
}

#csr-main-content {
    flex: 1;
    background: #fff;
    overflow-y: auto;
    position: relative;
}

#csr-close-dashboard {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #0073aa;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    z-index: 1000;
}

#csr-close-dashboard:hover {
    background: #005a87;
}

/* Loading states */
.csr-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
    font-size: 16px;
    color: #666;
}

.csr-loading:before {
    content: "";
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-top: 2px solid #0073aa;
    border-radius: 50%;
    animation: csr-spin 1s linear infinite;
    margin-right: 10px;
}

@keyframes csr-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error states */
.csr-error {
    background: #fff2f2;
    border: 1px solid #dc3232;
    color: #dc3232;
    padding: 15px;
    margin: 20px;
    border-radius: 3px;
}

/* API not configured warning */
.csr-api-warning {
    background: #fff8e1;
    border: 1px solid #ffb900;
    color: #8a6914;
    padding: 20px;
    margin: 20px;
    border-radius: 3px;
    text-align: center;
}

.csr-api-warning h3 {
    margin-top: 0;
    color: #8a6914;
}

.csr-api-warning .button {
    margin-top: 10px;
}
</style>

<div id="csr-dashboard-overlay">
    <div id="csr-dashboard">
        <!-- Close button -->
        <button id="csr-close-dashboard" onclick="closeDashboard()">
            <?php _e( '← 回到 WordPress 後台', 'catering-sales-report' ); ?>
        </button>
        
        <!-- Left Panel -->
        <div id="csr-left-panel">
            <?php include CSR_PLUGIN_PATH . 'template/left-panel.php'; ?>
        </div>
        
        <!-- Main Content Area -->
        <div id="csr-main-content">
            <?php if ( ! $credentials_configured ): ?>
                <div class="csr-api-warning">
                    <h3><?php _e( 'API Configuration Required', 'catering-sales-report' ); ?></h3>
                    <p><?php _e( 'Please configure your WooCommerce API credentials to start viewing sales reports.', 'catering-sales-report' ); ?></p>
                    <a href="<?php echo admin_url( 'admin.php?page=csr-settings' ); ?>" class="button button-primary" onclick="closeDashboard()">
                        <?php _e( 'Configure API Settings', 'catering-sales-report' ); ?>
                    </a>
                </div>
            <?php else: ?>
                <div id="csr-content-area">
                    <div class="csr-loading">
                        <?php _e( 'Loading report data...', 'catering-sales-report' ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Dashboard JavaScript functionality
jQuery(document).ready(function($) {
    // Initialize dashboard
    initializeDashboard();
    
    // Load initial content if API is configured
    <?php if ( $credentials_configured ): ?>
        loadReportContent('<?php echo esc_js( $current_page ); ?>');
    <?php endif; ?>
});

function initializeDashboard() {
    // Hide WordPress admin elements
    jQuery('#adminmenumain').hide();
    jQuery('#wpadminbar').hide();
    jQuery('html').css('margin-top', '0');
    
    // Set up navigation handlers
    jQuery('.csr-nav-item').on('click', function(e) {
        e.preventDefault();
        
        var reportType = jQuery(this).data('report');
        if (reportType) {
            loadReportContent(reportType);
            
            // Update active state
            jQuery('.csr-nav-item').removeClass('active');
            jQuery(this).addClass('active');
            
            // Update URL without page reload
            var newUrl = updateUrlParameter(window.location.href, 'report', reportType);
            window.history.pushState({path: newUrl}, '', newUrl);
        }
    });
}

function loadReportContent(reportType) {
    var $contentArea = jQuery('#csr-content-area');
    
    // Show loading state
    $contentArea.html('<div class="csr-loading">' + csr_ajax.strings.loading + '</div>');
    
    // Make AJAX request for report content
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_content',
        report_type: reportType,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        
        if (response.success) {
            $contentArea.html(response.data.html);
            
            // Initialize any charts or interactive elements
            if (typeof initializeCharts === 'function') {
                initializeCharts(response.data.chartData);
            }
        } else {
            $contentArea.html('<div class="csr-error">' + response.data.message + '</div>');
        }
    })
    .fail(function() {
        $contentArea.html('<div class="csr-error">' + csr_ajax.strings.error + '</div>');
    });
}

function closeDashboard() {
    window.location.href = '<?php echo admin_url( ); ?>';
}

function updateUrlParameter(url, param, paramVal) {
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var additionalURL = tempArray[1];
    var temp = "";
    if (additionalURL) {
        tempArray = additionalURL.split("&");
        for (var i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
                newAdditionalURL += temp + tempArray[i];
                temp = "&";
            }
        }
    }
    
    var rows_txt = temp + "" + param + "=" + paramVal;
    return baseURL + "?" + newAdditionalURL + rows_txt;
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function(e) {
    var urlParams = new URLSearchParams(window.location.search);
    var reportType = urlParams.get('report') || 'overview';
    loadReportContent(reportType);
    
    // Update active navigation
    jQuery('.csr-nav-item').removeClass('active');
    jQuery('.csr-nav-item[data-report="' + reportType + '"]').addClass('active');
});

// Prevent accidental page leave
window.addEventListener('beforeunload', function(e) {
    if (jQuery('#csr-dashboard-overlay').is(':visible')) {
        var message = '<?php _e( "Are you sure you want to leave the sales dashboard?", "catering-sales-report" ); ?>';
        e.returnValue = message;
        return message;
    }
});

</script>