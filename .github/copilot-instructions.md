# Copilot Instructions - Catering Sales Report Plugin

## Project Overview
WordPress plugin providing sales reporting/analytics for a WooCommerce-based catering business (盧太太 / "Ms. Lo Soups"). Displays a full-screen admin dashboard with multiple report views (overview, trends, products, regions, channels, membership, payments, promotions, exports).

## Architecture

### Core Files
- `catering-sales-report.php` - Main plugin file, defines `CateringSalesReport` class, registers admin menus & settings
- `include/init.php` - `CSR_Init` class handles AJAX endpoints, hooks, and database table creation
- `include/interface.php` - `CSR_WooCommerce_Interface` class for all data fetching (2000+ lines)

### Data Layer Pattern
**HPOS-First Approach**: The plugin queries WooCommerce High-Performance Order Storage tables directly via SQL rather than REST API for performance:
```php
// Preferred: Direct HPOS query
$this->get_orders_via_hpos($start_date, $end_date);
$this->get_sales_report_via_hpos($start_date, $end_date);

// Fallback: REST API (only for coupons, system status)
$this->make_request_with_params('coupons', $params);
```

Key HPOS tables used:
- `wp_wc_orders` - Order data
- `wp_wc_order_addresses` - Billing/shipping addresses  
- `wp_wc_order_operational_data` - Order metadata (created_via, etc.)
- `wp_wc_order_coupon_lookup` - Coupon usage tracking

### Frontend Architecture
- Dashboard renders in full-screen overlay (`template/dashboard.php`)
- Left panel navigation loads report content via AJAX (`template/left-panel.php`)
- Each report type has its own template in `template/` folder
- Chart.js 3.9.1 for visualizations, jQuery UI datepicker for date ranges
- Orange color theme (#D2691E primary, #FF8C00 secondary)

## Key Conventions

### AJAX Pattern
All AJAX handlers follow this security pattern:
```php
public static function ajax_handler() {
    if (!wp_verify_nonce($_POST['nonce'], 'csr_ajax_nonce')) {
        wp_die('Security check failed');
    }
    if (!current_user_can('view_catering_report')) {
        wp_die('Insufficient permissions');
    }
    // ... handler logic
    wp_send_json_success($data);
}
```

### Custom Capability
Uses `view_catering_report` capability for access control (added to roles in `CSR_Init::add_view_catering_report_capability()`).

### Constants
- `CSR_PLUGIN_URL` - Plugin URL for assets
- `CSR_PLUGIN_PATH` - Plugin filesystem path
- `CSR_VERSION` - Version string

### Report Types
Valid report types: `overview`, `trend`, `product-sales`, `region`, `channel`, `membership`, `payment`, `promotion`, `export`

### Date Handling
Date presets handled in `CSR_Init::calculate_date_range()`: today, yesterday, this_week, last_week, this_month, last_month, this_year, last_year, custom

## Custom Database Tables
Created on plugin activation:
- `wp_csr_product_views` - Product page view tracking
- `wp_csr_user_logins` - Monthly active user tracking

## Development Notes

### Adding New Reports
1. Add entry to `CSR_Init::get_report_pages()` array
2. Create template in `template/{report-type}.php`
3. Add data method in `CSR_WooCommerce_Interface::get_report_data()` switch
4. Implement `get_{type}_data()` method using HPOS queries

### Text Domain
All strings use `'catering-sales-report'` text domain. UI includes Traditional Chinese (繁體中文) labels.

### Dependencies
- WooCommerce (required)
- Automattic WooCommerce PHP SDK via Composer (`vendor/automattic/woocommerce`)
