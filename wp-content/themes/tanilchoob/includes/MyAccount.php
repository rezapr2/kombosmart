<?php

namespace TanilChoob\Theme;

class MyAccount {

	const REWRITE_VERSION = '1.1';

	public function __construct() {
		add_action( 'init', [ $this, 'register_endpoints' ] );
		add_filter( 'woocommerce_account_menu_items', [ $this, 'filter_menu_items' ] );
		add_action( 'woocommerce_account_recently-viewed_endpoint', [ $this, 'recently_viewed_content' ] );
		add_action( 'woocommerce_account_reviews_endpoint',         [ $this, 'reviews_content' ] );
		add_action( 'woocommerce_account_questions_endpoint',       [ $this, 'questions_content' ] );
		add_action( 'woocommerce_account_messages_endpoint',        [ $this, 'messages_content' ] );
		add_action( 'woocommerce_account_wishlist_endpoint',        [ $this, 'wishlist_content' ] );
		add_action( 'woocommerce_account_tc-addresses_endpoint',   [ $this, 'tc_addresses_content' ] );
	}

	public function register_endpoints() {
		add_rewrite_endpoint( 'recently-viewed', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'reviews',         EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'questions',       EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'messages',        EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'wishlist',        EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'tc-addresses',   EP_ROOT | EP_PAGES );

		if ( get_option( 'tc_myaccount_rewrite_v' ) !== self::REWRITE_VERSION ) {
			flush_rewrite_rules();
			update_option( 'tc_myaccount_rewrite_v', self::REWRITE_VERSION );
		}
	}

	public function filter_menu_items( $items ) {
		return [
			'orders'          => 'سفارش های من',
			'reviews'         => 'نظرات',
			'recently-viewed' => 'آخرین کالاهای دیده شده',
			'wishlist'        => 'کالاهای مورد علاقه',
			'questions'       => 'پرسش و پاسخ',
			'edit-account'    => 'مشخصات فردی',
			'tc-addresses'    => 'آدرس‌های من',
			'messages'        => 'پیام ها',
			'customer-logout' => 'خروج از حساب کاربری',
		];
	}

	// ── Recently Viewed ────────────────────────────────────────────────────

	public function recently_viewed_content() {
		$viewed = isset( $_COOKIE['woocommerce_recently_viewed'] )
			? array_reverse( array_filter( array_map( 'absint', explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ) ) )
			: [];

		if ( empty( $viewed ) ) {
			echo '<p class="wc-account-empty-msg">هیچ محصولی مشاهده نشده است.</p>';
			return;
		}

		echo '<div class="wc-account-product-list">';
		foreach ( $viewed as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}
			$title      = $product->get_name();
			$permalink  = $product->get_permalink();
			$image_id   = $product->get_image_id();
			$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : wc_placeholder_img_src();
			$price_html = $product->get_price_html();
			if ( $price_html ) {
				$price_html = preg_replace( '/<\/?(ins)[^>]*>/', '', $price_html );
			}
			?>
			<div class="wc-account-product-row">
				<div class="wc-account-product-row__img">
					<a href="<?php echo esc_url( $permalink ); ?>">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
					</a>
				</div>
				<div class="wc-account-product-row__info">
					<h3 class="wc-account-product-row__title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					</h3>
					<?php if ( $price_html ) : ?>
					<p class="wc-account-product-row__price"><?php echo $price_html; ?></p>
					<?php endif; ?>
				</div>
				<div class="wc-account-product-row__action">
					<a href="<?php echo esc_url( $permalink ); ?>" class="btn-view-product">مشاهده محصول</a>
				</div>
			</div>
			<?php
		}
		echo '</div>';
	}

	// ── Reviews ────────────────────────────────────────────────────────────

	public function reviews_content() {
		$user_id  = get_current_user_id();
		$comments = get_comments( [
			'user_id' => $user_id,
			'type'    => 'review',
			'status'  => 'approve',
			'number'  => 20,
		] );

		if ( empty( $comments ) ) {
			echo '<p class="wc-account-empty-msg">هنوز نظری ثبت نکرده‌اید.</p>';
			return;
		}

		echo '<div class="wc-account-reviews-list">';
		foreach ( $comments as $comment ) {
			$product = wc_get_product( $comment->comment_post_ID );
			if ( ! $product ) {
				continue;
			}
			$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
			?>
			<div class="wc-account-review-item">
				<div class="wc-account-review-item__product">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				</div>
				<p class="wc-account-review-item__text"><?php echo esc_html( $comment->comment_content ); ?></p>
				<span class="wc-account-review-item__date"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></span>
			</div>
			<?php
		}
		echo '</div>';
	}

	// ── Questions ──────────────────────────────────────────────────────────

	public function questions_content() {
		$user_id   = get_current_user_id();
		$questions = get_posts( [
			'post_type'      => 'product_questions',
			'author'         => $user_id,
			'numberposts'    => 20,
			'post_status'    => [ 'publish', 'pending' ],
		] );

		if ( empty( $questions ) ) {
			echo '<p class="wc-account-empty-msg">هنوز سوالی ثبت نکرده‌اید.</p>';
			return;
		}

		echo '<div class="wc-account-questions-list">';
		foreach ( $questions as $q ) {
			$product_id = get_post_meta( $q->ID, 'product_id', true );
			$product    = $product_id ? wc_get_product( $product_id ) : null;
			$answer     = get_post_meta( $q->ID, 'answer', true );
			?>
			<div class="wc-account-question-item">
				<?php if ( $product ) : ?>
				<div class="wc-account-question-item__product">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				</div>
				<?php endif; ?>
				<p class="wc-account-question-item__text"><?php echo esc_html( $q->post_title ); ?></p>
				<?php if ( $answer ) : ?>
				<div class="wc-account-question-item__answer"><?php echo esc_html( $answer ); ?></div>
				<?php endif; ?>
			</div>
			<?php
		}
		echo '</div>';
	}

	// ── Messages ───────────────────────────────────────────────────────────

	public function messages_content() {
		echo '<p class="wc-account-empty-msg">هیچ پیامی وجود ندارد.</p>';
	}

	// ── Wishlist ───────────────────────────────────────────────────────────

	public function wishlist_content() {
		echo '<p class="wc-account-empty-msg">لیست علاقه‌مندی‌ها خالی است.</p>';
	}

	// ── Addresses ─────────────────────────────────────────────────────────

	public function tc_addresses_content() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$addresses = get_user_meta( $user_id, 'tc_saved_addresses', true );
		if ( ! \is_array( $addresses ) ) {
			$addresses = [];
		}

		wp_localize_script( 'scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ajax-nonce' ),
		] );
		?>
		<div class="tc-account-addresses" dir="rtl">

			<div class="tc-addresses" id="tc-addresses-list">
				<?php if ( empty( $addresses ) ) : ?>
					<p class="tc-addresses__empty">هنوز آدرسی ذخیره نکرده‌اید. یک آدرس جدید اضافه کنید.</p>
				<?php else : ?>
					<?php foreach ( $addresses as $i => $addr ) : ?>
					<div class="tc-address-card" data-id="<?php echo esc_attr( $addr['id'] ); ?>">
						<label class="tc-address-card__inner">
							<input type="radio" name="tc_selected_address" value="<?php echo esc_attr( $addr['id'] ); ?>" class="tc-address-radio" <?php checked( $i, 0 ); ?>>
							<span class="tc-address-card__check-icon">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 12l6 6L20 6" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<div class="tc-address-card__body">
								<div class="tc-address-card__name">
									<strong>نام و نام خوادگی تحویل گیرنده :</strong>
									<span><?php echo esc_html( $addr['first_name'] . ' ' . $addr['last_name'] ); ?></span>
								</div>
								<div class="tc-address-card__addr">
									<strong>آدرس :</strong>
									<span><?php echo esc_html( ( $addr['city'] ? $addr['city'] . '، ' : '' ) . $addr['address_1'] ); ?></span>
								</div>
								<div class="tc-address-card__meta">
									<span><strong>شماره تماس :</strong> <?php echo esc_html( $addr['phone'] ); ?></span>
									<?php if ( $addr['postcode'] ) : ?>
										<span><strong>کد پستی :</strong> <?php echo esc_html( $addr['postcode'] ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</label>
						<div class="tc-address-card__actions">
							<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-address-delete" data-id="<?php echo esc_attr( $addr['id'] ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 5.98c-3.33-.33-6.68-.5-10.02-.5-1.98 0-3.96.1-5.94.3L3 5.98M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62c1.69 0 1.82.75 1.97 1.67l.22 1.3M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
								حذف آدرس
							</button>
							<button class="tc-btn tc-btn--sm tc-btn--outline tc-address-edit"
								data-id="<?php echo esc_attr( $addr['id'] ); ?>"
								data-address="<?php echo esc_attr( wp_json_encode( $addr ) ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6l-8.21 8.69c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.13.71 1.93 1.83 1.75l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21.63-4.74-1.44-1.54-3.12-.94-4.03.62z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
		<?php
	}
}
