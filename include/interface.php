<?php
use Automattic\WooCommerce\Client;


/**
 * WooCommerce REST API Interface Class
 * 
 * Handles all interactions with WooCommerce REST API
 * Based on WooCommerce REST API Documentation: https://woocommerce.github.io/woocommerce-rest-api-docs/
 * 
 * @package CateringSalesReport
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce API Interface Class
 */
class CSR_WooCommerce_Interface {
    
    private $store_url;
    private $consumer_key;
    private $consumer_secret;
    private $api_version = 'wc/v3';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->store_url = get_option( 'csr_wc_store_url', get_site_url() );
        $this->consumer_key = get_option( 'csr_wc_consumer_key' );
        $this->consumer_secret = get_option( 'csr_wc_consumer_secret' );
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        $response = $this->make_request_with_params( 'system_status' );
        
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'message' => __( 'Successfully connected to WooCommerce API', 'catering-sales-report' )
        );
    }
    
    /**
     * Get report data based on type and date range
     */
    public function get_report_data( $report_type, $start_date = null, $end_date = null ) {
        
        switch ( $report_type ) {
            case 'overview':
                return $this->get_overview_data( $start_date, $end_date );
            
            case 'trend':
                return $this->get_trend_data( $start_date, $end_date );
            
            case 'product-sales':
                return $this->get_product_sales_data( $start_date, $end_date );
            
            case 'region':
                return $this->get_region_data( $start_date, $end_date );
            
            case 'channel':
                return $this->get_channel_data( $start_date, $end_date );
            
            case 'membership':
                return $this->get_membership_data( $start_date, $end_date );
            
            case 'payment':
                return $this->get_payment_data( $start_date, $end_date );
            
            case 'promotion':
                return $this->get_promotion_data( $start_date, $end_date );
            
            case 'export':
                return $this->get_export_data( $start_date, $end_date );
            
            default:
                throw new Exception( __( 'Invalid report type', 'catering-sales-report' ) );
        }
    }
    
    /**
     * Get overview data
     */
    private function get_overview_data( $start_date = null, $end_date = null ) {
        // NEW: Get sales report using HPOS SQL query instead of REST API
        $sales_report = $this->get_sales_report_via_hpos( $start_date, $end_date );

        $orders = $this->get_orders_via_hpos( $start_date, $end_date );
        
        $top_products = $this->get_top_sellers_via_hpos( $start_date, $end_date );
        
        return array(
            'sales_report' => $sales_report,
            'top_products' => $top_products,
            'top_regions' => $this->get_top_regions_summary( $orders ),
            'monthly_comparison' => $this->get_monthly_comparison(),
            'average_sales' => $this->get_average_sales_data(),
            'page_views' => $this->get_page_view_data( $start_date, $end_date )
        );
    }
    
    /**
     * Get trend analysis data
     */
    private function get_trend_data( $start_date = null, $end_date = null ) {
        if ( !$start_date || !$end_date ) {
            return array(
                'sales_trend' => array(),
                'revenue_trend' => array(),
                'order_trend' => array(),
                'period_comparison' => array(),
                'yearly_comparison' => $this->get_yearly_comparison()
            );
        }
        
        // Calculate period length in days
        $period_start = new DateTime( $start_date );
        $period_end = new DateTime( $end_date );
        $period_length = $period_end->diff( $period_start )->days + 1;
        
        // Calculate comparison period (same length, immediately before)
        $comparison_end = clone $period_start;
        $comparison_end->modify( '-1 day' );
        $comparison_start = clone $comparison_end;
        $comparison_start->modify( '-' . ($period_length - 1) . ' days' );
        
        // Fetch double date range (comparison period + current period)
        $comparison_start = $comparison_start->format('Y-m-d');
        $comparison_end = $comparison_end->format('Y-m-d');
        
        // NEW: Get sales data using HPOS SQL query instead of REST API
        $sales_data_current = $this->get_sales_report_via_hpos( $start_date, $end_date );
        $sales_data_comparison = $this->get_sales_report_via_hpos( $comparison_start, $comparison_end );
        
        return array(
            'period_comparison' => $this->get_detailed_period_comparison( $sales_data_current, $sales_data_comparison ),
            'yearly_comparison' => $this->get_yearly_comparison()
        );
    }

    /**
     * Get payment method analysis data
     */
    private function get_payment_data( $start_date = null, $end_date = null ) {
  
        $orders = $this->get_orders_via_hpos( $start_date, $end_date );

        // Get monthly trends for last 12 months
        $monthly_trends = $this->get_payment_monthly_trends();
        
        return array(
            'orders' => $orders,
            'monthly_trends' => $monthly_trends
        );
    }
    
    /**
     * Get product sales data
     */
    private function get_product_sales_data( $start_date = null, $end_date = null ) {
        $orders = $this->get_orders_via_hpos( $start_date, $end_date );

        // Process all product data in a single loop for efficiency
        $product_data = $this->process_all_product_data( $orders, $start_date, $end_date );
        
        // Generate color mappings for consistent colors across all charts
        $sales_color_map = $this->get_product_color_map( $product_data['top_products_by_sales'] );
        $quantity_color_map = $this->get_product_color_map( $product_data['top_products_by_quantity'] );
        
        return array(
            'top_products_by_sales' => $product_data['top_products_by_sales'],
            'top_products_by_quantity' => $product_data['top_products_by_quantity'],
            'sales_trends' => $product_data['sales_trends'],
            'quantity_trends' => $product_data['quantity_trends'],
            'sales_color_map' => $sales_color_map,
            'quantity_color_map' => $quantity_color_map
        );
    }
    
    /**
     * Get regional sales data
     */
    private function get_region_data( $start_date = null, $end_date = null ) {
        $orders = $this->get_orders_via_hpos( $start_date, $end_date );
        
        // Process all regional data in a single loop for efficiency
        $regional_data = $this->process_all_regional_data( $orders, $start_date, $end_date );
        
        return array(
            'regional_summary' => $regional_data['regional_summary'],
            'monthly_trends' => $regional_data['monthly_trends'],
            'district_details' => $regional_data['district_details'],
            'top_districts' => $regional_data['top_districts']
        );
    }
    
    /**
     * Get channel analysis data
     */
    private function get_channel_data( $start_date = null, $end_date = null ) {
        $orders = $this->get_orders_via_hpos( $start_date, $end_date );
        
        // Get monthly trends for last 12 months
        $monthly_trends = $this->get_channel_monthly_trends();
        
        return array(
            'orders' => $orders,
            'monthly_trends' => $monthly_trends,
            'channel_usage' => $this->analyze_sales_channels( $orders ),
            'channel_summary' => $this->calculate_channel_summary( $orders )
        );
    }

    /**
     * Get promotion analysis data - OPTIMIZED using wc_order_coupon_lookup table
     */
    private function get_promotion_data( $start_date = null, $end_date = null ) {
        // Get coupons from WooCommerce
        $coupon_params = array( 'per_page' => 100 );
        $coupons = $this->make_request_with_params( 'coupons', $coupon_params );
        
        if ( is_wp_error( $coupons ) ) {
            throw new Exception( $coupons->get_error_message() );
        }
        
        // NEW: Process coupon usage data using wc_order_coupon_lookup table (much faster)
        $coupon_usage_data = $this->process_coupon_usage_via_lookup( $coupons, $start_date, $end_date );
        
        // NEW: Generate promotion sales trend using get_sales_report_via_hpos() for consistency
        $sales_trend = $this->generate_promotion_sales_trend_via_hpos( $start_date, $end_date );
        
        set_transient( 'debug', $sales_trend , 30 ); // For debugging
        return array(
            'coupons' => $coupon_usage_data,
            'sales_trend' => $sales_trend
        );
    }
    
    /**
     * Get membership analysis data
     */
    private function get_membership_data( $start_date = null, $end_date = null ) {
        // Process all membership data using HPOS-optimized single query approach
        $membership_data = $this->process_all_membership_data( $start_date, $end_date );
        
        return array(
            'user_trends' => $membership_data['user_trends'],
            'geographic_distribution' => $membership_data['geographic_distribution'],
            'monthly_analytics' => $membership_data['monthly_analytics'],
            'summary' => $membership_data['summary']
        );
    }

    
    /**
     * Process all membership data using HPOS-optimized queries
     * Combines user registration trends, geographic distribution, and purchase analytics
     */
    private function process_all_membership_data( $start_date = null, $end_date = null ) {
        global $wpdb;
        
        // Determine date range - default to last 12 months
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-12 months' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // HPOS table names
        $orders_table = $wpdb->prefix . 'wc_orders';
        $order_addresses_table = $wpdb->prefix . 'wc_order_addresses';
        
        // Initialize data structures
        $user_trends = array();
        $geographic_distribution = array();
        $monthly_analytics = array();
        
        // Initialize monthly data structure
        $start = new DateTime( $start_date );
        $end = new DateTime( $end_date );
        $interval = new DateInterval( 'P1M' );
        $period = new DatePeriod( $start, $interval, $end );
        
        $monthly_data = array();
        foreach ( $period as $month ) {
            $month_key = $month->format( 'Y-m' );
            $monthly_data[ $month_key ] = array(
                'month' => $month_key,
                'month_label' => $month->format( 'M Y' ),
                'new_users' => 0,
                'active_users' => array(), // Track unique active users
                'total_orders' => 0,
                'total_sales' => 0,
                'repeat_customers_90d' => array() // Track repeat customers
            );
        }
        
        // Step 1: Get user registration trends
        $user_registration_sql = $wpdb->prepare(
            "SELECT DATE_FORMAT(user_registered, '%%Y-%%m') as month,
                    COUNT(*) as new_users
             FROM {$wpdb->users}
             WHERE user_registered BETWEEN %s AND %s
             GROUP BY month
             ORDER BY month",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );
        
        $user_registrations = $wpdb->get_results( $user_registration_sql );
        

        // Process user registration data
        foreach ( $user_registrations as $reg ) {
            if ( isset( $monthly_data[ $reg->month ] ) ) {
                $monthly_data[ $reg->month ]['new_users'] = intval( $reg->new_users );
            }
        }
        
        // Step 2: Get geographic distribution from user meta
        $geographic_sql = "SELECT meta_value as city, COUNT(DISTINCT user_id) as user_count
                          FROM {$wpdb->usermeta}
                          WHERE meta_key = 'shipping_city'
                            AND meta_value != ''
                          GROUP BY meta_value
                          ORDER BY user_count DESC
                          LIMIT 20";
        
        $geographic_results = $wpdb->get_results( $geographic_sql );
        
        // Process geographic data
        $total_geo_users = 0;
        foreach ( $geographic_results as $geo ) {
            $total_geo_users += intval( $geo->user_count );
        }
        
        foreach ( $geographic_results as $geo ) {
            $user_count = intval( $geo->user_count );
            $geographic_distribution[ $geo->city ] = array(
                'user_count' => $user_count,
                'percentage' => $total_geo_users > 0 ? round( ( $user_count / $total_geo_users ) * 100, 2 ) : 0
            );
        }
        
        // Step 3: Get order data using HPOS tables
        $orders_sql = $wpdb->prepare(
            "SELECT o.id,
                    o.customer_id,
                    o.total_amount,
                    DATE_FORMAT(o.date_created_gmt, '%%Y-%%m') as order_month,
                    o.date_created_gmt,
                    oa.city as shipping_city
             FROM {$orders_table} o
             LEFT JOIN {$order_addresses_table} oa ON o.id = oa.order_id 
                 AND oa.address_type = 'shipping'
             WHERE o.type = 'shop_order'
               AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
               AND o.date_created_gmt BETWEEN %s AND %s
               AND o.customer_id > 0
             ORDER BY o.customer_id, o.date_created_gmt",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );
        
        $orders = $wpdb->get_results( $orders_sql );
        
        // Step 4: Process order data for monthly analytics
        $customer_order_history = array(); // Track all orders per customer for repeat analysis
        $customer_monthly_spending = array(); // Track spending per customer per month
        
        foreach ( $orders as $order ) {
            $month = $order->order_month;
            $customer_id = intval( $order->customer_id );
            $order_total = floatval( $order->total_amount );
            $order_date = $order->date_created_gmt;
            
            if ( isset( $monthly_data[ $month ] ) ) {
                // Track total orders and sales (but not active users - that's login-based now)
                $monthly_data[ $month ]['total_orders']++;
                $monthly_data[ $month ]['total_sales'] += $order_total;
                
                // Track customer spending per month
                if ( !isset( $customer_monthly_spending[ $month ] ) ) {
                    $customer_monthly_spending[ $month ] = array();
                }
                if ( !isset( $customer_monthly_spending[ $month ][ $customer_id ] ) ) {
                    $customer_monthly_spending[ $month ][ $customer_id ] = 0;
                }
                $customer_monthly_spending[ $month ][ $customer_id ] += $order_total;
            }
            
            // Track order history for repeat customer analysis
            if ( !isset( $customer_order_history[ $customer_id ] ) ) {
                $customer_order_history[ $customer_id ] = array();
            }
            $customer_order_history[ $customer_id ][] = array(
                'date' => $order_date,
                'amount' => $order_total,
                'month' => $month
            );
        }
        
        // Step 5: Calculate repeat customers within 90 days for each month
        foreach ( $monthly_data as $month => &$data ) {
            $month_start = new DateTime( $month . '-01' );
            $month_end = clone $month_start;
            $month_end->modify( 'last day of this month' );
            
            $repeat_customers = array();
            
            foreach ( $customer_order_history as $customer_id => $order_history ) {
                if ( count( $order_history ) < 2 ) continue; // Need at least 2 orders to be repeat
                
                // Check for repeat purchases within 90 days for this month
                foreach ( $order_history as $i => $order ) {
                    $order_date = new DateTime( $order['date'] );
                    
                    // If order is in current month, check for previous orders within 90 days
                    if ( $order_date >= $month_start && $order_date <= $month_end ) {
                        for ( $j = 0; $j < $i; $j++ ) {
                            $prev_order_date = new DateTime( $order_history[$j]['date'] );
                            $days_diff = $order_date->diff( $prev_order_date )->days;
                            
                            if ( $days_diff <= 90 ) {
                                $repeat_customers[ $customer_id ] = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            $data['repeat_customers_90d'] = count( $repeat_customers );
        }
        
        // Step 5.5: Get login-based active users from tracking table
        $login_table = $wpdb->prefix . 'csr_user_logins';
        foreach ( $monthly_data as $month => &$data ) {
            // Get active users who logged in during this month
            $active_users_sql = $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) as active_count
                 FROM {$login_table}
                 WHERE login_month = %s
                   AND activity_count > 0",
                $month
            );
            set_transient( 'debug', $active_users_sql , 30 ); // For debugging
            $active_result = $wpdb->get_var( $active_users_sql );
            $data['active_users'] = intval( $active_result );
        }
        
        // Step 6: Calculate final metrics for each month
        foreach ( $monthly_data as $month => &$data ) {
            // Active users is now already set from login tracking
            $active_user_count = $data['active_users'];
            
            // Calculate average spending per customer (based on purchasing customers, not login-based active users)
            $purchasing_customers = count( $customer_monthly_spending[ $month ] ?? array() );
            $data['avg_spending_per_customer'] = $purchasing_customers > 0 ? 
                round( $data['total_sales'] / $purchasing_customers, 0) : 0;
            
            // Calculate repeat purchase rate (based on purchasing customers)
            $data['repeat_purchase_rate'] = $purchasing_customers > 0 ? 
                round( ( $data['repeat_customers_90d'] / $purchasing_customers ) * 100, 2 ) : 0;
        }
        
        // Step 7: Calculate cumulative total users for bar chart
        $total_users_cumulative = $this->get_cumulative_user_counts( $start_date, $end_date );
        
        // Step 8: Build user trends data for bar chart
        $user_trends_data = array();
        
        foreach ( $monthly_data as $month => $month_data ) {
            $user_trends_data[] = array(
                'month' => $month,
                'month_label' => $month_data['month_label'],
                'new_users' => $month_data['new_users'],
                'active_users' => $month_data['active_users'],
                'total_users' => isset( $total_users_cumulative[ $month ] ) ? $total_users_cumulative[ $month ] : 0
            );
        }

        // Step 9: Calculate overall summary
        $summary = $this->calculate_membership_summary( $monthly_data, $geographic_distribution );
        
        return array(
            'user_trends' => $user_trends_data,
            'geographic_distribution' => $geographic_distribution,
            'monthly_analytics' => array_values( $monthly_data ),
            'summary' => $summary
        );
    }
    
    /**
     * Get cumulative user counts for each month
     */
    private function get_cumulative_user_counts( $start_date, $end_date ) {
        global $wpdb;
        
        $cumulative_counts = array();
        
        // Initialize monthly data structure
        $start = new DateTime( $start_date );
        $end = new DateTime( $end_date );
        $interval = new DateInterval( 'P1M' );
        $period = new DatePeriod( $start, $interval, $end );
        
        foreach ( $period as $month ) {
            $month_key = $month->format( 'Y-m' );
            $month_end = clone $month;
            $month_end->modify( 'last day of this month' );
            $month_end->setTime( 23, 59, 59 );
            
            // Count total users registered up to this month
            $total_users = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered <= %s",
                $month_end->format( 'Y-m-d H:i:s' )
            ));
            
            $cumulative_counts[ $month_key ] = intval( $total_users );
        }
        
        return $cumulative_counts;
    }
    
    /**
     * Calculate membership summary statistics
     */
    private function calculate_membership_summary( $monthly_data, $geographic_distribution ) {
        $total_new_users = 0;
        $total_active_users = array();
        $total_orders = 0;
        $total_sales = 0;
        $total_repeat_customers = array();
        
        foreach ( $monthly_data as $data ) {
            $total_new_users += $data['new_users'];
            $total_orders += $data['total_orders'];
            $total_sales += $data['total_sales'];
        }
        
        // Get current month data
        $current_month = date( 'Y-m' );
        $current_month_data = isset( $monthly_data[ $current_month ] ) ? 
            $monthly_data[ $current_month ] : array(
                'active_users' => 0,
                'avg_spending_per_customer' => 0,
                'repeat_purchase_rate' => 0
            );
        
        // Get top cities
        $top_cities = array_slice( $geographic_distribution, 0, 5, true );
        
        return array(
            'total_new_users' => $total_new_users,
            'current_active_users' => $current_month_data['active_users'],
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'avg_order_value' => $total_orders > 0 ? $total_sales / $total_orders : 0,
            'current_avg_spending' => $current_month_data['avg_spending_per_customer'],
            'current_repeat_rate' => $current_month_data['repeat_purchase_rate'],
            'top_cities' => $top_cities
        );
    }
    
    /**
     * Make API request with custom parameters
     */
    private function make_request_with_params( $endpoint, $params = array() ) {
        try {
            // Initialize WooCommerce client
            $woocommerce = new Client(
                $this->store_url,
                $this->consumer_key,
                $this->consumer_secret,
                [
                    'version' => $this->api_version,
                ]
            );

            // Make the API request
            $response = $woocommerce->get( $endpoint, $params );
            
            // The WooCommerce client returns the data directly, not a WP response object
            return $response;
            
        } catch ( Exception $e ) {
            // Convert exceptions to WP_Error for consistency
            return new WP_Error( 'api_error', $e->getMessage() );
        }
    }

    /**
     * Get monthly comparison data (current month vs last month)
     */
    private function get_monthly_comparison() {
        // Current month
        $current_month_start = date( 'Y-m-01' );
        $current_month_end = date( 'Y-m-d' );
        
        // Last month
        $last_month_start = date( 'Y-m-01', strtotime( '-1 month' ) );
        $last_month_end = date( 'Y-m-t', strtotime( '-1 month' ) );
        
        // NEW: Get current month sales data using HPOS SQL query instead of REST API
        $current_month_sales_data = $this->get_sales_report_via_hpos( $current_month_start, $current_month_end );
        
        // NEW: Get last month sales data using HPOS SQL query instead of REST API
        $last_month_sales_data = $this->get_sales_report_via_hpos( $last_month_start, $last_month_end );

        // Extract sales and customer data directly from API response
        $current_month_sales = 0;
        $current_month_customers = 0;
        if ( !is_wp_error( $current_month_sales_data ) && isset($current_month_sales_data[0]) ) {
            $current_month_sales = floatval( $current_month_sales_data[0]->total_sales );
            $current_month_customers = intval( $current_month_sales_data[0]->total_customers );
        }
        
        $last_month_sales = 0;
        $last_month_customers = 0;
        if ( !is_wp_error( $last_month_sales_data ) && isset($last_month_sales_data[0]) ) {
            $last_month_sales = floatval( $last_month_sales_data[0]->total_sales );
            $last_month_customers = intval( $last_month_sales_data[0]->total_customers );
        }
        
        return array(
            'current_month_sales' => $current_month_sales,
            'last_month_sales' => $last_month_sales,
            'current_month_customers' => $current_month_customers,
            'last_month_customers' => $last_month_customers
        );
    }
    
    /**
     * Get average sales data for 30/60/90 days
     */
    private function get_average_sales_data() {
        $results = array();
        $periods = array( 30, 60, 90 );
        
        foreach ( $periods as $days ) {
            $start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
            $end_date = date( 'Y-m-d' );
            
            // NEW: Use HPOS SQL query instead of REST API
            $sales_data = $this->get_sales_report_via_hpos( $start_date, $end_date );

            $total_sales = 0;
            if ( isset($sales_data[0]) ) {
                $total_sales = floatval( $sales_data[0]->total_sales );
            }
            
            $average = $total_sales / $days;
            $results["avg_{$days}_days"] = $average;
        }
        
        return $results;
    }
    
    /**
     * Get top viewed products from tracking data
     */
    public function get_top_viewed_products( $start_date, $end_date, $limit = 10 ) {
        return CSR_Init::get_top_viewed_products( $start_date, $end_date, $limit );
    }
    
    /**
     * Get page view data for reports
     */
    public function get_page_view_data( $start_date, $end_date ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'csr_product_views';
        
        // Get total views for the period
        $total_views = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(view_count) FROM $table_name WHERE view_date BETWEEN %s AND %s",
            $start_date,
            $end_date
        ));
        
        // Get daily view trends
        $daily_views = $wpdb->get_results( $wpdb->prepare(
            "SELECT view_date, SUM(view_count) as views 
             FROM $table_name 
             WHERE view_date BETWEEN %s AND %s 
             GROUP BY view_date 
             ORDER BY view_date ASC",
            $start_date,
            $end_date
        ));
        
        // Get top products
        $top_products = $this->get_top_viewed_products( $start_date, $end_date, 5 );
        
        return array(
            'total_views' => intval( $total_views ),
            'daily_trends' => $daily_views,
            'top_products' => $top_products,
            'period' => array(
                'start' => $start_date,
                'end' => $end_date
            )
        );
    }
    
    /**
     * Get detailed period comparison for trend chart
     */
    private function get_detailed_period_comparison( $sales_data_current, $sales_data_comparison ) {
        if ( !$sales_data_current || !$sales_data_comparison ) {
            return array(
                'current_period' => array(),
                'comparison_period' => array()
            );
        }
        
        // Extract totals from sales data
        $current_totals = isset($sales_data_current[0]->totals) ? $sales_data_current[0]->totals : new stdClass();
        $comparison_totals = isset($sales_data_comparison[0]->totals) ? $sales_data_comparison[0]->totals : new stdClass();
        
        // Convert totals objects to arrays for easier processing
        $current_daily = array();
        $comparison_daily = array();
        
        // Process current period totals
        foreach ($current_totals as $date => $data) {
            $current_daily[] = floatval($data->sales);
        }
        
        // Process comparison period totals
        foreach ($comparison_totals as $date => $data) {
            $comparison_daily[] = floatval($data->sales);
        }
        
        return array(
            'current_period' => $current_daily,
            'comparison_period' => $comparison_daily,
            'current_totals' => $current_totals,
            'comparison_totals' => $comparison_totals
        );
    }
    
    /**
     * Get yearly comparison data for the last 3 years
     */
    private function get_yearly_comparison() {
        $current_year = date( 'Y' );
        $years_data = array();
        
        // Get data for last 3 years
        for ( $i = 2; $i >= 0; $i-- ) {
            $year = $current_year - $i;
            $year_data = $this->get_monthly_sales_for_year( $year );
            if ( !empty( $year_data ) ) {
                $years_data[$year] = $year_data;
            }
        }
        
        return $years_data;
    }
    
    /**
     * Get monthly sales data for a specific year
     */
    private function get_monthly_sales_for_year( $year ) {
        $monthly_sales = array_fill( 0, 12, 0 ); // Initialize 12 months with zero
        
        // Get sales data for the entire year
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        
        // NEW: Use HPOS SQL query instead of REST API
        $sales_data = $this->get_sales_report_via_hpos( $start_date, $end_date );

        // Check if we have sales data
        if ( !isset($sales_data[0]->totals) ) {
            return $monthly_sales;
        }
        
        $totals = $sales_data[0]->totals;
        
        // Process the totals - for yearly data, keys will be in YYYY-MM format
        foreach ( $totals as $date_key => $data ) {
            // Extract month from date key (could be YYYY-MM-DD or YYYY-MM format)
            if ( strpos($date_key, $year . '-') === 0 ) {
                $date_parts = explode('-', $date_key);
                if ( count($date_parts) >= 2 ) {
                    $month_index = intval($date_parts[1]) - 1; // Convert to 0-based index
                    
                    if ( $month_index >= 0 && $month_index < 12 ) {
                        $monthly_sales[$month_index] += floatval($data->sales);
                    }
                }
            }
        }
        
        return $monthly_sales;
    }
    
    /**
     * Get payment method trends for last 12 months
     */
    private function get_payment_monthly_trends() {
        $monthly_trends = array();
        $current_date = new DateTime();
        
        // Get data for last 12 months
        for ( $i = 11; $i >= 0; $i-- ) {
            $month_start = clone $current_date;
            $month_start->modify( "-{$i} months" );
            $month_start->modify( 'first day of this month' );
            $month_start->setTime( 0, 0, 0 );
            
            $month_end = clone $month_start;
            $month_end->modify( 'last day of this month' );
            $month_end->setTime( 23, 59, 59 );
            
            // Get orders for this month using HPOS SQL query
            $orders = $this->get_orders_via_hpos( $month_start->format( 'Y-m-d' ), $month_end->format( 'Y-m-d' ) );
            
            $payment_methods = array();
            if ( !is_wp_error( $orders ) ) {
                foreach ( $orders as $order ) {

                    if ( !empty( $order->payment_method_title ) ) {
                        switch ( $order->payment_method_title ) {
                            case 'Credit / Debit Card':
                                $method = '其他';
                                break;
                            case 'Unknown':
                                $method = '其他';
                                break;
                            default:
                                $method = $order->payment_method_title;
                        }
                    } else {
                        $method = '其他';
                    }
                    
                    if ( !isset( $payment_methods[$method] ) ) {
                        $payment_methods[$method] = 0;
                    }
                    
                    $payment_methods[$method] += floatval( $order->total );
                }
            }
            
            $monthly_trends[] = array(
                'month' => $month_start->format( 'Y-m' ),
                'payment_methods' => $payment_methods
            );
        }

        return $monthly_trends;
    }
    
    /**
     * Get top regions summary for overview widget
     */
    private function get_top_regions_summary( $orders ) {
        if ( empty( $orders ) ) {
            return array();
        }
        
        // Filter orders from last 180 days (matching frontend logic)
        $cutoff_date = new DateTime();
        $cutoff_date->modify( '-180 days' );
        
        $city_totals = array();
        
        foreach ( $orders as $order ) {
            $order_date = new DateTime( $order->date_created );
            
            // Only include orders from last 180 days
            if ( $order_date >= $cutoff_date ) {
                // Get city from shipping address
                $city = '';
                if ( isset( $order->shipping->city ) && !empty( $order->shipping->city ) ) {
                    $city = $order->shipping->city;
                } elseif ( isset( $order->billing->city ) && !empty( $order->billing->city ) ) {
                    // Fallback to billing city if shipping city is empty
                    $city = $order->billing->city;
                }
                
                if ( !empty( $city ) ) {
                    if ( !isset( $city_totals[$city] ) ) {
                        $city_totals[$city] = 0;
                    }
                    $city_totals[$city] += floatval( $order->total );
                }
            }
        }
        
        // Sort by sales amount and get top 5
        arsort( $city_totals );
        $top_cities = array_slice( $city_totals, 0, 5, true );
        
        // Convert to format expected by frontend
        $result = array();
        foreach ( $top_cities as $city => $amount ) {
            $result[] = array(
                'city' => $city,
                'amount' => $amount
            );
        }
        
        return $result;
    }
    
    /**
     * Process all regional data in a single loop for efficiency
     * Combines functionality of analyze_regional_sales, generate_regional_monthly_trends,
     * get_district_details, and get_top_districts
     */
    private function process_all_regional_data( $orders, $start_date = null, $end_date = null ) {
        // Hong Kong district mapping
        $district_mapping = array(
            // 香港島
            '中西區' => '香港島', '灣仔區' => '香港島', '東區' => '香港島', '南區' => '香港島',
            // 九龍
            '油尖旺區' => '九龍', '深水埗區' => '九龍', '九龍城區' => '九龍', '黃大仙區' => '九龍', '觀塘區' => '九龍',
            // 新界
            '離島區' => '新界', '荃灣區' => '新界', '屯門區' => '新界', '元朗區' => '新界', '北區' => '新界',
            '大埔區' => '新界', '沙田區' => '新界', '西貢區' => '新界', '葵青區' => '新界'
        );
        
        // Initialize data structures
        $regional_totals = array(
            '香港島' => array('sales' => 0, 'orders' => 0, 'customers' => array(), 'districts' => array()),
            '九龍' => array('sales' => 0, 'orders' => 0, 'customers' => array(), 'districts' => array()),
            '新界' => array('sales' => 0, 'orders' => 0, 'customers' => array(), 'districts' => array())
        );
        
        $district_data = array();
        $district_totals = array(); // For top districts
        
        // Initialize all 18 districts
        $all_districts = array(
            '中西區', '灣仔區', '東區', '南區',
            '油尖旺區', '深水埗區', '九龍城區', '黃大仙區', '觀塘區',
            '離島區', '荃灣區', '屯門區', '元朗區', '北區', '大埔區', '沙田區', '西貢區', '葵青區'
        );
        
        foreach ( $all_districts as $district ) {
            $district_data[$district] = array(
                'name' => $district,
                'sales' => 0,
                'orders' => 0,
                'customers' => array()
            );
            $district_totals[$district] = 0;
        }
        
        // Determine date range for monthly trends
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-12 months' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // Initialize monthly data structure
        $start = new DateTime( $start_date );
        $end = new DateTime( $end_date );
        $interval = new DateInterval( 'P1M' );
        $period = new DatePeriod( $start, $interval, $end );
        
        $monthly_data = array();
        foreach ( $period as $month ) {
            $month_key = $month->format( 'Y-m' );
            $monthly_data[ $month_key ] = array(
                'month' => $month_key,
                'month_label' => $month->format( 'M Y' ),
                '香港島' => 0,
                '九龍' => 0,
                '新界' => 0,
                'total' => 0
            );
        }
        
        // Single loop through all orders
        foreach ( $orders as $order ) {
            $district = $this->extract_district_from_order( $order );
            $order_total = floatval( $order->total );
            $customer_id = $order->customer_id ?? 0;
            $order_date = date( 'Y-m', strtotime( $order->date_created ) );
            
            // Process regional and district data
            if ( !empty( $district ) && isset( $district_mapping[$district] ) ) {
                $region = $district_mapping[$district];
                
                // Update regional totals
                $regional_totals[$region]['sales'] += $order_total;
                $regional_totals[$region]['orders']++;
                
                // Track unique customers per region
                if ( !empty( $customer_id ) ) {
                    $regional_totals[$region]['customers'][$customer_id] = true;
                }
                
                // Update district data within region
                if ( !isset( $regional_totals[$region]['districts'][$district] ) ) {
                    $regional_totals[$region]['districts'][$district] = array(
                        'sales' => 0, 
                        'orders' => 0, 
                        'customers' => array()
                    );
                }
                $regional_totals[$region]['districts'][$district]['sales'] += $order_total;
                $regional_totals[$region]['districts'][$district]['orders']++;
                if ( !empty( $customer_id ) ) {
                    $regional_totals[$region]['districts'][$district]['customers'][$customer_id] = true;
                }
                
                // Update district details
                $district_data[$district]['sales'] += $order_total;
                $district_data[$district]['orders']++;
                if ( !empty( $customer_id ) ) {
                    $district_data[$district]['customers'][$customer_id] = true;
                }
                
                // Update top districts data
                $district_totals[$district] += $order_total;
                
                // Update monthly trends
                if ( isset( $monthly_data[ $order_date ] ) ) {
                    $monthly_data[ $order_date ][$region] += $order_total;
                    $monthly_data[ $order_date ]['total'] += $order_total;
                }
            } else {
                // Handle orders without district mapping for monthly totals
                if ( isset( $monthly_data[ $order_date ] ) ) {
                    $monthly_data[ $order_date ]['total'] += $order_total;
                }
            }
        }
        
        // Convert customer arrays to counts
        foreach ( $regional_totals as &$region_data ) {
            $region_data['customers'] = count( $region_data['customers'] );
            
            foreach ( $region_data['districts'] as &$district_info ) {
                $district_info['customers'] = count( $district_info['customers'] );
            }
        }
        
        foreach ( $district_data as &$data ) {
            $data['customers'] = count( $data['customers'] );
        }
        
        // Build top districts array
        arsort( $district_totals );
        $top_districts = array();
        foreach ( $district_totals as $district => $amount ) {
            $top_districts[] = array(
                'district' => $district,
                'sales' => $amount
            );
        }
        
        return array(
            'regional_summary' => $regional_totals,
            'monthly_trends' => array_values( $monthly_data ),
            'district_details' => $district_data,
            'top_districts' => $top_districts
        );
    }
    
    /**
     * Extract district from order address
     */
    private function extract_district_from_order( $order ) {
        // Try to get district from shipping address first
        $address = '';
        if ( isset( $order->shipping->address_1 ) ) {
            $address = $order->shipping->address_1 . ' ' . $order->shipping->address_2;
        } elseif ( isset( $order->billing->address_1 ) ) {
            $address = $order->billing->address_1 . ' ' . $order->billing->address_2;
        }
        
        // Hong Kong districts list
        $districts = array(
            '中西區', '灣仔區', '東區', '南區',
            '油尖旺區', '深水埗區', '九龍城區', '黃大仙區', '觀塘區',
            '離島區', '荃灣區', '屯門區', '元朗區', '北區', '大埔區', '沙田區', '西貢區', '葵青區'
        );
        
        // Check if any district name appears in the address
        foreach ( $districts as $district ) {
            if ( strpos( $address, $district ) !== false ) {
                return $district;
            }
        }
        
        // Fallback: try to extract from city field
        $city = '';
        if ( isset( $order->shipping->city ) && !empty( $order->shipping->city ) ) {
            $city = $order->shipping->city;
        } elseif ( isset( $order->billing->city ) && !empty( $order->billing->city ) ) {
            $city = $order->billing->city;
        }
        
        foreach ( $districts as $district ) {
            if ( strpos( $city, $district ) !== false ) {
                return $district;
            }
        }
        
        return '';
    }
    
    /**
     * Get channel trends for last 12 months
     */
    private function get_channel_monthly_trends() {
        $monthly_trends = array();
        $current_date = new DateTime();
        
        // Get data for last 12 months
        for ( $i = 11; $i >= 0; $i-- ) {
            $month_start = clone $current_date;
            $month_start->modify( "-{$i} months" );
            $month_start->modify( 'first day of this month' );
            $month_start->setTime( 0, 0, 0 );
            
            $month_end = clone $month_start;
            $month_end->modify( 'last day of this month' );
            $month_end->setTime( 23, 59, 59 );
            
            $orders = $this->get_orders_via_hpos( $month_start->format( 'Y-m-d' ), $month_end->format( 'Y-m-d' ) );
            
            $channels = array();
            if ( !is_wp_error( $orders ) ) {
                foreach ( $orders as $order ) {
                    $channel = $this->get_channel_label( $order->created_via ?? '' );
                    
                    if ( !isset( $channels[$channel] ) ) {
                        $channels[$channel] = 0;
                    }
                    
                    $channels[$channel] += floatval( $order->total );
                }
            }
            
            $monthly_trends[] = array(
                'month' => $month_start->format( 'Y-m' ),
                'channels' => $channels
            );
        }
        
        return $monthly_trends;
    }
    
    /**
     * Analyze sales channels from orders
     */
    private function analyze_sales_channels( $orders ) {
        $channels = array();
        
        foreach ( $orders as $order ) {
            $channel = $this->get_channel_label( $order->created_via ?? '' );
            
            if ( !isset( $channels[$channel] ) ) {
                $channels[$channel] = array(
                    'channel' => $channel,
                    'count' => 0,
                    'total' => 0
                );
            }
            
            $channels[$channel]['count']++;
            $channels[$channel]['total'] += floatval( $order->total );
        }
        
        return array_values( $channels );
    }
    
    /**
     * Calculate channel summary statistics
     */
    private function calculate_channel_summary( $orders ) {
        $total_orders = count( $orders );
        $total_amount = 0;
        $channels = array();
        
        foreach ( $orders as $order ) {
            $total_amount += floatval( $order->total );
            $channel = $this->get_channel_label( $order->created_via ?? '' );
            
            if ( !isset( $channels[$channel] ) ) {
                $channels[$channel] = array(
                    'count' => 0,
                    'total' => 0
                );
            }
            
            $channels[$channel]['count']++;
            $channels[$channel]['total'] += floatval( $order->total );
        }
        
        // Calculate percentages
        foreach ( $channels as $channel => $data ) {
            $channels[$channel]['percentage'] = $total_orders > 0 ? 
                round( ( $data['count'] / $total_orders ) * 100, 2 ) : 0;
            $channels[$channel]['amount_percentage'] = $total_amount > 0 ? 
                round( ( $data['total'] / $total_amount ) * 100, 2 ) : 0;
        }
        
        return array(
            'total_orders' => $total_orders,
            'total_amount' => $total_amount,
            'channels' => $channels,
            'average_order_value' => $total_orders > 0 ? $total_amount / $total_orders : 0
        );
    }
    
    /**
     * Get channel label from created_via value
     */
    private function get_channel_label( $created_via ) {
        switch ( $created_via ) {
            case 'admin':
                return '線下/活動銷售';
            case 'checkout':
                return '網頁銷售';
            default:
                return '其他渠道';
        }
    }
    
    /**
     * Process all product data in a single loop for efficiency
     * Combines functionality of analyze_products_by_sales, analyze_products_by_quantity,
     * get_product_sales_trends, and get_product_quantity_trends
     */
    private function process_all_product_data( $orders, $start_date = null, $end_date = null ) {
        $products_by_sales = array();
        $products_by_quantity = array();
        
        // Convert HPOS results to full order objects for line_items access
        foreach ( $orders as $order_data ) {
            $order = wc_get_order( $order_data->id );
            if ( !$order ) {
                continue; // Skip if order not found
            }
            
            // Get line items from the WooCommerce order object
            $line_items = $order->get_items();
            
            if ( !empty( $line_items ) ) {
                foreach ( $line_items as $item ) {
                    // Get proper product name using product_id
                    $product_name = $this->get_product_name_by_id( $item->get_product_id() );
                    if ( empty( $product_name ) ) {
                        $product_name = $item->get_name() ?: '未知產品';
                    }
                    
                    $quantity = intval( $item->get_quantity() );
                    $item_total = floatval( $item->get_total() );
                    
                    // Collect sales data
                    if ( !isset( $products_by_sales[$product_name] ) ) {
                        $products_by_sales[$product_name] = array(
                            'name' => $product_name,
                            'total_sales' => 0,
                            'quantity' => 0,
                            'orders' => 0,
                            'monthly_sales' => array(),
                            'monthly_quantity' => array()
                        );
                    }
                    $products_by_sales[$product_name]['total_sales'] += $item_total;
                    $products_by_sales[$product_name]['quantity'] += $quantity;
                    $products_by_sales[$product_name]['orders']++;
                    
                    // Collect quantity data (same structure)
                    if ( !isset( $products_by_quantity[$product_name] ) ) {
                        $products_by_quantity[$product_name] = array(
                            'name' => $product_name,
                            'quantity' => 0,
                            'total_sales' => 0,
                            'orders' => 0,
                            'monthly_sales' => array(),
                            'monthly_quantity' => array()
                        );
                    }
                    $products_by_quantity[$product_name]['quantity'] += $quantity;
                    $products_by_quantity[$product_name]['total_sales'] += $item_total;
                    $products_by_quantity[$product_name]['orders']++;
                    
                    // Add monthly data to product records
                    if ( $start_date && $end_date ) {
                        $order_date = new DateTime( $order_data->date_created );
                        $order_month = $order_date->format( 'Y-m' );
                        
                        // Sales monthly data
                        if ( !isset( $products_by_sales[$product_name]['monthly_sales'][$order_month] ) ) {
                            $products_by_sales[$product_name]['monthly_sales'][$order_month] = 0;
                        }
                        $products_by_sales[$product_name]['monthly_sales'][$order_month] += $item_total;
                        
                        // Quantity monthly data
                        if ( !isset( $products_by_sales[$product_name]['monthly_quantity'][$order_month] ) ) {
                            $products_by_sales[$product_name]['monthly_quantity'][$order_month] = 0;
                        }
                        $products_by_sales[$product_name]['monthly_quantity'][$order_month] += $quantity;
                        
                        // Same for quantity array (for consistency)
                        if ( !isset( $products_by_quantity[$product_name]['monthly_sales'][$order_month] ) ) {
                            $products_by_quantity[$product_name]['monthly_sales'][$order_month] = 0;
                        }
                        $products_by_quantity[$product_name]['monthly_sales'][$order_month] += $item_total;
                        
                        if ( !isset( $products_by_quantity[$product_name]['monthly_quantity'][$order_month] ) ) {
                            $products_by_quantity[$product_name]['monthly_quantity'][$order_month] = 0;
                        }
                        $products_by_quantity[$product_name]['monthly_quantity'][$order_month] += $quantity;
                    }
                }
            }
        }
        
        // Second pass: Sort and get top 10 products
        uasort( $products_by_sales, function( $a, $b ) {
            return $b['total_sales'] <=> $a['total_sales'];
        });
        // Filter out products with $0 total_sales and limit to 10
        $products_by_sales_filtered = array_filter( $products_by_sales, function( $product ) {
            return $product['total_sales'] > 0;
        });
        $top_products_by_sales = array_slice( array_values( $products_by_sales_filtered ), 0, 10 );
        
        uasort( $products_by_quantity, function( $a, $b ) {
            return $b['quantity'] <=> $a['quantity'];
        });
        // Keep all products for quantity charts but limit to 10
        $top_products_by_quantity = array_slice( array_values( $products_by_quantity ), 0, 10 );
        
        // Third pass: Build monthly trends only for top products
        $monthly_sales_trends = array();
        $monthly_quantity_trends = array();
        
        if ( $start_date && $end_date ) {
            // Initialize monthly structure
            $current_date = new DateTime( $start_date );
            $end_date_obj = new DateTime( $end_date );
            
            while ( $current_date <= $end_date_obj ) {
                $month_key = $current_date->format( 'Y-m' );
                $month_label = $current_date->format( 'Y年n月' );
                
                // Build sales trends for top sales products only
                $month_sales_data = array();
                foreach ( $top_products_by_sales as $product ) {
                    $product_name = $product['name'];
                    $sales_amount = isset( $products_by_sales[$product_name]['monthly_sales'][$month_key] ) 
                        ? $products_by_sales[$product_name]['monthly_sales'][$month_key] 
                        : 0;
                    
                    if ( $sales_amount > 0 ) { // Only include non-zero sales
                        $month_sales_data[$product_name] = $sales_amount;
                    }
                }
                
                $monthly_sales_trends[] = array(
                    'month' => $month_key,
                    'month_label' => $month_label,
                    'products' => $month_sales_data
                );
                
                // Build quantity trends for top quantity products only
                $month_quantity_data = array();
                foreach ( $top_products_by_quantity as $product ) {
                    $product_name = $product['name'];
                    $quantity_amount = isset( $products_by_quantity[$product_name]['monthly_quantity'][$month_key] ) 
                        ? $products_by_quantity[$product_name]['monthly_quantity'][$month_key] 
                        : 0;
                    
                    $month_quantity_data[$product_name] = $quantity_amount;
                }
                
                $monthly_quantity_trends[] = array(
                    'month' => $month_key,
                    'month_label' => $month_label,
                    'products' => $month_quantity_data
                );
                
                $current_date->modify( 'first day of next month' );
            }
        }
        
        return array(
            'top_products_by_sales' => $top_products_by_sales,
            'top_products_by_quantity' => $top_products_by_quantity,
            'sales_trends' => $monthly_sales_trends,
            'quantity_trends' => $monthly_quantity_trends,
        );
    }

    /**
     * Get color mapping for a list of products with sequential assignment
     * Returns array with product names as keys and colors as values
     * Uses sequential assignment to avoid hash collisions for small product sets
     */
    private function get_product_color_map( $products ) {
        // High contrast color palette with 21 maximally distinct colors
        $colors = array(
            '#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
            '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4', 
            '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000', 
            '#aaffc3', '#808000', '#ffd8b1', '#000075', '#a9a9a9', 
            '#000000'
        );
        
        $color_map = array();
        $product_names = array();
        
        // Extract product names and sort them for consistent assignment
        foreach ( $products as $product ) {
            $product_name = is_array( $product ) ? $product['name'] : $product;
            $product_names[] = $product_name;
        }
        
        // Sort product names to ensure consistent color assignment order
        sort( $product_names );
        
        // Assign colors sequentially to avoid collisions
        foreach ( $product_names as $index => $product_name ) {
            // Use sequential assignment for small sets, hash-based for larger sets
            if ( count( $product_names ) <= count( $colors ) ) {
                // Sequential assignment for small product sets
                $color_index = $index % count( $colors );
            } else {
                // Hash-based assignment for larger sets with better distribution
                $hash = md5( $product_name );
                $hash_int = hexdec( substr( $hash, 0, 8 ) );
                $color_index = $hash_int % count( $colors );
            }
            
            $color_map[$product_name] = $colors[$color_index];
        }
        
        return $color_map;
    }

    /**
     * Get product name by product ID using WooCommerce core functions
     */
    private function get_product_name_by_id( $product_id ) {
        if ( empty( $product_id ) ) {
            return '';
        }
        
        // Use WooCommerce core function to get product
        $product = wc_get_product( $product_id );
        
        if ( $product ) {
            return $product->get_name();
        }
        
        return '';
    }
    
    /**
     * Get orders using native HPOS SQL query (faster than REST API)
     * Returns orders in format compatible with REST API response
     */
    private function get_orders_via_hpos( $start_date = null, $end_date = null ) {
        global $wpdb;
        
        // HPOS table names
        $orders_table = $wpdb->prefix . 'wc_orders';
        $order_addresses_table = $wpdb->prefix . 'wc_order_addresses';
        $order_operational_data_table = $wpdb->prefix . 'wc_order_operational_data';
        
        // Build WHERE clause for date filtering
        $where_conditions = array();
        $where_conditions[] = "o.type = 'shop_order'";
        $where_conditions[] = "o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')";
        
        $prepare_values = array();
        
        if ( $start_date ) {
            $where_conditions[] = "o.date_created_gmt >= %s";
            $prepare_values[] = $start_date . ' 00:00:00';
        }
        
        if ( $end_date ) {
            $where_conditions[] = "o.date_created_gmt <= %s";
            $prepare_values[] = $end_date . ' 23:59:59';
        }
        
        $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        
        // Build the main query
        $sql = "SELECT 
                    o.id,
                    o.customer_id,
                    o.total_amount as total,
                    o.date_created_gmt as date_created,
                    o.status,
                    o.payment_method,
                    o.payment_method_title,
                    ood.created_via,
                    -- Billing address
                    ba.city as billing_city,
                    ba.address_1 as billing_address_1,
                    ba.address_2 as billing_address_2,
                    -- Shipping address
                    sa.city as shipping_city,
                    sa.address_1 as shipping_address_1,
                    sa.address_2 as shipping_address_2
                FROM {$orders_table} o
                LEFT JOIN {$order_addresses_table} ba ON o.id = ba.order_id AND ba.address_type = 'billing'
                LEFT JOIN {$order_addresses_table} sa ON o.id = sa.order_id AND sa.address_type = 'shipping'
                LEFT JOIN {$order_operational_data_table} ood ON o.id = ood.order_id
                {$where_clause}
                ORDER BY o.date_created_gmt DESC";
        
        // Prepare and execute the query
        if ( !empty( $prepare_values ) ) {
            $results = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_values ) );
        } else {
            
            $results = $wpdb->get_results( $sql );
        }
        
        if ( $wpdb->last_error ) {
            
            throw new Exception( 'Database error: ' . $wpdb->last_error );
        }
        
        // Convert to format compatible with REST API response
        $orders = array();
        foreach ( $results as $row ) {
            $order = new stdClass();
            
            // Basic order info
            $order->id = intval( $row->id );
            $order->customer_id = intval( $row->customer_id );
            $order->total = floatval( $row->total );
            $order->date_created = $row->date_created;
            $order->status = $row->status;
            $order->payment_method = $row->payment_method ?: '';
            $order->payment_method_title = $row->payment_method_title ?: 'Unknown';
            $order->created_via = $row->created_via ?: 'unknown';
            
            // Billing address object (simplified)
            $order->billing = new stdClass();
            $order->billing->city = $row->billing_city ?: '';
            $order->billing->address_1 = $row->billing_address_1 ?: '';
            $order->billing->address_2 = $row->billing_address_2 ?: '';
            
            // Shipping address object (simplified)
            $order->shipping = new stdClass();
            $order->shipping->city = $row->shipping_city ?: '';
            $order->shipping->address_1 = $row->shipping_address_1 ?: '';
            $order->shipping->address_2 = $row->shipping_address_2 ?: '';
            
            $orders[] = $order;
        }
        
        return $orders;
    }
    
    /**
     * Get sales report data using HPOS - replacement for reports/sales endpoint
     * Maintains exact WooCommerce API response structure with 60-day aggregation rule
     */
    private function get_sales_report_via_hpos( $start_date = null, $end_date = null ) {
        global $wpdb;
        
        // Set default date range if not provided
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // Calculate period length for aggregation decision
        $start_date_obj = new DateTime( $start_date );
        $end_date_obj = new DateTime( $end_date );
        $period_days = $end_date_obj->diff( $start_date_obj )->days + 1;
        
        // HPOS table name
        $orders_table = $wpdb->prefix . 'wc_orders';
        
        // Base query for total aggregations
        $base_sql = $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_orders,
                COUNT(DISTINCT customer_id) as total_customers,
                SUM(total_amount) as total_sales,
                COUNT(DISTINCT DATE(date_created_gmt)) as sales_days
             FROM {$orders_table}
             WHERE type = 'shop_order'
               AND status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
               AND date_created_gmt BETWEEN %s AND %s",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );
        
        $totals_result = $wpdb->get_row( $base_sql );
        
        // Build totals breakdown based on 60-day rule
        $totals = new stdClass();
        
        if ( $period_days <= 60 ) {
            // Daily aggregation for periods <= 60 days - ensure ALL dates are included
            $daily_sql = $wpdb->prepare(
                "SELECT 
                    DATE(date_created_gmt) as sales_date,
                    COUNT(*) as orders,
                    COUNT(DISTINCT customer_id) as customers, 
                    SUM(total_amount) as sales
                 FROM {$orders_table}
                 WHERE type = 'shop_order'
                   AND status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
                   AND date_created_gmt BETWEEN %s AND %s
                 GROUP BY DATE(date_created_gmt)
                 ORDER BY sales_date",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            );
            
            $daily_results = $wpdb->get_results( $daily_sql );
            
            // Convert to associative array for easy lookup
            $daily_data = array();
            foreach ( $daily_results as $daily ) {
                $daily_data[ $daily->sales_date ] = array(
                    'orders' => intval( $daily->orders ),
                    'customers' => intval( $daily->customers ),
                    'sales' => floatval( $daily->sales ),
                );
            }
            
            // Generate ALL dates in range, including zero-sales dates
            $current_date = new DateTime( $start_date );
            $end_date_obj = new DateTime( $end_date );
            
            while ( $current_date <= $end_date_obj ) {
                $date_key = $current_date->format( 'Y-m-d' );
                
                if ( isset( $daily_data[ $date_key ] ) ) {
                    // Use actual data
                    $totals->$date_key = (object) $daily_data[ $date_key ];
                } else {
                    // Use zero data for dates with no sales
                    $totals->$date_key = (object) array(
                        'orders' => 0,
                        'customers' => 0,
                        'sales' => 0,
                    );
                }
                
                $current_date->modify( '+1 day' );
            }
        } else {
            // Monthly aggregation for periods > 60 days - ensure ALL months are included
            $monthly_sql = $wpdb->prepare(
                "SELECT 
                    DATE_FORMAT(date_created_gmt, '%%Y-%%m') as sales_month,
                    COUNT(*) as orders,
                    COUNT(DISTINCT customer_id) as customers,
                    SUM(total_amount) as sales
                 FROM {$orders_table}
                 WHERE type = 'shop_order'
                   AND status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
                   AND date_created_gmt BETWEEN %s AND %s
                 GROUP BY DATE_FORMAT(date_created_gmt, '%%Y-%%m')
                 ORDER BY sales_month",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            );
            
            $monthly_results = $wpdb->get_results( $monthly_sql );
            
            // Convert to associative array for easy lookup
            $monthly_data = array();
            foreach ( $monthly_results as $monthly ) {
                $monthly_data[ $monthly->sales_month ] = array(
                    'orders' => intval( $monthly->orders ),
                    'customers' => intval( $monthly->customers ),
                    'sales' => floatval( $monthly->sales ),
                );
            }
            
            // Generate ALL months in range, including zero-sales months
            $current_month = new DateTime( $start_date );
            $current_month->modify( 'first day of this month' ); // Start from beginning of start month
            $end_month = new DateTime( $end_date );
            $end_month->modify( 'first day of this month' ); // End at beginning of end month
            
            while ( $current_month <= $end_month ) {
                $month_key = $current_month->format( 'Y-m' );
                
                if ( isset( $monthly_data[ $month_key ] ) ) {
                    // Use actual data
                    $totals->$month_key = (object) $monthly_data[ $month_key ];
                } else {
                    // Use zero data for months with no sales
                    $totals->$month_key = (object) array(
                        'orders' => 0,
                        'customers' => 0,
                        'sales' => 0,
                    );
                }
                
                $current_month->modify( '+1 month' );
            }
        }
        
        // Build response matching WooCommerce API structure
        $response = array(
            (object) array(
                'total_sales' => floatval( $totals_result->total_sales ?? 0 ),
                'net_sales' => floatval( ( $totals_result->total_sales ?? 0 ) - ( $totals_result->total_tax ?? 0 ) ),
                'average_sales' => $totals_result->sales_days > 0 ? 
                    floatval( $totals_result->total_sales ) / intval( $totals_result->sales_days ) : 0,
                'total_orders' => intval( $totals_result->total_orders ?? 0 ),
                'total_items' => intval( $totals_result->total_orders ?? 0 ), // Approximate
                'total_refunds' => 0, // Would need separate query for refunds
                'total_discount' => 0, // Would need separate query for discounts
                'totals_grouped_by' => $period_days <= 60 ? 'day' : 'month',
                'totals' => $totals,
                'total_customers' => intval( $totals_result->total_customers ?? 0 ),
                'period' => $period_days
            )
        );
        
        return $response;
    }
    
    /**
     * Get top sellers data using HPOS - replacement for reports/top_sellers endpoint  
     * Maintains exact WooCommerce API response structure
     */
    private function get_top_sellers_via_hpos( $start_date = null, $end_date = null, $limit = 10 ) {
        global $wpdb;
        
        // Set default date range if not provided
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // HPOS table names
        $orders_table = $wpdb->prefix . 'wc_orders';
        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
        
        // Get top selling products by quantity
        $top_sellers_sql = $wpdb->prepare(
            "SELECT 
                oim_product.meta_value as product_id,
                SUM(oim_qty.meta_value) as quantity,
                p.post_title as name
             FROM {$orders_table} o
             INNER JOIN {$order_items_table} oi ON o.id = oi.order_id
             INNER JOIN {$order_itemmeta_table} oim_product ON oi.order_item_id = oim_product.order_item_id 
                 AND oim_product.meta_key = '_product_id'
             INNER JOIN {$order_itemmeta_table} oim_qty ON oi.order_item_id = oim_qty.order_item_id 
                 AND oim_qty.meta_key = '_qty'
             LEFT JOIN {$wpdb->posts} p ON oim_product.meta_value = p.ID
             WHERE o.type = 'shop_order'
               AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
               AND o.date_created_gmt BETWEEN %s AND %s
               AND oi.order_item_type = 'line_item'
               AND oim_product.meta_value > 0
             GROUP BY oim_product.meta_value, p.post_title
             ORDER BY quantity DESC
             LIMIT %d",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59',
            $limit
        );
        
        $results = $wpdb->get_results( $top_sellers_sql );
        
        // Build response matching WooCommerce API structure
        $top_sellers = array();
        
        foreach ( $results as $result ) {
            $product_id = intval( $result->product_id );
            $quantity = intval( $result->quantity );
            $name = $result->name ?: '未知產品';
            
            $top_sellers[] = (object) array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'name' => $name,
                'slug' => sanitize_title( $name )
            );
        }
        
        return $top_sellers;
    }
    
    /**
     * Process coupon usage data using wc_order_coupon_lookup table - OPTIMIZED
     * Much faster than looping through all orders
     */
    private function process_coupon_usage_via_lookup( $coupons, $start_date = null, $end_date = null ) {
        global $wpdb;
        
        // Set default date range if not provided
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        $coupon_usage = array();
        
        // Initialize coupon data
        foreach ( $coupons as $coupon ) {
            $coupon_usage[$coupon->id] = array(
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description ?? '',
                'discount_type' => $coupon->discount_type ?? '',
                'amount' => $coupon->amount ?? 0,
                'date_created' => $coupon->date_created ?? '',
                'date_expires' => $coupon->date_expires ?? '',
                'usage_count' => 0,
                'total_revenue' => 0,
                'total_discount' => 0
            );
        }
        
        // HPOS table names
        $orders_table = $wpdb->prefix . 'wc_orders';
        $coupon_lookup_table = $wpdb->prefix . 'wc_order_coupon_lookup';
        
        // Get coupon usage data from lookup table - much faster than scanning all orders
        $coupon_usage_sql = $wpdb->prepare(
            "SELECT 
                ocl.coupon_id,
                p.post_title as coupon_code,
                ocl.discount_amount,
                o.total_amount as order_total,
                COUNT(*) as usage_count,
                SUM(o.total_amount) as total_revenue,
                SUM(ocl.discount_amount) as total_discount
             FROM {$coupon_lookup_table} ocl
             INNER JOIN {$orders_table} o ON ocl.order_id = o.id
             INNER JOIN {$wpdb->posts} p ON ocl.coupon_id = p.ID
             WHERE o.type = 'shop_order'
               AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
               AND o.date_created_gmt BETWEEN %s AND %s
               AND p.post_type = 'shop_coupon'
             GROUP BY ocl.coupon_id, p.post_title
             ORDER BY usage_count DESC",
            $start_date . ' 00:00:00',
            $end_date . ' 23:59:59'
        );
        
        $usage_results = $wpdb->get_results( $coupon_usage_sql );
        
        // Update coupon usage data with actual usage statistics
        foreach ( $usage_results as $usage ) {
            $coupon_id = $usage->coupon_id;

            if ( isset( $coupon_usage[$coupon_id] ) ) {
                $coupon_usage[$coupon_id]['usage_count'] = intval( $usage->usage_count );
                $coupon_usage[$coupon_id]['total_revenue'] = floatval( $usage->total_revenue );
                $coupon_usage[$coupon_id]['total_discount'] = floatval( $usage->total_discount );
            }
        }
        
        return array_values( $coupon_usage );
    }
    
    /**
     * Generate promotion sales trend using HPOS sales report - follows 60-day aggregation rule
     * Much more efficient and consistent with other trend data
     */
    private function generate_promotion_sales_trend_via_hpos( $start_date = null, $end_date = null ) {
        global $wpdb;
        
        // Set default date range if not provided
        if ( !$start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-12 months' ) );
        }
        if ( !$end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // Get overall sales report using our optimized HPOS method
        $sales_report = $this->get_sales_report_via_hpos( $start_date, $end_date );
        $sales_totals = $sales_report[0]->totals ?? new stdClass();
        
        // Calculate period length for aggregation decision (same as sales report)
        $start_date_obj = new DateTime( $start_date );
        $end_date_obj = new DateTime( $end_date );
        $period_days = $end_date_obj->diff( $start_date_obj )->days + 1;
        
        // HPOS table names
        $orders_table = $wpdb->prefix . 'wc_orders';
        $coupon_lookup_table = $wpdb->prefix . 'wc_order_coupon_lookup';
        
        // Get coupon sales data following same aggregation rules
        if ( $period_days <= 60 ) {
            // Daily aggregation for periods <= 60 days
            $coupon_sales_sql = $wpdb->prepare(
                "SELECT 
                    DATE(o.date_created_gmt) as sales_date,
                    COUNT(DISTINCT ocl.order_id) as coupon_orders,
                    SUM(o.total_amount) as coupon_sales
                 FROM {$coupon_lookup_table} ocl
                 INNER JOIN {$orders_table} o ON ocl.order_id = o.id
                 WHERE o.type = 'shop_order'
                   AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
                   AND o.date_created_gmt BETWEEN %s AND %s
                 GROUP BY DATE(o.date_created_gmt)
                 ORDER BY sales_date",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            );
        } else {
            // Monthly aggregation for periods > 60 days
            $coupon_sales_sql = $wpdb->prepare(
                "SELECT 
                    DATE_FORMAT(o.date_created_gmt, '%%Y-%%m') as sales_month,
                    COUNT(DISTINCT ocl.order_id) as coupon_orders,
                    SUM(o.total_amount) as coupon_sales
                 FROM {$coupon_lookup_table} ocl
                 INNER JOIN {$orders_table} o ON ocl.order_id = o.id
                 WHERE o.type = 'shop_order'
                   AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
                   AND o.date_created_gmt BETWEEN %s AND %s
                 GROUP BY DATE_FORMAT(o.date_created_gmt, '%%Y-%%m')
                 ORDER BY sales_month",
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            );
        }
        
        $coupon_sales_results = $wpdb->get_results( $coupon_sales_sql );
        
        // Convert to lookup array for easy merging
        $coupon_sales_data = array();
        foreach ( $coupon_sales_results as $coupon_sales ) {
            $date_key = $period_days <= 60 ? $coupon_sales->sales_date : $coupon_sales->sales_month;
            $coupon_sales_data[$date_key] = array(
                'coupon_orders' => intval( $coupon_sales->coupon_orders ),
                'coupon_sales' => floatval( $coupon_sales->coupon_sales )
            );
        }
        
        // Build final trend data by merging sales report totals with coupon data
        $trend_data = array();
        
        foreach ( $sales_totals as $date_key => $sales_data ) {
            $trend_entry = array(
                'period' => $date_key,
                'period_label' => $period_days <= 60 ? 
                    date( 'M j', strtotime( $date_key ) ) : 
                    date( 'M Y', strtotime( $date_key . '-01' ) ),
                'total_sales' => floatval( $sales_data->sales ?? 0 ),
                'total_orders' => intval( $sales_data->orders ?? 0 ),
                'coupon_sales' => 0,
                'coupon_orders' => 0
            );
            
            // Add coupon data if exists for this period
            if ( isset( $coupon_sales_data[$date_key] ) ) {
                $trend_entry['coupon_sales'] = $coupon_sales_data[$date_key]['coupon_sales'];
                $trend_entry['coupon_orders'] = $coupon_sales_data[$date_key]['coupon_orders'];
            }
            
            $trend_data[] = $trend_entry;
        }
        
        return $trend_data;
    }
    
    /**
     * Get export data - placeholder for export page
     */
    private function get_export_data( $start_date = null, $end_date = null ) {
        // Return basic stats for the export page
        return array(
            'total_customers' => $this->get_total_customers_count(),
            'customers_with_orders' => $this->get_customers_with_orders_count(),
            'date_range' => array(
                'start' => $start_date,
                'end' => $end_date
            )
        );
    }
    
    /**
     * Get total customers count
     */
    private function get_total_customers_count() {
        global $wpdb;
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->users} 
             WHERE EXISTS (
                SELECT 1 FROM {$wpdb->usermeta} 
                WHERE user_id = {$wpdb->users}.ID 
                AND meta_key = '{$wpdb->prefix}capabilities' 
                AND meta_value LIKE '%customer%'
             )"
        );
        
        return intval( $count );
    }
    
    /**
     * Get customers with orders count
     */
    private function get_customers_with_orders_count() {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'wc_orders';
        
        $count = $wpdb->get_var(
            "SELECT COUNT(DISTINCT customer_id) FROM {$orders_table} 
             WHERE customer_id > 0 
             AND type = 'shop_order' 
             AND status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')"
        );
        
        return intval( $count );
    }
    
    /**
     * Export customers data as CSV
     */
    public function export_customers_csv() {
        global $wpdb;
        
        // Get all customers with their order data
        $customers_data = $this->get_customers_export_data();
        
        // Create CSV content
        $csv_output = '';
        
        // Add BOM for proper UTF-8 encoding in Excel
        $csv_output .= "\xEF\xBB\xBF";
        
        // CSV Headers
        $headers = array(
            '客戶電郵',
            '客戶姓名',
            '客戶電話',
            '是否曾下訂單',
            '過往訂單號碼',
            '總消費金額'
        );
        
        $csv_output .= implode(',', array_map( array( $this, 'csv_escape' ), $headers ) ) . "\n";
        
        // Add customer data
        foreach ( $customers_data as $customer ) {
            $row = array(
                $customer['email'],
                $customer['name'],
                $customer['phone'],
                $customer['has_orders'] ? '是' : '否',
                $customer['order_numbers'],
                'HK$' . number_format( $customer['total_spent'], 2 )
            );
            
            $csv_output .= implode(',', array_map( array( $this, 'csv_escape' ), $row ) ) . "\n";
        }
        
        return $csv_output;
    }
    
    /**
     * Get customers export data with all required fields
     */
    private function get_customers_export_data() {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'wc_orders';
        $order_addresses_table = $wpdb->prefix . 'wc_order_addresses';
        
        // Get all customers (users with customer role)
        $customers = $wpdb->get_results(
            "SELECT u.ID, u.user_email, u.display_name
             FROM {$wpdb->users} u
             INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
             WHERE um.meta_key = '{$wpdb->prefix}capabilities'
             AND um.meta_value LIKE '%customer%'
             ORDER BY u.user_email ASC"
        );
        
        $export_data = array();
        
        foreach ( $customers as $customer ) {
            // Get customer meta data
            $first_name = get_user_meta( $customer->ID, 'first_name', true );
            $last_name = get_user_meta( $customer->ID, 'last_name', true );
            $shipping_phone = get_user_meta( $customer->ID, 'shipping_phone', true );
            
            // Build full name
            $full_name = trim( $last_name . ' ' . $first_name );
            if ( empty( $full_name ) ) {
                $full_name = $customer->display_name;
            }
            
            // Get customer's order information using HPOS
            $orders_query = $wpdb->prepare(
                "SELECT o.id, o.total_amount 
                 FROM {$orders_table} o
                 WHERE o.customer_id = %d 
                 AND o.type = 'shop_order'
                 AND o.status IN ('wc-completed', 'wc-processing', 'wc-partially-paid')
                 ORDER BY o.date_created_gmt DESC",
                $customer->ID
            );
            
            $customer_orders = $wpdb->get_results( $orders_query );
            
            $has_orders = !empty( $customer_orders );
            $total_spent = 0;
            $order_numbers = array();
            
            if ( $has_orders ) {
                foreach ( $customer_orders as $order_data ) {
                    $total_spent += floatval( $order_data->total_amount );
                    
                    // Get custom order number using WooCommerce order object
                    $order = wc_get_order( $order_data->id );
                    if ( $order ) {
                        $custom_order_number = $order->get_meta( '_seq_order_number', true );
                        if ( !empty( $custom_order_number ) ) {
                            $order_numbers[] = $custom_order_number;
                        } else {
                            // Fallback to order ID if no custom number
                            $order_numbers[] = '#' . $order_data->id;
                        }
                    }
                }
            }
            
            $export_data[] = array(
                'email' => $customer->user_email,
                'name' => $full_name,
                'phone' => $shipping_phone,
                'has_orders' => $has_orders,
                'order_numbers' => implode( ', ', $order_numbers ),
                'total_spent' => $total_spent
            );
        }
        
        return $export_data;
    }
    
    /**
     * Escape CSV field
     */
    private function csv_escape( $field ) {
        // Handle null or empty values
        if ( $field === null || $field === '' ) {
            return '""';
        }
        
        // Convert to string
        $field = (string) $field;
        
        // If field contains comma, quote, or newline, wrap in quotes and escape quotes
        if ( strpos( $field, ',' ) !== false || 
             strpos( $field, '"' ) !== false || 
             strpos( $field, "\n" ) !== false || 
             strpos( $field, "\r" ) !== false ) {
            
            $field = '"' . str_replace( '"', '""', $field ) . '"';
        }
        
        return $field;
    }
}
