=== Exchange Rate Pricing for WooCommerce ===
Contributors: rezapr2
Tags: exchange rate, currency, dollar price, dynamic pricing, woocommerce
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Price products in USD (or any currency) and sell them in your store currency, updated automatically from an exchange rate you control.

== Description ==

If you buy stock in a foreign currency, your prices move with the exchange rate. Exchange Rate Pricing for WooCommerce lets you enter a product's price in a base currency such as USD, and calculates the store price for you:

`store price = round( base price × rate × (1 + markup %) + fixed markup )`

When you change the rate, every exchange-rate priced product is recalculated in the background.

= Features =

* Base currency price for simple, external and variable products (each variation has its own price).
* Choose per product: store default, exchange rate or manual price. The store default can be "all products" or "only products I choose".
* Markup as a percent and/or fixed amount: store-wide, per product category (subcategories inherit) and per product.
* Rounding to any step (for example 10,000) up, down or to the nearest.
* Sale prices as a base currency price, a discount percent, or both.
* Live preview of the calculated price while editing a product.
* Background recalculation with Action Scheduler, in batches, safe for large catalogs.
* Rate history log, and the rate at purchase time saved on each order.
* Quick rate update from the dashboard widget or the admin bar.
* Products list column, quick edit and bulk edit.
* Product CSV import/export columns.
* WP-CLI commands: `wp erpfw rate`, `wp erpfw recalculate`, `wp erpfw status`.
* Iranian Rial stores can enter amounts in Toman.
* Compatible with High-Performance Order Storage and the Cart and Checkout blocks.
* Translation ready. Includes Persian, Portuguese (Brazil) and Portuguese (Portugal) translations.

= How prices are stored =

Calculated prices are written to WooCommerce's normal price fields. Sorting, price filters, carts, coupons, payment gateways, feeds and structured data keep working without changes, and nothing is calculated on page load. If you deactivate the plugin, products keep their last calculated prices.

= For developers =

Filters and actions:

* `erpfw_rate_providers` – register automatic rate sources (implement `ExchangeRatePricing\RateProviders\RateProviderInterface`).
* `erpfw_calculation_args` / `erpfw_calculated_prices` – adjust the calculation.
* `erpfw_supported_product_types` – add product types.
* `erpfw_rate_updated` – fires when a rate changes.
* `erpfw_prices_recalculated` – fires after a recalculation run, useful for purging page caches.
* `erpfw_manage_capability` – capability needed to change rates (default `manage_woocommerce`).

Helper functions: `erpfw_get_rate()` and `erpfw_convert_to_store_price( $amount )`.

== Installation ==

1. Install and activate the plugin. WooCommerce must be active.
2. Go to WooCommerce → Settings → Exchange rates, choose the base currency and enter the current rate.
3. Set markup and rounding.
4. Edit a product, set Pricing to "Exchange rate" and enter the base regular price. Or make exchange rate the store default.

== Frequently Asked Questions ==

= Where does the exchange rate come from? =

You enter it. The rate source is pluggable, so automatic sources can be added by extensions.

= What happens to products without a base price? =

They keep their current price and are flagged in the products list. A price is never set to zero.

= Does it slow down my store? =

No. Prices are calculated when the rate or a product changes, not when pages load.

= What happens when a cart is open while the rate changes? =

The cart uses the product's current price, like any price change in WooCommerce. Orders keep the price and rate they were placed with.

= Can I translate the plugin? =

Yes. All text can be translated on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/exchange-rate-pricing-for-woocommerce/). Persian and Portuguese translations are included with the plugin.

== Changelog ==

= 1.0.0 =
* First release.
