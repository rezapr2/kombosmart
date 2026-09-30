<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined('ABSPATH') || exit;

?>
<div
	class="cart_totals flex justify-between items-center <?php echo (WC()->customer->has_calculated_shipping()) ? 'calculated_shipping' : ''; ?>">

	<div class="yekan-28 color-black"><?php esc_html_e('مبلغ قابل پرداخت:', 'smartw'); ?></div>
	<div class="flex items-center gap-20">
		<div class="yekan-30 bold color-primary"><?php wc_cart_totals_order_total_html(); ?></div>
		<div class="wc-proceed-to-checkout bg-primary text-white yekan-24">
			<?php do_action('woocommerce_proceed_to_checkout'); ?>
		</div>
	</div>

</div>