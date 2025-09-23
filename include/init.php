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
        
        // Create database tables
        self::create_database_tables();
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
        
        // Frontend hooks for page view tracking
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_enqueue_scripts' ) );
        add_action( 'wp_ajax_csr_track_page_view', array( __CLASS__, 'ajax_track_page_view' ) );
        add_action( 'wp_ajax_nopriv_csr_track_page_view', array( __CLASS__, 'ajax_track_page_view' ) );
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
        if ( ! current_user_can( 'manage_catering' ) ) {
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
        if ( ! current_user_can( 'manage_catering' ) ) {
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
        if ( ! current_user_can( 'manage_catering' ) ) {
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
            set_transient( 'debug' ,$html,20);
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
        if ( ! current_user_can( 'manage_catering' ) ) {
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
            'today' => __( '今天', 'catering-sales-report' ),
            'yesterday' => __( '昨天', 'catering-sales-report' ),
            'this_week' => __( '本週', 'catering-sales-report' ),
            'last_week' => __( '上週', 'catering-sales-report' ),
            'this_month' => __( '本月', 'catering-sales-report' ),
            'last_month' => __( '上月', 'catering-sales-report' ),
            'this_year' => __( '本年', 'catering-sales-report' ),
            'last_year' => __( '去年', 'catering-sales-report' ),
            'custom' => __( '自訂範圍', 'catering-sales-report' )
        );
    }
    
    /**
     * Create database tables for tracking
     */
    public static function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'csr_product_views';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            view_count int(11) NOT NULL DEFAULT 1,
            view_date date NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_date (product_id, view_date),
            KEY view_date (view_date)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    /**
     * Enqueue frontend scripts for page view tracking
     */
    public static function frontend_enqueue_scripts() {
        // Only load on single product pages
        if ( ! is_product() ) {
            return;
        }
        
        // Enqueue script for tracking
        wp_enqueue_script( 
            'csr-page-tracking', 
            CSR_PLUGIN_URL . 'assets/js/page-tracking.js', 
            array( 'jquery' ), 
            CSR_VERSION, 
            true 
        );
        
        // Localize for AJAX
        wp_localize_script( 'csr-page-tracking', 'csr_tracking', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'csr_tracking_nonce' ),
            'product_id' => get_the_ID()
        ));
    }
    
    /**
     * AJAX handler for tracking page views
     */
    public static function ajax_track_page_view() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'csr_tracking_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $product_id = intval( $_POST['product_id'] );
        
        if ( ! $product_id ) {
            wp_send_json_error( 'Invalid product ID' );
        }
        
        // Check if product exists and is valid WooCommerce product
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( 'Product not found' );
        }
        
        // Check cookie to avoid duplicate views
        $cookie_name = 'csr_viewed_' . $product_id;
        $today = date( 'Y-m-d' );
        
        if ( ! isset( $_COOKIE[$cookie_name] ) || $_COOKIE[$cookie_name] !== $today ) {
            // Track the view
            self::track_product_view( $product_id );
            
            // Set cookie for 24 hours
            setcookie( $cookie_name, $today, time() + DAY_IN_SECONDS, '/' );
            
            wp_send_json_success( 'View tracked' );
        } else {
            wp_send_json_success( 'Already tracked today' );
        }
    }
    
    /**
     * Track a product page view
     */
    private static function track_product_view( $product_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'csr_product_views';
        $today = date( 'Y-m-d' );
        
        // Check if we already have a record for this product today
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE product_id = %d AND view_date = %s",
            $product_id,
            $today
        ));
        
        if ( $existing ) {
            // Increment existing record
            $wpdb->update(
                $table_name,
                array( 'view_count' => $existing->view_count + 1 ),
                array( 'id' => $existing->id ),
                array( '%d' ),
                array( '%d' )
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table_name,
                array(
                    'product_id' => $product_id,
                    'view_count' => 1,
                    'view_date' => $today
                ),
                array( '%d', '%d', '%s' )
            );
        }
    }
    
    /**
     * Get top viewed products for a date range
     */
    public static function get_top_viewed_products( $start_date, $end_date, $limit = 10 ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'csr_product_views';
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT product_id, SUM(view_count) as total_views 
             FROM $table_name 
             WHERE view_date BETWEEN %s AND %s 
             GROUP BY product_id 
             ORDER BY total_views DESC 
             LIMIT %d",
            $start_date,
            $end_date,
            $limit
        ));
        
        $products = array();
        foreach ( $results as $result ) {
            $product = wc_get_product( $result->product_id );
            if ( $product ) {
                $products[] = array(
                    'id' => $result->product_id,
                    'name' => $product->get_name(),
                    'views' => intval( $result->total_views ),
                    'url' => $product->get_permalink()
                );
            }
        }
        
        return $products;
    }
}
