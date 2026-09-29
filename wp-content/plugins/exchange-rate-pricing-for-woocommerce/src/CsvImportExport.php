<?php
/**
 * WooCommerce product CSV importer/exporter columns.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the exchange rate pricing fields to WooCommerce's product CSV tools.
 *
 * Amounts use the same units as WooCommerce's own price columns: base prices in the
 * base currency, fixed markup in the store currency.
 */
final class CsvImportExport {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'export_columns' ) );
		add_filter( 'woocommerce_product_export_product_default_columns', array( __CLASS__, 'export_columns' ) );

		foreach ( array_keys( self::columns() ) as $column ) {
			add_filter( 'woocommerce_product_export_product_column_' . $column, array( __CLASS__, 'export_value' ), 10, 3 );
		}

		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'mapping_options' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'default_mapping' ) );
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'import_values' ), 10, 2 );
		add_action( 'woocommerce_product_import_inserted_product_object', array( __CLASS__, 'after_import' ), 10, 2 );
	}

	/**
	 * Column ids mapped to meta keys.
	 *
	 * @return array
	 */
	private static function columns() {
		return array(
			'erpfw_mode'           => Pricer::META_MODE,
			'erpfw_regular_price'  => Pricer::META_REGULAR,
			'erpfw_sale_price'     => Pricer::META_SALE,
			'erpfw_sale_percent'   => Pricer::META_SALE_PERCENT,
			'erpfw_markup_percent' => Pricer::META_MARKUP_PERCENT,
			'erpfw_markup_fixed'   => Pricer::META_MARKUP_FIXED,
		);
	}

	/**
	 * Column labels. These are also the CSV headers used for automatic mapping.
	 *
	 * @return array
	 */
	private static function labels() {
		return array(
			'erpfw_mode'           => __( 'Exchange rate pricing mode', 'exchange-rate-pricing-for-woocommerce' ),
			'erpfw_regular_price'  => __( 'Base regular price', 'exchange-rate-pricing-for-woocommerce' ),
			'erpfw_sale_price'     => __( 'Base sale price', 'exchange-rate-pricing-for-woocommerce' ),
			'erpfw_sale_percent'   => __( 'Base sale percent', 'exchange-rate-pricing-for-woocommerce' ),
			'erpfw_markup_percent' => __( 'Markup percent', 'exchange-rate-pricing-for-woocommerce' ),
			'erpfw_markup_fixed'   => __( 'Fixed markup', 'exchange-rate-pricing-for-woocommerce' ),
		);
	}

	/**
	 * Add export columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function export_columns( $columns ) {
		return array_merge( $columns, self::labels() );
	}

	/**
	 * Export a column value.
	 *
	 * @param mixed      $value   Default value.
	 * @param WC_Product $product Product.
	 * @param string     $column  Column id.
	 * @return string
	 */
	public static function export_value( $value, $product, $column ) {
		$columns = self::columns();

		if ( ! $product instanceof WC_Product || ! isset( $columns[ $column ] ) ) {
			return $value;
		}

		return (string) $product->get_meta( $columns[ $column ] );
	}

	/**
	 * Add importer mapping options.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public static function mapping_options( $options ) {
		return array_merge( $options, self::labels() );
	}

	/**
	 * Map CSV headers to our columns automatically (English and translated headers).
	 *
	 * @param array $columns Header => column id.
	 * @return array
	 */
	public static function default_mapping( $columns ) {
		foreach ( self::labels() as $id => $label ) {
			$columns[ $label ] = $id;
			$columns[ $id ]    = $id;
		}

		return $columns;
	}

	/**
	 * Copy imported values into product meta.
	 *
	 * @param WC_Product $product Product being imported.
	 * @param array      $data    Parsed row.
	 * @return WC_Product
	 */
	public static function import_values( $product, $data ) {
		if ( ! $product instanceof WC_Product ) {
			return $product;
		}

		foreach ( self::columns() as $column => $meta_key ) {
			if ( ! array_key_exists( $column, $data ) ) {
				continue;
			}

			if ( 'erpfw_mode' === $column ) {
				$mode = sanitize_key( (string) $data[ $column ] );
				$product->update_meta_data( $meta_key, in_array( $mode, array( Pricer::MODE_FOREIGN, Pricer::MODE_MANUAL ), true ) ? $mode : Pricer::MODE_DEFAULT );
				continue;
			}

			$product->update_meta_data( $meta_key, Format::parse_number( $data[ $column ] ) );
		}

		return $product;
	}

	/**
	 * Recalculate prices after a product was imported.
	 *
	 * @param WC_Product $product Imported product.
	 * @param array      $data    Parsed row.
	 */
	public static function after_import( $product, $data ) {
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$touched = array_intersect( array_keys( self::columns() ), array_keys( (array) $data ) );

		if ( $touched && ( $product->is_type( 'variation' ) || Pricer::is_foreign( $product ) ) ) {
			Pricer::apply( $product );
		}
	}
}
