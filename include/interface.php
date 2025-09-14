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
        $response = $this->make_request( 'system_status' );
        
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
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get sales report
        $sales_report = $this->make_request( 'reports/sales',  $start_date, $end_date  );
        if ( is_wp_error( $sales_report ) ) {
            throw new Exception( $sales_report->get_error_message() );
        }
        
        // // Get orders
        // $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        // if ( is_wp_error( $orders ) ) {
        //     throw new Exception( $orders->get_error_message() );
        // }
        
        // // Get top selling products
        // $top_products = $this->make_request( 'reports/top_sellers', $params );
        // if ( is_wp_error( $top_products ) ) {
        //     throw new Exception( $top_products->get_error_message() );
        // }
        
        return array(
            'sales_report' => $sales_report,
            'recent_orders' => array_slice( $orders, 0, 10 ), // Last 10 orders
            'top_products' => $top_products,
            'summary' => $this->calculate_overview_summary( $sales_report, $orders )
        );
    }
    
    /**
     * Get trend analysis data
     */
    private function get_trend_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get sales data for the period
        $sales_data = $this->make_request( 'reports/sales', $params );
        if ( is_wp_error( $sales_data ) ) {
            throw new Exception( $sales_data->get_error_message() );
        }
        
        // Get orders for trend analysis
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        return array(
            'sales_trend' => $this->process_sales_trend( $orders ),
            'revenue_trend' => $this->process_revenue_trend( $orders ),
            'order_trend' => $this->process_order_trend( $orders ),
            'period_comparison' => $this->get_period_comparison( $start_date, $end_date )
        );
    }
    
    /**
     * Get product sales data
     */
    private function get_product_sales_data( $start_date = null, $end_date = null ) {
        
        // Get top selling products
        $top_products = $this->make_request( 'reports/top_sellers', $start_date, $end_date );
        if ( is_wp_error( $top_products ) ) {
            throw new Exception( $top_products->get_error_message() );
        }
        
        // Get all products for additional analysis
        $products = $this->make_request( 'products', array( 'per_page' => 100 ) );
        if ( is_wp_error( $products ) ) {
            throw new Exception( $products->get_error_message() );
        }
        
        // Get orders to analyze product performance
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        return array(
            'top_sellers' => $top_products,
            'product_performance' => $this->analyze_product_performance( $orders, $products ),
            'category_performance' => $this->analyze_category_performance( $orders, $products ),
            'inventory_status' => $this->get_inventory_status( $products )
        );
    }
    
    /**
     * Get regional sales data
     */
    private function get_regional_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get orders to analyze by region
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        return array(
            'sales_by_country' => $this->group_sales_by_country( $orders ),
            'sales_by_state' => $this->group_sales_by_state( $orders ),
            'sales_by_city' => $this->group_sales_by_city( $orders ),
            'shipping_analysis' => $this->analyze_shipping_by_region( $orders )
        );
    }
    
    /**
     * Get channel analysis data
     */
    private function get_channel_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get orders to analyze channels
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        return array(
            'sales_by_source' => $this->analyze_sales_by_source( $orders ),
            'device_analysis' => $this->analyze_device_usage( $orders ),
            'referrer_analysis' => $this->analyze_referrers( $orders ),
            'conversion_rates' => $this->calculate_conversion_rates( $orders )
        );
    }
    
    /**
     * Get membership analysis data
     */
    private function get_membership_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get orders and customers
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        $customers = $this->make_request( 'customers', array( 'per_page' => 100 ) );
        if ( is_wp_error( $customers ) ) {
            throw new Exception( $customers->get_error_message() );
        }
        
        return array(
            'member_vs_guest' => $this->analyze_member_vs_guest_sales( $orders ),
            'customer_lifetime_value' => $this->calculate_customer_ltv( $customers, $orders ),
            'repeat_customer_rate' => $this->calculate_repeat_customer_rate( $orders ),
            'new_vs_returning' => $this->analyze_new_vs_returning_customers( $orders, $customers )
        );
    }
    
    /**
     * Get payment method analysis data
     */
    private function get_payment_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get orders to analyze payment methods
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        // Get payment gateways
        $payment_gateways = $this->make_request( 'payment_gateways' );
        if ( is_wp_error( $payment_gateways ) ) {
            throw new Exception( $payment_gateways->get_error_message() );
        }
        
        return array(
            'payment_method_usage' => $this->analyze_payment_methods( $orders ),
            'payment_success_rates' => $this->calculate_payment_success_rates( $orders ),
            'average_transaction_by_method' => $this->calculate_avg_transaction_by_payment_method( $orders ),
            'payment_gateways' => $payment_gateways
        );
    }
    
    /**
     * Get promotion analysis data
     */
    private function get_promotion_data( $start_date = null, $end_date = null ) {
        $params = $this->build_date_params( $start_date, $end_date );
        
        // Get coupons
        $coupons = $this->make_request( 'coupons', array( 'per_page' => 100 ) );
        if ( is_wp_error( $coupons ) ) {
            throw new Exception( $coupons->get_error_message() );
        }
        
        // Get orders to analyze promotion usage
        $orders = $this->make_request( 'orders', array_merge( $params, array( 'per_page' => 100 ) ) );
        if ( is_wp_error( $orders ) ) {
            throw new Exception( $orders->get_error_message() );
        }
        
        return array(
            'coupon_usage' => $this->analyze_coupon_usage( $orders, $coupons ),
            'discount_effectiveness' => $this->analyze_discount_effectiveness( $orders ),
            'promotion_roi' => $this->calculate_promotion_roi( $orders, $coupons ),
            'seasonal_promotions' => $this->analyze_seasonal_promotions( $orders, $coupons )
        );
    }
    
    /**
     * Make API request to WooCommerce
     */
    private function make_request( $endpoint, $start_date = null, $end_date = null ) {

        $query = [
            'date_min' => $start_date, 
            'date_max' => $end_date
        ];
        $woocommerce = new Client(
            $this->store_url,
            $this->consumer_key,
            $this->consumer_secret,
            [
                'version' => $this->api_version,
            ]
        );

        $response = $woocommerce->get('reports/sales', $query);

        set_transient('debug', $query , 30);

        // $response = wp_remote_get( $url, array(
        //     'headers' => array(
        //         'Authorization' => 'Basic ' . base64_encode( $this->consumer_key . ':' . $this->consumer_secret )
        //     ),
        //     'timeout' => 30
        // ));
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        // $response_code = wp_remote_retrieve_response_code( $response );
        // $body = wp_remote_retrieve_body( $response );
        
        // if ( $response_code !== 200 ) {
        //     return new WP_Error( 'api_error', sprintf( 
        //         __( 'API request failed with status %d: %s', 'catering-sales-report' ), 
        //         $response_code, 
        //         $body 
        //     ));
        // }
        
        return json_decode( $body, true );
    }
    
    /**
     * Build date parameters for API requests
     */
    private function build_date_params( $start_date = null, $end_date = null ) {
        $params = array();
        
        if ( $start_date ) {
            $params['after'] = date( 'Y-m-d\TH:i:s', strtotime( $start_date ) );
        }
        
        if ( $end_date ) {
            $params['before'] = date( 'Y-m-d\TH:i:s', strtotime( $end_date . ' +1 day' ) );
        }
        
        return $params;
    }
    
    /**
     * Calculate overview summary from sales data
     */
    private function calculate_overview_summary( $sales_report, $orders ) {
        $total_revenue = 0;
        $total_orders = count( $orders );
        $total_items = 0;
        
        foreach ( $orders as $order ) {
            $total_revenue += floatval( $order['total'] );
            $total_items += count( $order['line_items'] );
        }
        
        $average_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
        
        return array(
            'total_revenue' => $total_revenue,
            'total_orders' => $total_orders,
            'total_items' => $total_items,
            'average_order_value' => $average_order_value
        );
    }
    
    /**
     * Process sales trend data
     */
    private function process_sales_trend( $orders ) {
        $daily_sales = array();
        
        foreach ( $orders as $order ) {
            $date = date( 'Y-m-d', strtotime( $order['date_created'] ) );
            
            if ( !isset( $daily_sales[$date] ) ) {
                $daily_sales[$date] = array(
                    'date' => $date,
                    'sales' => 0,
                    'orders' => 0
                );
            }
            
            $daily_sales[$date]['sales'] += floatval( $order['total'] );
            $daily_sales[$date]['orders']++;
        }
        
        return array_values( $daily_sales );
    }
    
    /**
     * Process revenue trend data
     */
    private function process_revenue_trend( $orders ) {
        // Similar to sales trend but focused on revenue
        return $this->process_sales_trend( $orders );
    }
    
    /**
     * Process order trend data
     */
    private function process_order_trend( $orders ) {
        $daily_orders = array();
        
        foreach ( $orders as $order ) {
            $date = date( 'Y-m-d', strtotime( $order['date_created'] ) );
            
            if ( !isset( $daily_orders[$date] ) ) {
                $daily_orders[$date] = 0;
            }
            
            $daily_orders[$date]++;
        }
        
        return $daily_orders;
    }
    
    /**
     * Get period comparison data
     */
    private function get_period_comparison( $start_date, $end_date ) {
        // Calculate previous period
        $period_length = strtotime( $end_date ) - strtotime( $start_date );
        $prev_start = date( 'Y-m-d', strtotime( $start_date ) - $period_length );
        $prev_end = date( 'Y-m-d', strtotime( $start_date ) - 1 );
        
        // Get previous period data
        $prev_params = $this->build_date_params( $prev_start, $prev_end );
        $prev_orders = $this->make_request( 'orders', array_merge( $prev_params, array( 'per_page' => 100 ) ) );
        
        if ( is_wp_error( $prev_orders ) ) {
            return array(
                'error' => $prev_orders->get_error_message()
            );
        }
        
        return array(
            'current_period' => array(
                'start' => $start_date,
                'end' => $end_date
            ),
            'previous_period' => array(
                'start' => $prev_start,
                'end' => $prev_end,
                'data' => $this->calculate_overview_summary( null, $prev_orders )
            )
        );
    }
    
    // Additional helper methods for data analysis...
    
    private function analyze_product_performance( $orders, $products ) {
        // Implement product performance analysis
        return array();
    }
    
    private function analyze_category_performance( $orders, $products ) {
        // Implement category performance analysis
        return array();
    }
    
    private function get_inventory_status( $products ) {
        // Implement inventory status analysis
        return array();
    }
    
    private function group_sales_by_country( $orders ) {
        $country_sales = array();
        
        foreach ( $orders as $order ) {
            $country = $order['billing']['country'] ?? 'Unknown';
            
            if ( !isset( $country_sales[$country] ) ) {
                $country_sales[$country] = array(
                    'country' => $country,
                    'sales' => 0,
                    'orders' => 0
                );
            }
            
            $country_sales[$country]['sales'] += floatval( $order['total'] );
            $country_sales[$country]['orders']++;
        }
        
        return array_values( $country_sales );
    }
    
    private function group_sales_by_state( $orders ) {
        // Similar implementation for states
        return array();
    }
    
    private function group_sales_by_city( $orders ) {
        // Similar implementation for cities
        return array();
    }
    
    private function analyze_shipping_by_region( $orders ) {
        // Implement shipping analysis by region
        return array();
    }
    
    private function analyze_sales_by_source( $orders ) {
        // Implement sales source analysis
        return array();
    }
    
    private function analyze_device_usage( $orders ) {
        // Implement device usage analysis
        return array();
    }
    
    private function analyze_referrers( $orders ) {
        // Implement referrer analysis
        return array();
    }
    
    private function calculate_conversion_rates( $orders ) {
        // Implement conversion rate calculation
        return array();
    }
    
    private function analyze_member_vs_guest_sales( $orders ) {
        $member_sales = 0;
        $guest_sales = 0;
        $member_orders = 0;
        $guest_orders = 0;
        
        foreach ( $orders as $order ) {
            if ( $order['customer_id'] > 0 ) {
                $member_sales += floatval( $order['total'] );
                $member_orders++;
            } else {
                $guest_sales += floatval( $order['total'] );
                $guest_orders++;
            }
        }
        
        return array(
            'member_sales' => $member_sales,
            'guest_sales' => $guest_sales,
            'member_orders' => $member_orders,
            'guest_orders' => $guest_orders
        );
    }
    
    private function calculate_customer_ltv( $customers, $orders ) {
        // Implement customer lifetime value calculation
        return array();
    }
    
    private function calculate_repeat_customer_rate( $orders ) {
        // Implement repeat customer rate calculation
        return array();
    }
    
    private function analyze_new_vs_returning_customers( $orders, $customers ) {
        // Implement new vs returning customer analysis
        return array();
    }
    
    private function analyze_payment_methods( $orders ) {
        $payment_methods = array();
        
        foreach ( $orders as $order ) {
            $method = $order['payment_method_title'] ?? 'Unknown';
            
            if ( !isset( $payment_methods[$method] ) ) {
                $payment_methods[$method] = array(
                    'method' => $method,
                    'count' => 0,
                    'total' => 0
                );
            }
            
            $payment_methods[$method]['count']++;
            $payment_methods[$method]['total'] += floatval( $order['total'] );
        }
        
        return array_values( $payment_methods );
    }
    
    private function calculate_payment_success_rates( $orders ) {
        // Implement payment success rate calculation
        return array();
    }
    
    private function calculate_avg_transaction_by_payment_method( $orders ) {
        // Implement average transaction calculation by payment method
        return array();
    }
    
    private function analyze_coupon_usage( $orders, $coupons ) {
        $coupon_usage = array();
        
        foreach ( $orders as $order ) {
            if ( !empty( $order['coupon_lines'] ) ) {
                foreach ( $order['coupon_lines'] as $coupon_line ) {
                    $coupon_code = $coupon_line['code'];
                    
                    if ( !isset( $coupon_usage[$coupon_code] ) ) {
                        $coupon_usage[$coupon_code] = array(
                            'code' => $coupon_code,
                            'usage_count' => 0,
                            'total_discount' => 0
                        );
                    }
                    
                    $coupon_usage[$coupon_code]['usage_count']++;
                    $coupon_usage[$coupon_code]['total_discount'] += floatval( $coupon_line['discount'] );
                }
            }
        }
        
        return array_values( $coupon_usage );
    }
    
    private function analyze_discount_effectiveness( $orders ) {
        // Implement discount effectiveness analysis
        return array();
    }
    
    private function calculate_promotion_roi( $orders, $coupons ) {
        // Implement promotion ROI calculation
        return array();
    }
    
    private function analyze_seasonal_promotions( $orders, $coupons ) {
        // Implement seasonal promotion analysis
        return array();
    }
}
