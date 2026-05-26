<?php
/**
 * Checkout Form — custom 4-step flow.
 *
 * Steps: سبد خرید → مشخصات و آدرس → شیوه پرداخت → پیش فاکتور
 */

defined('ABSPATH') || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}

$cart = WC()->cart;
$user_id = get_current_user_id();
$addresses = get_user_meta($user_id, 'tc_saved_addresses', true);
if (!is_array($addresses)) {
	$addresses = [];
}

$cart_total = number_format((float) $cart->get_total(''), 0, '.', ',');

$payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();

// SVG icons keyed by gateway ID (fallback used for unknowns)
$gateway_icons = [
	'cod'           => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M12 13.43a3.12 3.12 0 1 0 0-6.24 3.12 3.12 0 0 0 0 6.24z" stroke="currentColor" stroke-width="1.5"/><path d="M3.62 8.49c1.97-8.66 14.8-8.65 16.76.01 1.15 5.08-2.01 9.38-4.78 12.04a5.19 5.19 0 0 1-7.21 0c-2.76-2.66-5.92-6.97-4.77-12.05z" stroke="currentColor" stroke-width="1.5"/></svg>',
	'bacs'          => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M11.02 2.22c.54-.3 1.42-.3 1.96 0l7.53 4.2c.54.3.97 1.03.97 1.64v2.97c0 .55-.45 1-1 1H3.5c-.55 0-1-.45-1-1V8.06c0-.61.43-1.34.97-1.64l7.55-4.2zM2.5 21h19M12 17v4M7 17v4M17 17v4M2.5 13h19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	'cheque'        => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M17 2H7C4 2 2 4 2 7v10c0 3 2 5 5 5h10c3 0 5-2 5-5V7c0-3-2-5-5-5z" stroke="currentColor" stroke-width="1.5"/><path d="M8 12h4M8 16h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
	'_online'       => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M22 10v6c0 3-1.5 5-5 5H7c-3.5 0-5-2-5-5v-6h20zm0-2H2V7c0-3 1.5-5 5-5h10c3.5 0 5 2 5 5v1z" stroke="currentColor" stroke-width="1.5"/><path d="M7 15h2M11 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
	'_default'      => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M22 10v6c0 3-1.5 5-5 5H7c-3.5 0-5-2-5-5v-6h20zm0-2H2V7c0-3 1.5-5 5-5h10c3.5 0 5 2 5 5v1z" stroke="currentColor" stroke-width="1.5"/><path d="M7 15h2M11 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
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
		<div class="hidden md:flex top-bg bg-black-03 w-full absolute"></div>

		<!-- Mobile step nav -->
		<div class="tc-mobile-step-nav flex md:hidden justify-between items-center"
			id="tc-mobile-step-nav"
			data-steps="<?php echo esc_attr(wp_json_encode($steps)); ?>">
			<span class="tc-mobile-current-step yekan-16 color-primary bold" id="tc-mobile-current-step"><?php echo esc_html($steps[1]); ?></span>
			<button class="tc-mobile-back-btn yekan-14 color-black-60" id="tc-mobile-back-btn" style="display:none"></button>
		</div>

		<div class="items hidden md:flex justify-between relative z-index-1 direction-ltr">
			<?php foreach (array_reverse($steps, true) as $num => $label): ?>
				<div class="flex flex-col items-center gap-15 item circle-radius <?php echo $num === 1 ? 'active' : ''; ?>"
					data-step="<?php echo $num; ?>">
					<div class="circle circle-radius hidden md:flex item-center">
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
					<div class="tc-cart-table__head-qty flex justify-center">مقدار</div>
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
					$product_name = $product->get_name();
					$qty = $cart_item['quantity'];
					$line_total = number_format((float) $cart_item['line_total'], 0, '.', ',');

					// Variation attributes
					$variation_lines = [];
					if (!empty($cart_item['variation'])) {
						
						foreach ( $cart_item['variation'] as $name => $value ) {
							$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

							if ( taxonomy_exists( $taxonomy ) ) {
								// If this is a term slug, get the term's nice name.
								$term = get_term_by( 'slug', $value, $taxonomy );
								if ( ! is_wp_error( $term ) && $term && $term->name ) {
									$value = $term->name;
								}
								$label = wc_attribute_label( $taxonomy );
							} else {
								// If this is a custom option slug, get the options name.
								$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $cart_item['data'] );
								$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
							}

							// Check the nicename against the title.
							if ( '' === $value || wc_is_attribute_in_product_name( $value, $cart_item['data']->get_name() ) ) {
								continue;
							}
							$variation_lines[] = array(
								'key'   => $label,
								'value' => $value,
							);
							
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
									class="tc-cart-table__product-name color-black yekan-16 md:yekan-20"><?php echo esc_html($product_name); ?></a>
								<?php 	$product_components_text = get_field('product_components_text', $product_id); 
								if($product_components_text):?>
									<div class="product_components_text color-black-40 yekan-12 md:yekan-16">شامل: <?php echo $product_components_text; ?></div>
								<?php endif; ?>

							</div>
						</div>

						<div class="tc-cart-table__col-qty flex justify-center">
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
							<?php foreach ($variation_lines as $vl): ?>
									<div class="tc-cart-table__meta"><span class='color-black-80 yekan-14 bold'><?php echo esc_html($vl['key']); ?> : </span> <span class='color-black-70 yekan-14'><?php echo esc_html($vl['value']); ?></span></div>
								<?php endforeach; ?>
							<?php if ($option_lines): ?>
								<?php foreach ($option_lines as $ol): ?><span><?php echo esc_html($ol); ?></span><?php endforeach; ?>
							<?php else: ?>
								<span class="tc-muted"></span>
							<?php endif; ?>
						</div>

						<div class="tc-cart-table__col-price">
							<div class="price relative">
								<span class="tc-cart-table__price-val"
									data-key="<?php echo esc_attr($cart_item_key); ?>"><?php echo esc_html($line_total); ?></span>
								<span class="tc-toman absolute">تومان</span>
							</div>
							
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
							<path d="M15.5819 11.9999C15.5819 13.9799 13.9819 15.5799 12.0019 15.5799C10.0219 15.5799 8.42188 13.9799 8.42188 11.9999C8.42188 10.0199 10.0219 8.41992 12.0019 8.41992C13.9819 8.41992 15.5819 10.0199 15.5819 11.9999Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.9998 20.2707C15.5298 20.2707 18.8198 18.1907 21.1098 14.5907C22.0098 13.1807 22.0098 10.8107 21.1098 9.4007C18.8198 5.8007 15.5298 3.7207 11.9998 3.7207C8.46984 3.7207 5.17984 5.8007 2.88984 9.4007C1.98984 10.8107 1.98984 13.1807 2.88984 14.5907C5.17984 18.1907 8.46984 20.2707 11.9998 20.2707Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							</a>', esc_url(get_permalink($product_id))); // PHPCS: XSS ok.
							?>
						</div>

					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="tc-notes-wrap">
			<label for="tc-order-notes color-black">توضیحات تکمیلی:</label>
			<textarea id="tc-order-notes" placeholder="درصورت نیاز توضیحات تکمیلی را اینجا وارد نمایید."
				rows="4"></textarea>
		</div>
	</div>

	<!-- ── Step 2: Address ──────────────────────────────── -->
	<div class="tc-checkout__step" data-step-panel="2">

		<div class="tc-addresses" id="tc-addresses-list">
			<?php if (empty($addresses)): ?>
				<p class="tc-addresses__empty">هنوز آدرسی ذخیره نکرده‌اید. یک آدرس جدید اضافه کنید.</p>
			<?php else: ?>
				<?php foreach ($addresses as $i => $addr): ?>
					<div class="tc-address-card flex justify-between" data-id="<?php echo esc_attr($addr['id']); ?>">
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
									<strong>نام و نام خوانوادگی تحویل گیرنده :</strong>
									<span><?php echo esc_html($addr['first_name'] . ' ' . $addr['last_name']); ?></span>
								</div>
								<div class="tc-address-card__addr">
									<strong>آدرس :</strong>
									<span><?php echo esc_html(($addr['city'] ? $addr['city'] . '، ' : '') . $addr['address_1']); ?></span>
								</div>
								<div class="tc-address-card__meta">
									<span><strong>شماره تماس :</strong> <?php echo esc_html($addr['phone']); ?></span>
									<?php if ( ! empty( $addr['fixedphone'] ) ): ?>
										<span><strong>تلفن ثابت :</strong> <?php echo esc_html($addr['fixedphone']); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $addr['nationalcode'] ) ): ?>
										<span><strong>کد ملی :</strong> <?php echo esc_html($addr['nationalcode']); ?></span>
									<?php endif; ?>
									<?php if ($addr['postcode']): ?>
										<span><strong>کد پستی :</strong> <?php echo esc_html($addr['postcode']); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</label>
						<div class="tc-address-card__actions flex flex-col">
							<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-address-delete flex justify-between"
								data-id="<?php echo esc_attr($addr['id']); ?>">
								
								حذف آدرس
								
								<svg  viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M21 5.98047C17.67 5.65047 14.32 5.48047 10.98 5.48047C9 5.48047 7.02 5.58047 5.04 5.78047L3 5.98047" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="#currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M18.8484 9.14062L18.1984 19.2106C18.0884 20.7806 17.9984 22.0006 15.2084 22.0006H8.78844C5.99844 22.0006 5.90844 20.7806 5.79844 19.2106L5.14844 9.14062" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M10.3281 16.5H13.6581" stroke="#currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M9.5 12.5H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								

							</button>
							<button class="tc-btn tc-btn--sm tc-btn--outline tc-address-edit flex justify-between"
								data-id="<?php echo esc_attr($addr['id']); ?>"
								data-address="<?php echo esc_attr(wp_json_encode($addr)); ?>">
								
								ویرایش آدرس
								<svg  viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M13.2594 3.59924L5.04936 12.2892C4.73936 12.6192 4.43936 13.2692 4.37936 13.7192L4.00936 16.9592C3.87936 18.1292 4.71936 18.9292 5.87936 18.7292L9.09936 18.1792C9.54936 18.0992 10.1794 17.7692 10.4894 17.4292L18.6994 8.73924C20.1194 7.23924 20.7594 5.52924 18.5494 3.43924C16.3494 1.36924 14.6794 2.09924 13.2594 3.59924Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M11.8906 5.05078C12.3206 7.81078 14.5606 9.92078 17.3406 10.2008" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M3 22H21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>

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
					<label for="tc-addr-fixedphone">تلفن ثابت <span class="required">*</span></label>
					<input type="tel" id="tc-addr-fixedphone" placeholder="0xxxxxxxxx" dir="ltr">
				</div>
				<div class="tc-form-field">
					<label for="tc-addr-postcode">کد پستی <span class="required">*</span></label>
					<input type="text" id="tc-addr-postcode" placeholder="کد پستی" dir="ltr">
				</div>
				<div class="tc-form-field">
					<label for="tc-addr-nationalcode">کد ملی <span class="required">*</span></label>
					<input type="text" id="tc-addr-nationalcode" placeholder="کد ملی" dir="ltr">
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

		<h2 class="tc-section-title bg-black-03 color-black-80">انتخاب شیوه پرداخت</h2>

		<div class="tc-payment-methods">
			<?php
			$first_gateway = true;
			foreach (array_reverse($payment_gateways, true) as $gw_id => $gateway):
				$icon_svg = $gateway_icons[$gw_id] ?? $gateway_icons['_default'];
			?>
				<label class="tc-payment-card" data-method="<?php echo esc_attr($gw_id); ?>">
					<input type="radio" name="tc_payment_method" value="<?php echo esc_attr($gw_id); ?>"
						class="tc-payment-radio" <?php checked($first_gateway, true); ?>>
					<span class="tc-payment-card__check">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
							<path d="M4 12l6 6L20 6" stroke="white" stroke-width="2.5" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</span>
					<div class="tc-payment-card__icon">
						<?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG is hardcoded ?>
					</div>
					<div class="tc-payment-card__body">
						<div class="tc-payment-card__title"><?php echo esc_html($gateway->get_title()); ?></div>
						<div class="tc-payment-card__desc"><?php echo esc_html($gateway->get_description()); ?></div>
					</div>
				</label>
			<?php
				$first_gateway = false;
			endforeach;
			?>
		</div>

		<div class="tc-payment-warning" >
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
				<path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10zm0-6v-4m0-4h.01"
					stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
			<span>هزینه ارسال محصول، به صورت پس کرایه می باشد.</span>
			<a href="#" class="tc-payment-warning__link" id="tc-cod-info-link">پس کرایه چیست؟</a>
		</div>

		<div class="tc-payment-warning payments">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
				<path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10zm0-6v-4m0-4h.01"
					stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
			<span>انواع روش های پرداخت نقد و اقساط</span>
			<a href="<?php echo esc_url(  get_option( 'all_payments_blog_url', 'option' ) ); ?>" class="tc-payment-warning__link">بیشتر بدانید</a>
		</div>

	</div>

	<!-- ── Step 4: Invoice ──────────────────────────────── -->
	<div class="tc-checkout__step" data-step-panel="4">
		<div class="tc-order-success flex flex-col gap-10">
			
			<div class="tc-order-success__msg text-center w-full">سفارش شما با موفقیت ثبت شد</div>
			<div class="tc-order-success__num flex justify-between"><span class="flex-shrink-0">کد پیگیری سفارش:</span> <span id="tc-order-number"></span></div>
		</div>
		<div class="tc-order-support_msg color-primary text-center">
			سفارش شما در حال بررسی و تایید مدیرت فروش می باشد. برای پیگیری سفارش خود می توانید با کارشناسان فروش ما در ارتباط باشید. کارشناسان ما در 24 ساعت آینده برای تایید نهایی سفارش با شما تماس خواهند گرفت
		</div>
		<div class="tc-invoice-actions flex gap-10">
			<button class="tc-btn tc-btn--outline-primary" onclick="window.print()">
				دانلود پیش فاکتور
			</button>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="tc-btn tc-btn--primary">پیگیری سفارش</a>
		</div>
		<div class="tc-invoice" id="tc-invoice">
			<!-- rendered by JS -->
		</div>
		
	</div>

	<!-- ── COD Info Modal ───────────────────────────────── -->
	<div class="tc-modal-overlay" id="tc-cod-modal" aria-hidden="true">
		<div class="tc-modal" role="dialog" aria-modal="true" dir="rtl">
			<button class="tc-modal__close" id="tc-cod-modal-close" aria-label="بستن">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M9.16992 14.8299L14.8299 9.16992" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M14.8299 14.8299L9.16992 9.16992" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<h3 class="tc-modal__title">پس کرایه چیست؟</h3>
			<p class="tc-modal__body">
				<?php the_field('whats_the_afterpay_text', 'option'); ?>
			</p>
		</div>
	</div>

	<!-- ── Footer bar ───────────────────────────────────── -->
	<div class="tc-checkout__footer" id="tc-checkout-footer">
		<div class="tc-checkout__footer-total flex flex-col md:flex-row items-start md:items-center">
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
		<div class="flex gap-30 items-center flex-wrap">
			<div class="color-primary">
				<strong id="tc-cart-total"><?php echo esc_html($cart_total); ?></strong>
				<span><?php echo wp_kses_post(get_woocommerce_currency_symbol()); ?></span>
			</div>
			<button class="tc-btn tc-btn--primary tc-btn--lg tc-checkout-next" id="tc-checkout-next">
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