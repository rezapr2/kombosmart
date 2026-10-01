<?php
/**
 * My Account navigation — custom with icons.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$icons = [
	'orders' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3.17188 7.43945L12.0019 12.5494L20.7719 7.46942" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 21.6091V12.5391" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.9306 2.48L4.59061 5.45003C3.38061 6.12003 2.39062 7.80001 2.39062 9.18001V14.83C2.39062 16.21 3.38061 17.89 4.59061 18.56L9.9306 21.53C11.0706 22.16 12.9406 22.16 14.0806 21.53L19.4206 18.56C20.6306 17.89 21.6206 16.21 21.6206 14.83V9.18001C21.6206 7.80001 20.6306 6.12003 19.4206 5.45003L14.0806 2.48C12.9306 1.84 11.0706 1.84 9.9306 2.48Z" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16.9978 13.2396V9.57965L7.50781 4.09961" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

	'reviews' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.98 10.79V14.79C17.98 15.05 17.97 15.3 17.94 15.54C17.71 18.24 16.12 19.58 13.19 19.58H12.79C12.54 19.58 12.3 19.7 12.15 19.9L10.95 21.5C10.42 22.21 9.56 22.21 9.03 21.5L7.82999 19.9C7.69999 19.73 7.41 19.58 7.19 19.58H6.79001C3.60001 19.58 2 18.79 2 14.79V10.79C2 7.86001 3.35001 6.27001 6.04001 6.04001C6.28001 6.01001 6.53001 6 6.79001 6H13.19C16.38 6 17.98 7.60001 17.98 10.79Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M21.9791 6.79001V10.79C21.9791 13.73 20.6291 15.31 17.9391 15.54C17.9691 15.3 17.9791 15.05 17.9791 14.79V10.79C17.9791 7.60001 16.3791 6 13.1891 6H6.78906C6.52906 6 6.27906 6.01001 6.03906 6.04001C6.26906 3.35001 7.85906 2 10.7891 2H17.1891C20.3791 2 21.9791 3.60001 21.9791 6.79001Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M13.4955 13.25H13.5045" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.9955 13.25H10.0045" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M6.4955 13.25H6.5045" stroke="#292D32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

	'recently-viewed' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3.17188 7.43945L12.0019 12.5494L20.7719 7.46942" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12 21.6091V12.5391" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.9306 2.48L4.59061 5.45003C3.38061 6.12003 2.39062 7.80001 2.39062 9.18001V14.83C2.39062 16.21 3.38061 17.89 4.59061 18.56L9.9306 21.53C11.0706 22.16 12.9406 22.16 14.0806 21.53L19.4206 18.56C20.6306 17.89 21.6206 16.21 21.6206 14.83V9.18001C21.6206 7.80001 20.6306 6.12003 19.4206 5.45003L14.0806 2.48C12.9306 1.84 11.0706 1.84 9.9306 2.48Z" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16.9978 13.2396V9.57965L7.50781 4.09961" stroke="#2F2F2F" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',
	'wishlist' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.62 20.8096C12.28 20.9296 11.72 20.9296 11.38 20.8096C8.48 19.8196 2 15.6896 2 8.68961C2 5.59961 4.49 3.09961 7.56 3.09961C9.38 3.09961 10.99 3.97961 12 5.33961C13.01 3.97961 14.63 3.09961 16.44 3.09961C19.51 3.09961 22 5.59961 22 8.68961C22 15.6896 15.52 19.8196 12.62 20.8096Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

	'edit-account' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16 2H8C4 2 2 4 2 8V21C2 21.55 2.45 22 3 22H16C20 22 22 20 22 16V8C22 4 20 2 16 2Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7 9.5H17" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M7 14.5H14" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

	'tc-addresses' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.0028 13.4295C13.7259 13.4295 15.1228 12.0326 15.1228 10.3095C15.1228 8.58633 13.7259 7.18945 12.0028 7.18945C10.2797 7.18945 8.88281 8.58633 8.88281 10.3095C8.88281 12.0326 10.2797 13.4295 12.0028 13.4295Z" stroke="#292D32" stroke-width="1.5"/>
<path d="M3.61776 8.49C5.58776 -0.169998 18.4178 -0.159997 20.3778 8.5C21.5278 13.58 18.3678 17.88 15.5978 20.54C13.5878 22.48 10.4078 22.48 8.38776 20.54C5.62776 17.88 2.46776 13.57 3.61776 8.49Z" stroke="#292D32" stroke-width="1.5"/>
</svg>
',

	'messages' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17 20.5H7C4 20.5 2 19 2 15.5V8.5C2 5 4 3.5 7 3.5H17C20 3.5 22 5 22 8.5V15.5C22 19 20 20.5 17 20.5Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M17 9L13.87 11.5C12.84 12.32 11.15 12.32 10.12 11.5L7 9" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',

	'customer-logout' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.89844 7.56023C9.20844 3.96023 11.0584 2.49023 15.1084 2.49023H15.2384C19.7084 2.49023 21.4984 4.28023 21.4984 8.75023V15.2702C21.4984 19.7402 19.7084 21.5302 15.2384 21.5302H15.1084C11.0884 21.5302 9.23844 20.0802 8.90844 16.5402" stroke="#FF0000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14.9972 12H3.61719" stroke="#FF0000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M5.85 8.65039L2.5 12.0004L5.85 15.3504" stroke="#FF0000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
',
];

do_action('woocommerce_before_account_navigation');

// Count new review replies
$user_id = get_current_user_id();
$review_badge = 0;

if ($user_id) {
	$user_reviews = get_comments([
		'user_id' => $user_id,
		'type'    => 'review',
		'status'  => 'approve',
		'number'  => 50,
		'fields'  => 'ids',
	]);
	if ($user_reviews) {
		$seen_reply_ids  = (array) get_user_meta($user_id, '_tc_seen_review_replies', true);
		$all_reply_ids   = get_comments([
			'parent__in' => $user_reviews,
			'status'     => 'approve',
			'fields'     => 'ids',
		]);
		$review_badge = count(array_diff((array) $all_reply_ids, $seen_reply_ids));
	}

}

$message_badge = 0;
if ($user_id) {
	$messages = \TanilChoob\Theme\PostType\CustomerMessage::get_for_user($user_id);
	if ($messages) {
		$read_message_ids = (array) get_user_meta($user_id, '_tc_read_messages', true);
		foreach ($messages as $msg) {
			$is_all_msg   = (bool) get_post_meta($msg->ID, '_tc_message_recipient_all', true);
			$is_group_msg = (bool) get_post_meta($msg->ID, '_tc_message_group_id', true);
			$read = ($is_all_msg || $is_group_msg)
				? in_array($msg->ID, $read_message_ids, true)
				: (bool) get_post_meta($msg->ID, '_tc_message_read', true);
			if (!$read) {
				$message_badge++;
			}
		}
	}
}

$nav_badges = [
	'reviews'   => $review_badge,
	'messages'  => $message_badge,
];
?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e('Account pages', 'woocommerce'); ?>">
	<ul>
		<?php foreach (wc_get_account_menu_items() as $endpoint => $label):
			$classes = wc_get_account_menu_item_classes($endpoint);
			$icon    = $icons[$endpoint] ?? '';
			$badge   = $nav_badges[$endpoint] ?? 0;
			?>
			<li class="<?php echo esc_attr($classes); ?>">
				<a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>>
					<?php if ($icon): ?>
						<span class="nav-icon" aria-hidden="true"><?php echo $icon; ?></span>
					<?php endif; ?>
					<span class="nav-label"><?php echo esc_html($label); ?></span>
					<?php if ($badge > 0): ?>
						<span class="nav-badge">
							<span class="nav-badge__count">+<?php echo esc_html($badge); ?></span>
							<span class="nav-badge__text"><?php
								if ($endpoint === 'reviews') echo 'پاسخ جدید برای نظر شما';
								else echo 'پیام جدید';
							?></span>
						</span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>