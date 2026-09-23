<?php
/**
 * Pay-for-order receipt page — theme override.
 *
 * Reached only via the "on checkout" redirect URL (WC_Order::get_checkout_payment_url(true))
 * that online gateways like SnappPay return from process_payment(). WooCommerce fires
 * woocommerce_receipt_{gateway_id} below, which is where the gateway renders its own
 * "pay" button/form or redirects straight to its hosted payment page.
 *
 * ob_start() below guarantees that later redirect (wp_redirect()) still works even if the
 * gateway echoes markup before calling it — otherwise it depends on the server's
 * output_buffering ini setting, which isn't guaranteed.
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

ob_start();

$tc_notices = array_merge( wc_get_notices( 'error' ), wc_get_notices( 'notice' ) );
if ( $tc_notices ) {
	wc_clear_notices();
}
?>
<div class="tc-checkout" dir="rtl">

	<?php foreach ( $tc_notices as $tc_notice ) : ?>
		<p class="tc-form-msg is-error"><?php echo wp_kses_post( $tc_notice['notice'] ); ?></p>
	<?php endforeach; ?>

	<h2 class="tc-section-title bg-black-03 color-black-80">تکمیل پرداخت</h2>

	<div class="tc-invoice">
		<div class="tc-invoice-info">
			<div class="tc-invoice-info__group">
				<div class="tc-invoice-info__row"><strong>شماره سفارش :</strong><span><?php echo esc_html( $order->get_order_number() ); ?></span></div>
				<div class="tc-invoice-info__row"><strong>مبلغ قابل پرداخت :</strong><span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></div>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<div class="tc-invoice-info__row"><strong>روش پرداخت :</strong><span><?php echo esc_html( $order->get_payment_method_title() ); ?></span></div>
				<?php endif; ?>
			</div>
		</div>

		<table class="tc-invoice-items">
			<thead>
				<tr><th>محصول</th><th style="text-align:center">مقدار</th><th>قیمت کل</th></tr>
			</thead>
			<tbody>
				<?php foreach ( $order->get_items() as $item ) : ?>
					<tr>
						<td data-label="نام محصول"><?php echo esc_html( $item->get_name() ); ?></td>
						<td data-label="مقدار"><?php echo esc_html( $item->get_quantity() ); ?></td>
						<td data-label="قیمت کل"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="tc-checkout__footer">
		<?php
		/**
		 * The payment gateway (e.g. WC_Gateway_SnappPay::process_payment_request())
		 * hooks in here to render its own "pay" button/form or redirect immediately.
		 */
		do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() );
		?>
	</div>

</div>
<?php
ob_end_flush();
