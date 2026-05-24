<?php

namespace TanilChoob\Theme;

class MyAccount
{

	const REWRITE_VERSION = '1.1';

	public function __construct()
	{
		add_action('template_redirect', [$this, 'track_product_view']);
		add_action('init', [$this, 'register_endpoints']);
		add_filter('woocommerce_account_menu_items', [$this, 'filter_menu_items']);
		add_action('woocommerce_account_recently-viewed_endpoint', [$this, 'recently_viewed_content']);
		add_action('woocommerce_account_reviews_endpoint', [$this, 'reviews_content']);
		add_action('woocommerce_account_questions_endpoint', [$this, 'questions_content']);
		add_action('woocommerce_account_messages_endpoint', [$this, 'messages_content']);
		add_action('woocommerce_account_wishlist_endpoint', [$this, 'wishlist_content']);
		add_action('woocommerce_account_tc-addresses_endpoint', [$this, 'tc_addresses_content']);
		add_action('wp_ajax_tc_delete_review', [$this, 'ajax_delete_review']);
		add_action('wp_ajax_tc_toggle_wishlist', [$this, 'ajax_toggle_wishlist']);
		add_action('woocommerce_save_account_details', [$this, 'save_account_custom_fields']);
	}

	public function register_endpoints()
	{
		add_rewrite_endpoint('recently-viewed', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('reviews', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('questions', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('wishlist', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('tc-addresses', EP_ROOT | EP_PAGES);

		if (get_option('tc_myaccount_rewrite_v') !== self::REWRITE_VERSION) {
			flush_rewrite_rules();
			update_option('tc_myaccount_rewrite_v', self::REWRITE_VERSION);
		}
	}

	public function save_account_custom_fields(int $user_id)
	{
		if (isset($_POST['billing_national_id'])) {
			update_user_meta($user_id, 'billing_national_id', sanitize_text_field($_POST['billing_national_id']));
		}
		if (isset($_POST['billing_phone'])) {
			update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
		}
		update_user_meta($user_id, 'tc_newsletter', !empty($_POST['tc_newsletter']) ? '1' : '');
	}

	public function filter_menu_items($items)
	{
		return [
			'orders' => 'سفارش های من',
			'reviews' => 'نظرات',
			'recently-viewed' => 'آخرین کالاهای دیده شده',
			'wishlist' => 'کالاهای مورد علاقه',
			'questions' => 'پرسش و پاسخ',
			'edit-account' => 'مشخصات فردی',
			'tc-addresses' => 'آدرس‌های من',
			'messages' => 'پیام ها',
			'customer-logout' => 'خروج از حساب کاربری',
		];
	}

	// ── Recently Viewed ────────────────────────────────────────────────────

	public function track_product_view()
	{
		if (!is_singular('product')) {
			return;
		}

		$viewed = empty($_COOKIE['woocommerce_recently_viewed'])
			? []
			: wp_parse_id_list(explode('|', wp_unslash($_COOKIE['woocommerce_recently_viewed'])));

		$product_id = get_the_ID();

		// Move to end (most recent) if already present
		$viewed = array_diff($viewed, [$product_id]);
		$viewed[] = $product_id;

		if (\count($viewed) > 15) {
			array_shift($viewed);
		}

		wc_setcookie('woocommerce_recently_viewed', implode('|', $viewed));
	}

	public function recently_viewed_content()
	{
		$viewed = isset($_COOKIE['woocommerce_recently_viewed'])
			? array_reverse(array_filter(array_map('absint', explode('|', wp_unslash($_COOKIE['woocommerce_recently_viewed'])))))
			: [];

		if (empty($viewed)) {
			echo '<p class="wc-account-empty-msg">هیچ محصولی مشاهده نشده است.</p>';
			return;
		}

		echo '<div class="wc-account-product-list">';
		foreach ($viewed as $product_id) {
			$product = wc_get_product($product_id);
			if (!$product) {
				continue;
			}
			$title = $product->get_name();
			$permalink = $product->get_permalink();
			$image_id = $product->get_image_id();
			$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src();
			$price_html = $product->get_price_html();
			if ($price_html) {
				$price_html = preg_replace('/<\/?(ins)[^>]*>/', '', $price_html);
			}
			?>
			<div class="wc-account-product-row">
				<div class="wc-account-product-row__img">
					<a href="<?php echo esc_url($permalink); ?>">
						<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
					</a>
				</div>
				<div class="wc-account-product-row__info">
					<h3 class="wc-account-product-row__title">
						<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
					</h3>
					<?php if ($price_html): ?>
						<p class="wc-account-product-row__price"><?php echo $price_html; ?></p>
					<?php endif; ?>
				</div>
				<div class="wc-account-product-row__action flex flex-col">
					<a href="<?php echo esc_url($permalink); ?>" class="btn-view-product">مشاهده محصول</a>
					
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<script>
			(function ($) {
				$(document).on('click', '.tc-recently-viewed-delete', function () {
					var $btn = $(this);
					var id = String($btn.data('id'));
					var cookie = decodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)woocommerce_recently_viewed\s*=\s*([^;]*).*$)|^.*$/, '$1'));
					var ids = cookie ? cookie.split('|').filter(function (v) { return v && v !== id; }) : [];
					var expires = ids.length ? '; path=/; max-age=' + (60 * 60 * 24 * 30) : '; path=/; max-age=0';
					document.cookie = 'woocommerce_recently_viewed=' + ids.join('|') + expires;
					$btn.closest('.wc-account-product-row').fadeOut(300, function () { $(this).remove(); });
				});
			})(jQuery);
		</script>
		<?php
	}

	// ── Reviews ────────────────────────────────────────────────────────────

	public function reviews_content()
	{
		$user_id = get_current_user_id();
		$comments = get_comments([
			'user_id' => $user_id,
			'type' => 'review',
			'status' => 'approve',
			'number' => 20,
		]);

		// Mark all current reply IDs as seen
		$review_ids = wp_list_pluck($comments, 'comment_ID');
		if ($review_ids) {
			$reply_ids = get_comments([
				'parent__in' => $review_ids,
				'status'     => 'approve',
				'fields'     => 'ids',
			]);
			update_user_meta($user_id, '_tc_seen_review_replies', (array) $reply_ids);
		}

		wp_localize_script('scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce'),
		]);

		if (empty($comments)) {
			echo '<p class="wc-account-empty-msg">هنوز نظری ثبت نکرده‌اید.</p>';
			return;
		}

		echo '<div class="wc-account-reviews-list">';
		foreach ($comments as $comment) {
			$product = wc_get_product($comment->comment_post_ID);
			if (!$product) {
				continue;
			}
			$rating      = (int) get_comment_meta($comment->comment_ID, 'rating', true);
			$author      = trim($comment->comment_author) ?: 'ناشناس';
			$date        = get_comment_date('j F Y', $comment);
			$replies     = get_comments([
				'parent'  => $comment->comment_ID,
				'status'  => 'approve',
				'number'  => 1,
			]);
			$reply = !empty($replies) ? $replies[0] : null;
			?>
			<div class="wc-account-review-item comment_container" data-id="<?php echo esc_attr($comment->comment_ID); ?>">
				<div class="w-full flex justify-between">
					<span class="yekan-16 md:yekan-20 color-black-80"><?php echo esc_html($author); ?></span>
					<a class="wc-account-review-item__product" href="<?php echo esc_url($product->get_permalink()); ?>">
						<?php echo esc_html($product->get_name()); ?>
					</a>
				</div>
				
				<div class="comment-text">
					<div class="flex flex-col">
						<div class="flex items-center">
							<span class="yekan-10 md:yekan-14 color-black-50">در تاریخ: <?php echo esc_html($date); ?></span>
							<?php if ($rating > 0) : $aria_label = sprintf('Rated %d out of 5', $rating); ?>
							<div class="review-stars" role="img" aria-label="<?php echo esc_attr($aria_label); ?>">
								<?php for ($i = 1; $i <= 5; $i++) : $filled = $i <= $rating; ?>
									<span class="star <?php echo $filled ? 'filled' : ''; ?>" aria-hidden="true">
										<svg class="star-icon" width="14" height="14" viewBox="0 0 37 37" xmlns="http://www.w3.org/2000/svg" focusable="false">
											<path d="M21.6086 2.63915L24.8511 9.12417C25.2933 10.0269 26.4724 10.8928 27.4673 11.0586L33.3443 12.0351C37.1027 12.6614 37.987 15.3881 35.2788 18.0779L30.7098 22.6469C29.936 23.4207 29.5122 24.913 29.7517 25.9815L31.0598 31.6375C32.0915 36.1144 29.7149 37.8462 25.7539 35.5064L20.2453 32.2455C19.2504 31.6559 17.6108 31.6559 16.5975 32.2455L11.0889 35.5064C7.14629 37.8462 4.75126 36.096 5.78297 31.6375L7.09102 25.9815C7.33053 24.913 6.90679 23.4207 6.13301 22.6469L1.56402 18.0779C-1.12579 15.3881 -0.259893 12.6614 3.49847 12.0351L9.37552 11.0586C10.352 10.8928 11.5311 10.0269 11.9732 9.12417L15.2157 2.63915C16.9844 -0.879715 19.8584 -0.879715 21.6086 2.63915Z" fill="currentColor" />
										</svg>
									</span>
								<?php endfor; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="yekan-14 md:yekan-18 color-black-70 mt-10 description"><?php echo esc_html($comment->comment_content); ?></div>
					<?php if ($reply) : ?>
					<div class="wc-account-review-item__reply">
						<div class="wc-account-review-item__reply-header">
							<span class="wc-account-review-item__reply-label">مدیریت</span>
							<span class="wc-account-review-item__reply-date"><?php echo esc_html(get_comment_date('j F Y', $reply)); ?></span>
						</div>
						<p class="wc-account-review-item__reply-text"><?php echo esc_html($reply->comment_content); ?></p>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<?php
	}

	public function ajax_delete_review()
	{
		check_ajax_referer('ajax-nonce', 'nonce');

		$comment_id = absint($_POST['comment_id'] ?? 0);
		if (!$comment_id) {
			wp_send_json_error('شناسه نظر نامعتبر است');
		}

		$comment = get_comment($comment_id);
		if (!$comment || (int) $comment->user_id !== get_current_user_id()) {
			wp_send_json_error('دسترسی غیرمجاز');
		}

		if (wp_delete_comment($comment_id, true)) {
			wp_send_json_success();
		} else {
			wp_send_json_error('حذف نظر با خطا مواجه شد');
		}
	}

	// ── Questions ──────────────────────────────────────────────────────────

	public function questions_content()
	{
		$user_id = get_current_user_id();
		$questions = get_posts([
			'post_type' => 'product_questions',
			'author' => $user_id,
			'numberposts' => 999,
			'post_status' => ['publish', 'pending'],
		]);

		// Mark all answered question IDs as seen
		if ($questions) {
			$answered_ids = [];
			foreach ($questions as $q) {
				if (get_post_meta($q->ID, 'answer_text', true)) {
					$answered_ids[] = $q->ID;
				}
			}
			update_user_meta($user_id, '_tc_seen_question_answers', $answered_ids);
		}

		if (empty($questions)) {
			echo '<p class="wc-account-empty-msg">هنوز سوالی ثبت نکرده‌اید.</p>';
			return;
		}

		echo '<div class="wc-account-questions-list">';
		foreach ($questions as $q) {
			$product_id = get_post_meta($q->ID, 'product', true);
			$product = $product_id ? wc_get_product($product_id) : null;
			$answer = get_post_meta($q->ID, 'answer_text', true);
			?>
			<div class="wc-account-question-item">
				<?php if ($product): ?>
					<div class="wc-account-question-item__product">
						<a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
					</div>
				<?php endif; ?>
				<div class="wc-account-question-item__question flex items-center gap-15 relative">
					<div class="icon__box">
						<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M24.082 26.1101H18.4154L12.1112 30.3034C11.1762 30.9267 9.91536 30.261 9.91536 29.1276V26.1101C5.66536 26.1101 2.83203 23.2768 2.83203 19.0268V10.5267C2.83203 6.27669 5.66536 3.44336 9.91536 3.44336H24.082C28.332 3.44336 31.1654 6.27669 31.1654 10.5267V19.0268C31.1654 23.2768 28.332 26.1101 24.082 26.1101Z"
								stroke="#5D0E87" stroke-width="2.125" stroke-miterlimit="10" stroke-linecap="round"
								stroke-linejoin="round" />
							<path
								d="M17.0001 16.0937V15.7963C17.0001 14.833 17.5951 14.3229 18.1901 13.9121C18.7709 13.5154 19.3517 13.0055 19.3517 12.0705C19.3517 10.7671 18.3034 9.71875 17.0001 9.71875C15.6967 9.71875 14.6484 10.7671 14.6484 12.0705"
								stroke="#5D0E87" stroke-width="2.125" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M16.9949 19.4798H17.0077" stroke="#5D0E87" stroke-width="2.125" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>

					</div>
					<div class="flex flex-col gap-5">
						<p class="yekan-16 color-black-80">
							<?php echo esc_html($q->post_content); ?>
						</p>
						<span class="yekan-14 color-black-30 absolute bottom-0 left-0 px-15 py-15"><?php echo esc_html( get_the_date( 'Y/m/j', $q ) ); ?></span>
					</div>

				</div>
				<?php if ($answer): ?>

					<div class="wc-account-question-item__answer flex items-center gap-15">
						<div class="icon__box">
						<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M11.4136 24.8048V23.2098C8.25109 21.2985 5.65234 17.5723 5.65234 13.6123C5.65234 6.806 11.9086 1.471 18.9761 3.011C22.0836 3.6985 24.8061 5.761 26.2223 8.60725C29.0961 14.3823 26.0711 20.5148 21.6298 23.196V24.791C21.6298 25.1898 21.7811 26.111 20.3098 26.111H12.7336C11.2211 26.1248 11.4136 25.5335 11.4136 24.8048Z"
								stroke="black" stroke-width="2.0625" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M11.6875 30.2504C14.8362 29.3566 18.1637 29.3566 21.3125 30.2504" stroke="black"
								stroke-width="2.0625" stroke-linecap="round" stroke-linejoin="round" />
						</svg>


					</div>
					<p class="yekan-16 color-black-50">
						<?php echo esc_html($answer); ?>
					</p>
				</div>
			<?php endif; ?>
			</div>
			<?php
		}
		echo '</div>';
	}

	// ── Messages ───────────────────────────────────────────────────────────

	public function messages_content()
	{
		$user_id = get_current_user_id();
		$messages = \TanilChoob\Theme\PostType\CustomerMessage::get_for_user($user_id);

		wp_localize_script('scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce'),
		]);

		if (empty($messages)) {
			echo '<p class="wc-account-empty-msg">هیچ پیامی وجود ندارد.</p>';
			return;
		}
		?>
		<div class="tc-messages-list">
			<?php foreach ($messages as $msg):
				$read = get_post_meta($msg->ID, '_tc_message_read', true);
				$subject = get_the_title($msg);
				$body = wpautop(wp_kses_post($msg->post_content));
				$date = get_the_date('Y/m/d', $msg);
				?>
				<div class="tc-message-item <?php echo $read ? 'is-read' : 'is-unread'; ?>"
					data-id="<?php echo esc_attr($msg->ID); ?>">
					<div class="tc-message-item__header">
						<span class="tc-message-item__subject"><?php echo esc_html($subject); ?></span>
						<span class="tc-message-item__date"><?php echo esc_html($date); ?></span>
						<?php if (!$read): ?>
							<span class="tc-message-item__badge">جدید</span>
						<?php endif; ?>
					</div>
					<div class="tc-message-item__body"><?php echo $body; ?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<script>
			(function ($) {
				$('.tc-message-item.is-unread').each(function () {
					var id = $(this).data('id');
					$.post(tcCheckout.ajaxUrl, { action: 'tc_mark_message_read', nonce: tcCheckout.nonce, message_id: id });
					$(this).removeClass('is-unread').addClass('is-read').find('.tc-message-item__badge').remove();
				});
			})(jQuery);
		</script>
		<?php
	}

	// ── Wishlist ───────────────────────────────────────────────────────────

	public static function get_wishlist(int $user_id): array
	{
		$list = get_user_meta($user_id, 'tc_wishlist', true);
		return is_array($list) ? $list : [];
	}

	public function ajax_toggle_wishlist()
	{
		check_ajax_referer('ajax-nonce', 'nonce');

		$product_id = absint($_POST['product_id'] ?? 0);
		if (!$product_id) {
			wp_send_json_error('شناسه محصول نامعتبر است');
		}

		$user_id = get_current_user_id();
		if (!$user_id) {
			wp_send_json_error('لطفاً وارد حساب کاربری خود شوید');
		}

		$list = self::get_wishlist($user_id);

		if (in_array($product_id, $list, true)) {
			$list = array_values(array_diff($list, [$product_id]));
			$in_wishlist = false;
		} else {
			$list[] = $product_id;
			$in_wishlist = true;
		}

		update_user_meta($user_id, 'tc_wishlist', $list);
		wp_send_json_success(['in_wishlist' => $in_wishlist]);
	}

	public function wishlist_content()
	{
		$user_id = get_current_user_id();
		$list = self::get_wishlist($user_id);

		wp_localize_script('scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce'),
		]);

		if (empty($list)) {
			echo '<p class="wc-account-empty-msg">لیست علاقه‌مندی‌ها خالی است.</p>';
			return;
		}

		echo '<div class="wc-account-product-list">';
		foreach ($list as $product_id) {
			$product = wc_get_product($product_id);
			if (!$product) {
				continue;
			}
			$title = $product->get_name();
			$permalink = $product->get_permalink();
			$image_id = $product->get_image_id();
			$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src();
			$price_html = $product->get_price_html();
			if ($price_html) {
				$price_html = preg_replace('/<\/?(ins)[^>]*>/', '', $price_html);
			}
			?>
			<div class="wc-account-product-row" data-id="<?php echo esc_attr($product_id); ?>">
				<div class="wc-account-product-row__img">
					<a href="<?php echo esc_url($permalink); ?>">
						<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
					</a>
				</div>
				<div class="wc-account-product-row__info">
					<h3 class="wc-account-product-row__title">
						<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
					</h3>
					<?php if ($price_html): ?>
						<p class="wc-account-product-row__price"><?php echo $price_html; ?></p>
					<?php endif; ?>
				</div>
				<div class="wc-account-product-row__action flex flex-col">
					<a href="<?php echo esc_url($permalink); ?>" class="btn-view-product">مشاهده محصول</a>
					<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-wishlist-remove"
						data-id="<?php echo esc_attr($product_id); ?>">
						حذف
					</button>
				</div>
			</div>
			<?php
		}
		echo '</div>';
		?>
		<script>
			(function ($) {
				$(document).on('click', '.tc-wishlist-remove', function () {
					var $btn = $(this);
					var id = $btn.data('id');
					$btn.prop('disabled', true);
					$.post(tcCheckout.ajaxUrl, { action: 'tc_toggle_wishlist', nonce: tcCheckout.nonce, product_id: id }, function (res) {
						if (res && res.success) {
							$btn.closest('.wc-account-product-row').fadeOut(300, function () { $(this).remove(); });
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

	public function tc_addresses_content()
	{
		if (!is_user_logged_in()) {
			return;
		}

		$user_id = get_current_user_id();
		$addresses = get_user_meta($user_id, 'tc_saved_addresses', true);
		if (!\is_array($addresses)) {
			$addresses = [];
		}

		wp_localize_script('scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce'),
		]);
		?>
		<div class="tc-account-addresses" dir="rtl">

			<div class="tc-addresses" id="tc-addresses-list">
				<?php if (empty($addresses)): ?>
					<div class="tc-addresses__empty">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/no_address_vector.png" alt="No Address">
						<p class="">تاکنون آدرسی ثبت نکرده اید!</p>
					</div>
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
							<div class="tc-address-card__actions flex flex-col">
								<button class="tc-btn tc-btn--sm tc-btn--danger-outline tc-address-delete flex justify-between"
									data-id="<?php echo esc_attr($addr['id']); ?>">

									حذف آدرس

									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M21 5.98047C17.67 5.65047 14.32 5.48047 10.98 5.48047C9 5.48047 7.02 5.58047 5.04 5.78047L3 5.98047"
											stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97"
											stroke="#currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
										<path
											d="M18.8484 9.14062L18.1984 19.2106C18.0884 20.7806 17.9984 22.0006 15.2084 22.0006H8.78844C5.99844 22.0006 5.90844 20.7806 5.79844 19.2106L5.14844 9.14062"
											stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M10.3281 16.5H13.6581" stroke="#currentColor" stroke-width="1.5" stroke-linecap="round"
											stroke-linejoin="round" />
										<path d="M9.5 12.5H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
											stroke-linejoin="round" />
									</svg>


								</button>
								<button class="tc-btn tc-btn--sm tc-btn--outline tc-address-edit flex justify-between"
									data-id="<?php echo esc_attr($addr['id']); ?>"
									data-address="<?php echo esc_attr(wp_json_encode($addr)); ?>">

									ویرایش آدرس
									<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M13.2594 3.59924L5.04936 12.2892C4.73936 12.6192 4.43936 13.2692 4.37936 13.7192L4.00936 16.9592C3.87936 18.1292 4.71936 18.9292 5.87936 18.7292L9.09936 18.1792C9.54936 18.0992 10.1794 17.7692 10.4894 17.4292L18.6994 8.73924C20.1194 7.23924 20.7594 5.52924 18.5494 3.43924C16.3494 1.36924 14.6794 2.09924 13.2594 3.59924Z"
											stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
											stroke-linejoin="round" />
										<path d="M11.8906 5.05078C12.3206 7.81078 14.5606 9.92078 17.3406 10.2008" stroke="currentColor"
											stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M3 22H21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"
											stroke-linecap="round" stroke-linejoin="round" />
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
							<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
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
							<input type="tel" id="tc-addr-phone" placeholder="09xxxxxxxxx" dir="ltr" required>
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
