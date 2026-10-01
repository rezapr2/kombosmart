<?php
/**
 * WP-CLI commands.
 *
 * @package RateMint
 */

namespace RateMint;

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
				/* translators: %s: currency code such as USD. */
				WP_CLI::error( sprintf( __( 'No rate set for %s.', 'ratemint-exchange-rate-pricing' ), $currency ) );
			}

			WP_CLI::log(
				sprintf(
					/* translators: 1: exchange rate such as "1 USD = 100,000 Toman", 2: time span such as "2 hours". */
					__( '%1$s (updated %2$s ago)', 'ratemint-exchange-rate-pricing' ),
					Format::rate( $entry['rate'], $currency ),
					human_time_diff( (int) $entry['updated_at'] )
				)
			);
			return;
		}

		$value = Format::parse_float( $args[0] );

		if ( null === $value ) {
			WP_CLI::error( __( 'The rate must be a number.', 'ratemint-exchange-rate-pricing' ) );
		}

		$result = Rates::set( $currency, Settings::to_store( $value ), 'cli', get_current_user_id() );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success(
			sprintf(
				/* translators: 1: exchange rate such as "1 USD = 100,000 Toman", 2: result message. */
				__( '%1$s. %2$s', 'ratemint-exchange-rate-pricing' ),
				Format::rate( Settings::to_store( $value ), $currency ),
				$result ? __( 'Recalculation scheduled.', 'ratemint-exchange-rate-pricing' ) : __( 'Rate unchanged.', 'ratemint-exchange-rate-pricing' )
			)
		);
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
			WP_CLI::error( __( 'Set the exchange rate first.', 'ratemint-exchange-rate-pricing' ) );
		}

		if ( empty( $assoc_args['now'] ) ) {
			/* translators: %d: number of products. */
			WP_CLI::success( sprintf( _n( 'Scheduled recalculation of %d product.', 'Scheduled recalculation of %d products.', (int) $state['total'], 'ratemint-exchange-rate-pricing' ), (int) $state['total'] ) );
			return;
		}

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Recalculating', 'ratemint-exchange-rate-pricing' ), max( 1, (int) $state['total'] ) );
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

		/* translators: %s: currency code such as USD. */
		WP_CLI::log( $entry ? Format::rate( $entry['rate'] ) : sprintf( __( 'No rate set for %s.', 'ratemint-exchange-rate-pricing' ), Settings::base_currency() ) );
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
				/* translators: 1: run status, 2: products processed, 3: total products, 4: products updated, 5: products unchanged, 6: products without a base price. */
				__( 'Recalculation: %1$s. %2$d of %3$d processed, %4$d updated, %5$d unchanged, %6$d missing a base price.', 'ratemint-exchange-rate-pricing' ),
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
