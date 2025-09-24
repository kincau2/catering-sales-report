<?php
/**
 * Export Data Template
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="csr-export-container">
    <style>
    /* Export specific styles - Following design draft layout */
    .csr-export-container {
        padding: 30px;
        margin: 0 auto;
        background: #f5f5f5;
    }
    
    .csr-export-header {
        margin-bottom: 30px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 6px;
    }
    
    .csr-export-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-export-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }
    
    /* Export widgets */
    .csr-export-widgets {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .csr-export-widget {
        background: #fff;
        border-radius: 6px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .csr-export-widget h3 {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: #23282d;
    }
    
    .csr-export-widget p {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 25px;
    }
    
    .csr-export-button {
        background: #D2691E;
        color: #fff;
        border: none;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        text-decoration: none;
        display: inline-block;
        min-width: 200px;
    }
    
    .csr-export-button:hover {
        background: #B8860B;
        color: #fff;
        text-decoration: none;
    }
    
    .csr-export-button:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .csr-export-button.secondary {
        background: #0073aa;
        color: #fff;
    }
    
    .csr-export-button.secondary:hover {
        background: #005a87;
    }
    
    /* Stats section */
    .csr-export-stats {
        background: #fff;
        border-radius: 6px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .csr-export-stats h3 {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: #23282d;
        text-align: center;
    }
    
    .csr-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .csr-stat-item {
        text-align: center;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 6px;
    }
    
    .csr-stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #D2691E;
        margin: 0;
        line-height: 1.2;
    }
    
    .csr-stat-label {
        font-size: 14px;
        color: #666;
        margin: 5px 0 0 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Loading states */
    .csr-loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100px;
        flex-direction: column;
        gap: 10px;
    }
    
    .csr-loading-spinner {
        display: inline-block;
        width: 30px;
        height: 30px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #D2691E;
        border-radius: 50%;
        animation: csr-spin 1s linear infinite;
    }
    
    @keyframes csr-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .csr-loading-text {
        color: #666;
        font-size: 14px;
        font-style: italic;
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
    
    .csr-success-message {
        background: #e8f5e8;
        border: 1px solid #46b450;
        color: #2e7d2e;
        padding: 15px;
        border-radius: 3px;
        margin: 20px 0;
        text-align: center;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .csr-export-widgets {
            grid-template-columns: 1fr;
        }
        
        .csr-stats-grid {
            grid-template-columns: 1fr;
        }
        
        .csr-export-container {
            padding: 20px;
        }
    }
    </style>

    <div class="csr-export-header">
        <h1><?php _e( '導出資料', 'catering-sales-report' ); ?></h1>
        <p class="csr-export-subtitle"><?php _e( '導出客戶資料和銷售資料以進行外部分析', 'catering-sales-report' ); ?></p>
    </div>

    <!-- Export Statistics -->
    <div class="csr-export-stats">
        <h3><?php _e( '資料統計', 'catering-sales-report' ); ?></h3>
        <div id="csr-export-stats-content">
            <div class="csr-loading-container">
                <div class="csr-loading-spinner"></div>
                <p class="csr-loading-text"><?php _e( '正在載入統計資料...', 'catering-sales-report' ); ?></p>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="csr-export-widgets">
        <!-- Customer Export -->
        <div class="csr-export-widget">
            <h3><?php _e( '客戶資料導出', 'catering-sales-report' ); ?></h3>
            <p><?php _e( '導出所有客戶的詳細資料，包括聯絡資料、訂單歷史和消費金額。', 'catering-sales-report' ); ?></p>
            
            <p><strong><?php _e( '包含欄位：', 'catering-sales-report' ); ?></strong></p>
            <ul style="text-align: left; margin: 15px 0; padding-left: 20px; color: #666;">
                <li><?php _e( '客戶電郵', 'catering-sales-report' ); ?></li>
                <li><?php _e( '客戶姓名', 'catering-sales-report' ); ?></li>
                <li><?php _e( '客戶電話', 'catering-sales-report' ); ?></li>
                <li><?php _e( '是否曾下訂單', 'catering-sales-report' ); ?></li>
                <li><?php _e( '過往訂單號碼', 'catering-sales-report' ); ?></li>
                <li><?php _e( '總消費金額', 'catering-sales-report' ); ?></li>
            </ul>
            
            <button type="button" class="csr-export-button" id="csr-export-customers-btn">
                <?php _e( '導出客戶 CSV', 'catering-sales-report' ); ?>
            </button>
        </div>


    </div>
    
    <!-- Export Status -->
    <div id="csr-export-status" style="display: none;"></div>
</div>

<script>
// Export page JavaScript
jQuery(document).ready(function($) {
    // Load export statistics
    loadExportStats();
    
    // Bind export button click
    $('#csr-export-customers-btn').on('click', function() {
        exportCustomersCSV();
    });
});

function loadExportStats() {
    var dateRange = getCurrentDateRange();
    
    jQuery.post(csr_ajax.ajax_url, {
        action: 'csr_get_report_data',
        report_type: 'export',
        start_date: dateRange.start,
        end_date: dateRange.end,
        nonce: csr_ajax.nonce
    })
    .done(function(response) {
        if (response.success) {
            displayExportStats(response.data);
        } else {
            showExportError('載入統計資料失敗: ' + (response.data.message || '未知錯誤'));
        }
    })
    .fail(function() {
        showExportError('載入統計資料時發生網絡錯誤');
    });
}

function displayExportStats(data) {
    var statsHtml = '<div class="csr-stats-grid">' +
        '<div class="csr-stat-item">' +
            '<div class="csr-stat-number">' + (data.total_customers || 0) + '</div>' +
            '<div class="csr-stat-label"><?php _e( "總客戶數", "catering-sales-report" ); ?></div>' +
        '</div>' +
        '<div class="csr-stat-item">' +
            '<div class="csr-stat-number">' + (data.customers_with_orders || 0) + '</div>' +
            '<div class="csr-stat-label"><?php _e( "有訂單客戶", "catering-sales-report" ); ?></div>' +
        '</div>' +
        '<div class="csr-stat-item">' +
            '<div class="csr-stat-number">' + Math.round(((data.customers_with_orders || 0) / Math.max(data.total_customers || 1, 1)) * 100) + '%</div>' +
            '<div class="csr-stat-label"><?php _e( "客戶轉換率", "catering-sales-report" ); ?></div>' +
        '</div>' +
    '</div>';
    
    jQuery('#csr-export-stats-content').html(statsHtml);
}

function showExportError(message) {
    var errorHtml = '<div class="csr-error-message">' + escapeHtml(message) + '</div>';
    jQuery('#csr-export-stats-content').html(errorHtml);
}

function exportCustomersCSV() {
    var $button = jQuery('#csr-export-customers-btn');
    var $status = jQuery('#csr-export-status');
    
    // Disable button and show loading state
    $button.prop('disabled', true).text('<?php _e( "正在生成 CSV...", "catering-sales-report" ); ?>');
    
    // Show status message
    $status.html('<div class="csr-loading-container">' +
        '<div class="csr-loading-spinner"></div>' +
        '<p class="csr-loading-text"><?php _e( "正在處理客戶資料，請稍候...", "catering-sales-report" ); ?></p>' +
    '</div>').show();
    
    // Create a form and submit it to trigger file download
    var form = jQuery('<form>', {
        'method': 'POST',
        'action': csr_ajax.ajax_url,
        'target': '_blank' // Open in new tab/window for download
    });
    
    // Add form fields
    form.append(jQuery('<input>', { 'type': 'hidden', 'name': 'action', 'value': 'csr_export_customers' }));
    form.append(jQuery('<input>', { 'type': 'hidden', 'name': 'nonce', 'value': csr_ajax.nonce }));
    
    // Append form to body and submit
    jQuery('body').append(form);
    form.submit();
    form.remove();
    
    // Reset button state after a delay
    setTimeout(function() {
        $button.prop('disabled', false).text('<?php _e( "導出客戶 CSV", "catering-sales-report" ); ?>');
        $status.html('<div class="csr-success-message"><?php _e( "CSV 檔案已開始下載", "catering-sales-report" ); ?></div>');
        
        // Hide status after 3 seconds
        setTimeout(function() {
            $status.fadeOut();
        }, 3000);
    }, 2000);
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>