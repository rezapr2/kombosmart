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
			if ($sale_end_date && $sale_end_date > new DateTime()): ?>
				<div class="product-offer-countdown ">
					<div class="countdown-timer regular" data-end-date="<?php echo esc_attr($sale_end_date->date('Y-m-d H:i:s')); ?>">
						<div class="countdown-timer__time flex  w-full h-100 justify-evenly">
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
				<div class="add-to-cart h-100 flex py-20 px-40 bg-black">
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
	if ($full_product_image || $sub_products) :
	?>
		<div class="container-right sub-products-container overflow-hidden flex items-center mb-40 gap-40">
			<?php if ($full_product_image) : ?>
				<div class="full-product-image z-index-5">
					<?php echo wp_get_attachment_image($full_product_image['ID'], 'medium', false, array('class' => 'w-full h-full block object-cover')); ?>
				</div>
			<?php endif; ?>
			<?php if ($sub_products) : ?>
				<div class="sub-products swiper-container flex gap-10 overflow-hidden">
					<div class="swiper-wrapper gap-40">
						<?php foreach ($sub_products as $sub_product) : ?>
							<div class="swiper-slide sub-product h-auto flex flex-col justify-content-end items-center gap-20 <?php if (!$sub_product['purchasable']) echo 'not-purchasable'; ?>">
								<?php echo wp_get_attachment_image($sub_product['image']['ID'], 'thumbnail'); ?>
								<a href="<?php echo isset($sub_product['link']['url']) ? $sub_product['link']['url'] : '#'; ?>" class="sub-product-info flex items-center flex-col">
									<?php if ($sub_product['purchasable']) : ?>
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
	<?php endif; ?>

<div class="container mb-40">
	<?php
		$description = $product->get_description();

		$product_cat = get_field('product_cat');
		$product_style = get_field('product_style');
		$product_group = get_field('product_group');
		$usage_material = get_field('usage_material');
		$material_of_bases = get_field('material_of_bases');
		$coating_material = get_field('coating_material');
		$wood_color = get_field('wood_color');
		$fabric_color = get_field('fabric_color');
		$fabric_type = get_field('fabric_type');
		$drawers_type = get_field('drawers_type');

		$specs_fields = array(
			$product_cat,
			$product_style,
			$product_group,
			$usage_material,
			$material_of_bases,
			$coating_material,
			$wood_color,
			$fabric_color,
			$fabric_type,
			$drawers_type
		);
		// Verify if the fields are not empty
		$has_specs = false;

		// Check each field to see if it has content
		foreach ($specs_fields as $spec) :
			if (!empty($spec)) :
				$has_specs = true;
				break;
			endif;
		endforeach;

		$has_technical_review = false;

		$general_product_specifications = get_field('general_product_specifications');
		$product_material_specifications = get_field('product_material_specifications');
		$functional_features_and_capabilities = get_field('functional_features_and_capabilities');
		$product_technical_specifications = get_field('product_technical_specifications');
		$product_installation_specifications = get_field('product_installation_specifications');
		
		$technical_reviews = array(
			$general_product_specifications,
			$product_material_specifications,
			$functional_features_and_capabilities,
			$product_technical_specifications,
			$product_installation_specifications
		);
		// Check each field to see if it has content
		foreach ($technical_reviews as $review) :
			if (!empty($review)) :
				$has_technical_review = true;
				break;
			endif;
		endforeach;
		$product_size_images = get_field('product_size_images');

		$product_maintenance = get_field('product_maintenance');
		
		$production_process_video_link = get_field('production_process_video_link');
		$production_process_title = get_field('production_process_title');
		$production_process_video_poster = get_field('production_process_video_poster');
		

	?>
	<div class="tab-contents">
		<div class="tabs flex w-full">
			<?php if ($description) : ?>
				<div id="tab-desc" class="tab-item yekan-14 color-black-30 pointer active">توضیحات محصول</div>
			<?php endif;
			if ($has_specs) : ?>
				<div id="tab-specs" class="tab-item yekan-14 color-black-30 pointer">مشخصات کلی</div>
			<?php endif; ?>
			<?php if ($has_technical_review) : ?>
				<div id="tab-technical-review" class="tab-item yekan-14 color-black-30 pointer">بررسی تخصصی</div>
			<?php endif; ?>
			<?php if($product_size_images) : ?>
			<div id="tab-dimensions" class="tab-item yekan-14 color-black-30 pointer">ابعاد محصول</div>
			<?php endif; ?>
			<div id="tab-faqs" class="tab-item yekan-14 color-black-30 pointer">پرسش و پاسخ</div>
			<div id="tab-reviews" class="tab-item yekan-14 color-black-30 pointer">نظرات مشتریان</div>
			<?php if($product_maintenance) : ?>
			<div id="tab-maintenance" class="tab-item yekan-14 color-black-30 pointer">نحوه نگهداری محصول</div>
			<?php endif; ?>
			<?php if($production_process_video_link) : ?>
			<div id="tab-production" class="tab-item yekan-14 color-black-30 pointer">روند تولید</div>
			<?php endif; ?>
		</div>
		<div class="tab-content flex flex-col gap-20">
			<?php if ($description) : ?>
				<div id="tab-desc-content" class="tab-content-item yekan-18 px-40 py-25 color-black-60 bg-black-03 active">
					<?php echo nl2br($description); ?>
				</div>
			<?php endif;
			if ($has_specs) : ?>
				<div id="tab-specs-content" class="tab-content-item py-20">
					<div class="items grid grid-cols-2 gap-07">
						<?php if ($product_cat) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">دسته بندی محصول</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($product_cat as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($product_style) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">سبک محصول</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($product_style as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($product_group) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">گروه محصول</div>
								<div class="spec-value yekan-18 color-black-50"><?php echo $product_group; ?></div>
							</div>
						<?php endif; ?>
						<?php if ($usage_material) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">متریال مصرفی</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($usage_material as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($material_of_bases) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">جنس پایه‌ها (و ستون‌ها)</div>
								<div class="spec-value yekan-18 color-black-50"><?php echo $material_of_bases; ?></div>
							</div>
						<?php endif; ?>
						<?php if ($coating_material) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">جنس روکش</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($coating_material as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($wood_color) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">رنگ چوب</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($wood_color as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($fabric_type) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">جنس پارچه</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($fabric_type as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($fabric_color) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">رنگ پارچه</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($fabric_color as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
						<?php if ($drawers_type) : ?>
							<div class="spec-item flex flex-col py-20 px-40 bg-black-03">
								<div class="spec-name yekan-20 color-black-80">نوع کشوها</div>
								<div class="spec-value yekan-18 color-black-50"><?php
																				$cat_names = array();
																				foreach ($drawers_type as $cat) {
																					$cat_names[] = $cat->name;
																				}
																				echo implode(' | ', $cat_names);
																				?></div>
							</div>
						<?php endif; ?>
					</div>

				</div>
			<?php endif; ?>
			<?php if ($has_technical_review) : ?>
				<div id="tab-technical-review-content" class="tab-content-item px-25 py-25 bg-black-03">
					<div class="flex flex-col gap-20">
						<?php if ($general_product_specifications) : ?>
							<div class="technical-review-item slide-down-wrapper flex flex-col gap-10 active">
								<div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
									<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span>مشخصات کلی محصول</span>
								</div>
								<div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80">
									<?php foreach ($general_product_specifications as $spec) : ?>
										<div class="spec-item flex gap-10">
											<div class="spec-name"><?php echo $spec['label']; ?></div>
											<div class="spec-value"><?php echo $spec['value']; ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($product_material_specifications) : ?>
							<div class="technical-review-item slide-down-wrapper flex flex-col gap-10 ">
								<div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
									<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span>مشخصات جنس محصول</span>
								</div>
								<div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80" style="display: none;">
									<?php foreach ($product_material_specifications as $spec) : ?>
										<div class="spec-item flex gap-10">
											<div class="spec-name"><?php echo $spec['label']; ?></div>
											<div class="spec-value"><?php echo $spec['value']; ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($functional_features_and_capabilities) : ?>
							<div class="technical-review-item slide-down-wrapper flex flex-col gap-10 ">
								<div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
									<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span> امکانات و قابیلت های کاربردی </span>
								</div>
								<div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80" style="display: none;">
									<?php foreach ($functional_features_and_capabilities as $spec) : ?>
										<div class="spec-item flex gap-10">
											<div class="spec-name"><?php echo $spec['label']; ?></div>
											<div class="spec-value"><?php echo $spec['value']; ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($product_technical_specifications) : ?>
							<div class="technical-review-item slide-down-wrapper flex flex-col gap-10 ">
								<div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
									<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span>مشخصات فنی محصول</span>
								</div>
								<div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80" style="display: none;">
									<?php foreach ($product_technical_specifications as $spec) : ?>
										<div class="spec-item flex gap-10">
											<div class="spec-name"><?php echo $spec['label']; ?></div>
											<div class="spec-value"><?php echo $spec['value']; ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($product_installation_specifications) : ?>
							<div class="technical-review-item slide-down-wrapper flex flex-col gap-10 ">
								<div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
									<svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span>مشخصات نصب محصول</span>
								</div>
								<div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80" style="display: none;">
									<?php foreach ($product_installation_specifications as $spec) : ?>
										<div class="spec-item flex gap-10">
											<div class="spec-name"><?php echo $spec['label']; ?></div>
											<div class="spec-value"><?php echo $spec['value']; ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if($product_size_images) : ?>
				<div id="tab-dimensions-content" class="tab-content-item  bg-black-03 py-20">
					<div class="flex items-center justify-center w-full">
						<?php foreach ($product_size_images as $image) : ?>
							<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
						<?php endforeach; ?>
					</div>
					

				</div>
			<?php endif; ?>
			<?php if ($product_maintenance) : ?>
				<div id="tab-maintenance-content" class="tab-content-item yekan-18 px-40 py-25 color-black-60 bg-black-03">
					<?php echo ($product_maintenance); ?>
				</div>
			<?php endif; ?>
			<?php if($production_process_video_link) : ?>
				<div id="tab-production-content" class="tab-content-item  px-25 py-25 bg-black-03">
					<div class="flex flex-col items-center gap-20">
						<div class="title yekan-26 color-black-80">
							<?php echo ($production_process_title); ?>
						</div>
						<div class="production-process-video">
							<a class="video relative video-lightbox" data-video-url="<?php echo esc_url($production_process_video_link); ?>">
								<img class=" flex" src="<?php echo esc_url($production_process_video_poster['url']); ?>" alt="<?php echo esc_attr($production_process_title); ?>">
								<div class="absolute center z-index-5">
										<img class="transform" src="<?php echo Helper::getAssetUri('images/play_icon.svg'); ?>" alt="Play Icon">
								</div>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="tab-reviews-content">
				
			</div>
		</div>
	</div>
</div>
<?php 
	$faqs = get_field('faqs');
	if ($faqs) : ?>
	<div class="product-faqs flex flex-col gap-20 mb-40">
		<div class="container flex">
			<div class="flex flex-col gap-20">
				<div class="faq-title yekan-28 bold color-primary">سوالات متداول</div>
				<div class="faq-items flex flex-col gap-10">
					<?php foreach ($faqs as $faq) : ?>
						<div class="faq-item slide-down-wrapper flex flex-col">
							<div class="faq-question slide-down-trigger yekan-20 color-black-80 flex justify-between items-center cursor-pointer">
								<span><?php echo $faq['question']; ?></span>
							</div>
							<div class="faq-answer slide-down-content yekan-20 color-black" style="display: none;"><?php echo $faq['answer']; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="faqs-desc flex flex-col items-center justify-center">
				<?php
				$faq_icon = Helper::getAssetUri('images/faq_icon.png');
				echo '<img src="' . esc_url($faq_icon) . '" alt="FAQ Icon" />';
				?>
				<div class="faq-icon-text yekan-26 color-black-80">شما عزیزان می توانید با مراجعه به بخش <a href="#faq-items" class="color-white bg-black px-25 inline-block">( پرسش های متدوال)</a> بخش تمامی سوالات احتمالی خود را دریافت کنید</div>
			</div>
		</div>

	</div>
<?php endif; ?>
<div class="container mb-25">
	<div class="cta help-cta py-40 px-40">
		<div class="flex justify-between items-center">
			<div class="flex flex-col">
				<div class="yekan-34 color-white">برای خرید به مشاوره نیاز داری؟</div>
				<div class="yekan-34 color-white">درمورد این محصول سوالی دارید؟</div>
			</div>
			<div class="flex flex-col gap-04">
				<a href="#" class="btn flex items-center gap-10 yekan-22 color-white">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6.9 20.6C8.4 21.5 10.2 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 13.8 2.5 15.5 3.3 17L2.44044 20.306C2.24572 21.0549 2.93892 21.7317 3.68299 21.5191L6.9 20.6Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M16.5 14.8485C16.5 15.0105 16.4639 15.177 16.3873 15.339C16.3107 15.501 16.2116 15.654 16.0809 15.798C15.86 16.041 15.6167 16.2165 15.3418 16.329C15.0714 16.4415 14.7784 16.5 14.4629 16.5C14.0033 16.5 13.512 16.392 12.9937 16.1715C12.4755 15.951 11.9572 15.654 11.4434 15.2805C10.9251 14.9025 10.4339 14.484 9.9652 14.0205C9.501 13.5525 9.08187 13.062 8.70781 12.549C8.33826 12.036 8.04081 11.523 7.82449 11.0145C7.60816 10.5015 7.5 10.011 7.5 9.543C7.5 9.237 7.55408 8.9445 7.66224 8.6745C7.77041 8.4 7.94166 8.148 8.18052 7.923C8.46895 7.6395 8.78443 7.5 9.11793 7.5C9.24412 7.5 9.37031 7.527 9.48297 7.581C9.60015 7.635 9.70381 7.716 9.78493 7.833L10.8305 9.3045C10.9116 9.417 10.9702 9.5205 11.0108 9.6195C11.0513 9.714 11.0739 9.8085 11.0739 9.894C11.0739 10.002 11.0423 10.11 10.9792 10.2135C10.9206 10.317 10.835 10.425 10.7268 10.533L10.3843 10.8885C10.3348 10.938 10.3122 10.9965 10.3122 11.0685C10.3122 11.1045 10.3167 11.136 10.3257 11.172C10.3393 11.208 10.3528 11.235 10.3618 11.262C10.4429 11.4105 10.5826 11.604 10.7809 11.838C10.9837 12.072 11.2 12.3105 11.4344 12.549C11.6778 12.7875 11.9121 13.008 12.151 13.2105C12.3853 13.4085 12.5791 13.5435 12.7323 13.6245C12.7549 13.6335 12.7819 13.647 12.8135 13.6605C12.8495 13.674 12.8856 13.6785 12.9261 13.6785C13.0028 13.6785 13.0613 13.6515 13.1109 13.602L13.4534 13.2645C13.5661 13.152 13.6743 13.0665 13.7779 13.0125C13.8816 12.9495 13.9852 12.918 14.0979 12.918C14.1835 12.918 14.2737 12.936 14.3728 12.9765C14.472 13.017 14.5756 13.0755 14.6883 13.152L16.18 14.2095C16.2972 14.2905 16.3783 14.385 16.4279 14.4975C16.473 14.61 16.5 14.7225 16.5 14.8485Z" stroke="white" stroke-width="1.5" stroke-miterlimit="10"/>
					</svg>
					<span>پیام در واتساپ</span>
				</a>
				<a href="#" class="btn flex items-center gap-10 yekan-22 color-white">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="#734B6C" stroke-width="1.5" stroke-miterlimit="10"/>
					</svg>
					<span>تماس تلفنی</span>
				</a>
			</div>
		</div>

	</div>
</div>
<?php get_template_part('template-parts/product/consult-cta'); ?>



</div>

<?php do_action('woocommerce_after_single_product'); ?>