<?php
/**
 * WP-CLI commands.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

use WP_CLI;

defined( 'ABSPATH' ) || exit;

/**
 * Manage exchange rates and recalculate prices.
 *
 * ## EXAMPLES
 *
 *     wp erpfw rate
 *     wp erpfw rate 100000
 *     wp erpfw recalculate --now
 *     wp erpfw status
 */
final class CLI {

	/**
	 * Show or set the exchange rate.
	 *
	 * The rate uses the same unit as the settings page (for example Toman).
	 *
	 * ## OPTIONS
	 *
	 * [<rate>]
	 * : New rate. Leave out to show the current rate.
	 *
	 * [--currency=<code>]
	 * : Foreign currency code. Defaults to the base currency.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function rate( $args, $assoc_args ) {
		$currency = isset( $assoc_args['currency'] ) ? strtoupper( $assoc_args['currency'] ) : Settings::base_currency();

		if ( empty( $args ) ) {
			$entry = Rates::get( $currency );

			if ( ! $entry ) {
				WP_CLI::error( sprintf( 'No rate set for %s.', $currency ) );
			}

			WP_CLI::log( sprintf( '%s (updated %s ago)', Format::rate( $entry['rate'], $currency ), human_time_diff( (int) $entry['updated_at'] ) ) );
			return;
		}

		$value = Format::parse_float( $args[0] );

		if ( null === $value ) {
			WP_CLI::error( 'The rate must be a number.' );
		}

		$result = Rates::set( $currency, Settings::to_store( $value ), 'cli', get_current_user_id() );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( sprintf( '%s. %s', Format::rate( Settings::to_store( $value ), $currency ), $result ? 'Recalculation scheduled.' : 'Rate unchanged.' ) );
	}

	/**
	 * Recalculate all exchange-rate priced products.
	 *
	 * ## OPTIONS
	 *
	 * [--now]
	 * : Process every batch immediately instead of in the background.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function recalculate( $args, $assoc_args ) {
		$state = Recalculator::schedule( 'cli' );

		if ( 'no_rate' === $state['status'] ) {
			WP_CLI::error( 'Set an exchange rate first.' );
		}

		if ( empty( $assoc_args['now'] ) ) {
			WP_CLI::success( sprintf( 'Scheduled recalculation of %d products.', $state['total'] ) );
			return;
		}

		$progress = \WP_CLI\Utils\make_progress_bar( 'Recalculating', max( 1, (int) $state['total'] ) );
		$done     = 0;
		$state    = Recalculator::run_now(
			static function ( $current ) use ( $progress, &$done ) {
				$progress->tick( max( 0, (int) $current['processed'] - $done ) );
				$done = (int) $current['processed'];
			}
		);
		$progress->finish();

		$this->print_state( $state );
	}

	/**
	 * Show the current rate and the last recalculation.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function status( $args, $assoc_args ) {
		$entry = Rates::get();

		WP_CLI::log( $entry ? Format::rate( $entry['rate'] ) : sprintf( 'No rate set for %s.', Settings::base_currency() ) );
		$this->print_state( Recalculator::state() );
	}

	/**
	 * Print a recalculation summary.
	 *
	 * @param array $state Run state.
	 */
	private function print_state( array $state ) {
		WP_CLI::log(
			sprintf(
				'Recalculation: %s — %d/%d processed, %d updated, %d unchanged, %d missing a base price.',
				$state['status'],
				$state['processed'],
				$state['total'],
				$state['updated'],
				$state['unchanged'],
				$state['missing']
			)
		);
	}
}
