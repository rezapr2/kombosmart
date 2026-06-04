<?php

/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/review.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container">



		<div class="comment-text">
			<div class="flex flex-col">
				<span class="yekan-16 md:yekan-20 color-black-60"><?php comment_author(); ?></span>
				<div class="flex items-center">
					<span class="yekan-10 md:yekan-18 color-black-40">در تاریخ: <?php echo esc_html(\TanilChoob\Theme\Helper::jalali_date((int) get_comment_date('U'))); ?></span>
					<?php
					// Custom star display using theme icons, replacing default Woo hook output.
					$rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
					if ($rating > 0) :
						$aria_label = sprintf(esc_html__('Rated %d out of 5', 'woocommerce'), $rating);
					?>
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
			<div class="yekan-14 md:yekan-18 color-black-70 description"><?php comment_text(); ?></div>

			<?php


		

			do_action('woocommerce_review_after_comment_text', $comment);
			?>

		</div>
	</div>