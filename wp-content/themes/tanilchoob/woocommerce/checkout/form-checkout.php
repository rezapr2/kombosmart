<?php
/**
 * Checkout Form — custom 4-step flow.
 *
 * Steps: سبد خرید → مشخصات و آدرس → شیوه پرداخت → پیش فاکتور
 */

defined('ABSPATH') || exit;


$cart = WC()->cart;
$user_id = get_current_user_id();
$addresses = get_user_meta($user_id, 'tc_saved_addresses', true);
if (!is_array($addresses)) {
	$addresses = [];
}

$cart_total = number_format((float) $cart->get_total(''), 0, '.', ',');

$payment_methods = [
	'cod' => ['title' => 'پرداخت در محل', 'desc' => 'ویژه تهران و حومه'],
	'online' => ['title' => 'پرداخت آنلاین', 'desc' => 'از طریق درگاه بانکی'],
	'bank_transfer' => ['title' => 'واریز به حساب', 'desc' => 'واریز از طریق شماره حساب'],
	'installment' => ['title' => 'پرداخت اقساطی', 'desc' => 'پرداخت اقساطی به شرایط بانکی'],
];

$steps = [
	1 => 'سبد خرید',
	2 => 'مشخصات و آدرس',
	3 => 'شیوه پرداخت',
	4 => 'پیش فاکتور',
];

?>
<div class="tc-checkout" dir="rtl">

	<!-- ── Progress Steps ───────────────────────────────── -->
	<?php
	$step_icons = [
		1 => 'icon_cart.png',
		2 => 'icon_location.png',
		3 => 'icon_paymentmethode.png',
		4 => 'icon_invoice.png',
	];
	$template_uri = get_template_directory_uri();
	?>
	<div class="header relative">
		<div class="top-bg bg-black-03 w-full absolute"></div>
		<div class="items flex justify-between relative z-index-1 direction-ltr">
			<?php foreach (array_reverse($steps, true) as $num => $label): ?>
				<div class="flex flex-col items-center gap-15 item circle-radius <?php echo $num === 1 ? 'active' : ''; ?>"
					data-step="<?php echo $num; ?>">
					<div class="circle circle-radius flex item-center">
						<img src="<?php echo esc_url($template_uri . '/assets/frontend/dist/images/' . $step_icons[$num]); ?>"
							alt="<?php echo esc_attr($label); ?>">
					</div>
					<span class="label yekan-18 color-black"><?php echo esc_html($label); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- ── Step 1: Cart ─────────────────────────────────── -->
	<div class="tc-checkout__step is-active" data-step-panel="1">
		<div class="tc-cart-table-wrap">
			<div class="tc-cart-table">
				<div class="tc-cart-table__head">
					<div class="tc-cart-table__head-product">محصول</div>
					<div class="tc-cart-table__head-qty">مقدار</div>
					<div class="tc-cart-table__head-custom">سفارش سازی ها</div>
					<div class="tc-cart-table__head-price">قیمت</div>
					<div></div>
				</div>

				<?php foreach ($cart->get_cart() as $cart_item_key => $cart_item):
					$product = $cart_item['data'];
					if (!$product || !$product->exists())
						continue;

					$product_id = $cart_item['product_id'];
					$image_id = $product->get_image_id();
					$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src('thumbnail');
					$name = $product->get_name();
					$qty = $cart_item['quantity'];
					$line_total = number_format((float) $cart_item['line_total'], 0, '.', ',');

					// Variation attributes
					$variation_lines = [];
					if (!empty($cart_item['variation'])) {
						foreach ($cart_item['variation'] as $attr => $val) {
							$label = wc_attribute_label(str_replace('attribute_', '', $attr));
							$variation_lines[] = $label . ': ' . $val;
						}
					}

					// Custom option adjustments
					$option_lines = [];
					if (!empty($cart_item['tc_option_adjustments'])) {
						foreach ($cart_item['tc_option_adjustments'] as $opt) {
							$option_lines[] = ($opt['label'] ?? '');
						}
					}
					?>
					<div class="tc-cart-table__row" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
						

						<div class="tc-cart-table__col-product">
							<img class="tc-cart-table__img" src="<?php echo esc_url($image_url); ?>"
								alt="<?php echo esc_attr($name); ?>">
							<div class="tc-cart-table__product-info">
								<a href="<?php echo esc_url(get_permalink($product_id)); ?>"
									class="tc-cart-table__product-name"><?php echo esc_html($name); ?></a>
								<?php foreach ($variation_lines as $vl): ?>
									<span class="tc-cart-table__meta"><?php echo esc_html($vl); ?></span>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="tc-cart-table__col-qty">
							<div class="tc-qty">
								<button class="tc-qty__btn tc-qty-plus"
									data-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
								<span class="tc-qty__val"
									data-key="<?php echo esc_attr($cart_item_key); ?>"><?php echo esc_html($qty); ?></span>
								<button class="tc-qty__btn tc-qty-minus"
									data-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
							</div>
						</div>

						<div class="tc-cart-table__col-custom">
							<?php if ($option_lines): ?>
								<?php foreach ($option_lines as $ol): ?><span><?php echo esc_html($ol); ?></span><?php endforeach; ?>
							<?php else: ?>
								<span class="tc-muted">—</span>
							<?php endif; ?>
						</div>

						<div class="tc-cart-table__col-price">
							<span class="tc-cart-table__price-val"
								data-key="<?php echo esc_attr($cart_item_key); ?>"><?php echo esc_html($line_total); ?></span>
							<span class="tc-toman">تومان</span>
						</div>

						<div class="tc-cart-table__col-actions flex item-center gap-10">
							<button class="tc-icon-btn tc-icon-btn--danger tc-cart-remove"
								data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="حذف">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
									<path
										d="M21 5.98c-3.33-.33-6.68-.5-10.02-.5-1.98 0-3.96.1-5.94.3L3 5.98M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62c1.69 0 1.82.75 1.97 1.67l.22 1.3M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14M10.33 16.5h3.33M9.5 12.5h5"
										stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</button>
							<?php
							printf('<a href="%s" class="tc-icon-btn tc-icon-btn--view tc-cart-view"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15.5819 11.9999C15.5819 13.9799 13.9819 15.5799 12.0019 15.5799C10.0219 15.5799 8.42188 13.9799 8.42188 11.9999C8.42188 10.0199 10.0219 8.41992 12.0019 8.41992C13.9819 8.41992 15.5819 10.0199 15.5819 11.9999Z" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.9998 20.2707C15.5298 20.2707 18.8198 18.1907 21.1098 14.5907C22.0098 13.1807 22.0098 10.8107 21.1098 9.4007C18.8198 5.8007 15.5298 3.7207 11.9998 3.7207C8.46984 3.7207 5.17984 5.8007 2.88984 9.4007C1.98984 10.8107 1.98984 13.1807 2.88984 14.5907C5.17984 18.1907 8.46984 20.2707 11.9998 20.2707Z" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							</a>', esc_url(get_permalink($product_id))); // PHPCS: XSS ok.
							?>
						</div>

					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- ── Step 2: Address ──────────────────────────────── -->
	<div class="tc-checkout__step" data-step-panel="2">

		<div class="tc-addresses" id="tc-addresses-list">
			<?php if (empty($addresses)): ?>
				<p class="tc-addresses__empty">هنوز آدرسی ذخیره نکرده‌اید. یک آدرس جدید اضافه کنید.</p>
			<?php else: ?>
				<?php foreach ($addresses as $i => $addr): ?>
					<div class="tc-address-card" data-id="<?php echo esc_attr($addr['id']); ?>">
						<label class="tc-address-card__inner">
							<input type="radio" name="tc_selected_address" value="<?php echo esc_attr($addr['id']); ?>"
								class="tc-address-radio" <?php checked($i, 0); ?>>
							<span class="tc-address-card__check-icon">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
									<path d="M4 12l6 6L20 6" stroke="white" stroke-width="2.5" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
							</span>
							<div class="tc-address-card__body">
								<div class="tc-address-card__name">
									<strong>نام و نام خوادگی تحویل گیرنده :</strong>
									<span><?php echo esc_html($addr['first_name'] . ' ' . $addr['last_name']); ?></span>
								</div>
								<div class="tc-address-card__addr">
									<strong>آدرس :</strong>
									<span><?php echo esc_html(($addr['city'] ? $addr['city'] . '، ' : '') . $addr['address_1']); ?></span>
								</div>
								<div class="tc-address-card__meta">
									<span><strong>شماره تماس :</strong> <?php echo esc_html($addr['phone']); ?></span>
									<?php if ($addr['postcode']): ?>
										<span><strong>کد پستی :</strong> <?php echo esc_html($addr['postcode']); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</label>
						<div class="tc-address-card__actions">
							<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-address-delete"
								data-id="<?php echo esc_attr($addr['id']); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
									<path
										d="M21 5.98c-3.33-.33-6.68-.5-10.02-.5-1.98 0-3.96.1-5.94.3L3 5.98M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62c1.69 0 1.82.75 1.97 1.67l.22 1.3M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14"
										stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
								حذف آدرس
							</button>
							<button class="tc-btn tc-btn--sm tc-btn--outline tc-address-edit"
								data-id="<?php echo esc_attr($addr['id']); ?>"
								data-address="<?php echo esc_attr(wp_json_encode($addr)); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none">
									<path
										d="M13.26 3.6l-8.21 8.69c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.13.71 1.93 1.83 1.75l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21.63-4.74-1.44-1.54-3.12-.94-4.03.62z"
										stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
										stroke-linejoin="round" />
								</svg>
								ویرایش آدرس
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<button class="tc-btn tc-btn--outline-primary tc-btn--add-address" id="tc-add-address-btn">
			+ افزودن آدرس جدید
		</button>

		<!-- Address form (hidden by default) -->
		<div class="tc-address-form" id="tc-address-form">
			<h3 class="tc-address-form__title" id="tc-address-form-title">افزودن آدرس جدید</h3>
			<input type="hidden" id="tc-address-id" value="">
			<div class="tc-form-grid">
				<div class="tc-form-field">
					<label for="tc-addr-first-name">نام <span class="required">*</span></label>
					<input type="text" id="tc-addr-first-name" placeholder="نام">
				</div>
				<div class="tc-form-field">
					<label for="tc-addr-last-name">نام خانوادگی <span class="required">*</span></label>
					<input type="text" id="tc-addr-last-name" placeholder="نام خانوادگی">
				</div>
				<div class="tc-form-field">
					<label for="tc-addr-phone">شماره تماس <span class="required">*</span></label>
					<input type="tel" id="tc-addr-phone" placeholder="09xxxxxxxxx" dir="ltr">
				</div>
				<div class="tc-form-field">
					<label for="tc-addr-postcode">کد پستی</label>
					<input type="text" id="tc-addr-postcode" placeholder="کد پستی" dir="ltr">
				</div>
				<div class="tc-form-field tc-form-field--full">
					<label for="tc-addr-city">شهر <span class="required">*</span></label>
					<input type="text" id="tc-addr-city" placeholder="شهر">
				</div>
				<div class="tc-form-field tc-form-field--full">
					<label for="tc-addr-address1">آدرس <span class="required">*</span></label>
					<textarea id="tc-addr-address1" placeholder="آدرس کامل" rows="3"></textarea>
				</div>
			</div>
			<p class="tc-form-msg" id="tc-address-msg"></p>
			<div class="tc-address-form__footer">
				<button class="tc-btn tc-btn--primary" id="tc-save-address-btn">ذخیره آدرس</button>
				<button class="tc-btn tc-btn--ghost" id="tc-cancel-address-btn">انصراف</button>
			</div>
		</div>

	</div>

	<!-- ── Step 3: Payment ──────────────────────────────── -->
	<div class="tc-checkout__step" data-step-panel="3">

		<h2 class="tc-section-title">انتخاب شیوه پرداخت</h2>

		<div class="tc-payment-methods">
			<?php foreach (array_reverse($payment_methods, true) as $method_key => $method): ?>
				<label class="tc-payment-card" data-method="<?php echo esc_attr($method_key); ?>">
					<input type="radio" name="tc_payment_method" value="<?php echo esc_attr($method_key); ?>"
						class="tc-payment-radio" <?php checked($method_key, 'cod'); ?>>
					<span class="tc-payment-card__check">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
							<path d="M4 12l6 6L20 6" stroke="white" stroke-width="2.5" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</span>
					<div class="tc-payment-card__icon">
						<?php if ($method_key === 'cod'): ?>
							<svg width="32" height="32" viewBox="0 0 24 24" fill="none">
								<path d="M12 13.43a3.12 3.12 0 1 0 0-6.24 3.12 3.12 0 0 0 0 6.24z" stroke="currentColor"
									stroke-width="1.5" />
								<path
									d="M3.62 8.49c1.97-8.66 14.8-8.65 16.76.01 1.15 5.08-2.01 9.38-4.78 12.04a5.19 5.19 0 0 1-7.21 0c-2.76-2.66-5.92-6.97-4.77-12.05z"
									stroke="currentColor" stroke-width="1.5" />
							</svg>
						<?php elseif ($method_key === 'online'): ?>
							<svg width="32" height="32" viewBox="0 0 24 24" fill="none">
								<path
									d="M22 10v6c0 3-1.5 5-5 5H7c-3.5 0-5-2-5-5v-6h20zm0-2H2V7c0-3 1.5-5 5-5h10c3.5 0 5 2 5 5v1z"
									stroke="currentColor" stroke-width="1.5" />
								<path d="M7 15h2M11 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
							</svg>
						<?php elseif ($method_key === 'bank_transfer'): ?>
							<svg width="32" height="32" viewBox="0 0 24 24" fill="none">
								<path
									d="M11.02 2.22c.54-.3 1.42-.3 1.96 0l7.53 4.2c.54.3.97 1.03.97 1.64v2.97c0 .55-.45 1-1 1H3.5c-.55 0-1-.45-1-1V8.06c0-.61.43-1.34.97-1.64l7.55-4.2zM2.5 21h19M12 17v4M7 17v4M17 17v4M2.5 13h19"
									stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						<?php else: ?>
							<svg width="32" height="32" viewBox="0 0 24 24" fill="none">
								<path d="M17 2H7C4 2 2 4 2 7v10c0 3 2 5 5 5h10c3 0 5-2 5-5V7c0-3-2-5-5-5z" stroke="currentColor"
									stroke-width="1.5" />
								<path d="M8 12h4M8 16h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
							</svg>
						<?php endif; ?>
					</div>
					<div class="tc-payment-card__body">
						<div class="tc-payment-card__title"><?php echo esc_html($method['title']); ?></div>
						<div class="tc-payment-card__desc"><?php echo esc_html($method['desc']); ?></div>
					</div>
				</label>
			<?php endforeach; ?>
		</div>

		<div class="tc-payment-warning" id="tc-cod-warning">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
				<path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10zm0-6v-4m0-4h.01"
					stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
			<span>هزینه ارسال محصول، به صورت پس کرایه می باشد.</span>
			<a href="#" class="tc-payment-warning__link">پس کرایه چیست؟</a>
		</div>

		<div class="tc-notes-wrap">
			<label for="tc-order-notes">توضیحات تکمیلی:</label>
			<textarea id="tc-order-notes" placeholder="درصورت نیاز توضیحات تکمیلی را اینجا وارد نمایید."
				rows="4"></textarea>
		</div>

	</div>

	<!-- ── Step 4: Invoice ──────────────────────────────── -->
	<div class="tc-checkout__step" data-step-panel="4">
		<div class="tc-order-success">
			<svg class="tc-order-success__icon" width="64" height="64" viewBox="0 0 24 24" fill="none">
				<circle cx="12" cy="12" r="10" fill="var(--color-primary)" opacity=".12" />
				<path d="M7.75 12l3.5 3.5 5-6" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round"
					stroke-linejoin="round" />
			</svg>
			<div class="tc-order-success__msg">سفارش شما با موفقیت ثبت شد</div>
			<div class="tc-order-success__num">کد پیگیری سفارش: <strong id="tc-order-number"></strong></div>
		</div>
		<div class="tc-invoice" id="tc-invoice">
			<!-- rendered by JS -->
		</div>
		<div class="tc-invoice-actions">
			<button class="tc-btn tc-btn--outline-primary" onclick="window.print()">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none">
					<path
						d="M6 17H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 8V3h12v5M6 14h12v7H6v-7z"
						stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
				</svg>
				پیش فاکتور سفارش
			</button>
		</div>
	</div>

	<!-- ── Footer bar ───────────────────────────────────── -->
	<div class="tc-checkout__footer" id="tc-checkout-footer">
		<div class="tc-checkout__footer-total">
			<span>مبلغ قابل پرداخت:</span>
			<div class="tc-coupon-wrap" id="tc-coupon-wrap">
				<button class="tc-coupon-toggle" id="tc-coupon-toggle">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21.41 11.58l-9-9A2 2 0 0 0 11 2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 .59 1.42l9 9A2 2 0 0 0 13 22a2 2 0 0 0 1.41-.59l7-7A2 2 0 0 0 22 13a2 2 0 0 0-.59-1.42zM6.5 8a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					کد تخفیف دارید؟
				</button>
				<div class="tc-coupon-form" id="tc-coupon-form">
					<input type="text" id="tc-coupon-code" placeholder="کد تخفیف" dir="ltr">
					<button class="tc-btn tc-btn--outline-primary" id="tc-apply-coupon-btn">اعمال</button>
				</div>
				<p class="tc-form-msg" id="tc-coupon-msg"></p>
				<div class="tc-applied-coupons" id="tc-applied-coupons">
					<?php foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) : ?>
					<div class="tc-coupon-tag" data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
						<span><?php echo esc_html( $coupon_code ); ?></span>
						<button class="tc-coupon-remove" data-coupon="<?php echo esc_attr( $coupon_code ); ?>" aria-label="حذف کد تخفیف">×</button>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="flex gap-30 items-center">
			<div class="color-primary">
				<strong id="tc-cart-total"><?php echo esc_html($cart_total); ?></strong>
				<span><?php echo wp_kses_post(get_woocommerce_currency_symbol()); ?></span>
			</div>
			<button class="tc-btn tc-btn--primary tc-btn--lg" id="tc-checkout-next">
				ادامه ثبت سفارش
			</button>
		</div>
	</div>

</div>
<?php
// Pass checkout config to JS
wp_localize_script('scripts', 'tcCheckout', [
	'ajaxUrl' => admin_url('admin-ajax.php'),
	'nonce' => wp_create_nonce('ajax-nonce'),
]);
?>