<?php
/**
 * View Order — custom layout
 *
 * @package TanilChoob
 */

defined( 'ABSPATH' ) || exit;

$order_id      = $order->get_id();
$order_number  = $order->get_order_number();
$order_date    = $order->get_date_created();
$order_status  = $order->get_status();
$payment_title = $order->get_payment_method_title();
$transaction   = $order->get_transaction_id();
$payment_note  = $order->get_meta( '_tc_payment_note' );
$customer_note = $order->get_customer_note();

$total         = $order->get_total();
$shipping      = (float) $order->get_shipping_total();
$date_str      = $order_date ? $order_date->date_i18n( 'l j F Y، ساعت H:i' ) : '—';

// Shipping method
$shipping_method = '';
foreach ( $order->get_shipping_methods() as $sm ) {
	$shipping_method = $sm->get_name();
	break;
}

// Recipient
$first   = $order->get_shipping_first_name() ?: $order->get_billing_first_name();
$last    = $order->get_shipping_last_name()  ?: $order->get_billing_last_name();
$phone   = $order->get_billing_phone();
$city    = $order->get_shipping_city()       ?: $order->get_billing_city();
$state   = $order->get_shipping_state()      ?: $order->get_billing_state();
$addr1   = $order->get_shipping_address_1()  ?: $order->get_billing_address_1();

// Status label
$paid_statuses = [ 'processing', 'completed', 'on-hold' ];
$tx_status     = in_array( $order_status, $paid_statuses, true ) ? 'موفق' : 'ناموفق';
$tx_status_cls = in_array( $order_status, $paid_statuses, true ) ? 'success' : 'fail';

$delivered_statuses = [ 'completed' ];
$item_status_label  = in_array( $order_status, $delivered_statuses, true ) ? 'تحویل شده' : wc_get_order_status_name( $order_status );
$item_status_cls    = in_array( $order_status, $delivered_statuses, true ) ? 'delivered' : 'pending';
?>

<div class="tc-view-order" dir="rtl">

	<!-- Back + Invoice -->
	<div class="tc-view-order__topbar">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="tc-view-order__back">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			جزئیات بیشتر
		</a>
		<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>?print=1" class="tc-view-order__invoice-link" target="_blank">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M8 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-3M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2M8 6h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			دریافت فاکتور
		</a>
	</div>

	<!-- ── Order Info ──────────────────────────────────────────── -->
	<div class="tc-view-order__section">
		<h3 class="tc-view-order__section-title">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 22H15C20 22 22 20 22 15V9C22 4 20 2 15 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 12h10M7 8.5h6M7 15.5h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			مشخصات سفارش
		</h3>
		<div class="tc-view-order__info-grid">
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">کد سفارش :</span>
				<span class="tc-view-order__info-val" dir="ltr">TLC-<?php echo esc_html( $order_number ); ?></span>
			</div>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">تاریخ ثبت سفارش :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html( $date_str ); ?></span>
			</div>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">مبلغ کل :</span>
				<span class="tc-view-order__info-val tc-view-order__info-val--price">
					<?php echo esc_html( number_format( $total ) ); ?> تومان
				</span>
			</div>
			<?php if ( $shipping > 0 ) : ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">هزینه بسته بندی برای ارسال :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html( number_format( $shipping ) ); ?> تومان</span>
			</div>
			<?php endif; ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">مبلغ پرداخت شده :</span>
				<span class="tc-view-order__info-val tc-view-order__info-val--price">
					<?php echo esc_html( number_format( $total ) ); ?> تومان
				</span>
			</div>
		</div>
	</div>

	<!-- ── Transaction History ────────────────────────────────── -->
	<div class="tc-view-order__section">
		<h3 class="tc-view-order__section-title">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3.17 7.44L12 12.55l8.77-5.08M12 21.61V12.54" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.93 2.48L4.59 5.44c-1.21.67-2.2 2.35-2.2 3.73v5.65c0 1.38.99 3.06 2.2 3.73l5.34 2.97c1.14.64 3.01.64 4.15 0l5.34-2.97c1.21-.67 2.2-2.35 2.2-3.73V9.17c0-1.38-.99-3.06-2.2-3.73l-5.34-2.97c-1.15-.64-3.01-.64-4.15.01z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			تاریخچه تراکنش ها
		</h3>
		<div class="tc-view-order__table-wrap">
			<table class="tc-view-order__table">
				<thead>
					<tr>
						<th>تاریخ</th>
						<th>وضعیت</th>
						<th>مبلغ</th>
						<th>روش پرداخت</th>
						<th>شماره پی گیری</th>
						<th>توضیحات</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo esc_html( $date_str ); ?></td>
						<td>
							<span class="tc-view-order__tx-status tc-view-order__tx-status--<?php echo esc_attr( $tx_status_cls ); ?>">
								<?php echo esc_html( $tx_status ); ?>
							</span>
						</td>
						<td><?php echo esc_html( number_format( $total ) ); ?> تومان</td>
						<td><?php echo esc_html( $payment_title ?: '—' ); ?></td>
						<td dir="ltr"><?php echo esc_html( $transaction ?: '—' ); ?></td>
						<td><?php echo esc_html( $payment_note ?: $customer_note ?: '—' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- ── Recipient & Destination ───────────────────────────── -->
	<div class="tc-view-order__section">
		<h3 class="tc-view-order__section-title">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM20.59 22c0-3.31-3.85-6-8.59-6s-8.59 2.69-8.59 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			مشخصات گیرنده و مقصد
		</h3>
		<div class="tc-view-order__info-grid">
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">شماره تماس :</span>
				<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html( $phone ?: '—' ); ?></span>
			</div>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">تحویل گیرنده :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html( trim( $first . ' ' . $last ) ?: '—' ); ?></span>
			</div>
			<div class="tc-view-order__info-row tc-view-order__info-row--full">
				<span class="tc-view-order__info-key">ارسال به :</span>
				<span class="tc-view-order__info-val">
					<?php
					$address_parts = array_filter( [ $city, $state, $addr1 ] );
					echo esc_html( implode( ' / ', $address_parts ) ?: '—' );
					?>
				</span>
			</div>
		</div>
	</div>

	<!-- ── Package ────────────────────────────────────────────── -->
	<div class="tc-view-order__section">
		<div class="tc-view-order__package-header">
			<span class="tc-view-order__package-title">مرسوله ۱ از ۱</span>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3.17 7.44L12 12.55l8.77-5.08M12 21.61V12.54" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.93 2.48L4.59 5.44c-1.21.67-2.2 2.35-2.2 3.73v5.65c0 1.38.99 3.06 2.2 3.73l5.34 2.97c1.14.64 3.01.64 4.15 0l5.34-2.97c1.21-.67 2.2-2.35 2.2-3.73V9.17c0-1.38-.99-3.06-2.2-3.73l-5.34-2.97c-1.15-.64-3.01-.64-4.15.01z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</div>

		<div class="tc-view-order__package-meta">
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">کد پیگیری مرسوله :</span>
				<span class="tc-view-order__info-val" dir="ltr">TLC-<?php echo esc_html( $order_number ); ?></span>
			</div>
			<?php if ( $shipping_method ) : ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">نوع ارسال :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html( $shipping_method ); ?></span>
			</div>
			<?php endif; ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">زمان ارسال :</span>
				<span class="tc-view-order__info-val">
					<mark class="tc-view-order__date-mark"><?php echo esc_html( $date_str ); ?></mark>
				</span>
			</div>
			<?php if ( $shipping > 0 ) : ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">هزینه بسته بندی برای ارسال :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html( number_format( $shipping ) ); ?> تومان</span>
			</div>
			<?php endif; ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">وضعیت محصول :</span>
				<span class="tc-view-order__item-status tc-view-order__item-status--<?php echo esc_attr( $item_status_cls ); ?>">
					<?php echo esc_html( $item_status_label ); ?>
				</span>
			</div>
		</div>

		<!-- Items -->
		<div class="tc-view-order__items">
			<?php foreach ( $order->get_items() as $item ) :
				$product    = $item->get_product();
				$image_id   = $product ? $product->get_image_id() : 0;
				$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : wc_placeholder_img_src();
				$name       = $item->get_name();
				$subtotal   = $order->get_line_subtotal( $item, false, true );
				$permalink  = $product ? $product->get_permalink() : '#';
				// Try brand (ACF or taxonomy)
				$brand = '';
				if ( $product ) {
					$brand_terms = get_the_terms( $product->get_id(), 'product_brand' );
					if ( $brand_terms && ! is_wp_error( $brand_terms ) ) {
						$brand = $brand_terms[0]->name;
					}
				}
			?>
			<div class="tc-view-order__item">
				<div class="tc-view-order__item-info">
					<a href="<?php echo esc_url( $permalink ); ?>" class="tc-view-order__item-name">
						<?php echo esc_html( $name ); ?>
					</a>
					<?php if ( $brand ) : ?>
					<span class="tc-view-order__item-brand">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<?php echo esc_html( $brand ); ?>
					</span>
					<?php endif; ?>
					<span class="tc-view-order__item-price">
						<?php echo esc_html( number_format( $subtotal ) ); ?> تومان
					</span>
				</div>
				<a href="<?php echo esc_url( $permalink ); ?>" class="tc-view-order__item-img-wrap">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="tc-view-order__item-img">
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

</div>
