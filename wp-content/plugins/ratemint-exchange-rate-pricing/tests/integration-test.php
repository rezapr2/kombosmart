<?php
/**
 * Integration test for RateMint.
 *
 * Run from the WordPress root: wp eval-file wp-content/plugins/ratemint-exchange-rate-pricing/tests/integration-test.php
 * Creates temporary products and categories, restores settings and rates, and deletes
 * everything it created (including its rate history rows).
 */

use RateMint\Calculator;
use RateMint\Pricer;
use RateMint\Rates;
use RateMint\Recalculator;
use RateMint\Settings;
use RateMint\Orders;
use RateMint\Format;

$fails = 0;
function check( $label, $actual, $expected ) {
	global $fails;
	$ok = ( is_float( $expected ) || is_float( $actual ) ) ? abs( (float) $actual - (float) $expected ) < 0.001 : $actual === $expected;
	if ( ! $ok ) {
		$fails++;
	}
	printf( "%s %s => %s%s\n", $ok ? 'PASS' : 'FAIL', $label, var_export( $actual, true ), $ok ? '' : ' (expected ' . var_export( $expected, true ) . ')' );
}

// Save state to restore later.
$orig_settings = get_option( Settings::OPTION, null );
$orig_rates    = get_option( Rates::OPTION, null );
$orig_state    = get_option( Recalculator::STATE, null );
$created       = array();
$history_max   = (int) $GLOBALS['wpdb']->get_var( 'SELECT COALESCE( MAX(id), 0 ) FROM ' . Rates::table() );
$created_terms = array();

try {
	echo "--- Calculator\n";
	$base = array( 'rate' => 1000000, 'decimals' => 0 );
	check( 'plain', Calculator::calculate( $base + array( 'regular' => 499 ) )['regular'], 499000000.0 );
	check( 'markup 10% + 50000', Calculator::calculate( $base + array( 'regular' => 499, 'markup_percent' => 10, 'markup_fixed' => 50000 ) )['regular'], 548950000.0 );
	check( 'round up 100000', Calculator::calculate( $base + array( 'regular' => 483.725, 'rounding_step' => 100000 ) )['regular'], 483800000.0 );
	check( 'round nearest', Calculator::calculate( $base + array( 'regular' => 483.725, 'rounding_step' => 100000, 'rounding_mode' => 'nearest' ) )['regular'], 483700000.0 );
	check( 'round down', Calculator::calculate( $base + array( 'regular' => 483.799, 'rounding_step' => 100000, 'rounding_mode' => 'down' ) )['regular'], 483700000.0 );
	check( 'exact multiple not bumped', Calculator::calculate( array( 'rate' => 96800, 'decimals' => 0, 'regular' => 500, 'rounding_step' => 100000 ) )['regular'], 48400000.0 );
	check( 'sale price', Calculator::calculate( $base + array( 'regular' => 499, 'sale' => 449 ) )['sale'], 449000000.0 );
	check( 'sale percent on rounded regular', Calculator::calculate( $base + array( 'regular' => 483.725, 'sale_percent' => 10, 'rounding_step' => 100000 ) )['sale'], 435500000.0 );
	check( 'sale price wins in both', Calculator::calculate( $base + array( 'regular' => 499, 'sale' => 449, 'sale_percent' => 50 ) )['sale'], 449000000.0 );
	check( 'percent ignored in price mode', Calculator::calculate( $base + array( 'regular' => 499, 'sale_percent' => 10, 'sale_mode' => 'price' ) )['sale'], null );
	check( 'price ignored in percent mode', Calculator::calculate( $base + array( 'regular' => 499, 'sale' => 449, 'sale_mode' => 'percent' ) )['sale'], null );
	check( 'sale >= regular dropped', Calculator::calculate( $base + array( 'regular' => 499, 'sale' => 499 ) )['sale'], null );
	check( 'no regular => null', Calculator::calculate( $base + array( 'regular' => null ) ), null );
	check( 'no rate => null', Calculator::calculate( array( 'regular' => 10, 'rate' => 0 ) ), null );

	echo "--- Format\n";
	check( 'persian digits', Format::parse_number( '۱۲۳٬۴۵۶' ), '123456' );
	check( 'arabic decimal', Format::parse_number( '۴۹۹٫۹۹' ), '499.99' );
	check( 'thousands commas', Format::parse_number( '100,000' ), '100000' );
	check( 'empty', Format::parse_number( '' ), '' );
	check( 'garbage', Format::parse_number( 'abc' ), '' );

	echo "--- Settings / units (store currency " . get_woocommerce_currency() . ")\n";
	delete_option( Settings::OPTION );
	check( 'toman factor', Settings::unit_factor(), 10 );
	check( 'to_store', Settings::to_store( 100000 ), 1000000.0 );

	echo "--- Rates\n";
	delete_option( Rates::OPTION );
	check( 'no rate', Rates::get_rate(), null );
	check( 'reject zero', is_wp_error( Rates::set( 'USD', 0 ) ), true );
	check( 'set rate', Rates::set( 'USD', Settings::to_store( 100000 ), 'manual', 1 ), true );
	check( 'rate stored in rial', Rates::get_rate(), 1000000.0 );
	check( 'same rate => false', Rates::set( 'USD', 1000000, 'manual', 1 ), false );
	check( 'recalc scheduled by rate change', Recalculator::state()['reason'], 'rate' );
	check( 'history row', (float) Rates::history()[0]['rate'], 1000000.0 );
	check( 'display', Format::rate( Rates::get_rate() ), Format::rate( 1000000 ) );
	echo '    ', Format::rate( Rates::get_rate() ), "\n";

	echo "--- Products\n";
	update_option( Settings::OPTION, array_merge( Settings::defaults(), array( 'markup_percent' => '10', 'rounding_step' => Settings::to_store( 10000 ), 'rounding_mode' => 'up' ) ) );

	$cat_parent = wp_insert_term( 'ERPFW Test Parent', 'product_cat' );
	$cat_child  = wp_insert_term( 'ERPFW Test Child', 'product_cat', array( 'parent' => $cat_parent['term_id'] ) );
	$cat_other  = wp_insert_term( 'ERPFW Test Other', 'product_cat' );
	$created_terms = array( $cat_child['term_id'], $cat_parent['term_id'], $cat_other['term_id'] );
	update_term_meta( $cat_parent['term_id'], Pricer::TERM_MARKUP_PERCENT, '20' );
	update_term_meta( $cat_other['term_id'], Pricer::TERM_MARKUP_PERCENT, '5' );

	// Simple product, exchange rate mode, global markup.
	$simple = new WC_Product_Simple();
	$simple->set_name( 'ERPFW test simple' );
	$simple->set_regular_price( '123' );
	$simple->update_meta_data( Pricer::META_MODE, 'foreign' );
	$simple->update_meta_data( Pricer::META_REGULAR, '499' );
	$simple->save();
	$created[] = $simple->get_id();

	check( 'simple apply', Pricer::apply( $simple ), Pricer::STATUS_UPDATED );
	$simple = wc_get_product( $simple->get_id() );
	// 499 * 1,000,000 * 1.1 = 548,900,000 → up to 100,000 → 548,900,000.
	check( 'simple regular', $simple->get_regular_price(), '548900000' );
	check( 'simple _price', get_post_meta( $simple->get_id(), '_price', true ), '548900000' );
	check( 'lookup table', (float) $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT min_price FROM {$GLOBALS['wpdb']->wc_product_meta_lookup} WHERE product_id = %d", $simple->get_id() ) ), 548900000.0 );
	check( 'second apply unchanged', Pricer::apply( $simple ), Pricer::STATUS_UNCHANGED );

	// Category markup: child inherits parent 20%, other category 5% → highest = 20%.
	$simple->set_category_ids( array( $cat_child['term_id'], $cat_other['term_id'] ) );
	$simple->save();
	check( 'category markup highest', Pricer::resolve_markup( $simple )['percent'], 20.0 );
	update_option( Settings::OPTION, array_merge( Settings::all(), array( 'category_conflict' => 'lowest' ) ) );
	check( 'category markup lowest', Pricer::resolve_markup( $simple )['percent'], 5.0 );
	update_option( Settings::OPTION, array_merge( Settings::all(), array( 'category_conflict' => 'highest' ) ) );
	Pricer::apply( $simple );
	// 499 * 1,000,000 * 1.2 = 598,800,000.
	check( 'simple regular w/ category markup', wc_get_product( $simple->get_id() )->get_regular_price(), '598800000' );

	// Product override wins.
	$simple = wc_get_product( $simple->get_id() );
	$simple->update_meta_data( Pricer::META_MARKUP_PERCENT, '0' );
	$simple->update_meta_data( Pricer::META_SALE_PERCENT, '10' );
	$simple->save();
	Pricer::apply( $simple );
	$simple = wc_get_product( $simple->get_id() );
	check( 'product markup override', $simple->get_regular_price(), '499000000' );
	check( 'sale percent', $simple->get_sale_price(), '449100000' );
	check( 'is on sale', $simple->is_on_sale(), true );

	// Missing base price keeps current price.
	$missing = new WC_Product_Simple();
	$missing->set_name( 'ERPFW test missing' );
	$missing->set_regular_price( '777' );
	$missing->update_meta_data( Pricer::META_MODE, 'foreign' );
	$missing->save();
	$created[] = $missing->get_id();
	check( 'missing status', Pricer::apply( $missing ), Pricer::STATUS_MISSING );
	check( 'missing keeps price', wc_get_product( $missing->get_id() )->get_regular_price(), '777' );
	check( 'missing flagged', Pricer::is_missing_price( $missing ), true );

	// Manual product untouched.
	$manual = new WC_Product_Simple();
	$manual->set_name( 'ERPFW test manual' );
	$manual->set_regular_price( '555' );
	$manual->update_meta_data( Pricer::META_REGULAR, '10' );
	$manual->save();
	$created[] = $manual->get_id();
	check( 'manual (store default) status', Pricer::apply( $manual ), Pricer::STATUS_MANUAL );
	check( 'manual keeps price', wc_get_product( $manual->get_id() )->get_regular_price(), '555' );

	// Variable product.
	$variable = new WC_Product_Variable();
	$variable->set_name( 'ERPFW test variable' );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Size' );
	$attribute->set_options( array( 'S', 'L' ) );
	$attribute->set_variation( true );
	$attribute->set_visible( true );
	$variable->set_attributes( array( $attribute ) );
	$variable->update_meta_data( Pricer::META_MODE, 'foreign' );
	$variable->update_meta_data( Pricer::META_MARKUP_PERCENT, '0' );
	$variable->save();
	$created[] = $variable->get_id();
	foreach ( array( 'S' => '100', 'L' => '150' ) as $size => $usd ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $variable->get_id() );
		$variation->set_attributes( array( 'size' => $size ) );
		$variation->set_regular_price( '1' );
		$variation->update_meta_data( Pricer::META_REGULAR, $usd );
		$variation->save();
		$created[] = $variation->get_id();
	}
	check( 'variable apply', Pricer::apply( wc_get_product( $variable->get_id() ) ), Pricer::STATUS_UPDATED );
	$variable = wc_get_product( $variable->get_id() );
	check( 'variable min price', $variable->get_variation_price( 'min' ), '100000000' );
	check( 'variable max price', $variable->get_variation_price( 'max' ), '150000000' );

	echo "--- Recalculation after rate change\n";
	// Default mode manual: only products with mode=foreign are counted (plus any real ones already foreign).
	Rates::set( 'USD', Settings::to_store( 110000 ), 'manual', 1 );
	$state = Recalculator::state();
	check( 'run scheduled', $state['status'], 'running' );
	$state = Recalculator::run_now();
	check( 'run done', $state['status'], 'done' );
	echo '    ', \RateMint\Admin\Admin::recalc_summary( $state ), "\n";
	check( 'simple after rate change', wc_get_product( $simple->get_id() )->get_regular_price(), '548900000' );
	check( 'variation after rate change', wc_get_product( $variable->get_id() )->get_variation_price( 'max' ), '165000000' );
	check( 'synced rate meta', (float) wc_get_product( $simple->get_id() )->get_meta( Pricer::META_SYNCED_RATE ), 1100000.0 );
	check( 'stale generation ignored', Recalculator::process_batch( 999 )['status'], 'done' );

	echo "--- Default mode = foreign\n";
	update_option( Settings::OPTION, array_merge( Settings::all(), array( 'default_mode' => 'foreign' ) ) );
	check( 'settings change schedules run', Recalculator::state()['reason'], 'settings' );
	check( 'manual-by-default product now foreign', Pricer::is_foreign( wc_get_product( $manual->get_id() ) ), true );
	$explicit = wc_get_product( $manual->get_id() );
	$explicit->update_meta_data( Pricer::META_MODE, 'manual' );
	$explicit->save();
	check( 'explicit manual stays manual', Pricer::is_foreign( wc_get_product( $manual->get_id() ) ), false );
	Recalculator::run_now();
	update_option( Settings::OPTION, array_merge( Settings::all(), array( 'default_mode' => 'manual' ) ) );
	Recalculator::run_now();

	echo "--- Order snapshot\n";
	$order = wc_create_order();
	$item  = new WC_Order_Item_Product();
	$item->set_product( wc_get_product( $simple->get_id() ) );
	Orders::record_line_item( $item, 'key', array( 'data' => wc_get_product( $simple->get_id() ) ), $order );
	check( 'item base price (sale 10%)', $item->get_meta( Orders::ITEM_BASE_PRICE ), '449.10' );
	check( 'item rate', (float) $item->get_meta( Orders::ITEM_RATE ), 1100000.0 );
	check( 'order snapshot rate', (float) $order->get_meta( Orders::ORDER_META )['rate'], 1100000.0 );
	$order->delete( true );

	echo "--- Public helper\n";
	check( 'erpfw_convert_to_store_price', erpfw_convert_to_store_price( 10, array( 'markup_percent' => 0, 'rounding_step' => 0 ) ), 11000000.0 );
} catch ( Throwable $e ) {
	$fails++;
	echo 'EXCEPTION: ', $e->getMessage(), ' @ ', $e->getFile(), ':', $e->getLine(), "\n";
} finally {
	foreach ( array_reverse( $created ) as $id ) {
		$product = wc_get_product( $id );
		if ( $product ) {
			$product->delete( true );
		}
	}
	foreach ( $created_terms as $term_id ) {
		wp_delete_term( $term_id, 'product_cat' );
	}
	null === $orig_settings ? delete_option( Settings::OPTION ) : update_option( Settings::OPTION, $orig_settings );
	null === $orig_rates ? delete_option( Rates::OPTION ) : update_option( Rates::OPTION, $orig_rates );
	null === $orig_state ? delete_option( Recalculator::STATE ) : update_option( Recalculator::STATE, $orig_state );
	$GLOBALS['wpdb']->query( $GLOBALS['wpdb']->prepare( 'DELETE FROM ' . Rates::table() . ' WHERE id > %d', $history_max ) );
	as_unschedule_all_actions( Recalculator::HOOK );
	echo $fails ? "\n$fails FAILED\n" : "\nALL PASSED\n";
}
