<?php
/**
 * WooCommerce → Settings → Exchange rates tab.
 *
 * @package ExchangeRatePricing
 */

namespace ExchangeRatePricing\Admin;

use ExchangeRatePricing\Format;
use ExchangeRatePricing\RateProviders\Registry;
use ExchangeRatePricing\Rates;
use ExchangeRatePricing\Recalculator;
use ExchangeRatePricing\Settings;
use WC_Admin_Settings;
use WC_Settings_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Settings tab built on the WooCommerce settings API.
 */
class SettingsPage extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'erpfw';
		$this->label = __( 'Exchange rates', 'exchange-rate-pricing-for-woocommerce' );

		parent::__construct();

		add_action( 'woocommerce_admin_field_erpfw_rate', array( $this, 'output_rate_field' ) );
		add_action( 'woocommerce_admin_field_erpfw_amount', array( $this, 'output_amount_field' ) );
		add_action( 'woocommerce_admin_field_erpfw_recalc', array( $this, 'output_recalc_field' ) );
		add_action( 'woocommerce_admin_field_erpfw_history', array( $this, 'output_history_field' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize' ), 10, 3 );
	}

	/**
	 * Field id inside the settings array option.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private function field( $key ) {
		return Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Settings fields.
	 *
	 * @return array
	 */
	protected function get_settings_for_default_section() {
		$base       = Settings::base_currency();
		$currencies = array();

		foreach ( get_woocommerce_currencies() as $code => $name ) {
			if ( get_woocommerce_currency() !== $code ) {
				$currencies[ $code ] = sprintf( '%1$s (%2$s)', $name, $code );
			}
		}

		$settings = array(
			array(
				'title' => __( 'Exchange rate', 'exchange-rate-pricing-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => __( 'Products priced by exchange rate get their store price from a base currency price, this rate, your markup and rounding. Prices are saved as normal WooCommerce prices, so sorting, filters, carts and payment gateways work as usual.', 'exchange-rate-pricing-for-woocommerce' ),
				'id'    => 'erpfw_rate_section',
			),
			array(
				'title'    => __( 'Base currency', 'exchange-rate-pricing-for-woocommerce' ),
				'id'       => $this->field( 'base_currency' ),
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => $currencies,
				'default'  => 'USD',
				'desc_tip' => __( 'The currency you enter product prices in.', 'exchange-rate-pricing-for-woocommerce' ),
			),
		);

		if ( Settings::supports_toman() ) {
			$settings[] = array(
				'title'   => __( 'Enter amounts in', 'exchange-rate-pricing-for-woocommerce' ),
				'id'      => $this->field( 'amount_unit' ),
				'type'    => 'select',
				'options' => array(
					'toman' => __( 'Toman', 'exchange-rate-pricing-for-woocommerce' ),
					'store' => __( 'Rial (store currency)', 'exchange-rate-pricing-for-woocommerce' ),
				),
				'default' => 'toman',
				'desc'    => __( 'Used for the rate, fixed markup and rounding step. Prices are always saved in Rial.', 'exchange-rate-pricing-for-woocommerce' ),
			);
		}

		$settings = array_merge(
			$settings,
			array(
				array(
					'title'    => __( 'Rate source', 'exchange-rate-pricing-for-woocommerce' ),
					'id'       => $this->field( 'rate_provider' ),
					'type'     => 'select',
					'options'  => Registry::options(),
					'default'  => 'manual',
					'desc_tip' => __( 'Where the exchange rate comes from. Extensions can add automatic sources.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'     => __( 'Current rate', 'exchange-rate-pricing-for-woocommerce' ),
					'id'        => 'erpfw_rate',
					'type'      => 'erpfw_rate',
					'is_option' => false,
				),
				array(
					'title'             => __( 'Outdated rate warning', 'exchange-rate-pricing-for-woocommerce' ),
					'id'                => $this->field( 'stale_days' ),
					'type'              => 'number',
					'default'           => 3,
					'css'               => 'width:80px',
					'custom_attributes' => array(
						'min'  => 0,
						'step' => 1,
					),
					'desc'              => __( 'Warn in the admin when the rate is older than this many days. 0 turns the warning off.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'   => __( 'Admin bar', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'show_admin_bar' ),
					'type'    => 'checkbox',
					'default' => 'yes',
					'desc'    => __( 'Show the rate in the admin bar with a quick update form', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_rate_section',
				),

				array(
					'title' => __( 'Products', 'exchange-rate-pricing-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'erpfw_products_section',
				),
				array(
					'title'   => __( 'Default pricing', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'default_mode' ),
					'type'    => 'select',
					'options' => array(
						'manual'  => __( 'Manual price: products opt in one by one', 'exchange-rate-pricing-for-woocommerce' ),
						/* translators: %s: currency code such as USD. */
						'foreign' => sprintf( __( 'Exchange rate: all products are priced in %s', 'exchange-rate-pricing-for-woocommerce' ), $base ),
					),
					'default' => 'manual',
					'desc'    => __( 'Applies to products set to "Store default". Each product can override it.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'   => __( 'Sale prices', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'sale_mode' ),
					'type'    => 'select',
					'options' => array(
						'both'    => __( 'Base currency sale price or discount percent', 'exchange-rate-pricing-for-woocommerce' ),
						'price'   => __( 'Base currency sale price only', 'exchange-rate-pricing-for-woocommerce' ),
						'percent' => __( 'Discount percent only', 'exchange-rate-pricing-for-woocommerce' ),
					),
					'default' => 'both',
					'desc'    => __( 'When a product has both, the sale price wins.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_products_section',
				),

				array(
					'title' => __( 'Markup', 'exchange-rate-pricing-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Added on top of the converted price. Product categories and single products can override these values; leave their fields empty to inherit.', 'exchange-rate-pricing-for-woocommerce' ),
					'id'    => 'erpfw_markup_section',
				),
				array(
					'title'             => __( 'Markup percent', 'exchange-rate-pricing-for-woocommerce' ),
					'id'                => $this->field( 'markup_percent' ),
					'type'              => 'text',
					'default'           => '0',
					'css'               => 'width:80px',
					'custom_attributes' => array( 'inputmode' => 'decimal' ),
					'desc'              => '%',
				),
				array(
					'title'   => __( 'Fixed markup', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'markup_fixed' ),
					'type'    => 'erpfw_amount',
					'default' => '0',
					'desc'    => __( 'Added after the percent markup.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'   => __( 'Products in several categories', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'category_conflict' ),
					'type'    => 'select',
					'options' => array(
						'highest' => __( 'Use the highest category markup', 'exchange-rate-pricing-for-woocommerce' ),
						'lowest'  => __( 'Use the lowest category markup', 'exchange-rate-pricing-for-woocommerce' ),
					),
					'default' => 'highest',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_markup_section',
				),

				array(
					'title' => __( 'Rounding', 'exchange-rate-pricing-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'erpfw_rounding_section',
				),
				array(
					'title'   => __( 'Round to multiples of', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'rounding_step' ),
					'type'    => 'erpfw_amount',
					'default' => '0',
					'desc'    => __( 'For example 10000 turns 48,372,500 into 48,380,000. 0 turns rounding off.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'   => __( 'Rounding direction', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'rounding_mode' ),
					'type'    => 'select',
					'options' => array(
						'up'      => __( 'Up', 'exchange-rate-pricing-for-woocommerce' ),
						'nearest' => __( 'Nearest', 'exchange-rate-pricing-for-woocommerce' ),
						'down'    => __( 'Down', 'exchange-rate-pricing-for-woocommerce' ),
					),
					'default' => 'up',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_rounding_section',
				),

				array(
					'title' => __( 'Recalculation', 'exchange-rate-pricing-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Prices are recalculated automatically in the background whenever the rate, these settings or a category markup change.', 'exchange-rate-pricing-for-woocommerce' ),
					'id'    => 'erpfw_recalc_section',
				),
				array(
					'title'     => __( 'Status', 'exchange-rate-pricing-for-woocommerce' ),
					'id'        => 'erpfw_recalc',
					'type'      => 'erpfw_recalc',
					'is_option' => false,
				),
				array(
					'title'             => __( 'Batch size', 'exchange-rate-pricing-for-woocommerce' ),
					'id'                => $this->field( 'batch_size' ),
					'type'              => 'number',
					'default'           => 50,
					'css'               => 'width:80px',
					'custom_attributes' => array(
						'min'  => 5,
						'max'  => 500,
						'step' => 1,
					),
					'desc_tip'          => __( 'Products processed per background step. Lower it on slow hosting.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'title'     => __( 'Rate history', 'exchange-rate-pricing-for-woocommerce' ),
					'id'        => 'erpfw_history',
					'type'      => 'erpfw_history',
					'is_option' => false,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_recalc_section',
				),

				array(
					'title' => __( 'Uninstall', 'exchange-rate-pricing-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'erpfw_uninstall_section',
				),
				array(
					'title'   => __( 'Remove data', 'exchange-rate-pricing-for-woocommerce' ),
					'id'      => $this->field( 'delete_data' ),
					'type'    => 'checkbox',
					'default' => 'no',
					'desc'    => __( 'Delete settings, rate history and base prices when the plugin is deleted. Store prices and order records are kept.', 'exchange-rate-pricing-for-woocommerce' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'erpfw_uninstall_section',
				),
			)
		);

		return $settings;
	}

	/**
	 * Save the rate (not a regular option) before the other settings.
	 *
	 * Posted amounts are read with the unit the form was shown in, i.e. the saved unit.
	 */
	public function save() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WC_Admin_Settings::save() verified the nonce.
		if ( isset( $_POST['erpfw_rate'] ) && current_user_can( Settings::capability() ) ) {
			$raw      = sanitize_text_field( wp_unslash( $_POST['erpfw_rate'] ) );
			$value    = Format::parse_float( $raw );
			$currency = isset( $_POST['erpfw_rate_currency'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['erpfw_rate_currency'] ) ) ) : Settings::base_currency();

			$current = Rates::get_rate( $currency );
			$changed = null !== $value && ( null === $current || abs( $current - Settings::to_store( $value ) ) > 0.000001 );

			// Only save a changed rate, so saving other settings doesn't reset the "updated" time.
			if ( $changed ) {
				$result = Rates::set( $currency, Settings::to_store( $value ), 'manual', get_current_user_id() );

				if ( is_wp_error( $result ) ) {
					WC_Admin_Settings::add_error( $result->get_error_message() );
				}
			} elseif ( '' !== $raw ) {
				WC_Admin_Settings::add_error( __( 'The exchange rate must be a number greater than zero.', 'exchange-rate-pricing-for-woocommerce' ) );
			}
		}
		// phpcs:enable

		parent::save();
	}

	/**
	 * Sanitize numeric settings and convert amounts to the store currency.
	 *
	 * @param mixed $value     Value after WooCommerce's sanitizing.
	 * @param array $option    Field definition.
	 * @param mixed $raw_value Posted value.
	 * @return mixed
	 */
	public function sanitize( $value, $option, $raw_value ) {
		$prefix = Settings::OPTION . '[';

		if ( empty( $option['id'] ) || 0 !== strpos( $option['id'], $prefix ) ) {
			return $value;
		}

		$key    = substr( $option['id'], strlen( $prefix ), -1 );
		$number = Format::parse_float( $raw_value );

		switch ( $key ) {
			case 'markup_percent':
				return wc_format_decimal( max( -99, (float) $number ) );

			case 'markup_fixed':
				return wc_format_decimal( Settings::to_store( (float) $number ) );

			case 'rounding_step':
				return wc_format_decimal( Settings::to_store( abs( (float) $number ) ) );

			case 'stale_days':
				return absint( $number );

			case 'batch_size':
				return max( 5, min( 500, absint( $number ) ) );
		}

		return $value;
	}

	/**
	 * Table row opening markup shared by custom fields.
	 *
	 * @param array  $field Field definition.
	 * @param string $for   Input id the label points to.
	 */
	private function row_start( array $field, $for = '' ) {
		echo '<tr valign="top"><th scope="row" class="titledesc">';

		if ( $for ) {
			printf( '<label for="%1$s">%2$s</label>', esc_attr( $for ), esc_html( $field['title'] ) );
		} else {
			echo esc_html( $field['title'] );
		}

		echo '</th><td class="forminp">';
	}

	/**
	 * Current rate input.
	 *
	 * @param array $field Field definition.
	 */
	public function output_rate_field( $field ) {
		$currency = Settings::base_currency();
		$entry    = Rates::get( $currency );
		$value    = $entry ? wc_format_localized_decimal( Settings::to_display( $entry['rate'] ) ) : '';
		/* translators: %s: currency code such as USD. */
		$prefix = sprintf( __( '1 %s =', 'exchange-rate-pricing-for-woocommerce' ), $currency );

		$this->row_start( $field, 'erpfw_rate' );
		?>
		<span class="erpfw-rate-prefix"><?php echo esc_html( $prefix ); ?></span>
		<input type="text" inputmode="decimal" name="erpfw_rate" id="erpfw_rate" value="<?php echo esc_attr( $value ); ?>" style="width:160px" />
		<span><?php echo esc_html( Settings::unit_label() ); ?></span>
		<input type="hidden" name="erpfw_rate_currency" value="<?php echo esc_attr( $currency ); ?>" />
		<p class="description">
			<?php
			if ( $entry ) {
				$user = ! empty( $entry['user_id'] ) ? get_userdata( (int) $entry['user_id'] ) : false;

				echo esc_html(
					$user
						/* translators: 1: time span such as "2 hours", 2: user name. */
						? sprintf( __( 'Updated %1$s ago by %2$s.', 'exchange-rate-pricing-for-woocommerce' ), human_time_diff( (int) $entry['updated_at'] ), $user->display_name )
						/* translators: %s: time span such as "2 hours". */
						: sprintf( __( 'Updated %s ago.', 'exchange-rate-pricing-for-woocommerce' ), human_time_diff( (int) $entry['updated_at'] ) )
				);
			} else {
				esc_html_e( 'No rate set yet. Prices are only calculated once a rate is saved.', 'exchange-rate-pricing-for-woocommerce' );
			}
			?>
		</p>
		</td></tr>
		<?php
	}

	/**
	 * Amount input shown in the entry unit (e.g. Toman) and saved in the store currency.
	 *
	 * @param array $field Field definition.
	 */
	public function output_amount_field( $field ) {
		$value = wc_format_localized_decimal( Settings::to_display( (float) $field['value'] ) );

		$this->row_start( $field, $field['id'] );
		?>
		<input type="text" inputmode="decimal" name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" style="width:160px" />
		<span><?php echo esc_html( Settings::unit_label() ); ?></span>
		<?php if ( ! empty( $field['desc'] ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $field['desc'] ); ?></p>
		<?php endif; ?>
		</td></tr>
		<?php
	}

	/**
	 * Recalculation status with a "recalculate now" button.
	 *
	 * @param array $field Field definition.
	 */
	public function output_recalc_field( $field ) {
		$state = Recalculator::state();
		$total = max( 1, (int) $state['total'] );

		$this->row_start( $field );
		?>
		<div class="erpfw-recalc" data-status="<?php echo esc_attr( $state['status'] ); ?>">
			<p class="erpfw-recalc__summary"><?php echo esc_html( Admin::recalc_summary( $state ) ); ?></p>
			<div class="erpfw-progress" <?php echo 'running' === $state['status'] ? '' : 'hidden'; ?>>
				<div class="erpfw-progress__bar" style="width:<?php echo esc_attr( (string) floor( 100 * (int) $state['processed'] / $total ) ); ?>%"></div>
			</div>
			<button type="button" class="button erpfw-recalc__start"><?php esc_html_e( 'Recalculate all prices now', 'exchange-rate-pricing-for-woocommerce' ); ?></button>
		</div>
		</td></tr>
		<?php
	}

	/**
	 * Recent rate changes.
	 *
	 * @param array $field Field definition.
	 */
	public function output_history_field( $field ) {
		$rows      = Rates::history( '', 15 );
		$providers = Registry::all();

		$this->row_start( $field );

		if ( ! $rows ) {
			echo '<p>' . esc_html__( 'No rate changes yet.', 'exchange-rate-pricing-for-woocommerce' ) . '</p></td></tr>';
			return;
		}
		?>
		<table class="widefat striped erpfw-history">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'exchange-rate-pricing-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Rate', 'exchange-rate-pricing-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Change', 'exchange-rate-pricing-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Source', 'exchange-rate-pricing-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'User', 'exchange-rate-pricing-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$change = '—';

					if ( null !== $row['previous_rate'] && (float) $row['previous_rate'] > 0 ) {
						$percent = ( (float) $row['rate'] - (float) $row['previous_rate'] ) / (float) $row['previous_rate'] * 100;
						$change  = ( $percent >= 0 ? '+' : '−' ) . number_format_i18n( abs( $percent ), 2 ) . '%';
					}

					$user   = $row['user_id'] ? get_userdata( (int) $row['user_id'] ) : false;
					$source = isset( $providers[ $row['source'] ] ) ? $providers[ $row['source'] ]->get_label() : ( 'cli' === $row['source'] ? 'WP-CLI' : $row['source'] );
					?>
					<tr>
						<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row['created_at'] . ' UTC' ) ) ); ?></td>
						<td><?php echo esc_html( Format::store_amount( $row['rate'] ) ); ?></td>
						<td dir="ltr"><?php echo esc_html( $change ); ?></td>
						<td><?php echo esc_html( $source ); ?></td>
						<td><?php echo esc_html( $user ? $user->display_name : '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</td></tr>
		<?php
	}
}
