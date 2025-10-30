<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

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
	<div class="product-container container flex gap-15 mt-40 mb-40">
		<div class="product-summary flex flex-col w-full gap-04">
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
				<div class="product-offer-countdown ">
					<div class="countdown-timer regular" data-end-date="<?php echo esc_attr($sale_end_date->date('Y-m-d H:i:s')); ?>">
						<div class="countdown-timer__time  flex items-center gap-10 w-full h-100 justify-evenly">
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
			<div class="product-status flex gap-10 px-25 items-center">
				<span class="yekan-18">وضعیت محصول :</span>
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
				<span class="yekan-20 color-primary bold"><?php echo esc_html($status_label); ?></span>
			</div>
			<div class="buttons-wrapper flex gap-07 items-center">
				<div class="add-to-cart h-100 flex items-center gap-10 bg-black">
					<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.10156 2.10449H3.93219C5.06844 2.10449 5.96271 3.08293 5.86802 4.20866L4.99479 14.6874C4.8475 16.4023 6.20468 17.8752 7.9301 17.8752H19.1348C20.6498 17.8752 21.9754 16.6338 22.0911 15.1293L22.6593 7.23866C22.7855 5.49221 21.4599 4.07188 19.7029 4.07188H6.12053" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M17.0964 23.1458C17.8227 23.1458 18.4115 22.557 18.4115 21.8307C18.4115 21.1044 17.8227 20.5156 17.0964 20.5156C16.37 20.5156 15.7812 21.1044 15.7812 21.8307C15.7812 22.557 16.37 23.1458 17.0964 23.1458Z" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M8.68229 23.1458C9.4086 23.1458 9.9974 22.557 9.9974 21.8307C9.9974 21.1044 9.4086 20.5156 8.68229 20.5156C7.95598 20.5156 7.36719 21.1044 7.36719 21.8307C7.36719 22.557 7.95598 23.1458 8.68229 23.1458Z" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M9.46875 8.41699H22.0938" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<span class="yekan-22 color-white">افزودن به سبد خرید</span>
				</div>
				<div class="product-price h-100 flex items-center yekan-22 color-primary bold">
					<?php woocommerce_template_single_price(); ?>
				</div>
			</div>
		</div>
		<div class="product-images flex-shrink-0">
			<div class="product-gallery-container flex gap-10">

				<!-- Swiper Main -->
				<div class="product-gallery-main">
					<div class="swiper-container gallery-main overflow-hidden">
						<div class="swiper-wrapper">
							<?php

							$attachment_ids = $product->get_gallery_image_ids();
							$main_image_id = $product->get_image_id();

							// Add main image to the beginning of the gallery
							if ($main_image_id) {
								echo '<div class="swiper-slide">';
								echo wp_get_attachment_image($main_image_id, 'large');
								echo '</div>';
							}

							// Add gallery images
							if ($attachment_ids) {
								foreach ($attachment_ids as $attachment_id) {
									echo '<div class="swiper-slide">';
									echo wp_get_attachment_image($attachment_id, 'large');
									echo '</div>';
								}
							}
							?>
						</div>
					</div>
				</div>
				<!-- Swiper Thumbs -->
				<div class="product-gallery-thumbs">
					<div class="swiper-container gallery-thumbs overflow-hidden">
						<div class="swiper-wrapper">
							<?php


							// Add main image to the beginning of the gallery
							if ($main_image_id) {
								echo '<div class="swiper-slide">';
								echo '<div class="thumb-item">';
								echo wp_get_attachment_image($main_image_id, 'thumbnail');
								echo '</div>';
								echo '</div>';
							}

							// Add gallery images
							if ($attachment_ids) {
								foreach ($attachment_ids as $attachment_id) {
									echo '<div class="swiper-slide">';
									echo '<div class="thumb-item">';
									echo wp_get_attachment_image($attachment_id, 'thumbnail');
									echo '</div>';
									echo '</div>';
								}
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	$full_product_image = get_field('full_product_image');
	$sub_products = get_field('sub_products');
	if($full_product_image || $sub_products) :
	?>
	<div class="container-right sub-products-container overflow-hidden flex items-center mb-40 gap-40">
		<?php if($full_product_image) : ?>
			<div class="full-product-image z-index-5">
				<?php echo wp_get_attachment_image($full_product_image['ID'], 'medium', false, array('class' => 'w-full h-full block object-cover')); ?>
			</div>
		<?php endif; ?>
		<?php if($sub_products) : ?>
			<div class="sub-products swiper-container flex gap-10 overflow-hidden">
				<div class="swiper-wrapper gap-40">
				<?php foreach($sub_products as $sub_product) : ?>
					<div class="swiper-slide sub-product h-auto flex flex-col justify-content-end items-center gap-20 <?php if(!$sub_product['purchasable']) echo 'not-purchasable'; ?>">
						<?php echo wp_get_attachment_image($sub_product['image']['ID'], 'thumbnail'); ?>
						<a href="<?php echo $sub_product['link']; ?>" class="sub-product-info flex items-center flex-col">
							<?php if($sub_product['purchasable']) : ?>
								<div class="sub-product-name yekan-18 color-black-80"><?php echo $sub_product['title']; ?></div>
								<div class="sub-product-price yekan-16 color-primary"><?php echo $sub_product['price']; ?></div>
							<?php else : ?>
								<div class="sub-product-name yekan-18 color-black-50 text-center">غیر قابل فروش به صورت تکی</div>
							<?php endif; ?>
						</a>
					</div>
				<?php endforeach; ?>
					<div class="swiper-slide">
					</div>
				</div>
			</div>
		<?php endif; ?>

		</div>
	</div>
	<div class="container mb-40">
		<div class="tab-contents">
			<div class="tabs flex w-full">
				<div id="tab-desc" class="tab-item yekan-14 color-black-30 pointer active">توضیحات محصول</div>
				<div id="tab-specs" class="tab-item yekan-14 color-black-30 pointer">مشخصات کلی</div>
				<div id="tab-review" class="tab-item yekan-14 color-black-30 pointer">بررسی تخصصی</div>
				<div id="tab-dimensions" class="tab-item yekan-14 color-black-30 pointer">ابعاد محصول</div>
				<div id="tab-faqs" class="tab-item yekan-14 color-black-30 pointer">پرسش و پاسخ</div>
				<div id="tab-reviews" class="tab-item yekan-14 color-black-30 pointer">نظرات مشتریان</div>
				<div id="tab-maintenance" class="tab-item yekan-14 color-black-30 pointer">نحوه نگهداری محصول</div>
				<div id="tab-production" class="tab-item yekan-14 color-black-30 pointer">روند تولید</div>
			</div>
			<div class="tab-content flex flex-col gap-20">
				<div id="tab-desc-content" class="tab-content-item active">
					<?php echo $product->get_description(); ?>
				</div>
				<div id="tab-specs-content" class="tab-content-item">
					<?php echo $product->get_short_description(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; 
	$faqs = get_field('faqs');
	if ($faqs) : ?>
		<div class="product-faqs flex flex-col gap-20">
			<div class="container flex">
				<div class="flex flex-col gap-20">
					<div class="faq-title yekan-28 bold color-primary">سوالات متداول</div>
					<div class="faq-items flex flex-col gap-10">
						<?php foreach ($faqs as $faq) : ?>
							<div class="faq-item flex flex-col">
								<div class="faq-question yekan-20 color-black-80 flex justify-between items-center cursor-pointer">
									<span><?php echo $faq['question']; ?></span>
									<span class="faq-toggle thin">+</span>
								</div>
								<div class="faq-answer yekan-20 color-black" style="display: none;"><?php echo $faq['answer']; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="faqs-desc flex flex-col items-center justify-center">
					<?php
						$faq_icon = Helper::getAssetUri('images/faq_icon.png');
						echo '<img src="' . esc_url($faq_icon) . '" alt="FAQ Icon" />';
					?>
					<div class="faq-icon-text yekan-26 color-black-80">شما عزیزان می توانید با مراجعه به بخش <a href="#faq-items" class="color-white bg-black px-25 inline-block">( پرسش های متدوال)</a>  بخش تمامی سوالات احتمالی خود را دریافت کنید</div>
				</div>
			</div>
			
		</div>
	<?php endif; ?>
							



</div>

<?php do_action('woocommerce_after_single_product'); ?>