<?php
/**
 * View Order — custom layout
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

$order_id = $order->get_id();
$order_number = $order->get_order_number();
$order_date = $order->get_date_created();
$order_status = $order->get_status();
$payment_title = $order->get_payment_method_title();
$transaction = $order->get_transaction_id();
$payment_note = $order->get_meta('_tc_payment_note');
$customer_note = $order->get_customer_note();

$total = $order->get_total();
$shipping = (float) $order->get_shipping_total();
$date_str = $order_date
	? \TanilChoob\Theme\Helper::jalali_date($order_date->getTimestamp()) . '، ساعت ' . $order_date->date('H:i')
	: '—';

// Finance 
$finance = get_field('finance', $order_id);

// Shipping method
$post_section = get_field('post_section', $order_id);

// Shipping method label
$shipping_label = $post_section ? $post_section['post_type'] : '';
// Delivery slot meta (may be empty if no slot plugin is used)
$delivery_slot = $post_section ? $post_section['post_time'] : '';

// Recipient
$first        = $order->get_shipping_first_name() ?: $order->get_billing_first_name();
$last         = $order->get_shipping_last_name() ?: $order->get_billing_last_name();
$phone        = $order->get_billing_phone();
$fixedphone   = $order->get_meta('_tc_billing_fixedphone');
$nationalcode = $order->get_meta('_tc_billing_nationalcode');
$city         = $order->get_shipping_city() ?: $order->get_billing_city();
$state        = $order->get_shipping_state() ?: $order->get_billing_state();
$postcode     = $order->get_shipping_postcode() ?: $order->get_billing_postcode();
$addr1        = $order->get_shipping_address_1() ?: $order->get_billing_address_1();

// Status label
$paid_statuses = ['processing', 'completed', 'on-hold'];
$tx_status = in_array($order_status, $paid_statuses, true) ? 'موفق' : 'ناموفق';
$tx_status_cls = in_array($order_status, $paid_statuses, true) ? 'success' : 'fail';

$status_map = [
	'completed'  => ['label' => 'تحویل شده',   'cls' => 'delivered'],
	'processing' => ['label' => 'در حال تولید', 'cls' => 'processing'],
	'on-hold'    => ['label' => 'در حال ارسال', 'cls' => 'on-hold'],
	'pending'    => ['label' => 'در انتظار پرداخت', 'cls' => 'pending'],
	'cancelled'  => ['label' => 'لغو شده',      'cls' => 'cancelled'],
	'refunded'   => ['label' => 'مسترد شده',    'cls' => 'refunded'],
	'failed'     => ['label' => 'ناموفق',        'cls' => 'failed'],
];
$item_status_label = $status_map[$order_status]['label'] ?? wc_get_order_status_name($order_status);
$item_status_cls   = $status_map[$order_status]['cls']   ?? 'pending';
?>

<div class="tc-view-order">

	<!-- Back + Invoice (hidden on mobile — my-account.php header handles back) -->
	<div class="tc-view-order__topbar">
		<a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="tc-view-order__back">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
				<path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
					stroke-linejoin="round" />
			</svg>
			جزئیات بیشتر
		</a>
	</div>

	<!-- ── Order Info ──────────────────────────────────────────── -->
	<div class="tc-view-order__section">
		<h3 class="tc-view-order__section-title">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M3.67188 2.5V14.47C3.67188 15.45 4.13187 16.38 4.92188 16.97L10.1319 20.87C11.2419 21.7 12.7719 21.7 13.8819 20.87L19.0919 16.97C19.8819 16.38 20.3419 15.45 20.3419 14.47V2.5H3.67188Z"
					stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10" />
				<path d="M2 2.5H22" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" />
				<path d="M8 8H16" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
					stroke-linejoin="round" />
				<path d="M8 13H16" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
					stroke-linejoin="round" />
			</svg>
			مشخصات سفارش
		</h3>
		<div class="tc-view-order__info-grid">
			<div class="tc-view-order__info-row w-100">
				<span class="tc-view-order__info-key">کد سفارش :</span>
				<span class="tc-view-order__info-val" dir="ltr">TLC-<?php echo esc_html($order_number); ?></span>
			</div>
			<div class="tc-view-order__info-row w-100">
				<span class="tc-view-order__info-key">تاریخ ثبت سفارش :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html($date_str); ?></span>
			</div>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">مبلغ کل :</span>
				<span class="tc-view-order__info-val tc-view-order__info-val--price">
					<?php echo esc_html(number_format($total / 10)); ?> تومان
				</span>
			</div>
			<?php if (isset($finance['delivery_cost'])): ?>
				<div class="tc-view-order__info-row">
					<span class="tc-view-order__info-key">هزینه بسته بندی برای ارسال :</span>
					<span class="tc-view-order__info-val"><?php echo ($finance['delivery_cost']); ?></span>
				</div>
			<?php endif; ?>
			<?php if (isset($finance['pay_amount'])): ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">مبلغ پرداخت شده :</span>
				<span class="tc-view-order__info-val tc-view-order__info-val--price">
					<?php echo ($finance['pay_amount']); ?> 
				</span>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- ── Transaction History (online payments only) ───────────── -->
	<?php if (isset($finance['tr_history'])): ?>
		<div class="tc-view-order__section">
			<h3 class="tc-view-order__section-title">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M21.9331 6.76099L18.5631 20.291C18.3231 21.301 17.4231 22.001 16.3831 22.001H3.24306C1.73306 22.001 0.653075 20.5209 1.10308 19.0709L5.31307 5.55103C5.60307 4.61103 6.47308 3.96094 7.45308 3.96094H19.7531C20.7031 3.96094 21.4931 4.54094 21.8231 5.34094C22.0131 5.77094 22.0531 6.26099 21.9331 6.76099Z"
						stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10" />
					<path d="M16 22H20.78C22.07 22 23.08 20.91 22.99 19.62L22 6" stroke="#C4C4C4" stroke-width="1.5"
						stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
					<path d="M9.67969 6.37854L10.7197 2.05859" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10"
						stroke-linecap="round" stroke-linejoin="round" />
					<path d="M16.3828 6.39075L17.3228 2.05078" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10"
						stroke-linecap="round" stroke-linejoin="round" />
					<path d="M7.70312 12H15.7031" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10"
						stroke-linecap="round" stroke-linejoin="round" />
					<path d="M6.70312 16H14.7031" stroke="#C4C4C4" stroke-width="1.5" stroke-miterlimit="10"
						stroke-linecap="round" stroke-linejoin="round" />
				</svg>
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
						<?php
						// Assuming tr_history is an array of transactions with keys: date, status, amount, way, num, description
						foreach ($finance['tr_history'] as $tx) {
							$_ts      = is_numeric($tx['date']) ? (int)$tx['date'] : strtotime($tx['date']);
						$date_str = $_ts ? \TanilChoob\Theme\Helper::jalali_date($_ts) : $tx['date'];
							$tx_status = $tx['status'];
							$tx_status_cls = $tx['status_cls'];
							$total = $tx['amount'];
							$payment_title = $tx['way'];
							$transaction = $tx['num'];
							$payment_note = $tx['description'];

							echo '<tr>';
							echo '<td>' . ($date_str) . '</td>';
							echo '<td class="tc-view-order__tx-status tc-view-order__tx-status--' . esc_attr($tx_status_cls) . '">' . esc_html($tx_status) . '</td>';
							echo '<td>' . ($total) . '</td>';
							echo '<td>' . ($payment_title) . '</td>';
							echo '<td>' . ($transaction) . '</td>';
							echo '<td>' . ($payment_note) . '</td>';
							echo '</tr>';
							
						}
						?>
						
					</tbody>
				</table>
			</div>

			<!-- Mobile-only: key-value rows replacing the table -->
			<div class="tc-view-order__tx-mobile">
				<?php foreach ($finance['tr_history'] as $tx):
					$_ts          = is_numeric($tx['date']) ? (int) $tx['date'] : strtotime($tx['date']);
					$_date_str    = $_ts ? \TanilChoob\Theme\Helper::jalali_date($_ts) : $tx['date'];
					$_tx_status      = $tx['status'];
					$_tx_status_cls  = $tx['status_cls'];
					$_total          = $tx['amount'];
					$_payment_title  = $tx['way'];
					$_transaction    = $tx['num'];
					$_payment_note   = $tx['description'];
				?>
				<div class="tc-view-order__tx-mobile-item">
					<div class="tc-view-order__tx-row">
						<span class="tc-view-order__info-key">تاریخ :</span>
						<span class="tc-view-order__info-val"><?php echo esc_html($_date_str); ?></span>
					</div>
					<div class="tc-view-order__tx-row">
						<span class="tc-view-order__info-key">وضعیت :</span>
						<span class="tc-view-order__tx-status tc-view-order__tx-status--<?php echo esc_attr($_tx_status_cls); ?>"><?php echo esc_html($_tx_status); ?></span>
					</div>
					<div class="tc-view-order__tx-row">
						<span class="tc-view-order__info-key">مبلغ :</span>
						<span class="tc-view-order__info-val"><?php echo esc_html($_total); ?></span>
					</div>
					<div class="tc-view-order__tx-row">
						<span class="tc-view-order__info-key">روش پرداخت :</span>
						<span class="tc-view-order__info-val"><?php echo esc_html($_payment_title ?: '—'); ?></span>
					</div>
					<?php if ($_transaction): ?>
						<div class="tc-view-order__tx-row">
							<span class="tc-view-order__info-key">شماره پیگیری :</span>
							<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html($_transaction); ?></span>
						</div>
					<?php endif; ?>
					<?php if ($_payment_note): ?>
						<div class="tc-view-order__tx-row">
							<span class="tc-view-order__info-key">توضیحات :</span>
							<span class="tc-view-order__info-val"><?php echo esc_html($_payment_note); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- ── Recipient & Destination ───────────────────────────── -->
	<div class="tc-view-order__section">
		<h3 class="tc-view-order__section-title">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M12.0028 13.4314C13.7259 13.4314 15.1228 12.0345 15.1228 10.3114C15.1228 8.58828 13.7259 7.19141 12.0028 7.19141C10.2797 7.19141 8.88281 8.58828 8.88281 10.3114C8.88281 12.0345 10.2797 13.4314 12.0028 13.4314Z"
					stroke="#C4C4C4" stroke-width="1.5" />
				<path
					d="M3.61776 8.49C5.58776 -0.169998 18.4178 -0.159997 20.3778 8.5C21.5278 13.58 18.3678 17.88 15.5978 20.54C13.5878 22.48 10.4078 22.48 8.38776 20.54C5.62776 17.88 2.46776 13.57 3.61776 8.49Z"
					stroke="#C4C4C4" stroke-width="1.5" />
			</svg>
			مشخصات گیرنده و مقصد
		</h3>
		<div class="tc-view-order__info-grid">
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">تحویل گیرنده :</span>
				<span class="tc-view-order__info-val"><?php echo esc_html(trim($first . ' ' . $last) ?: '—'); ?></span>
			</div>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">شماره تماس :</span>
				<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html($phone ?: '—'); ?></span>
			</div>
			<?php if ($fixedphone): ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">تلفن ثابت :</span>
				<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html($fixedphone); ?></span>
			</div>
			<?php endif; ?>
			<?php if ($nationalcode): ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">کد ملی :</span>
				<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html($nationalcode); ?></span>
			</div>
			<?php endif; ?>
			<?php if ($postcode): ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">کد پستی :</span>
				<span class="tc-view-order__info-val" dir="ltr"><?php echo esc_html($postcode); ?></span>
			</div>
			<?php endif; ?>
			<div class="tc-view-order__info-row tc-view-order__info-row--full">
				<span class="tc-view-order__info-key">ارسال به :</span>
				<span class="tc-view-order__info-val">
					<?php
					$address_parts = array_filter([$city, $state, $addr1]);
					echo esc_html(implode(' / ', $address_parts) ?: '—');
					?>
				</span>
			</div>
		</div>
	</div>

	<!-- ── Package ────────────────────────────────────────────── -->
	<div class="tc-view-order__section">
		<div class="tc-view-order__package-header">
			<div class="flex flex-col  gap-10">

				<span class="tc-view-order__package-title">
					<span class="tc-view-order__item-count"><?php echo esc_html(count($order->get_items())); ?>
						کالا</span>
				</span>
				<?php if ($shipping_label): ?>
					<div class="flex flex-row items-center gap-10">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M11.9978 14H12.9978C14.0978 14 14.9978 13.1 14.9978 12V2H5.9978C4.4978 2 3.18781 2.82999 2.50781 4.04999"
								stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path
								d="M2 17C2 18.66 3.34 20 5 20H6C6 18.9 6.9 18 8 18C9.1 18 10 18.9 10 20H14C14 18.9 14.9 18 16 18C17.1 18 18 18.9 18 20H19C20.66 20 22 18.66 22 17V14H19C18.45 14 18 13.55 18 13V10C18 9.45 18.45 9 19 9H20.29L18.58 6.01001C18.22 5.39001 17.56 5 16.84 5H15V12C15 13.1 14.1 14 13 14H12"
								stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path
								d="M8 22C9.10457 22 10 21.1046 10 20C10 18.8954 9.10457 18 8 18C6.89543 18 6 18.8954 6 20C6 21.1046 6.89543 22 8 22Z"
								stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path
								d="M16 22C17.1046 22 18 21.1046 18 20C18 18.8954 17.1046 18 16 18C14.8954 18 14 18.8954 14 20C14 21.1046 14.8954 22 16 22Z"
								stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M22 12V14H19C18.45 14 18 13.55 18 13V10C18 9.45 18.45 9 19 9H20.29L22 12Z"
								stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M2 8H8" stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round"
								stroke-linejoin="round" />
							<path d="M2 11H6" stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round"
								stroke-linejoin="round" />
							<path d="M2 14H4" stroke="#C4C4C4" stroke-width="1.5" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
						<span class="color-primary yekan-12 md:yekan-16"><?php echo esc_html($shipping_label); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="tc-view-order__invoice-wrap">
				<?php $invoice_url = get_field('invoice_url', $order_id); ?>
				<?php if ($invoice_url): ?>
					<a href="<?php echo esc_url($invoice_url); ?>" class="tc-view-order__invoice-link">
						دریافت فاکتور
					</a>
				<?php endif; ?>
			</div>

		</div>

		<div class="tc-view-order__package-meta">
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">کد پیگیری مرسوله :</span>
				<span class="tc-view-order__info-val" dir="ltr">TLC-<?php echo esc_html($order_number); ?></span>
			</div>
			<?php if ($delivery_slot): ?>
				<div class="tc-view-order__info-row">
					<span class="tc-view-order__info-key">زمان ارسال :</span>
					<span class="tc-view-order__info-val">
						<mark class="tc-view-order__date-mark"><?php echo esc_html($delivery_slot); ?></mark>
					</span>
				</div>
			<?php endif; ?>
			<div class="tc-view-order__info-row">
				<span class="tc-view-order__info-key">وضعیت محصول :</span>
				<span
					class="tc-view-order__item-status tc-view-order__item-status--<?php echo esc_attr($item_status_cls); ?>">
					<?php echo esc_html($item_status_label); ?>
				</span>
			</div>
		</div>

		<!-- Items -->
		<div class="tc-view-order__items">
			<?php foreach ($order->get_items() as $item):
				$product = $item->get_product();
				$image_id = $product ? $product->get_image_id() : 0;
				$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src();
				$name = $item->get_name();
				$quantity = $item->get_quantity();
				$subtotal = $order->get_line_subtotal($item, false, true);
				$permalink = $product ? $product->get_permalink() : '#';
				// Try brand (ACF or taxonomy)
				$brand = '';
				if ($product) {
					$brand_terms = get_the_terms($product->get_id(), 'product_brand');
					if ($brand_terms && !is_wp_error($brand_terms)) {
						$brand = $brand_terms[0]->name;
					}
				}
				?>
				<div class="tc-view-order__item">
					<a href="<?php echo esc_url($permalink); ?>" class="tc-view-order__item-img-wrap">
						<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>"
							class="tc-view-order__item-img">
					</a>
					<div class="tc-view-order__item-info">
						<a href="<?php echo esc_url($permalink); ?>" class="tc-view-order__item-name">
							<?php echo esc_html($name); ?>
						</a>
						<?php if ($brand): ?>
							<span class="tc-view-order__item-brand">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
									<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor"
										stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
								<?php echo esc_html($brand); ?>
							</span>
						<?php endif; ?>
						<span class="tc-view-order__item-qty">
							<span class="tc-view-order__info-key">تعداد :</span>
							<span class="tc-view-order__info-val"><?php echo esc_html($quantity); ?></span>
						</span>
						<span class="tc-view-order__item-price">
							<?php echo esc_html(number_format($subtotal / 10)); ?> تومان
						</span>
						<?php foreach ( $item->get_formatted_meta_data( '_' ) as $meta ) : ?>
							<div class="tc-view-order__item-meta">
								<span class="tc-view-order__item-meta-key"><?php echo wp_kses_post( $meta->display_key ); ?> :</span>
								<span class="tc-view-order__item-meta-val"><?php echo wp_kses_post( $meta->display_value ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

				</div>
			<?php endforeach; ?>
		</div>
	</div>

</div>