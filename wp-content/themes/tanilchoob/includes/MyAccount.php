<?php

namespace TanilChoob\Theme;

class MyAccount {

	const REWRITE_VERSION = '1.1';

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'track_product_view' ] );
		add_action( 'init', [ $this, 'register_endpoints' ] );
		add_filter( 'woocommerce_account_menu_items', [ $this, 'filter_menu_items' ] );
		add_action( 'woocommerce_account_recently-viewed_endpoint', [ $this, 'recently_viewed_content' ] );
		add_action( 'woocommerce_account_reviews_endpoint',         [ $this, 'reviews_content' ] );
		add_action( 'woocommerce_account_questions_endpoint',       [ $this, 'questions_content' ] );
		add_action( 'woocommerce_account_messages_endpoint',        [ $this, 'messages_content' ] );
		add_action( 'woocommerce_account_wishlist_endpoint',        [ $this, 'wishlist_content' ] );
		add_action( 'woocommerce_account_tc-addresses_endpoint',   [ $this, 'tc_addresses_content' ] );
		add_action( 'wp_ajax_tc_delete_review',         [ $this, 'ajax_delete_review' ] );
		add_action( 'wp_ajax_tc_toggle_wishlist',       [ $this, 'ajax_toggle_wishlist' ] );
		add_action( 'woocommerce_save_account_details', [ $this, 'save_account_custom_fields' ] );
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

	public function save_account_custom_fields( int $user_id ) {
		if ( isset( $_POST['billing_national_id'] ) ) {
			update_user_meta( $user_id, 'billing_national_id', sanitize_text_field( $_POST['billing_national_id'] ) );
		}
		if ( isset( $_POST['billing_phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
		}
		update_user_meta( $user_id, 'tc_newsletter', ! empty( $_POST['tc_newsletter'] ) ? '1' : '' );
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

	public function track_product_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$viewed = empty( $_COOKIE['woocommerce_recently_viewed'] )
			? []
			: wp_parse_id_list( explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) );

		$product_id = get_the_ID();

		// Move to end (most recent) if already present
		$viewed = array_diff( $viewed, [ $product_id ] );
		$viewed[] = $product_id;

		if ( \count( $viewed ) > 15 ) {
			array_shift( $viewed );
		}

		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed ) );
	}

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
				<div class="wc-account-product-row__action flex flex-col">
					<a href="<?php echo esc_url( $permalink ); ?>" class="btn-view-product">مشاهده محصول</a>
					<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-recently-viewed-delete" data-id="<?php echo esc_attr( $product_id ); ?>">
						حذف
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21 5.98C17.67 5.65 14.32 5.48 10.98 5.48c-1.98 0-3.96.1-5.94.3L3 5.98" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C5.999 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10.33 16.5h3.33M9.5 12.5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<script>
		(function($){
			$(document).on('click', '.tc-recently-viewed-delete', function(){
				var $btn = $(this);
				var id = String($btn.data('id'));
				var cookie = decodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)woocommerce_recently_viewed\s*=\s*([^;]*).*$)|^.*$/, '$1'));
				var ids = cookie ? cookie.split('|').filter(function(v){ return v && v !== id; }) : [];
				var expires = ids.length ? '; path=/; max-age=' + (60 * 60 * 24 * 30) : '; path=/; max-age=0';
				document.cookie = 'woocommerce_recently_viewed=' + ids.join('|') + expires;
				$btn.closest('.wc-account-product-row').fadeOut(300, function(){ $(this).remove(); });
			});
		})(jQuery);
		</script>
		<?php
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

		wp_localize_script( 'scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ajax-nonce' ),
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
			?>
			<div class="wc-account-review-item" data-id="<?php echo esc_attr( $comment->comment_ID ); ?>">
				<div class="wc-account-review-item__product">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				</div>
				<p class="wc-account-review-item__text"><?php echo esc_html( $comment->comment_content ); ?></p>
				<div class="wc-account-review-item__footer">
					<span class="wc-account-review-item__date"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></span>
					<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-review-delete" data-id="<?php echo esc_attr( $comment->comment_ID ); ?>">
						حذف نظر
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21 5.98C17.67 5.65 14.32 5.48 10.98 5.48c-1.98 0-3.96.1-5.94.3L3 5.98" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C5.999 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10.33 16.5h3.33M9.5 12.5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<script>
		(function($){
			$(document).on('click', '.tc-review-delete', function(){
				var $btn = $(this);
				var id = $btn.data('id');
				if (!confirm('آیا از حذف این نظر اطمینان دارید؟')) return;
				$btn.prop('disabled', true);
				$.post(tcCheckout.ajaxUrl, { action: 'tc_delete_review', nonce: tcCheckout.nonce, comment_id: id }, function(res){
					if (res && res.success) {
						$btn.closest('.wc-account-review-item').fadeOut(300, function(){ $(this).remove(); });
					} else {
						alert(res && res.data ? res.data : 'خطا در حذف نظر');
						$btn.prop('disabled', false);
					}
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	public function ajax_delete_review() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$comment_id = absint( $_POST['comment_id'] ?? 0 );
		if ( ! $comment_id ) {
			wp_send_json_error( 'شناسه نظر نامعتبر است' );
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment || (int) $comment->user_id !== get_current_user_id() ) {
			wp_send_json_error( 'دسترسی غیرمجاز' );
		}

		if ( wp_delete_comment( $comment_id, true ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( 'حذف نظر با خطا مواجه شد' );
		}
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
		$user_id  = get_current_user_id();
		$messages = \TanilChoob\Theme\PostType\CustomerMessage::get_for_user( $user_id );

		wp_localize_script( 'scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ajax-nonce' ),
		] );

		if ( empty( $messages ) ) {
			echo '<p class="wc-account-empty-msg">هیچ پیامی وجود ندارد.</p>';
			return;
		}
		?>
		<div class="tc-messages-list">
			<?php foreach ( $messages as $msg ) :
				$read    = get_post_meta( $msg->ID, '_tc_message_read', true );
				$subject = get_the_title( $msg );
				$body    = wpautop( wp_kses_post( $msg->post_content ) );
				$date    = get_the_date( 'Y/m/d', $msg );
			?>
			<div class="tc-message-item <?php echo $read ? 'is-read' : 'is-unread'; ?>" data-id="<?php echo esc_attr( $msg->ID ); ?>">
				<div class="tc-message-item__header">
					<span class="tc-message-item__subject"><?php echo esc_html( $subject ); ?></span>
					<span class="tc-message-item__date"><?php echo esc_html( $date ); ?></span>
					<?php if ( ! $read ) : ?>
					<span class="tc-message-item__badge">جدید</span>
					<?php endif; ?>
				</div>
				<div class="tc-message-item__body"><?php echo $body; ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<script>
		(function($){
			$('.tc-message-item.is-unread').each(function(){
				var id = $(this).data('id');
				$.post(tcCheckout.ajaxUrl, { action: 'tc_mark_message_read', nonce: tcCheckout.nonce, message_id: id });
				$(this).removeClass('is-unread').addClass('is-read').find('.tc-message-item__badge').remove();
			});
		})(jQuery);
		</script>
		<?php
	}

	// ── Wishlist ───────────────────────────────────────────────────────────

	public static function get_wishlist( int $user_id ): array {
		$list = get_user_meta( $user_id, 'tc_wishlist', true );
		return is_array( $list ) ? $list : [];
	}

	public function ajax_toggle_wishlist() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );

		$product_id = absint( $_POST['product_id'] ?? 0 );
		if ( ! $product_id ) {
			wp_send_json_error( 'شناسه محصول نامعتبر است' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'لطفاً وارد حساب کاربری خود شوید' );
		}

		$list = self::get_wishlist( $user_id );

		if ( in_array( $product_id, $list, true ) ) {
			$list = array_values( array_diff( $list, [ $product_id ] ) );
			$in_wishlist = false;
		} else {
			$list[] = $product_id;
			$in_wishlist = true;
		}

		update_user_meta( $user_id, 'tc_wishlist', $list );
		wp_send_json_success( [ 'in_wishlist' => $in_wishlist ] );
	}

	public function wishlist_content() {
		$user_id = get_current_user_id();
		$list    = self::get_wishlist( $user_id );

		wp_localize_script( 'scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ajax-nonce' ),
		] );

		if ( empty( $list ) ) {
			echo '<p class="wc-account-empty-msg">لیست علاقه‌مندی‌ها خالی است.</p>';
			return;
		}

		echo '<div class="wc-account-product-list">';
		foreach ( $list as $product_id ) {
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
			<div class="wc-account-product-row" data-id="<?php echo esc_attr( $product_id ); ?>">
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
				<div class="wc-account-product-row__action flex flex-col">
					<a href="<?php echo esc_url( $permalink ); ?>" class="btn-view-product">مشاهده محصول</a>
					<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-wishlist-remove" data-id="<?php echo esc_attr( $product_id ); ?>">
						حذف از علاقه‌مندی‌ها
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21 5.98C17.67 5.65 14.32 5.48 10.98 5.48c-1.98 0-3.96.1-5.94.3L3 5.98" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M8.5 4.97l.22-1.31C8.88 2.71 9 2 10.69 2h2.62C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M18.85 9.14l-.65 10.07C18.09 20.78 18 22 15.21 22H8.79C5.999 22 5.91 20.78 5.8 19.21L5.15 9.14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M10.33 16.5h3.33M9.5 12.5h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<script>
		(function($){
			$(document).on('click', '.tc-wishlist-remove', function(){
				var $btn = $(this);
				var id = $btn.data('id');
				$btn.prop('disabled', true);
				$.post(tcCheckout.ajaxUrl, { action: 'tc_toggle_wishlist', nonce: tcCheckout.nonce, product_id: id }, function(res){
					if (res && res.success) {
						$btn.closest('.wc-account-product-row').fadeOut(300, function(){ $(this).remove(); });
					} else {
						$btn.prop('disabled', false);
					}
				});
			});
		})(jQuery);
		</script>
		<?php
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
					<div class="tc-address-card flex justify-between" data-id="<?php echo esc_attr( $addr['id'] ); ?>">
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

			<div class="tc-address-modal-overlay" id="tc-address-modal-overlay" aria-hidden="true">
				<div class="tc-address-form" id="tc-address-form" role="dialog" aria-modal="true" dir="rtl">
					<button class="tc-address-form__close" id="tc-cancel-address-btn" aria-label="بستن">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
							<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</button>
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
					</div>
				</div>
			</div>

		</div>
		<?php
	}
}
