<?php
/**
 * Rate provider registry.
 *
 * @package RateMint
 */

namespace RateMint\RateProviders;

defined( 'ABSPATH' ) || exit;

/**
 * Collects the available rate providers.
 */
final class Registry {

	/**
	 * All registered providers keyed by id.
	 *
	 * @return RateProviderInterface[]
	 */
	public static function all() {
		/**
		 * Filters the available exchange rate providers.
		 *
		 * @param RateProviderInterface[] $providers Provider instances.
		 */
		$providers = apply_filters( 'erpfw_rate_providers', array( new ManualProvider() ) );
		$keyed     = array();

		foreach ( (array) $providers as $provider ) {
			if ( $provider instanceof RateProviderInterface ) {
				$keyed[ $provider->get_id() ] = $provider;
			}
		}

		return $keyed;
	}

	/**
	 * A provider by id, falling back to manual entry.
	 *
	 * @param string $id Provider id.
	 * @return RateProviderInterface
	 */
	public static function get( $id ) {
		$providers = self::all();

		return isset( $providers[ $id ] ) ? $providers[ $id ] : new ManualProvider();
	}

	/**
	 * Provider labels keyed by id, for select fields.
	 *
	 * @return array
	 */
	public static function options() {
		$options = array();

		foreach ( self::all() as $id => $provider ) {
			$options[ $id ] = $provider->get_label();
		}

		return $options;
	}
}
