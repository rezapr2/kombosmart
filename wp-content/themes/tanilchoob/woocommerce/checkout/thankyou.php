<?php
/**
 * Thank you / order-received page — theme override.
 *
 * Reached after checkout for COD/BACS/cheque orders, and after a redirect-based
 * gateway (e.g. SnappPay) settles payment and calls $order->get_return_url().
 * Rebuilt with the same markup/classes as the custom checkout's step-4 invoice
 * panel (see woocommerce/checkout/form-checkout.php + checkout.js renderInvoice())
 * so customers land on a page that matches the rest of the flow instead of
 * WooCommerce's default unstyled template.
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

$template_uri = get_template_directory_uri();
?>
<div class="tc-checkout" dir="rtl">

<?php if ( ! $order ) : ?>

	<div class="tc-order-success flex flex-col gap-10">
		<div class="tc-order-success__msg text-center w-full">
			<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', 'سفارش شما دریافت شد.', false ) ); ?>
		</div>
	</div>

<?php else : ?>

	<?php ob_start(); ?>
	<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>
	<?php $tc_before_thankyou_html = ob_get_clean(); ?>
	<?php if ( trim( wp_strip_all_tags( $tc_before_thankyou_html ) ) !== '' ) : ?>
		<div class="tc-checkout-notices"><?php echo $tc_before_thankyou_html; // phpcs:ignore WordPress.Security.EscapeOutput -- gateway-provided notice markup ?></div>
	<?php endif; ?>

	<?php if ( $order->has_status( 'failed' ) ) : ?>

		<div class="tc-order-success flex flex-col gap-10">
			<p class="tc-form-msg is-error">متاسفانه سفارش شما پردازش نشد، زیرا بانک/درگاه پرداخت تراکنش را رد کرده است. لطفاً دوباره تلاش کنید.</p>
		</div>
		<div class="tc-invoice-actions flex gap-10">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="tc-btn tc-btn--primary">پرداخت مجدد</a>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="tc-btn tc-btn--outline-primary">پیگیری سفارش</a>
		</div>

	<?php else : ?>

		<div class="tc-order-success flex flex-col gap-10">
			<div class="tc-order-success__msg text-center w-full">سفارش شما با موفقیت ثبت شد</div>
			<div class="tc-order-success__num flex justify-between"><span class="flex-shrink-0">کد پیگیری سفارش:</span> <span><?php echo esc_html( $order->get_order_number() ); ?></span></div>
		</div>
		<div class="tc-order-support_msg color-primary text-center">
			سفارش شما در حال بررسی و تایید مدیرت فروش می باشد. برای پیگیری سفارش خود می توانید با کارشناسان فروش ما در ارتباط باشید. کارشناسان ما در 24 ساعت آینده برای تایید نهایی سفارش با شما تماس خواهند گرفت
		</div>
		<div class="tc-invoice-actions flex gap-10">
			<button class="tc-btn tc-btn--outline-primary" onclick="window.print()">دانلود پیش فاکتور</button>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="tc-btn tc-btn--primary">پیگیری سفارش</a>
		</div>
		<?php
		$preinvoice_description = get_field( 'preinvoice_description', 'option' );
		if ( $preinvoice_description ) :
			?>
			<div class="tc-invoice-preinvoice_description yekan-16 mt-30 text-center color-black-70">
				<?php echo $preinvoice_description; // phpcs:ignore WordPress.Security.EscapeOutput -- ACF-managed trusted content ?>
			</div>
		<?php endif; ?>

		<div class="tc-invoice" id="tc-invoice">
			<div class="tc-invoice-header">
				<div class="tc-invoice-header__title">پیش فاکتور فروش</div>
				<strong style="font-size:2rem;color:var(--color-primary)">تانیل چوب</strong>
			</div>
			<div class="tc-invoice-info">
				<div class="tc-invoice-info__group">
					<div class="tc-invoice-info__row"><strong>نام خریدار :</strong><span><?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></span></div>
					<div class="tc-invoice-info__row"><strong>شماره تماس :</strong><span><?php echo esc_html( $order->get_billing_phone() ); ?></span></div>
					<?php if ( $order->get_billing_postcode() ) : ?>
						<div class="tc-invoice-info__row"><strong>کد پستی :</strong><span><?php echo esc_html( $order->get_billing_postcode() ); ?></span></div>
					<?php endif; ?>
					<div class="tc-invoice-info__row"><strong>آدرس :</strong><span><?php echo esc_html( ( $order->get_billing_city() ? $order->get_billing_city() . '، ' : '' ) . $order->get_billing_address_1() ); ?></span></div>
					<?php $tc_nationalcode = $order->get_meta( '_tc_billing_nationalcode' ); ?>
					<?php if ( $tc_nationalcode ) : ?>
						<div class="tc-invoice-info__row"><strong>کد ملی :</strong><span><?php echo esc_html( $tc_nationalcode ); ?></span></div>
					<?php endif; ?>
				</div>
				<div class="tc-invoice-info__group">
					<div class="tc-invoice-info__row"><strong>شماره سفارش :</strong><span><?php echo esc_html( $order->get_order_number() ); ?></span></div>
					<div class="tc-invoice-info__row"><strong>تاریخ :</strong><span><?php echo esc_html( $order->get_date_created() ? \TanilChoob\Theme\Helper::jalali_date( $order->get_date_created()->getTimestamp() ) : '' ); ?></span></div>
					<?php if ( $order->get_payment_method_title() ) : ?>
						<div class="tc-invoice-info__row"><strong>روش پرداخت :</strong><span><?php echo esc_html( $order->get_payment_method_title() ); ?></span></div>
					<?php endif; ?>
					<?php $tc_notes = $order->get_meta( '_tc_payment_note' ); ?>
					<?php if ( $tc_notes ) : ?>
						<div class="tc-invoice-info__row"><strong>توضیحات :</strong><span><?php echo esc_html( $tc_notes ); ?></span></div>
					<?php endif; ?>
				</div>
			</div>

			<table class="tc-invoice-items">
				<thead>
					<tr><th>محصول</th><th style="text-align:center">مقدار</th><th>سفارش سازی ها</th><th>قیمت واحد</th><th>قیمت کل</th></tr>
				</thead>
				<tbody>
					<?php foreach ( $order->get_items() as $item ) : ?>
						<?php
						if ( ! $item instanceof WC_Order_Item_Product ) {
							continue;
						}
						$qty = $item->get_quantity();
						?>
						<tr>
							<td data-label="نام محصول"><?php echo esc_html( $item->get_name() ); ?></td>
							<td data-label="مقدار"><?php echo esc_html( $qty ); ?></td>
							<td class="flex-col" data-label="سفارش سازی ها">
								<?php
								$tc_has_meta = false;
								foreach ( $item->get_formatted_meta_data( '_', true ) as $meta ) {
									$tc_has_meta = true;
									?>
									<div class="tc-cart-table__meta"><span class="bold"><?php echo esc_html( wp_strip_all_tags( $meta->display_key ) ); ?> : </span><span><?php echo esc_html( wp_strip_all_tags( $meta->display_value ) ); ?></span></div>
									<?php
								}
								if ( ! $tc_has_meta ) {
									echo '—';
								}
								?>
							</td>
							<td data-label="قیمت واحد"><?php echo wp_kses_post( $qty > 0 ? wc_price( (float) $item->get_subtotal() / $qty ) : wc_price( 0 ) ); ?></td>
							<td data-label="قیمت کل"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="tc-invoice-total">
				<span>قیمت کل :</span>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</div>
		</div>

	<?php endif; ?>

	<?php
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
	do_action( 'woocommerce_thankyou', $order->get_id() );
	?>

<?php endif; ?>

</div>
