# kombosmart

A Persian (RTL) online store for smart home equipment, built on WordPress and WooCommerce.

- **Theme:** custom `kombosmart` theme in `wp-content/themes/kombosmart`
- **Pricing:** USD→Toman dynamic pricing via the bundled `exchange-rate-pricing-for-woocommerce` plugin
- **Payments:** Snapppay gateway integration
- **Other plugins:** Advanced Custom Fields Pro, Contact Form 7, Yoast SEO, Safe SVG

## Local setup

1. Copy `wp-config-sample.php` to `wp-config.php` and set your database credentials.
2. Import a database dump and serve the folder with PHP/nginx (e.g. ServBay).
3. Build theme assets from `wp-content/themes/kombosmart` with `npm install`.
