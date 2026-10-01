<?php
/**
 * Applies calculated prices to products.
 *
 * @package RateMint
 */

namespace RateMint;

use WC_Product;
use WC_Product_Variable;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves a product's pricing configuration (mode, markup) and writes the
 * calculated store prices into WooCommerce's own price fields, so sorting,
 * filters, carts, gateways and feeds keep working unchanged.
 */
final class Pricer {

	// Product and variation meta.
	const META_MODE           = '_erpfw_mode';
	const META_REGULAR        = '_erpfw_regular_price';
	const META_SALE           = '_erpfw_sale_price';
	const META_SALE_PERCENT   = '_erpfw_sale_percent';
	const META_MARKUP_PERCENT = '_erpfw_markup_percent';
	const META_MARKUP_FIXED   = '_erpfw_markup_fixed';
	const META_SYNCED_RATE    = '_erpfw_synced_rate';
	const META_SYNCED_AT      = '_erpfw_synced_at';

	// Product category term meta.
	const TERM_MARKUP_PERCENT = 'erpfw_markup_percent';
	const TERM_MARKUP_FIXED   = 'erpfw_markup_fixed';

	// Result of applying prices to a product.
	const STATUS_UPDATED     = 'updated';
	const STATUS_UNCHANGED   = 'unchanged';
	const STATUS_MISSING     = 'missing_price';
	const STATUS_NO_RATE     = 'no_rate';
	const STATUS_MANUAL      = 'manual';
	const STATUS_UNSUPPORTED = 'unsupported';

	const MODE_DEFAULT = '';
	const MODE_FOREIGN = 'foreign';
	const MODE_MANUAL  = 'manual';

	/**
	 * Product types that can be priced by exchange rate. Variations follow their parent.
	 *
	 * @return string[]
	 */
	public static function supported_types() {
		return (array) apply_filters( 'erpfw_supported_product_types', array( 'simple', 'external', 'variable' ) );
	}

	/**
	 * The product that holds the pricing mode and markup: the parent for variations.
	 *
	 * @param WC_Product      $product Product or variation.
	 * @param WC_Product|null $parent  Already loaded parent, if any.
	 * @return WC_Product|null
	 */
	public static function config_source( WC_Product $product, $parent = null ) {
		if ( ! $product->is_type( 'variation' ) ) {
			return $product;
		}

		if ( $parent instanceof WC_Product ) {
			return $parent;
		}

		$loaded = wc_get_product( $product->get_parent_id() );

		return $loaded ? $loaded : null;
	}

	/**
	 * Resolve a stored mode ('' means "use the store default").
	 *
	 * @param string $mode Stored mode.
	 * @return string foreign or manual.
	 */
	public static function effective_mode( $mode ) {
		if ( self::MODE_DEFAULT === (string) $mode ) {
			$mode = Settings::get( 'default_mode' );
		}

		return self::MODE_FOREIGN === $mode ? self::MODE_FOREIGN : self::MODE_MANUAL;
	}

	/**
	 * Whether a product (or a variation's parent) is priced by exchange rate.
	 *
	 * @param WC_Product      $product Product or variation.
	 * @param WC_Product|null $parent  Already loaded parent, if any.
	 * @return bool
	 */
	public static function is_foreign( WC_Product $product, $parent = null ) {
		$source = self::config_source( $product, $parent );

		if ( ! $source || ! in_array( $source->get_type(), self::supported_types(), true ) ) {
			return false;
		}

		return self::MODE_FOREIGN === self::effective_mode( $source->get_meta( self::META_MODE ) );
	}

	/**
	 * Resolve markup: product override, then product categories, then the global setting.
	 *
	 * @param WC_Product $source    Product holding the configuration (not a variation).
	 * @param array      $overrides Optional unsaved values: markup_percent, markup_fixed, category_ids.
	 * @return array { percent: float, fixed: float } Fixed markup is in store currency.
	 */
	public static function resolve_markup( WC_Product $source, array $overrides = array() ) {
		$fields = array(
			'percent' => array( 'markup_percent', self::META_MARKUP_PERCENT, self::TERM_MARKUP_PERCENT ),
			'fixed'   => array( 'markup_fixed', self::META_MARKUP_FIXED, self::TERM_MARKUP_FIXED ),
		);

		$category_ids = array_key_exists( 'category_ids', $overrides ) ? (array) $overrides['category_ids'] : $source->get_category_ids();
		$markup       = array();

		foreach ( $fields as $key => $keys ) {
			list( $setting, $product_meta, $term_meta ) = $keys;

			$value = array_key_exists( $setting, $overrides ) ? $overrides[ $setting ] : $source->get_meta( $product_meta );

			if ( null !== $value && '' !== (string) $value ) {
				$markup[ $key ] = (float) $value;
				continue;
			}

			$from_category  = self::category_markup( $category_ids, $term_meta );
			$markup[ $key ] = null !== $from_category ? $from_category : (float) Settings::get( $setting );
		}

		return $markup;
	}

	/**
	 * Markup set on the given categories or their closest ancestor.
	 *
	 * When categories disagree the highest (or lowest, per settings) value wins.
	 *
	 * @param int[]  $term_ids Product category ids.
	 * @param string $meta_key Term meta key.
	 * @return float|null
	 */
	public static function category_markup( array $term_ids, $meta_key ) {
		$found = array();

		foreach ( array_filter( array_map( 'absint', $term_ids ) ) as $term_id ) {
			$current = $term_id;
			$guard   = 0;

			while ( $current && $guard++ < 20 ) {
				$value = get_term_meta( $current, $meta_key, true );

				if ( '' !== (string) $value ) {
					$found[] = (float) $value;
					break;
				}

				$term    = get_term( $current, 'product_cat' );
				$current = ( $term && ! is_wp_error( $term ) ) ? (int) $term->parent : 0;
			}
		}

		if ( ! $found ) {
			return null;
		}

		return 'lowest' === Settings::get( 'category_conflict' ) ? min( $found ) : max( $found );
	}

	/**
	 * Build calculator arguments for a product or variation.
	 *
	 * @param WC_Product      $product   Simple, external product or variation.
	 * @param WC_Product|null $parent    Parent of a variation.
	 * @param array           $overrides Unsaved values: regular, sale, sale_percent, markup_percent, markup_fixed, category_ids.
	 * @return array|null Null when the configuration cannot be resolved or no rate is set.
	 */
	public static function calculation_args( WC_Product $product, $parent = null, array $overrides = array() ) {
		$source = self::config_source( $product, $parent );
		$rate   = Rates::get_rate();

		if ( ! $source || ! $rate ) {
			return null;
		}

		$value = static function ( $key, $meta ) use ( $overrides, $product ) {
			$raw = array_key_exists( $key, $overrides ) ? $overrides[ $key ] : $product->get_meta( $meta );

			return ( null === $raw || '' === (string) $raw ) ? null : (float) $raw;
		};

		$markup = self::resolve_markup( $source, $overrides );

		$args = array(
			'regular'        => $value( 'regular', self::META_REGULAR ),
			'sale'           => $value( 'sale', self::META_SALE ),
			'sale_percent'   => $value( 'sale_percent', self::META_SALE_PERCENT ),
			'rate'           => $rate,
			'markup_percent' => $markup['percent'],
			'markup_fixed'   => $markup['fixed'],
			'rounding_step'  => (float) Settings::get( 'rounding_step' ),
			'rounding_mode'  => (string) Settings::get( 'rounding_mode' ),
			'sale_mode'      => (string) Settings::get( 'sale_mode' ),
			'decimals'       => wc_get_price_decimals(),
		);

		/**
		 * Filters the arguments used to calculate a product's store prices.
		 *
		 * @param array      $args    Calculator arguments.
		 * @param WC_Product $product Product or variation.
		 */
		return apply_filters( 'erpfw_calculation_args', $args, $product );
	}

	/**
	 * Calculate store prices for a product or variation without saving.
	 *
	 * @param WC_Product      $product   Simple, external product or variation.
	 * @param WC_Product|null $parent    Parent of a variation.
	 * @param array           $overrides See calculation_args().
	 * @return array|null { regular: float, sale: float|null }
	 */
	public static function calculate( WC_Product $product, $parent = null, array $overrides = array() ) {
		$args = self::calculation_args( $product, $parent, $overrides );

		if ( null === $args ) {
			return null;
		}

		/**
		 * Filters calculated store prices before they are saved.
		 *
		 * @param array|null $prices  { regular, sale } or null when nothing can be priced.
		 * @param WC_Product $product Product or variation.
		 * @param array      $args    Calculator arguments.
		 */
		return apply_filters( 'erpfw_calculated_prices', Calculator::calculate( $args ), $product, $args );
	}

	/**
	 * Recalculate and save prices for any supported product.
	 *
	 * Variable products update every variation and then resync the parent.
	 *
	 * @param WC_Product $product Product or variation.
	 * @return string One of the STATUS_* constants.
	 */
	public static function apply( WC_Product $product ) {
		if ( $product->is_type( 'variation' ) ) {
			$parent = self::config_source( $product );
			$status = self::apply_single( $product, $parent );

			if ( self::STATUS_UPDATED === $status && $parent ) {
				WC_Product_Variable::sync( $parent->get_id() );
			}

			return $status;
		}

		if ( ! self::is_foreign( $product ) ) {
			return in_array( $product->get_type(), self::supported_types(), true ) ? self::STATUS_MANUAL : self::STATUS_UNSUPPORTED;
		}

		if ( ! $product->is_type( 'variable' ) ) {
			return self::apply_single( $product );
		}

		$statuses = array();

		foreach ( $product->get_children() as $child_id ) {
			$child = wc_get_product( $child_id );

			if ( $child ) {
				$statuses[] = self::apply_single( $child, $product );
			}
		}

		if ( in_array( self::STATUS_UPDATED, $statuses, true ) ) {
			WC_Product_Variable::sync( $product->get_id() );

			return self::STATUS_UPDATED;
		}

		if ( in_array( self::STATUS_NO_RATE, $statuses, true ) ) {
			return self::STATUS_NO_RATE;
		}

		if ( in_array( self::STATUS_UNCHANGED, $statuses, true ) ) {
			return self::STATUS_UNCHANGED;
		}

		return self::STATUS_MISSING;
	}

	/**
	 * Recalculate one simple product or variation and save it only if prices changed.
	 *
	 * @param WC_Product      $product Simple, external product or variation.
	 * @param WC_Product|null $parent  Parent of a variation.
	 * @return string One of the STATUS_* constants.
	 */
	private static function apply_single( WC_Product $product, $parent = null ) {
		$status = self::apply_to_object( $product, $parent );

		if ( self::STATUS_UPDATED === $status ) {
			$product->save();
		} elseif ( self::STATUS_UNCHANGED === $status && $product->get_id() ) {
			// Keep the bookkeeping fresh without a full save (which would clear caches).
			$rate = Rates::get_rate();
			update_post_meta( $product->get_id(), self::META_SYNCED_RATE, wc_format_decimal( $rate ) );
			update_post_meta( $product->get_id(), self::META_SYNCED_AT, time() );
		}

		return $status;
	}

	/**
	 * Set calculated prices on a product object without saving it.
	 *
	 * Used while WooCommerce is already saving the product, so it is saved once.
	 *
	 * @param WC_Product      $product Simple, external product or variation.
	 * @param WC_Product|null $parent  Parent of a variation.
	 * @return string One of the STATUS_* constants.
	 */
	public static function apply_to_object( WC_Product $product, $parent = null ) {
		if ( ! self::is_foreign( $product, $parent ) ) {
			return self::STATUS_MANUAL;
		}

		$rate = Rates::get_rate();

		if ( ! $rate ) {
			return self::STATUS_NO_RATE;
		}

		$prices = self::calculate( $product, $parent );

		if ( null === $prices ) {
			// No foreign price: leave the product's current price untouched.
			return self::STATUS_MISSING;
		}

		$decimals = wc_get_price_decimals();
		$regular  = wc_format_decimal( $prices['regular'], $decimals );
		$sale     = null === $prices['sale'] ? '' : wc_format_decimal( $prices['sale'], $decimals );

		if ( self::same_price( $product->get_regular_price( 'edit' ), $regular ) && self::same_price( $product->get_sale_price( 'edit' ), $sale ) ) {
			return self::STATUS_UNCHANGED;
		}

		$product->set_regular_price( $regular );
		$product->set_sale_price( $sale );
		$product->update_meta_data( self::META_SYNCED_RATE, wc_format_decimal( $rate ) );
		$product->update_meta_data( self::META_SYNCED_AT, time() );

		return self::STATUS_UPDATED;
	}

	/**
	 * Compare two stored prices, treating '' as "no price".
	 *
	 * @param string $a Price.
	 * @param string $b Price.
	 * @return bool
	 */
	private static function same_price( $a, $b ) {
		if ( '' === (string) $a || '' === (string) $b ) {
			return '' === (string) $a && '' === (string) $b;
		}

		return abs( (float) $a - (float) $b ) < 0.0001;
	}

	/**
	 * Whether a foreign-priced product is missing its foreign price.
	 *
	 * @param WC_Product $product Product (not a variation).
	 * @return bool
	 */
	public static function is_missing_price( WC_Product $product ) {
		if ( ! $product->is_type( 'variable' ) ) {
			return '' === (string) $product->get_meta( self::META_REGULAR );
		}

		foreach ( $product->get_children() as $child_id ) {
			if ( '' === (string) get_post_meta( $child_id, self::META_REGULAR, true ) ) {
				return true;
			}
		}

		return false;
	}
}
