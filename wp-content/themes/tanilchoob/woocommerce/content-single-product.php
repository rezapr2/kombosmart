<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
	<div class="product-container container flex mt-40">
		<div class="product-summary flex flex-col gap-04">
			<h1 class="product-title"><?php the_title(); ?></h1>
			<div class="product-comments-ratings flex items-center yekan-16">
				<div class="product-rating">
					<?php woocommerce_template_single_rating(); ?>
				</div>
				<div class="product-comments">
					12 دیدگاه کاربران
				</div>
				<div class="product-questions-answers">
					25 پرسش و پاسخ
				</div>
			</div>
			<!-- Product offer countdown -->
			<?php
			$sale_end_date = $product->get_date_on_sale_to();
			if ($sale_end_date): ?>
				<div class="product-offer-countdown">
					<div class="countdown-timer regular flex items-center gap-10 justify-between" data-end-date="<?php echo esc_attr($sale_end_date->date('Y-m-d H:i:s')); ?>">
						<div class="countdown-timer__time flex items-center gap-10">
							<div class="flex flex-col text-center">
								<div class="countdown-timer__seconds yekan-30">00</div>
								<div class="countdown-timer__label yekan-18">ثانیه</div>
							</div>
							<div class="flex flex-col text-center">
								<div class="countdown-timer__minutes yekan-30">00</div>
								<div class="countdown-timer__label yekan-18">دقیقه</div>
							</div>
							<div class="flex flex-col text-center">
								<div class="countdown-timer__hours yekan-30">00</div>
								<div class="countdown-timer__label yekan-18">ساعت</div>
							</div>
							<div class="flex flex-col text-center">
								<div class="countdown-timer__days yekan-30">00</div>
								<div class="countdown-timer__label yekan-18">روز</div>
							</div>
						</div>
					</div>
				</div>

			<?php endif; ?>
			<div class="product-status">
				<span>وضعیت محصول :</span>
				<?php 
				$product_status = get_post_meta($product->get_id(), '_product_status', true);
				$status_labels = array(
					'in_stock' => __('موجود و آماده ارسال', 'tanilchoob'),
					'in_produce' => __('در حال تولید', 'tanilchoob'),
					'out_of_stock_temporary' => __('توقف موقت تولید', 'tanilchoob'),
					'out_of_stock' => __('توقف کامل تولید', 'tanilchoob'),
				);
				$status_label = isset($status_labels[$product_status]) ? $status_labels[$product_status] : $status_labels['in_stock'];
				?>
				<span><?php echo esc_html($status_label); ?></span>
			</div>
		</div>
		<div class="product-images">
		</div>
		<!-- <div class="product-summary">
			<div class="summary entry-summary">
				<?php
				/**
				 * Hook: woocommerce_single_product_summary.
				 *
				 * @hooked woocommerce_template_single_title - 5
				 * @hooked woocommerce_template_single_rating - 10
				 * @hooked woocommerce_template_single_price - 10
				 * @hooked woocommerce_template_single_excerpt - 20
				 * @hooked woocommerce_template_single_add_to_cart - 30
				 * @hooked woocommerce_template_single_meta - 40
				 * @hooked woocommerce_template_single_sharing - 50
				 * @hooked WC_Structured_Data::generate_product_data() - 60
				 */
				do_action('woocommerce_single_product_summary');
				?>
			</div>
		</div>
		<div class="product-images">
			<?php

			?>
		</div> -->


	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	/* 	do_action('woocommerce_after_single_product_summary');
 */	?>
</div>

<?php do_action('woocommerce_after_single_product'); ?>