/**
 * Product Page View Tracking
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    // Track page view when document is ready
    if (typeof csr_tracking !== 'undefined' && csr_tracking.product_id) {
        trackPageView();
    }
    
    function trackPageView() {
        $.post(csr_tracking.ajax_url, {
            action: 'csr_track_page_view',
            product_id: csr_tracking.product_id,
            nonce: csr_tracking.nonce
        }, function(response) {
            // Optional: Log tracking result for debugging
            if (console && console.log) {
                // console.log('CSR Page View Tracking:', response);
            }
        }).fail(function() {
            // Fail silently to not affect user experience
            if (console && console.log) {
                // console.log('CSR Page View Tracking: Failed to track view');
            }
        });
    }
});
