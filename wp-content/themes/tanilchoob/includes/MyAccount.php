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
	}

	public function register_endpoints() {
		add_rewrite_endpoint( 'recently-viewed', EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'reviews',         EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'questions',       EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'messages',        EP_ROOT | EP_PAGES );
		add_rewrite_endpoint( 'wishlist',        EP_ROOT | EP_PAGES );

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
			'edit-address'    => 'نشانی ها',
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
}
