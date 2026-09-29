<?php
/**
 * Background recalculation of all exchange-rate priced products.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing;

defined( 'ABSPATH' ) || exit;

/**
 * Walks every exchange-rate priced product in batches with Action Scheduler.
 *
 * Each run has a generation number. Starting a new run (for example a second rate
 * change) bumps the generation, so batches from the older run stop by themselves.
 */
final class Recalculator {

	const HOOK  = 'erpfw_recalculate_batch';
	const GROUP = 'erpfw';
	const STATE = 'erpfw_recalc_state';
	const LOCK  = 'erpfw_recalc_lock';

	/**
	 * Register the Action Scheduler callback.
	 */
	public static function init() {
		add_action( self::HOOK, array( __CLASS__, 'handle_action' ) );
	}

	/**
	 * Current run state.
	 *
	 * @return array
	 */
	public static function state() {
		$state = get_option( self::STATE, array() );

		return array_merge(
			array(
				'generation'  => 0,
				'status'      => 'idle',
				'reason'      => '',
				'last_id'     => 0,
				'total'       => 0,
				'processed'   => 0,
				'updated'     => 0,
				'unchanged'   => 0,
				'missing'     => 0,
				'started_at'  => 0,
				'finished_at' => 0,
			),
			is_array( $state ) ? $state : array()
		);
	}

	/**
	 * Start a new recalculation run, replacing any run in progress.
	 *
	 * @param string $reason Why the run started, e.g. "rate" or "settings".
	 * @return array New state.
	 */
	public static function schedule( $reason = '' ) {
		$previous = self::state();
		$state    = array(
			'generation'  => (int) $previous['generation'] + 1,
			'status'      => Rates::get_rate() ? 'running' : 'no_rate',
			'reason'      => sanitize_key( $reason ),
			'last_id'     => 0,
			'total'       => self::count_products(),
			'processed'   => 0,
			'updated'     => 0,
			'unchanged'   => 0,
			'missing'     => 0,
			'started_at'  => time(),
			'finished_at' => 0,
		);

		update_option( self::STATE, $state, false );

		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::HOOK );
		}

		if ( 'running' === $state['status'] ) {
			if ( 0 === $state['total'] ) {
				self::finish( $state );
			} else {
				self::enqueue( $state['generation'] );
			}
		}

		return self::state();
	}

	/**
	 * Action Scheduler callback.
	 *
	 * @param int $generation Run the batch belongs to.
	 */
	public static function handle_action( $generation = 0 ) {
		self::process_batch( (int) $generation );
	}

	/**
	 * Process the next batch of the current run.
	 *
	 * Safe to call from several places at once (Action Scheduler, the settings page
	 * progress poll, WP-CLI): a lock makes sure only one batch runs at a time.
	 *
	 * @param int $generation Only process if this run is still current; 0 for any.
	 * @return array State after the batch.
	 */
	public static function process_batch( $generation = 0 ) {
		$state = self::state();

		if ( 'running' !== $state['status'] || ( $generation && (int) $state['generation'] !== $generation ) ) {
			return $state;
		}

		if ( ! self::lock() ) {
			return $state;
		}

		try {
			if ( ! Rates::get_rate() ) {
				$state['status'] = 'no_rate';
				update_option( self::STATE, $state, false );

				return $state;
			}

			$size = Settings::batch_size();
			$ids  = self::next_ids( (int) $state['last_id'], $size );

			foreach ( $ids as $id ) {
				$product = wc_get_product( $id );
				$status  = $product ? Pricer::apply( $product ) : Pricer::STATUS_UNSUPPORTED;

				++$state['processed'];

				if ( Pricer::STATUS_UPDATED === $status ) {
					++$state['updated'];
				} elseif ( Pricer::STATUS_MISSING === $status ) {
					++$state['missing'];
				} else {
					++$state['unchanged'];
				}

				$state['last_id'] = (int) $id;
			}

			// A newer run started while this batch was working: drop our results.
			$current = self::state();

			if ( (int) $current['generation'] !== (int) $state['generation'] ) {
				return $current;
			}

			if ( count( $ids ) < $size ) {
				self::finish( $state );
			} else {
				update_option( self::STATE, $state, false );
				self::enqueue( (int) $state['generation'] );
			}
		} finally {
			self::unlock();
		}

		return self::state();
	}

	/**
	 * Process every remaining batch right away (used by WP-CLI).
	 *
	 * @param callable|null $tick Called after each batch with the state.
	 * @return array Final state.
	 */
	public static function run_now( $tick = null ) {
		$state = self::state();

		while ( 'running' === $state['status'] ) {
			$before = (int) $state['processed'];
			$state  = self::process_batch();

			if ( $tick ) {
				call_user_func( $tick, $state );
			}

			if ( 'running' === $state['status'] && (int) $state['processed'] === $before ) {
				// Another process holds the lock; wait for it.
				sleep( 1 );
				$state = self::state();
			}
		}

		return $state;
	}

	/**
	 * Mark a run as finished.
	 *
	 * @param array $state Run state.
	 */
	private static function finish( array $state ) {
		$state['status']      = 'done';
		$state['finished_at'] = time();

		update_option( self::STATE, $state, false );

		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::HOOK );
		}

		/**
		 * Fires after all exchange-rate priced products were recalculated.
		 * Page cache plugins can hook in here to purge cached prices.
		 *
		 * @param array $state Run summary: total, updated, unchanged, missing.
		 */
		do_action( 'erpfw_prices_recalculated', $state );
	}

	/**
	 * Queue the next batch unless one is already waiting.
	 *
	 * @param int $generation Run number.
	 */
	private static function enqueue( $generation ) {
		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			return;
		}

		$pending = as_get_scheduled_actions(
			array(
				'hook'     => self::HOOK,
				'status'   => \ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 1,
			),
			'ids'
		);

		if ( ! $pending ) {
			as_enqueue_async_action( self::HOOK, array( (int) $generation ), self::GROUP );
		}
	}

	/**
	 * Whether products without a mode ("store default") are priced by exchange rate.
	 *
	 * @return int 1 or 0, used as a query parameter.
	 */
	private static function default_is_foreign() {
		return Pricer::MODE_FOREIGN === Settings::get( 'default_mode' ) ? 1 : 0;
	}

	/**
	 * Number of products the run will look at.
	 *
	 * @return int
	 */
	public static function count_products() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Counting products by one meta value; no cache needed.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} m ON ( m.post_id = p.ID AND m.meta_key = %s )
				WHERE p.post_type = 'product' AND p.post_status NOT IN ( 'trash', 'auto-draft' )
				AND ( m.meta_value = 'foreign' OR ( %d = 1 AND ( m.meta_value IS NULL OR m.meta_value = '' ) ) )",
				Pricer::META_MODE,
				self::default_is_foreign()
			)
		);
	}

	/**
	 * Next product ids after the given id.
	 *
	 * @param int $after_id Last processed id.
	 * @param int $limit    Batch size.
	 * @return int[]
	 */
	private static function next_ids( $after_id, $limit ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Keyset scan of products; results change during the run.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} m ON ( m.post_id = p.ID AND m.meta_key = %s )
				WHERE p.post_type = 'product' AND p.post_status NOT IN ( 'trash', 'auto-draft' ) AND p.ID > %d
				AND ( m.meta_value = 'foreign' OR ( %d = 1 AND ( m.meta_value IS NULL OR m.meta_value = '' ) ) )
				ORDER BY p.ID ASC LIMIT %d",
				Pricer::META_MODE,
				$after_id,
				self::default_is_foreign(),
				$limit
			)
		);

		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Take the batch lock. A lock older than five minutes is considered abandoned.
	 *
	 * @return bool
	 */
	private static function lock() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Atomic lock; the options API cannot do INSERT IGNORE.
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->options} ( option_name, option_value, autoload ) VALUES ( %s, %s, 'off' )",
				self::LOCK,
				time()
			)
		);

		if ( $inserted ) {
			return true;
		}

		$since = (int) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", self::LOCK ) );

		if ( $since && time() - $since > 5 * MINUTE_IN_SECONDS ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s", time(), self::LOCK ) );

			return true;
		}
		// phpcs:enable

		return false;
	}

	/**
	 * Release the batch lock.
	 */
	private static function unlock() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- See lock().
		$wpdb->delete( $wpdb->options, array( 'option_name' => self::LOCK ) );
	}
}
