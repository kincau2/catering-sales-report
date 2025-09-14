<?php
/**
 * Plugin Initialization File
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize plugin constants and core functionality
 */
class CSR_Init {
    
    /**
     * Initialize the plugin
     */
    public static function init() {
        // Load required files
        self::load_dependencies();
        
        // Initialize hooks
        self::init_hooks();
        
        // Load text domain
        self::load_textdomain();
    }
    
    /**
     * Load plugin dependencies
     */
    private static function load_dependencies() {
        // Load the WooCommerce API interface
        require_once CSR_PLUGIN_PATH . 'include/interface.php';
        require_once CSR_PLUGIN_PATH . 'vendor/autoload.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private static function init_hooks() {
        // Admin hooks
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
        add_action( 'wp_ajax_csr_get_report_data', array( __CLASS__, 'ajax_get_report_data' ) );
        add_action( 'wp_ajax_csr_get_report_content', array( __CLASS__, 'ajax_get_report_content' ) );
        add_action( 'wp_ajax_csr_get_quick_stats', array( __CLASS__, 'ajax_get_quick_stats' ) );
        add_action( 'wp_ajax_csr_test_connection', array( __CLASS__, 'ajax_test_connection' ) );
    }
    
    /**
     * Load plugin text domain for translations
     */
    private static function load_textdomain() {
        load_plugin_textdomain( 
            'catering-sales-report', 
            false, 
            dirname( plugin_basename( CSR_PLUGIN_PATH . 'catering-sales-report.php' ) ) . '/languages' 
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public static function admin_enqueue_scripts( $hook ) {
        // Only load on our admin page
        if ( strpos( $hook, 'catering-sales-report' ) === false ) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style( 
            'csr-admin-style', 
            CSR_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            CSR_VERSION 
        );
        
        // Enqueue JavaScript
        wp_enqueue_script( 
            'csr-admin-script', 
            CSR_PLUGIN_URL . 'assets/js/admin.js', 
            array( 'jquery' ), 
            CSR_VERSION, 
            true 
        );
        
        // Localize script for AJAX
        wp_localize_script( 'csr-admin-script', 'csr_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'csr_ajax_nonce' ),
            'strings' => array(
                'loading' => __( 'Loading...', 'catering-sales-report' ),
                'error' => __( 'An error occurred. Please try again.', 'catering-sales-report' ),
                'no_data' => __( 'No data available for the selected period.', 'catering-sales-report' ),
                'api_error' => __( 'API connection error. Please check your settings.', 'catering-sales-report' )
            )
        ));
        
        // Enqueue Chart.js for data visualization
        wp_enqueue_script( 
            'chartjs', 
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', 
            array(), 
            '3.9.1', 
            true 
        );
        
        // Enqueue date picker
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui-datepicker-style', 'https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css' );
    }
    
    /**
     * AJAX handler for getting report data
     */
    public static function ajax_get_report_data() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'csr_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $report_type = sanitize_text_field( $_POST['report_type'] );
        $start_date = sanitize_text_field( $_POST['start_date'] );
        $end_date = sanitize_text_field( $_POST['end_date'] );
        
        try {
            $api = new CSR_WooCommerce_Interface();
            $data = $api->get_report_data( $report_type, $start_date, $end_date );
            
            wp_send_json_success( $data );
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX handler for testing API connection
     */
    public static function ajax_test_connection() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'csr_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        try {
            $api = new CSR_WooCommerce_Interface();
            $result = $api->test_connection();
            
            if ( $result['success'] ) {
                wp_send_json_success( $result );
            } else {
                wp_send_json_error( $result );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX handler for getting report content HTML
     */
    public static function ajax_get_report_content() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'csr_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $report_type = sanitize_text_field( $_POST['report_type'] );
        
        // Validate report type
        $valid_reports = array_keys( self::get_report_pages() );
        if ( ! in_array( $report_type, $valid_reports ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid report type', 'catering-sales-report' )
            ));
        }
        
        try {
            // Load the appropriate template
            $template_path = CSR_PLUGIN_PATH . 'template/' . $report_type . '.php';
            
            if ( ! file_exists( $template_path ) ) {
                wp_send_json_error( array(
                    'message' => sprintf( __( 'Template not found: %s', 'catering-sales-report' ), $report_type )
                ));
            }
            
            // Capture template output
            ob_start();
            include $template_path;
            $html = ob_get_clean();
            
            wp_send_json_success( array(
                'html' => $html,
                'report_type' => $report_type
            ));
            
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX handler for getting quick stats
     */
    public static function ajax_get_quick_stats() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'csr_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $preset = sanitize_text_field( $_POST['preset'] ?? 'this_month' );
        $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
        $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
        
        try {
            $api = new CSR_WooCommerce_Interface();
            
            // Calculate date range based on preset
            $dates = self::calculate_date_range( $preset, $start_date, $end_date );
            
            // Get overview data for quick stats
            $data = $api->get_report_data( 'overview', $dates['start'], $dates['end'] );
            
            $stats = array(
                'today_sales' => self::format_currency( $data['summary']['total_revenue'] ?? 0 ),
                'month_sales' => self::format_currency( $data['summary']['total_revenue'] ?? 0 ),
                'total_orders' => number_format( $data['summary']['total_orders'] ?? 0 ),
                'avg_order' => self::format_currency( $data['summary']['average_order_value'] ?? 0 )
            );
            
            wp_send_json_success( $stats );
            
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Calculate date range based on preset or custom dates
     */
    private static function calculate_date_range( $preset, $start_date = '', $end_date = '' ) {
        if ( $preset === 'custom' && $start_date && $end_date ) {
            return array(
                'start' => $start_date,
                'end' => $end_date
            );
        }
        
        $end = new DateTime();
        $start = new DateTime();
        
        switch ( $preset ) {
            case 'today':
                // start is already today
                break;
            case 'yesterday':
                $start->modify( '-1 day' );
                $end->modify( '-1 day' );
                break;
            case 'this_week':
                $start->modify( 'monday this week' );
                break;
            case 'last_week':
                $start->modify( 'monday last week' );
                $end->modify( 'sunday last week' );
                break;
            case 'this_month':
                $start->modify( 'first day of this month' );
                break;
            case 'last_month':
                $start->modify( 'first day of last month' );
                $end->modify( 'last day of last month' );
                break;
            case 'this_quarter':
                $month = (int) $start->format( 'n' );
                $quarter_start_month = ( ceil( $month / 3 ) - 1 ) * 3 + 1;
                $start->setDate( $start->format( 'Y' ), $quarter_start_month, 1 );
                break;
            case 'last_quarter':
                $month = (int) $start->format( 'n' );
                $quarter_start_month = ( ceil( $month / 3 ) - 1 ) * 3 + 1;
                $start->setDate( $start->format( 'Y' ), $quarter_start_month - 3, 1 );
                $end->setDate( $end->format( 'Y' ), $quarter_start_month - 1, 1 );
                $end->modify( 'last day of this month' );
                break;
            case 'this_year':
                $start->modify( 'first day of january this year' );
                break;
            case 'last_year':
                $start->modify( 'first day of january last year' );
                $end->modify( 'last day of december last year' );
                break;
            default:
                // Default to this month
                $start->modify( 'first day of this month' );
        }
        
        return array(
            'start' => $start->format( 'Y-m-d' ),
            'end' => $end->format( 'Y-m-d' )
        );
    }
    
    /**
     * Format currency value
     */
    private static function format_currency( $amount ) {
        // Get WooCommerce currency settings if available
        if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
            $currency_symbol = get_woocommerce_currency_symbol();
            return $currency_symbol . number_format( floatval( $amount ), 2 );
        }
        
        // Fallback to USD
        return '$' . number_format( floatval( $amount ), 2 );
    }
    
    /**
     * Get available report pages
     */
    public static function get_report_pages() {
        return array(
            'overview' => array(
                'title' => __( 'Overview', 'catering-sales-report' ),
                'icon' => 'dashicons-chart-area',
                'description' => __( 'General sales overview and key metrics', 'catering-sales-report' )
            ),
            'trend' => array(
                'title' => __( 'Trend Analysis', 'catering-sales-report' ),
                'icon' => 'dashicons-chart-line',
                'description' => __( 'Sales trends and performance over time', 'catering-sales-report' )
            ),
            'product-sales' => array(
                'title' => __( 'Product Sales', 'catering-sales-report' ),
                'icon' => 'dashicons-products',
                'description' => __( 'Individual product performance analysis', 'catering-sales-report' )
            ),
            'region' => array(
                'title' => __( 'Regional Report', 'catering-sales-report' ),
                'icon' => 'dashicons-location',
                'description' => __( 'Sales performance by geographical region', 'catering-sales-report' )
            ),
            'channel' => array(
                'title' => __( 'Channel Analysis', 'catering-sales-report' ),
                'icon' => 'dashicons-networking',
                'description' => __( 'Performance across different sales channels', 'catering-sales-report' )
            ),
            'membership' => array(
                'title' => __( 'Membership Report', 'catering-sales-report' ),
                'icon' => 'dashicons-groups',
                'description' => __( 'Member vs non-member sales analysis', 'catering-sales-report' )
            ),
            'payment' => array(
                'title' => __( 'Payment Methods', 'catering-sales-report' ),
                'icon' => 'dashicons-money',
                'description' => __( 'Payment method preferences and trends', 'catering-sales-report' )
            ),
            'promotion' => array(
                'title' => __( 'Promotion Analysis', 'catering-sales-report' ),
                'icon' => 'dashicons-tag',
                'description' => __( 'Promotional campaign effectiveness', 'catering-sales-report' )
            )
        );
    }
    
    /**
     * Get current report page from query string
     */
    public static function get_current_page() {
        return isset( $_GET['report'] ) ? sanitize_text_field( $_GET['report'] ) : 'overview';
    }
    
    /**
     * Check if API credentials are configured
     */
    public static function are_credentials_configured() {
        $consumer_key = get_option( 'csr_wc_consumer_key' );
        $consumer_secret = get_option( 'csr_wc_consumer_secret' );
        
        return !empty( $consumer_key ) && !empty( $consumer_secret );
    }
    
    /**
     * Get date range options for reports
     */
    public static function get_date_range_options() {
        return array(
            'today' => __( 'Today', 'catering-sales-report' ),
            'yesterday' => __( 'Yesterday', 'catering-sales-report' ),
            'this_week' => __( 'This Week', 'catering-sales-report' ),
            'last_week' => __( 'Last Week', 'catering-sales-report' ),
            'this_month' => __( 'This Month', 'catering-sales-report' ),
            'last_month' => __( 'Last Month', 'catering-sales-report' ),
            'this_quarter' => __( 'This Quarter', 'catering-sales-report' ),
            'last_quarter' => __( 'Last Quarter', 'catering-sales-report' ),
            'this_year' => __( 'This Year', 'catering-sales-report' ),
            'last_year' => __( 'Last Year', 'catering-sales-report' ),
            'custom' => __( 'Custom Range', 'catering-sales-report' )
        );
    }
}
