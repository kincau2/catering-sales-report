<?php
/**
 * @link
 * @since             1.0.0
 * @package           catering-sales-report
 * Plugin Name:       Catering Sales Report
 * Description:       Customized plugin for ms. lo soups website sales report extension.
 * Author:            Louis Au
 * Version:           1.0.0
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       catering-sales-report
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'CSR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CSR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CSR_VERSION', '1.0.0' );

/**
 * Main Catering Sales Report Class
 */
class CateringSalesReport {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        
        // Use higher priority to ensure catering booking plugin loads first
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 15 );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        
        // Plugin activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        
        // Load plugin dependencies
        $this->load_dependencies();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once CSR_PLUGIN_PATH . 'include/init.php';
        
        // Initialize the plugin
        CSR_Init::init();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain( 'catering-sales-report', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        add_option( 'csr_wc_consumer_key', '' );
        add_option( 'csr_wc_consumer_secret', '' );
        add_option( 'csr_wc_store_url', get_site_url() );
        add_option( 'csr_settings_version', CSR_VERSION );
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Register settings
        register_setting( 'csr_settings_group', 'csr_wc_consumer_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting( 'csr_settings_group', 'csr_wc_consumer_secret', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting( 'csr_settings_group', 'csr_wc_store_url', array(
            'sanitize_callback' => 'esc_url_raw'
        ));
        
        // Add settings sections
        add_settings_section(
            'csr_api_settings',
            __( 'WooCommerce API Settings', 'catering-sales-report' ),
            array( $this, 'api_settings_callback' ),
            'csr-settings'
        );
        
        // Add settings fields
        add_settings_field(
            'csr_wc_store_url',
            __( 'Store URL', 'catering-sales-report' ),
            array( $this, 'store_url_field_callback' ),
            'csr-settings',
            'csr_api_settings'
        );
        
        add_settings_field(
            'csr_wc_consumer_key',
            __( 'Consumer Key', 'catering-sales-report' ),
            array( $this, 'consumer_key_field_callback' ),
            'csr-settings',
            'csr_api_settings'
        );
        
        add_settings_field(
            'csr_wc_consumer_secret',
            __( 'Consumer Secret', 'catering-sales-report' ),
            array( $this, 'consumer_secret_field_callback' ),
            'csr-settings',
            'csr_api_settings'
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( '廬太太報表', 'catering-sales-report' ),
            __( '廬太太報表', 'catering-sales-report' ),
            'manage_catering',
            'catering-sales-report',
            array( $this, 'dashboard_page' ),
            'dashicons-chart-line',
            3
        );
            
        // Settings submenu for standalone mode
        add_submenu_page(
            'catering-sales-report',
            __( 'API Settings', 'catering-sales-report' ),
            __( 'Settings', 'catering-sales-report' ),
            'manage_catering',
            'csr-settings',
            array( $this, 'settings_page' )
        );
    }
    

    
    /**
     * Settings page callback
     */
    public function api_settings_callback() {
        echo '<p>' . __( 'Configure your WooCommerce REST API credentials below. You can generate these in WooCommerce > Settings > Advanced > REST API.', 'catering-sales-report' ) . '</p>';
    }
    
    /**
     * Store URL field callback
     */
    public function store_url_field_callback() {
        $value = get_option( 'csr_wc_store_url', get_site_url() );
        echo '<input type="url" name="csr_wc_store_url" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . __( 'Your WooCommerce store URL (usually your site URL)', 'catering-sales-report' ) . '</p>';
    }
    
    /**
     * Consumer key field callback
     */
    public function consumer_key_field_callback() {
        $value = get_option( 'csr_wc_consumer_key' );
        echo '<input type="text" name="csr_wc_consumer_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . __( 'WooCommerce REST API Consumer Key', 'catering-sales-report' ) . '</p>';
    }
    
    /**
     * Consumer secret field callback
     */
    public function consumer_secret_field_callback() {
        $value = get_option( 'csr_wc_consumer_secret' );
        echo '<input type="password" name="csr_wc_consumer_secret" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . __( 'WooCommerce REST API Consumer Secret', 'catering-sales-report' ) . '</p>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php 
            // Check if credentials are set
            if ( $this->are_credentials_set() ) {
                $test_result = $this->test_api_connection();
                if ( $test_result['success'] ) {
                    echo '<div class="notice notice-success"><p><strong>' . __( 'API Connection Successful!', 'catering-sales-report' ) . '</strong> ' . esc_html( $test_result['message'] ) . '</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p><strong>' . __( 'API Connection Failed:', 'catering-sales-report' ) . '</strong> ' . esc_html( $test_result['message'] ) . '</p></div>';
                }
            }
            ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'csr_settings_group' );
                do_settings_sections( 'csr-settings' );
                submit_button();
                ?>
            </form>
            
            <h2><?php _e( 'How to Generate API Credentials', 'catering-sales-report' ); ?></h2>
            <ol>
                <li><?php _e( 'Go to WooCommerce > Settings > Advanced > REST API', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Click "Add Key"', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Set Description: "Catering Sales Report"', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Set User: Select an administrator user', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Set Permissions: Read', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Click "Generate API Key"', 'catering-sales-report' ); ?></li>
                <li><?php _e( 'Copy the Consumer Key and Consumer Secret to the fields above', 'catering-sales-report' ); ?></li>
            </ol>
        </div>
        <?php
    }
    
    /**
     * Check if API credentials are set
     */
    public function are_credentials_set() {
        $consumer_key = get_option( 'csr_wc_consumer_key' );
        $consumer_secret = get_option( 'csr_wc_consumer_secret' );
        
        return !empty( $consumer_key ) && !empty( $consumer_secret );
    }
    
    /**
     * Test API connection
     */
    public function test_api_connection() {
        if ( ! $this->are_credentials_set() ) {
            return array(
                'success' => false,
                'message' => __( 'API credentials not set', 'catering-sales-report' )
            );
        }
        
        $store_url = get_option( 'csr_wc_store_url', get_site_url() );
        $consumer_key = get_option( 'csr_wc_consumer_key' );
        $consumer_secret = get_option( 'csr_wc_consumer_secret' );
        
        $url = trailingslashit( $store_url ) . 'wp-json/wc/v3/system_status';
        
        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $consumer_key . ':' . $consumer_secret )
            ),
            'timeout' => 30
        ));
        
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( $response_code === 200 ) {
            return array(
                'success' => true,
                'message' => __( 'Connected to WooCommerce API successfully', 'catering-sales-report' )
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf( __( 'HTTP Error %d', 'catering-sales-report' ), $response_code )
            );
        }
    }
    
    /**
     * Get WooCommerce API credentials
     */
    public function get_api_credentials() {
        return array(
            'store_url' => get_option( 'csr_wc_store_url', get_site_url() ),
            'consumer_key' => get_option( 'csr_wc_consumer_key' ),
            'consumer_secret' => get_option( 'csr_wc_consumer_secret' )
        );
    }
    
    /**
     * Dashboard page - main full screen interface
     */
    public function dashboard_page() {
        $this->load_template( 'dashboard' );
    }
    
    /**
     * Load template file
     */
    private function load_template( $template_name ) {
        $template_path = CSR_PLUGIN_PATH . 'template/' . $template_name . '.php';
        
        if ( file_exists( $template_path ) ) {
            // Pass API credentials and other data to template
            $api_credentials = $this->get_api_credentials();
            $current_page = CSR_Init::get_current_page();
            $report_pages = CSR_Init::get_report_pages();
            $credentials_configured = CSR_Init::are_credentials_configured();
            
            include $template_path;
        } else {
            echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1>';
            echo '<p>' . sprintf( __( 'Template file not found: %s', 'catering-sales-report' ), esc_html( $template_name ) ) . '</p>';
            echo '</div>';
        }
    }
}

// Initialize the plugin
new CateringSalesReport();
