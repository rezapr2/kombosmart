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
		add_action('woocommerce_account_messages_endpoint', [$this, 'messages_content']);
		add_action('woocommerce_account_wishlist_endpoint', [$this, 'wishlist_content']);
		add_action('woocommerce_account_tc-addresses_endpoint', [$this, 'tc_addresses_content']);
		add_action('wp_ajax_tc_delete_review', [$this, 'ajax_delete_review']);
		add_action('wp_ajax_tc_toggle_wishlist', [$this, 'ajax_toggle_wishlist']);
		add_action('woocommerce_save_account_details', [$this, 'save_account_custom_fields']);
		add_filter('wc_order_statuses', [$this, 'rename_order_statuses']);
		add_action('init', [$this, 'rename_completed_post_status'], 20);
	}

	public function register_endpoints()
	{
		add_rewrite_endpoint('recently-viewed', EP_ROOT | EP_PAGES);
		add_rewrite_endpoint('reviews', EP_ROOT | EP_PAGES);
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
			'edit-account' => 'مشخصات فردی',
			'tc-addresses' => 'آدرس‌های من',
			'messages' => 'اطلاع رسانی',
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
			$date        = Helper::jalali_date((int) get_comment_date('U', $comment));
			$replies     = get_comments([
				'parent'  => $comment->comment_ID,
				'status'  => 'approve',
				'number'  => 1,
			]);
			$reply = !empty($replies) ? $replies[0] : null;
			?>
			<div class="wc-account-review-item comment_container" data-id="<?php echo esc_attr($comment->comment_ID); ?>">
				<div class="w-full flex justify-between flex-col-reverse md:flex-row">
					<span class="yekan-16 md:yekan-20 color-black-80"><?php echo esc_html($author); ?></span>
					<a class="wc-account-review-item__product" href="<?php echo esc_url($product->get_permalink()); ?>">
						<?php echo esc_html($product->get_name()); ?>
					</a>
				</div>
				
				<div class="comment-text">
					<div class="flex flex-col">
						<div class="flex items-center gap-20">
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

	// ── Messages ───────────────────────────────────────────────────────────

	public function messages_content()
	{
		$user_id  = get_current_user_id();
		$view_id  = absint($_GET['message'] ?? 0);

		wp_localize_script('scripts', 'tcCheckout', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('ajax-nonce'),
		]);

		// ── Single message view ────────────────────────────────────────────────
		if ($view_id) {
			$messages = \TanilChoob\Theme\PostType\CustomerMessage::get_for_user($user_id);
			$msg = null;
			foreach ($messages as $m) {
				if ((int) $m->ID === $view_id) { $msg = $m; break; }
			}

			if (!$msg) {
				echo '<p class="wc-account-empty-msg">پیام یافت نشد.</p>';
				return;
			}

			$read_message_ids = (array) get_user_meta($user_id, '_tc_read_messages', true);
			$is_all_msg   = (bool) get_post_meta($msg->ID, '_tc_message_recipient_all', true);
			$is_group_msg = (bool) get_post_meta($msg->ID, '_tc_message_group_id', true);
			$read = ( $is_all_msg || $is_group_msg )
				? \in_array($msg->ID, $read_message_ids, true)
				: (bool) get_post_meta($msg->ID, '_tc_message_read', true);

			if (!$read) {
				// mark as read immediately
				if ($is_all_msg || $is_group_msg) {
					if (!\in_array($msg->ID, $read_message_ids, true)) {
						$read_message_ids[] = $msg->ID;
						update_user_meta($user_id, '_tc_read_messages', $read_message_ids);
					}
				} else {
					update_post_meta($msg->ID, '_tc_message_read', 1);
				}
			}

			$back_url = wc_get_account_endpoint_url('messages');
			?>
			<div class="tc-message-detail">
				<a href="<?php echo esc_url($back_url); ?>" class="tc-message-detail__back">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none">
						<path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					بازگشت به لیست پیام‌ها
				</a>
				<h2 class="tc-message-detail__title"><?php echo esc_html(get_the_title($msg)); ?></h2>
				<span class="tc-message-detail__date"><?php echo esc_html(Helper::jalali_date((int) get_post_time('U', false, $msg))); ?></span>
				<?php
				$video_url      = function_exists('get_field') ? get_field('video_url', $msg->ID) : '';
				$video_position = function_exists('get_field') ? get_field('video_position', $msg->ID) : 'bottom';
				if (!$video_position) $video_position = 'bottom';
				$video_block = '';
				if ($video_url) {
					$video_block = '<div class="tc-message-detail__video"><video src="' . esc_url($video_url) . '" controls playsinline preload="metadata"></video></div>';
				}
				if ($video_url && $video_position === 'top') echo $video_block;
				?>
				<div class="tc-message-detail__body"><?php echo apply_filters('the_content', $msg->post_content); ?></div>
				<?php if ($video_url && $video_position !== 'top') echo $video_block; ?>
			</div>
			<?php
			return;
		}

		// ── Messages list ──────────────────────────────────────────────────────
		$messages = \TanilChoob\Theme\PostType\CustomerMessage::get_for_user($user_id);

		if (empty($messages)) {
			echo '<p class="wc-account-empty-msg">هیچ پیامی وجود ندارد.</p>';
			return;
		}

		$read_message_ids = (array) get_user_meta($user_id, '_tc_read_messages', true);
		$base_url = wc_get_account_endpoint_url('messages');
		?>
		<table class="tc-messages-table">
			<thead>
				<tr>
					<th>عنوان پیام</th>
					<th>تاریخ</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($messages as $msg):
				$is_all_msg   = (bool) get_post_meta($msg->ID, '_tc_message_recipient_all', true);
				$is_group_msg = (bool) get_post_meta($msg->ID, '_tc_message_group_id', true);
				$read = ( $is_all_msg || $is_group_msg )
					? \in_array($msg->ID, $read_message_ids, true)
					: (bool) get_post_meta($msg->ID, '_tc_message_read', true);
				$subject  = get_the_title($msg);
				$date     = Helper::jalali_date((int) get_post_time('U', false, $msg));
				$view_url = add_query_arg('message', $msg->ID, $base_url);
				?>
				<tr class="tc-messages-table__row <?php echo $read ? 'is-read' : 'is-unread'; ?>">
					<td class="tc-messages-table__subject">
						<?php if (!$read): ?>
							<span class="tc-message-item__badge">جدید</span>
						<?php endif; ?>
						<a href="<?php echo esc_url($view_url); ?>"><?php echo esc_html($subject); ?></a>
					</td>
					<td class="tc-messages-table__date"><?php echo esc_html($date); ?></td>
					<td class="tc-messages-table__action">
						<a href="<?php echo esc_url($view_url); ?>" class="tc-btn tc-btn--sm tc-btn--outline">مشاهده</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
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
										<strong>نام و نام خوانوادگی تحویل گیرنده :</strong>
										<span><?php echo esc_html($addr['first_name'] . ' ' . $addr['last_name']); ?></span>
									</div>
									<div class="tc-address-card__addr">
										<strong>آدرس :</strong>
										<span><?php echo esc_html(($addr['city'] ? $addr['city'] . '، ' : '') . $addr['address_1']); ?></span>
									</div>

									<div class="tc-address-card__meta">
										<span><strong>شماره تماس :</strong> <?php echo esc_html($addr['phone']); ?></span>
										<?php if (!empty($addr['fixedphone'])): ?>
											<span><strong>تلفن ثابت :</strong> <?php echo esc_html($addr['fixedphone']); ?></span>
										<?php endif; ?>
										<?php if (!empty($addr['nationalcode'])): ?>
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
							<label for="tc-addr-fixedphone">تلفن ثابت <span class="required">*</span></label>
							<input type="tel" id="tc-addr-fixedphone" placeholder="تلفن ثابت" dir="ltr">
						</div>
						<div class="tc-form-field">
							<label for="tc-addr-nationalcode">کد ملی <span class="required">*</span></label>
							<input type="text" id="tc-addr-nationalcode" placeholder="کد ملی" dir="ltr">
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

	public function rename_order_statuses(array $statuses): array
	{
		$statuses['wc-processing'] = 'در حال تولید';
		$statuses['wc-on-hold']    = 'در حال ارسال';
		$statuses['wc-completed']  = 'تحویل شده';
		return $statuses;
	}

	public function rename_completed_post_status(): void
	{
		global $wp_post_statuses;

		$renames = [
			'wc-processing' => 'در حال تولید',
			'wc-on-hold'    => 'در حال ارسال',
			'wc-completed'  => 'تحویل شده',
		];

		foreach ($renames as $status => $label) {
			if (isset($wp_post_statuses[$status])) {
				$wp_post_statuses[$status]->label       = $label;
				$wp_post_statuses[$status]->label_count = _n_noop(
					"$label <span class=\"count\">(%s)</span>",
					"$label <span class=\"count\">(%s)</span>"
				);
			}
		}
	}
}
