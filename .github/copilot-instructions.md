# Catering Sales Report Plugin - AI Development Guide

## Architecture Overview

This is a WordPress plugin that provides comprehensive sales analytics for WooCommerce stores, specifically designed for catering businesses (廬太太報表). The plugin creates a full-screen dashboard interface with multiple report types.

### Core Components

- **Main Plugin**: `catering-sales-report.php` - Standard WordPress plugin with settings, menu creation, and API credential management
- **Initialization**: `include/init.php` - Central controller for AJAX handlers, asset loading, and database operations  
- **API Interface**: `include/interface.php` - WooCommerce REST API client using `automattic/woocommerce` package
- **Templates**: `template/` - Full-screen dashboard UI with left navigation panel and content areas
- **Assets**: `assets/css/admin.css`, `assets/js/admin.js` - Dashboard styling and Chart.js integration

## Key Architectural Patterns

### AJAX-Driven Single Page Application
The dashboard operates as an SPA within WordPress admin:
- Full-screen overlay (`#csr-dashboard-overlay`) that hides WordPress chrome
- Left navigation panel loads report content via AJAX calls to `csr_get_report_content`
- All data fetching uses `csr_get_report_data` AJAX endpoint with nonce security

### WooCommerce REST API Integration
Uses the official `automattic/woocommerce` PHP client (v3.1+):
```php
$woocommerce = new Client($store_url, $consumer_key, $consumer_secret, ['version' => 'wc/v3']);
$response = $woocommerce->get('orders', $params);
```

### Report Type Architecture
Each report type has:
1. Method in `CSR_WooCommerce_Interface::get_report_data($report_type, $start_date, $end_date)`
2. Template file in `template/{report_type}.php` 
3. Navigation entry in `CSR_Init::get_report_pages()`

Available reports: `overview`, `trend`, `product-sales`, `region`, `channel`, `membership`, `payment`, `promotion`

### Performance Optimization Patterns
The interface uses single-loop data processing to avoid memory issues:
- `process_all_product_data()` - Aggregates sales, quantity, and monthly trends in one pass
- `process_all_regional_data()` - Combines regional analysis, district mapping, and monthly trends
- `process_all_membership_data()` - User registration, geographic distribution, and purchase analytics

## Critical Development Workflows

### Adding New Report Types
1. Add entry to `CSR_Init::get_report_pages()` with icon and description
2. Create `template/{report-type}.php` with chart containers and JavaScript
3. Add case in `CSR_WooCommerce_Interface::get_report_data()` switch statement
4. Implement data processing method following single-loop optimization pattern

### Database Operations
Custom table for page view tracking: `wp_csr_product_views`
- Created in `CSR_Init::create_database_tables()` using `dbDelta()`
- Frontend tracking via `csr_track_page_view` AJAX (with cookie deduplication)
- Aggregated in reports using `CSR_Init::get_top_viewed_products()`

### Date Range Handling
JavaScript function `getCurrentDateRange()` in `left-panel.php` calculates periods:
- Supports presets: today, yesterday, this_week, last_week, this_month, last_month, this_year, last_year, custom
- Returns `{start: 'YYYY-MM-DD', end: 'YYYY-MM-DD'}` format
- Used by all AJAX report requests

## Project-Specific Conventions

### Chinese/English Hybrid Codebase
- User-facing strings: Traditional Chinese (繁體中文)
- Code comments and variable names: English
- Use `__()` and `_e()` functions with 'catering-sales-report' text domain

### Hong Kong Geographic Mapping
Built-in district mapping in `extract_district_from_order()`:
```php
$district_mapping = array(
    '中西區' => '香港島', '灣仔區' => '香港島', '東區' => '香港島', '南區' => '香港島',
    '油尖旺區' => '九龍', '深水埗區' => '九龍', // ... etc
);
```

### Color Consistency for Charts
Use `get_product_color_map()` for consistent Chart.js colors:
- Sequential assignment for ≤21 products  
- Hash-based distribution for larger datasets
- 21-color high-contrast palette

### WordPress Integration Patterns
- Settings stored as WordPress options: `csr_wc_consumer_key`, `csr_wc_consumer_secret`, `csr_wc_store_url`
- Menu integration: Uses `manage_catering` capability (higher than standard)
- Full-screen UI: Hides `#adminmenumain` and `#wpadminbar` via JavaScript

## External Dependencies

### WooCommerce REST API (automattic/woocommerce)
- **Version**: ^3.1 (composer.json)
- **Key endpoints**: `/orders`, `/reports/sales`, `/reports/top_sellers`, `/coupons`, `/system_status`
- **Authentication**: Basic Auth with consumer key/secret
- **Error handling**: Exceptions converted to `WP_Error` objects

### Frontend Libraries (CDN)
- **Chart.js**: v3.9.1 for data visualization  
- **jQuery UI Datepicker**: For custom date range selection
- **WordPress jQuery**: Core dependency for AJAX and DOM manipulation

## Development Commands

### Asset Development
- CSS: Edit `assets/css/admin.css` directly (no build process)
- JavaScript: Edit `assets/js/admin.js` and `assets/js/page-tracking.js` directly

### Testing API Integration
Use WordPress admin Settings page test connection feature or:
```php
$api = new CSR_WooCommerce_Interface();
$result = $api->test_connection();
```

### Memory Debugging
Plugin includes empty `docs/memory-optimization-recommendations.md` - indicates awareness of memory concerns with large datasets.

## Security Considerations
- All AJAX endpoints verify `wp_verify_nonce()` with 'csr_ajax_nonce'
- User capability check: `current_user_can('manage_options')`  
- Input sanitization: `sanitize_text_field()` for all user inputs
- SQL injection prevention: `$wpdb->prepare()` for custom queries