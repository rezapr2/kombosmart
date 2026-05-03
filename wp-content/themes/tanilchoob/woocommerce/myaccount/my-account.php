<?php
/**
 * My Account page — custom two-column layout.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
?>

<div class="wc-my-account">

	<p class="wc-my-account__greeting">
		خوش آمدی <?php echo esc_html( $current_user->display_name ); ?>! 👋
	</p>

	<div class="wc-my-account__body flex flex-row-reverse">

		<div class="wc-my-account__content woocommerce-MyAccount-content">
			<?php do_action( 'woocommerce_account_content' ); ?>
		</div>

		<div class="wc-my-account__sidebar">
			<?php do_action( 'woocommerce_account_navigation' ); ?>
		</div>

	</div>

</div>
