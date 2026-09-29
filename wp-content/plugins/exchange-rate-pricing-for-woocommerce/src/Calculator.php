<?php
/**
 * Price calculation.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

defined( 'ABSPATH' ) || exit;

/**
 * Turns foreign prices into store prices. Has no WordPress dependencies so it can be unit tested.
 *
 * Formula: round( foreign × rate × (1 + markup% / 100) + fixed markup ).
 */
final class Calculator {

	/**
	 * Calculate store prices.
	 *
	 * @param array $args {
	 *     @type float|null $regular         Foreign regular price.
	 *     @type float|null $sale            Foreign sale price.
	 *     @type float|null $sale_percent    Discount percent applied to the regular price.
	 *     @type float      $rate            Store currency units per foreign unit.
	 *     @type float      $markup_percent  Markup percent.
	 *     @type float      $markup_fixed    Fixed markup in store currency.
	 *     @type float      $rounding_step   Round to multiples of this, 0 for none.
	 *     @type string     $rounding_mode   up, down or nearest.
	 *     @type string     $sale_mode       price, percent or both.
	 *     @type int        $decimals        Store price decimals.
	 * }
	 * @return array|null { regular: float, sale: float|null }, null when there is nothing to price.
	 */
	public static function calculate( array $args ) {
		$args = array_merge(
			array(
				'regular'        => null,
				'sale'           => null,
				'sale_percent'   => null,
				'rate'           => 0,
				'markup_percent' => 0,
				'markup_fixed'   => 0,
				'rounding_step'  => 0,
				'rounding_mode'  => 'up',
				'sale_mode'      => 'both',
				'decimals'       => 0,
			),
			$args
		);

		$regular = (float) $args['regular'];

		if ( $regular <= 0 || (float) $args['rate'] <= 0 ) {
			return null;
		}

		$regular_price = self::convert( $regular, $args );
		$sale_price    = null;
		$sale          = (float) $args['sale'];
		$percent       = (float) $args['sale_percent'];
		$allow_price   = in_array( $args['sale_mode'], array( 'price', 'both' ), true );
		$allow_percent = in_array( $args['sale_mode'], array( 'percent', 'both' ), true );

		if ( $allow_price && $sale > 0 ) {
			$sale_price = self::convert( $sale, $args );
		} elseif ( $allow_percent && $percent > 0 && $percent < 100 ) {
			// Applied to the rounded regular price so the discount customers see is exact.
			$sale_price = self::round( $regular_price * ( 1 - $percent / 100 ), $args );
		}

		if ( null !== $sale_price && ( $sale_price <= 0 || $sale_price >= $regular_price ) ) {
			$sale_price = null;
		}

		if ( $regular_price <= 0 ) {
			return null;
		}

		return array(
			'regular' => $regular_price,
			'sale'    => $sale_price,
		);
	}

	/**
	 * Convert one foreign amount with markup and rounding.
	 *
	 * @param float $amount Foreign amount.
	 * @param array $args   See calculate().
	 * @return float
	 */
	public static function convert( $amount, array $args ) {
		$price = (float) $amount * (float) $args['rate'];
		$price = $price * ( 1 + (float) $args['markup_percent'] / 100 ) + (float) $args['markup_fixed'];

		return self::round( max( 0, $price ), $args );
	}

	/**
	 * Round a store price to the configured step and decimals.
	 *
	 * @param float $price Price.
	 * @param array $args  See calculate().
	 * @return float
	 */
	public static function round( $price, array $args ) {
		$step = abs( (float) $args['rounding_step'] );

		if ( $step > 0 ) {
			// Rounding to 6 places first removes float noise such as 4840.0000000001.
			$units = round( $price / $step, 6 );

			switch ( $args['rounding_mode'] ) {
				case 'down':
					$units = floor( $units );
					break;
				case 'nearest':
					$units = round( $units );
					break;
				default:
					$units = ceil( $units );
			}

			$price = $units * $step;
		}

		return round( $price, max( 0, (int) $args['decimals'] ) );
	}
}
