<?php
/**
 * Records exchange rate details on orders.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

use WC_Order;
use WC_Order_Item_Product;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Stores the rate and base prices at purchase time, for accounting and margin reports.
 * Uses the order CRUD API, so it works with High-Performance Order Storage.
 */
final class Orders {

	const ORDER_META         = '_erpfw_rate_snapshot';
	const ITEM_BASE_PRICE    = '_erpfw_base_price';
	const ITEM_BASE_CURRENCY = '_erpfw_base_currency';
	const ITEM_RATE          = '_erpfw_rate';

	/**
	 * Register hooks.
	 */
	public static function init() {
		// Fires for both the classic and the block checkout.
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'record_line_item' ), 10, 4 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'show_order_rate' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'show_item_base_price' ), 10, 3 );
	}

	/**
	 * Save base price and rate on line items of exchange-rate priced products.
	 *
	 * @param WC_Order_Item_Product $item          Line item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values        Cart item.
	 * @param WC_Order              $order         Order.
	 */
	public static function record_line_item( $item, $cart_item_key, $values, $order ) {
		$product = isset( $values['data'] ) ? $values['data'] : null;

		if ( ! $product instanceof WC_Product || ! Pricer::is_foreign( $product ) ) {
			return;
		}

		$regular = (string) $product->get_meta( Pricer::META_REGULAR );

		if ( '' === $regular ) {
			return;
		}

		$base    = (float) $regular;
		$sale    = (string) $product->get_meta( Pricer::META_SALE );
		$percent = (float) $product->get_meta( Pricer::META_SALE_PERCENT );

		if ( $product->is_on_sale( 'edit' ) ) {
			if ( '' !== $sale ) {
				$base = (float) $sale;
			} elseif ( $percent > 0 && $percent < 100 ) {
				$base = $base * ( 1 - $percent / 100 );
			}
		}

		$currency  = Settings::base_currency();
		$item_rate = (string) $product->get_meta( Pricer::META_SYNCED_RATE );
		$rate      = Rates::get();

		$item->add_meta_data( self::ITEM_BASE_PRICE, wc_format_decimal( $base, 2 ), true );
		$item->add_meta_data( self::ITEM_BASE_CURRENCY, $currency, true );
		$item->add_meta_data( self::ITEM_RATE, '' !== $item_rate ? $item_rate : ( $rate ? wc_format_decimal( $rate['rate'] ) : '' ), true );

		if ( $order instanceof WC_Order && $rate && ! $order->get_meta( self::ORDER_META ) ) {
			$order->update_meta_data(
				self::ORDER_META,
				array(
					'currency'       => $currency,
					'rate'           => (float) $rate['rate'],
					'store_currency' => get_woocommerce_currency(),
					'updated_at'     => (int) $rate['updated_at'],
				)
			);
		}
	}

	/**
	 * Show the rate at purchase time on the admin order screen.
	 *
	 * @param WC_Order $order Order.
	 */
	public static function show_order_rate( $order ) {
		$snapshot = $order instanceof WC_Order ? $order->get_meta( self::ORDER_META ) : null;

		if ( ! is_array( $snapshot ) || empty( $snapshot['rate'] ) ) {
			return;
		}

		printf(
			'<p class="form-field form-field-wide erpfw-order-rate"><strong>%s</strong><br>%s</p>',
			esc_html__( 'Exchange rate at purchase', 'exchange-rate-pricing-for-woocommerce' ),
			esc_html( Format::rate( $snapshot['rate'], $snapshot['currency'] ) )
		);
	}

	/**
	 * Show the base price under an order line item in the admin.
	 *
	 * @param int                   $item_id Item id.
	 * @param WC_Order_Item_Product $item    Item.
	 * @param WC_Product|null       $product Product.
	 */
	public static function show_item_base_price( $item_id, $item, $product ) {
		if ( ! is_object( $item ) || ! method_exists( $item, 'get_meta' ) ) {
			return;
		}

		$base = $item->get_meta( self::ITEM_BASE_PRICE );

		if ( '' === (string) $base ) {
			return;
		}

		$currency = (string) $item->get_meta( self::ITEM_BASE_CURRENCY );
		$rate     = (string) $item->get_meta( self::ITEM_RATE );
		$text     = sprintf(
			/* translators: %s: price in the base currency, e.g. $499. */
			__( 'Base price: %s', 'exchange-rate-pricing-for-woocommerce' ),
			Format::foreign( $base, $currency )
		);

		if ( '' !== $rate ) {
			$text .= ' · ' . Format::rate( $rate, $currency );
		}

		printf( '<div class="erpfw-item-base-price"><small>%s</small></div>', esc_html( $text ) );
	}
}
