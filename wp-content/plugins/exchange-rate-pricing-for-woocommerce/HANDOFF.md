# Handoff: Exchange Rate Pricing for WooCommerce

Status as of 2026-09-26. Read this before changing the plugin.

## What it is

A WooCommerce plugin that lets a product's price be entered in a base currency (USD by default). The plugin calculates the store price from a manually entered exchange rate, plus markup and rounding. Built for the smartw store (Persian, currency IRR, theme shows Toman), but written to be published on WordPress.org later.

- Slug / folder / text domain: `exchange-rate-pricing-for-woocommerce` (slug was free on wordpress.org on 2026-09-26; re-check before submitting)
- Prefix: `erpfw` (functions, hooks, options, meta). PHP namespace: `ExchangeRatePricing`
- Requires WP 6.5+, PHP 7.4+, WooCommerce 8.0+. Tested locally on WP 6.9, WC 10.4.3, PHP 8.4
- **Not committed to git.** The smartw repo must not be pushed (see project memory). Commit only when the user asks.

## Decisions the user made (don't re-ask)

| Topic | Decision |
|---|---|
| Rate source | Manual entry now; API later (the provider interface is already in place) |
| Which products | Global default mode (manual or exchange rate) + per-product override: Store default / Exchange rate / Manual |
| Markup | Percent + fixed amount. Global → category (subcategories inherit from nearest ancestor) → product. Only applies to exchange-rate products |
| Category conflict | Product in several categories with different markups: **highest** wins by default (setting allows lowest). Not Yoast primary category, to avoid depending on Yoast |
| Rounding | Configurable step + direction (up / nearest / down) |
| Sale | Configurable: base currency sale price, discount %, or both (sale price wins) |
| No ACF | Uses WooCommerce product data panel hooks, term meta, and the WC Settings API |
| WP.org-ready | English source strings, bundled `fa_IR` translation, prefixed everything, HPOS + Cart/Checkout blocks compatibility declared |

## Core design

**Prices are materialized, not converted on page load.** The plugin writes the calculated price into WooCommerce's own `_regular_price` / `_sale_price` (via the `WC_Product` API, so `_price` and `wc_product_meta_lookup` stay in sync). Sorting, filters, cart, SnappPay, Yoast and feeds need no changes. Deactivating the plugin leaves the last calculated prices in place.

Formula (`src/Calculator.php`, no WordPress calls, unit-testable):

```
store = round( base × rate × (1 + markup% / 100) + fixed markup )
sale  = base sale converted the same way, OR round( rounded regular × (1 − discount%) )
```

A sale price that is ≥ regular is dropped. A product in exchange-rate mode with **no base price is left untouched** (never set to 0) and is flagged in the products list.

### Units (important for smartw)

- All money amounts (rate, fixed markup, rounding step, category/product fixed markup) are **stored in the store currency** (Rial for smartw).
- If the store currency is IRR, the setting "Enter amounts in: Toman / Rial" (default Toman) sets a factor of 10 for input and display (`Settings::to_store()` / `to_display()` / `unit_label()`).
- On the settings page, posted amounts are read with the **saved** unit (the unit the form was rendered with), so switching the unit never changes stored values.
- The smartw theme divides every displayed price by 10 (`themes/smartw/includes/Frontend.php` → `raw_woocommerce_price`). The plugin never uses `wc_price()` for its own output; it uses `Format::*` so the theme filter can't distort admin-bar or preview numbers.
- `Rates::get()` ignores a saved rate if the store currency has changed since it was saved.

## File map

| File | Role |
|---|---|
| `exchange-rate-pricing-for-woocommerce.php` | Header, constants, autoloader, activation hooks, HPOS/blocks declarations |
| `src/Plugin.php` | Wiring; schedules a recalculation when the rate or price-affecting settings change |
| `src/Settings.php` | Option `erpfw_settings` + defaults, unit conversion, capability (`manage_woocommerce`, filterable) |
| `src/Format.php` | Parses Persian/Arabic digits and separators; formats amounts without `wc_price()` |
| `src/Rates.php` | Current rates (option `erpfw_rates`, keyed by currency) + history table `{prefix}erpfw_rate_history` |
| `src/RateProviders/*` | `RateProviderInterface`, `ManualProvider`, `Registry` (filter `erpfw_rate_providers`) |
| `src/Calculator.php` | Pure pricing math |
| `src/Pricer.php` | Resolves mode and markup for a product, `apply()` (saves only if changed), `apply_to_object()` (no save, used inside WC's own save) |
| `src/Recalculator.php` | Background run over all exchange-rate products with Action Scheduler (keyset batches, generation number, DB lock) |
| `src/Orders.php` | Stores base price + rate on order items and a rate snapshot on the order (HPOS-safe); shown in the admin order screen |
| `src/CsvImportExport.php` | WC product CSV import/export columns |
| `src/CLI.php` | `wp erpfw rate [<value>]`, `wp erpfw recalculate [--now]`, `wp erpfw status` |
| `src/Installer.php` | Creates the history table (dbDelta), `erpfw_db_version` |
| `src/functions.php` | Public helpers `erpfw_get_rate()`, `erpfw_convert_to_store_price()` |
| `src/Admin/Admin.php` | Admin wiring, asset loading, notices (no/outdated rate), recalculation AJAX, `recalc_summary()` |
| `src/Admin/SettingsPage.php` | WooCommerce → Settings → "Exchange rates" tab (`WC_Settings_Page`), custom field types `erpfw_rate`, `erpfw_amount`, `erpfw_recalc`, `erpfw_history` |
| `src/Admin/ProductFields.php` | Mode/markup fields (General tab), base price fields (pricing group + each variation), save hooks, live preview AJAX |
| `src/Admin/CategoryFields.php` | Category markup fields; changing them schedules a recalculation |
| `src/Admin/ProductList.php` | "Base price (USD)" column, quick edit, bulk edit |
| `src/Admin/RateWidget.php` | Dashboard widget + admin bar quick-update form (AJAX, with an admin-post.php fallback for no-JS) |
| `assets/js/admin.js` | Product screen (mode toggle, locks WC price inputs, preview), quick edit fill, settings progress bar |
| `assets/js/rate.js` | Vanilla JS for the rate forms (loads on the front end too, for the admin bar) |
| `languages/` | `.pot`, `fa_IR` `.po/.mo/.l10n.php` |
| `tests/integration-test.php` | End-to-end test (61 checks), self-cleaning |
| `.distignore` | Excludes `tests/`, this file, etc. from release zips |

## Data

| Where | Key | Meaning |
|---|---|---|
| Product (parent) meta | `_erpfw_mode` | `''` = store default, `foreign`, `manual` |
| Product/variation meta | `_erpfw_regular_price`, `_erpfw_sale_price`, `_erpfw_sale_percent` | Base currency prices / discount % |
| Product (parent) meta | `_erpfw_markup_percent`, `_erpfw_markup_fixed` | `''` = inherit; fixed is in store currency |
| Product/variation meta | `_erpfw_synced_rate`, `_erpfw_synced_at` | Rate used for the last calculation |
| Term meta (`product_cat`) | `erpfw_markup_percent`, `erpfw_markup_fixed` | Category markup; absent = inherit |
| Options | `erpfw_settings`, `erpfw_rates`, `erpfw_recalc_state`, `erpfw_db_version` | `erpfw_recalc_lock` is a transient lock row written with raw SQL |
| Order meta | `_erpfw_rate_snapshot` | `{currency, rate, store_currency, updated_at}` |
| Order item meta | `_erpfw_base_price`, `_erpfw_base_currency`, `_erpfw_rate` | Hidden (underscore) |

Variations follow their parent's mode and markup.

## Save flows (why the hooks are where they are)

- **Simple/external product form:** `woocommerce_admin_process_product_object` stores our meta and sets the calculated prices on the object, so WooCommerce saves once. This overrides the (read-only) posted native prices.
- **Variations (AJAX "Save changes"):** `woocommerce_admin_process_variation_object` stores meta and calculates using the parent's *saved* mode. `woocommerce_ajax_save_product_variations` then resyncs the parent's price range.
- **Variable product form:** `woocommerce_process_product_meta` at priority 50 runs `Pricer::apply(parent)`, which recalculates every variation (the mode or markup may have just changed) and calls `WC_Product_Variable::sync()`.
- **Quick/bulk edit:** WooCommerce saves first, then our `*_edit_save` handler saves meta and calls `Pricer::apply()`.
- **CSV import:** meta is set in `woocommerce_product_import_pre_insert_product_object`, then `apply()` runs after the insert.

## Recalculation

`Recalculator::schedule($reason)` bumps a generation number, resets the state, cancels pending actions and enqueues `erpfw_recalculate_batch`. Each batch:
1. Takes a DB lock (`INSERT IGNORE` into options; stale after 5 minutes).
2. Scans product IDs above `last_id` whose mode is foreign (or empty when the default is foreign).
3. Applies prices, then re-reads the state and discards its results if a newer generation started.
4. Queues the next batch unless one is already pending.

The settings page progress poll also processes batches inline, so progress moves while the page is open. When a run finishes it fires `erpfw_prices_recalculated`, which is the hook to purge page caches.

Triggers: rate change (`erpfw_rate_updated`), change to any key in `Settings::PRICE_AFFECTING_KEYS`, category markup change, the "Recalculate all prices now" button, `wp erpfw recalculate`.

## Testing

WP-CLI isn't installed globally on this machine. Download the phar and run it with ServBay's PHP:

```sh
curl -sSLo wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
php wp-cli.phar --path=/Users/rezarajabi/Sites/localhost/smartw eval-file \
  wp-content/plugins/exchange-rate-pricing-for-woocommerce/tests/integration-test.php
```

Verified on 2026-09-26:
- The integration test passes (calculator, units, Persian digits, rates, markup inheritance, sale modes, missing-price safety, variable products, background runs, order snapshot, helper).
- Tested over real HTTP with an admin cookie: settings save, product form save, variation AJAX save, live preview, quick edit, category edit, dashboard widget, admin bar on the storefront, rate AJAX + no-JS fallback, recalculation AJAX, CSV columns. The storefront showed the stored Rial price ÷10 as Toman.
- WordPress.org **Plugin Check**: no errors except `Tested up to: 6.9 < 7.1` (bump after testing on the current WP), plus one warning for `load_plugin_textdomain` (kept on purpose so the bundled `fa_IR` loads before the plugin is on WordPress.org).

Rebuilding translations after changing strings:

```sh
wp i18n make-pot <plugin> <plugin>/languages/exchange-rate-pricing-for-woocommerce.pot --exclude=languages
# add or adjust fa_IR strings in the .po (all 125 were translated on 2026-09-26), then:
wp i18n make-mo <plugin>/languages && wp i18n make-php <plugin>/languages
```

## Current state on the local site

The plugin is **active** with no settings saved and no rate, and the default mode is manual. It changes no product until the user enters a rate and switches products (or the default) to exchange-rate pricing. All test products and categories were deleted; the store still has its 29 products.

## Not done yet / next steps

1. **API rate provider (v2):** implement `RateProviderInterface`, register it through `erpfw_rate_providers`, and add a recurring Action Scheduler fetch with safeguards: keep the last good rate on failure, hold changes above X% for approval, show a stale-rate notice. External requests must be disclosed in `readme.txt` (WordPress.org rule).
2. **Option to switch to call-for-price when the rate is stale**: planned but not built. Today there is only an admin notice.
3. **Several base currencies per product**: rates are already keyed by currency; the product meta and UI still assume one base currency.
4. **smartw theme add-ons** (`_tc_price_options`, ACF `sub_products`) are still priced in Toman. Use `erpfw_convert_to_store_price()` in the theme if they should follow the rate.
5. **Before WordPress.org submission:**
   - Set `Contributors:` in `readme.txt` to the user's real wordpress.org username (currently a placeholder, `rezarajabi`).
   - Bump `Tested up to`, then re-run Plugin Check.
   - Add screenshots, banner and icon assets.
   - Consider PHPUnit tests for `Calculator`.
6. Admin bar dropdown: WordPress may close it when the mouse leaves while you're typing. It hasn't been checked in a real browser (no browser automation was available). The same goes for the JS UX in general; only the HTTP/server paths were verified.
