<?php
/**
 * Number parsing and formatting helpers.
 *
 * @package RateMint
 */

namespace RateMint;

defined( 'ABSPATH' ) || exit;

/**
 * Parses user input and formats amounts without going through wc_price(), so themes
 * that filter store prices for display (for example Rial to Toman) cannot distort them.
 */
final class Format {

	/**
	 * Parse a user-typed number. Accepts Persian and Arabic digits and separators.
	 *
	 * @param mixed $raw Raw input.
	 * @return string Decimal string using "." as separator, or '' when empty/invalid.
	 */
	public static function parse_number( $raw ) {
		if ( is_int( $raw ) || is_float( $raw ) ) {
			return wc_format_decimal( $raw );
		}

		$value = trim( wp_unslash( (string) $raw ) );

		if ( '' === $value ) {
			return '';
		}

		$value = strtr(
			$value,
			array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'٫' => '.',
				'٬' => '',
				'،' => '',
			)
		);

		$value = wc_format_decimal( $value );

		return is_numeric( $value ) ? $value : '';
	}

	/**
	 * Parse a user-typed number into a float, or null when empty.
	 *
	 * @param mixed $raw Raw input.
	 * @return float|null
	 */
	public static function parse_float( $raw ) {
		$value = self::parse_number( $raw );

		return '' === $value ? null : (float) $value;
	}

	/**
	 * Currency symbol straight from WooCommerce's list, bypassing symbol filters.
	 *
	 * @param string $currency Currency code.
	 * @return string
	 */
	public static function currency_symbol( $currency ) {
		$symbols = get_woocommerce_currency_symbols();
		$symbol  = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency;

		return html_entity_decode( $symbol, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Format a number with the site locale, trimming useless decimals.
	 *
	 * @param float $amount   Amount.
	 * @param int   $decimals Maximum decimals.
	 * @return string
	 */
	public static function number( $amount, $decimals = 2 ) {
		$amount   = (float) $amount;
		$decimals = ( floor( $amount ) === $amount ) ? 0 : $decimals;

		return number_format_i18n( $amount, $decimals );
	}

	/**
	 * Format a store currency amount in the entry unit, e.g. "48,400,000 Toman".
	 *
	 * @param float $store_amount Amount in the store currency.
	 * @return string
	 */
	public static function store_amount( $store_amount ) {
		return sprintf(
			/* translators: 1: amount, 2: unit such as Toman or a currency symbol. */
			_x( '%1$s %2$s', 'amount with unit', 'ratemint-exchange-rate-pricing' ),
			self::number( Settings::to_display( $store_amount ), wc_get_price_decimals() ),
			Settings::unit_label()
		);
	}

	/**
	 * Format a foreign currency amount, e.g. "$499.99".
	 *
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code; defaults to the base currency.
	 * @return string
	 */
	public static function foreign( $amount, $currency = '' ) {
		$currency = $currency ? $currency : Settings::base_currency();

		return self::currency_symbol( $currency ) . self::number( $amount, 2 );
	}

	/**
	 * Describe a rate, e.g. "1 USD = 100,000 Toman".
	 *
	 * @param float  $rate     Rate in store currency.
	 * @param string $currency Currency code; defaults to the base currency.
	 * @return string
	 */
	public static function rate( $rate, $currency = '' ) {
		$currency = $currency ? $currency : Settings::base_currency();

		return sprintf(
			/* translators: 1: currency code such as USD, 2: amount with unit. */
			__( '1 %1$s = %2$s', 'ratemint-exchange-rate-pricing' ),
			$currency,
			self::store_amount( $rate )
		);
	}
}
