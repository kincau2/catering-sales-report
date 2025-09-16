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
                return $this->get_regional_data( $start_date, $end_date );
            
            case 'channel':
                return $this->get_channel_data( $start_date, $end_date );
            
            case 'membership':
                return $this->get_membership_data( $start_date, $end_date );
            
            case 'payment':
                return $this->get_payment_data( $start_date, $end_date );
            
            case 'promotion':
                return $this->get_promotion_data( $start_date, $end_date );
            
            default:
                throw new Exception( __( 'Invalid report type', 'catering-sales-report' ) );
        }
    }
    
    /**
     * Get overview data
     */
    private function get_overview_data( $start_date = null, $end_date = null ) {
        // Get sales report
        $sales_report = $this->make_request( 'reports/sales', $start_date, $end_date );
        if ( is_wp_error( $sales_report ) ) {
            throw new Exception( $sales_report->get_error_message() );
        }
        
        // Get orders with date filtering
        if ( $start_date ) {
            $order_params['after'] = date( 'Y-m-d\TH:i:s', strtotime( $start_date ) );
        }
        if ( $end_date ) {
            $order_params['before'] = date( 'Y-m-d\TH:i:s', strtotime( $end_date . ' +1 day' ) );
        }
        
        $orders = $this->make_request_with_params( 'orders', $order_params );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        set_transient( 'debug', $orders, 30 );
        // Get top selling products
        $top_products = $this->make_request( 'reports/top_sellers', $start_date, $end_date );
        if ( is_wp_error( $top_products ) ) {
            throw new Exception( $top_products->get_error_message() );
        }

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
        // Get sales data for the extended period
        $sales_data_current = $this->make_request( 'reports/sales', $start_date, $end_date );
        if ( is_wp_error( $sales_data_current ) ) {
            throw new Exception( $sales_data_current->get_error_message() );
        }
        $sales_data_comparison = $this->make_request( 'reports/sales', $comparison_start, $comparison_end );

        if ( is_wp_error( $sales_data_comparison ) ) {
            throw new Exception( $sales_data_current->get_error_message() );
        }
        
        return array(
            'period_comparison' => $this->get_detailed_period_comparison( $sales_data_current, $sales_data_comparison ),
            'yearly_comparison' => $this->get_yearly_comparison()
        );
    }

    /**
     * Get payment method analysis data
     */
    private function get_payment_data( $start_date = null, $end_date = null ) {
        // Get orders for payment method analysis
        if ( $start_date ) {
            $order_params['after'] = date( 'Y-m-d\TH:i:s', strtotime( $start_date ) );
        }
        if ( $end_date ) {
            $order_params['before'] = date( 'Y-m-d\TH:i:s', strtotime( $end_date . ' +1 day' ) );
        }
        
        $orders = $this->make_request_with_params( 'orders', $order_params );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        // Get monthly trends for last 12 months
        $monthly_trends = $this->get_payment_monthly_trends();
        
        return array(
            'orders' => $orders,
            'monthly_trends' => $monthly_trends,
            'payment_method_usage' => $this->analyze_payment_methods( $orders ),
            'payment_summary' => $this->calculate_payment_summary( $orders )
        );
    }
    
    /**
     * Get product sales data
     */
    private function get_product_sales_data( $start_date = null, $end_date = null ) {
        // Get orders for product analysis
        $order_params = array( 'per_page' => 100 );
        if ( $start_date ) {
            $order_params['after'] = date( 'Y-m-d\TH:i:s', strtotime( $start_date ) );
        }
        if ( $end_date ) {
            $order_params['before'] = date( 'Y-m-d\TH:i:s', strtotime( $end_date . ' +1 day' ) );
        }
        
        $orders = $this->make_request_with_params( 'orders', $order_params );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        // Process all product data in a single loop for efficiency
        $product_data = $this->process_all_product_data( $orders, $start_date, $end_date );
        
        return array(
            'top_products_by_sales' => $product_data['top_products_by_sales'],
            'top_products_by_quantity' => $product_data['top_products_by_quantity'],
            'sales_trends' => $product_data['sales_trends'],
            'quantity_trends' => $product_data['quantity_trends'],
            'product_summary' => $product_data['product_summary']
        );
    }
    
    /**
     * Get regional sales data
     */
    private function get_regional_data( $start_date = null, $end_date = null ) {

    }
    
    /**
     * Get channel analysis data
     */
    private function get_channel_data( $start_date = null, $end_date = null ) {
        // Get orders for channel analysis
        $order_params = array( 'per_page' => 100 );
        if ( $start_date ) {
            $order_params['after'] = date( 'Y-m-d\TH:i:s', strtotime( $start_date ) );
        }
        if ( $end_date ) {
            $order_params['before'] = date( 'Y-m-d\TH:i:s', strtotime( $end_date . ' +1 day' ) );
        }
        
        $orders = $this->make_request_with_params( 'orders', $order_params );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
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
     * Get membership analysis data
     */
    private function get_membership_data( $start_date = null, $end_date = null ) {

    }

    
    /**
     * Get promotion analysis data
     */
    private function get_promotion_data( $start_date = null, $end_date = null ) {

    }
    
    /**
     * Make API request to WooCommerce
     */
    private function make_request( $endpoint, $start_date = null, $end_date = null ) {
        try {
            // Build query parameters for date filtering
            $query = array();
            if ( $start_date ) {
                $query['date_min'] = $start_date;
            }
            if ( $end_date ) {
                $query['date_max'] = $end_date;
            }

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
            $response = $woocommerce->get( $endpoint, $query );

            // The WooCommerce client returns the data directly, not a WP response object
            return $response;
            
        } catch ( Exception $e ) {
            // Convert exceptions to WP_Error for consistency
            return new WP_Error( 'api_error', $e->getMessage() );
        }
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
     * Build date parameters for API requests
     */
    private function build_date_params( $start_date = null, $end_date = null ) {
        $params = array();
        
        if ( $start_date ) {
            $params['date_min'] = date( 'Y-m-d', strtotime( $start_date ) );
        }
        
        if ( $end_date ) {
            $params['date_max'] = date( 'Y-m-d', strtotime( $end_date ) );
        }
        
        return $params;
    }
    
    private function analyze_payment_methods( $orders ) {
        $payment_methods = array();
        
        foreach ( $orders as $order ) {
            $method = $order->payment_method_title ?? 'Unknown';
            
            if ( !isset( $payment_methods[$method] ) ) {
                $payment_methods[$method] = array(
                    'method' => $method,
                    'count' => 0,
                    'total' => 0
                );
            }
            
            $payment_methods[$method]['count']++;
            $payment_methods[$method]['total'] += floatval( $order->total );
        }
        
        return array_values( $payment_methods );
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
        
        // Get current month sales data using reports/sales endpoint
        $current_month_sales_data = $this->make_request( 'reports/sales', $current_month_start, $current_month_end );

        // Get last month sales data using reports/sales endpoint
        $last_month_sales_data = $this->make_request( 'reports/sales', $last_month_start, $last_month_end );
        
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
            
            // Use reports/sales endpoint instead of fetching orders
            $sales_data = $this->make_request( 'reports/sales', $start_date, $end_date );
            
            $total_sales = 0;
            if ( !is_wp_error( $sales_data ) && isset($sales_data[0]) ) {
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
     * Process daily sales from orders
     */
    private function process_daily_sales( $orders, $start_date, $end_date ) {
        $daily_sales = array();
        
        // Initialize all days with zero
        $current_date = clone $start_date;
        while ( $current_date <= $end_date ) {
            $daily_sales[$current_date->format('Y-m-d')] = 0;
            $current_date->modify( '+1 day' );
        }
        
        // Aggregate sales by day
        foreach ( $orders as $order ) {
            $order_date = new DateTime( $order->date_created );
            $date_key = $order_date->format('Y-m-d');
            
            if ( isset( $daily_sales[$date_key] ) ) {
                $daily_sales[$date_key] += floatval( $order->total );
            }
        }
        
        return $daily_sales;
    }
    
    /**
     * Get monthly sales data for a specific year
     */
    private function get_monthly_sales_for_year( $year ) {
        $monthly_sales = array_fill( 0, 12, 0 ); // Initialize 12 months with zero
        
        // Get sales data for the entire year
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        
        $sales_data = $this->make_request( 'reports/sales', $start_date, $end_date );

        if ( is_wp_error( $sales_data ) ) {
            return $monthly_sales;
        }
        
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
            
            // Get orders for this month
            $order_params = array(
                'per_page' => 100,
                'after' => $month_start->format( 'Y-m-d\TH:i:s' ),
                'before' => $month_end->format( 'Y-m-d\TH:i:s' )
            );
            
            $orders = $this->make_request_with_params( 'orders', $order_params );
            set_transient( 'debug', $orders, 30 );
            $payment_methods = array();
            if ( !is_wp_error( $orders ) ) {
                foreach ( $orders as $order ) {
                    $method = ( empty( $order->payment_method_title ) ) ? '其他' : $order->payment_method_title;
                    
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
     * Calculate payment summary statistics
     */
    private function calculate_payment_summary( $orders ) {
        $total_orders = count( $orders );
        $total_amount = 0;
        $payment_methods = array();
        
        foreach ( $orders as $order ) {
            $total_amount += floatval( $order->total );
            $method = ( empty( $order->payment_method_title ) ) ? '其他' : $order->payment_method_title;

            if ( !isset( $payment_methods[$method] ) ) {
                $payment_methods[$method] = array(
                    'count' => 0,
                    'total' => 0
                );
            }
            
            $payment_methods[$method]['count']++;
            $payment_methods[$method]['total'] += floatval( $order->total );
        }
        
        // Calculate percentages
        foreach ( $payment_methods as $method => $data ) {
            $payment_methods[$method]['percentage'] = $total_orders > 0 ? 
                round( ( $data['count'] / $total_orders ) * 100, 2 ) : 0;
            $payment_methods[$method]['amount_percentage'] = $total_amount > 0 ? 
                round( ( $data['total'] / $total_amount ) * 100, 2 ) : 0;
        }

        return array(
            'total_orders' => $total_orders,
            'total_amount' => $total_amount,
            'payment_methods' => $payment_methods,
            'average_order_value' => $total_orders > 0 ? $total_amount / $total_orders : 0
        );
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
            
            // Get orders for this month
            $order_params = array(
                'per_page' => 100,
                'after' => $month_start->format( 'Y-m-d\TH:i:s' ),
                'before' => $month_end->format( 'Y-m-d\TH:i:s' )
            );
            
            $orders = $this->make_request_with_params( 'orders', $order_params );
            
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
        $monthly_sales_trends = array();
        $monthly_quantity_trends = array();
        $product_summary_stats = array();
        
        $total_products = 0;
        $total_revenue = 0;
        
        // Initialize monthly arrays if date range is provided
        $monthly_data = array();
        if ( $start_date && $end_date ) {
            $current_date = new DateTime( $start_date );
            $end_date_obj = new DateTime( $end_date );
            
            while ( $current_date <= $end_date_obj ) {
                $month_key = $current_date->format( 'Y-m' );
                $monthly_data[$month_key] = array(
                    'month' => $month_key,
                    'month_label' => $current_date->format( 'Y年n月' ),
                    'sales' => array(),
                    'quantity' => array()
                );
                $current_date->modify( 'first day of next month' );
            }
        }
        
        // Single loop through all orders
        foreach ( $orders as $order ) {
            $order_month = null;
            if ( $start_date && $end_date ) {
                $order_date = new DateTime( $order->date_created );
                $order_month = $order_date->format( 'Y-m' );
            }
            
            if ( isset( $order->line_items ) && is_array( $order->line_items ) ) {
                foreach ( $order->line_items as $item ) {
                    // Get proper product name using product_id
                    $product_name = $this->get_product_name_by_id( $item->product_id ?? 0 );
                    if ( empty( $product_name ) ) {
                        $product_name = $item->name ?? '未知產品';
                    }
                    
                    $quantity = intval( $item->quantity );
                    $item_total = floatval( $item->total );
                    
                    // Update totals for summary
                    $total_products += $quantity;
                    $total_revenue += $item_total;
                    
                    // Process products by sales
                    if ( !isset( $products_by_sales[$product_name] ) ) {
                        $products_by_sales[$product_name] = array(
                            'name' => $product_name,
                            'total_sales' => 0,
                            'quantity' => 0,
                            'orders' => 0
                        );
                    }
                    $products_by_sales[$product_name]['total_sales'] += $item_total;
                    $products_by_sales[$product_name]['quantity'] += $quantity;
                    $products_by_sales[$product_name]['orders']++;
                    
                    // Process products by quantity (same data, different sorting)
                    if ( !isset( $products_by_quantity[$product_name] ) ) {
                        $products_by_quantity[$product_name] = array(
                            'name' => $product_name,
                            'quantity' => 0,
                            'total_sales' => 0,
                            'orders' => 0
                        );
                    }
                    $products_by_quantity[$product_name]['quantity'] += $quantity;
                    $products_by_quantity[$product_name]['total_sales'] += $item_total;
                    $products_by_quantity[$product_name]['orders']++;
                    
                    // Process monthly trends if date range provided
                    if ( $order_month && isset( $monthly_data[$order_month] ) ) {
                        // Sales trends
                        if ( !isset( $monthly_data[$order_month]['sales'][$product_name] ) ) {
                            $monthly_data[$order_month]['sales'][$product_name] = 0;
                        }
                        $monthly_data[$order_month]['sales'][$product_name] += $item_total;
                        
                        // Quantity trends
                        if ( !isset( $monthly_data[$order_month]['quantity'][$product_name] ) ) {
                            $monthly_data[$order_month]['quantity'][$product_name] = 0;
                        }
                        $monthly_data[$order_month]['quantity'][$product_name] += $quantity;
                    }
                    
                    // Product summary statistics
                    if ( !isset( $product_summary_stats[$product_name] ) ) {
                        $product_summary_stats[$product_name] = array(
                            'quantity' => 0,
                            'revenue' => 0
                        );
                    }
                    $product_summary_stats[$product_name]['quantity'] += $quantity;
                    $product_summary_stats[$product_name]['revenue'] += $item_total;
                }
            }
        }
        
        // Sort and limit results
        uasort( $products_by_sales, function( $a, $b ) {
            return $b['total_sales'] <=> $a['total_sales'];
        });
        // Filter out products with $0 total_sales for sales charts (free gifts)
        $products_by_sales_filtered = array_filter( $products_by_sales, function( $product ) {
            return $product['total_sales'] > 0;
        });
        $top_products_by_sales = array_slice( array_values( $products_by_sales_filtered ), 0, 10 );
        
        uasort( $products_by_quantity, function( $a, $b ) {
            return $b['quantity'] <=> $a['quantity'];
        });
        // Keep all products for quantity charts (including free gifts)
        $top_products_by_quantity = array_slice( array_values( $products_by_quantity ), 0, 10 );
        
        // Convert monthly data to expected format
        foreach ( $monthly_data as $month_key => $data ) {
            // Filter out $0 sales products from monthly sales trends
            $filtered_sales = array_filter( $data['sales'], function( $amount ) {
                return $amount > 0;
            });
            
            $monthly_sales_trends[] = array(
                'month' => $data['month'],
                'month_label' => $data['month_label'],
                'products' => $filtered_sales
            );
            
            // Keep all products for quantity trends (including free gifts)
            $monthly_quantity_trends[] = array(
                'month' => $data['month'],
                'month_label' => $data['month_label'],
                'products' => $data['quantity']
            );
        }
        
        // Calculate summary statistics
        $unique_products = count( $product_summary_stats );
        $average_revenue_per_product = $unique_products > 0 ? $total_revenue / $unique_products : 0;
        $average_quantity_per_product = $unique_products > 0 ? $total_products / $unique_products : 0;
        
        $product_summary = array(
            'total_products_sold' => $total_products,
            'total_revenue' => $total_revenue,
            'unique_products' => $unique_products,
            'average_revenue_per_product' => $average_revenue_per_product,
            'average_quantity_per_product' => $average_quantity_per_product,
            'product_stats' => $product_summary_stats
        );
        
        return array(
            'top_products_by_sales' => $top_products_by_sales,
            'top_products_by_quantity' => $top_products_by_quantity,
            'sales_trends' => $monthly_sales_trends,
            'quantity_trends' => $monthly_quantity_trends,
            'product_summary' => $product_summary
        );
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
}
