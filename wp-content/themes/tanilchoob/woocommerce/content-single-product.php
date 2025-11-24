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

$product_id = $product->get_id();
?>
<div id="product-<?php $product_id; ?>" <?php wc_product_class('', $product); ?>>
	<div class="product-container container flex gap-15 mt-40 mb-40">
		<div class="product-summary flex flex-col gap-04">
			<h1 class="product-title"><?php the_title(); ?></h1>
			<ul class="product-comments-ratings flex items-center yekan-16 gap-10">
				<li class="product-comments">
					<?php
						// Display average rating and count in the desired format: "3.1 ⭐ (15نفر)"
						$average      = $product ? (float) $product->get_average_rating() : 0.0;
						$rating_count = $product ? (int) $product->get_rating_count() : 0;
						if ($rating_count === 0 && $product) {
							$rating_count = (int) $product->get_review_count();
						}
						$average_str  = $average > 0 ? number_format($average, 1) : '0.0';
						echo esc_html($average_str) . ' ' . '⭐' . ' (' . esc_html($rating_count) . 'نفر)';
					?>
				</li>
				<li class="product-questions-answers">
					<?php
						// Show users reviews count
						$product_obj  = isset($product) && $product instanceof WC_Product ? $product : wc_get_product(get_the_ID());
						$reviews_count = $product_obj ? (int) $product_obj->get_review_count() : 0;
						echo esc_html($reviews_count) . ' دیدگاه کاربران';
					?>
				</li>
			</ul>
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
				$product_status = get_post_meta($product_id, '_product_status', true);
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
			<?php
			$product_components_text = get_field('product_components_text', $product_id);
			if($product_components_text):
			?>
			<div class="accordion-box slide-down-wrapper flex flex-col gap-10">
				<div class="box-title flex items-center justify-between">
					<span class="yekan-18 color-black-60">اجزای محصول:</span>
					<div class="slide-down-trigger transition" role="button" aria-expanded="false">
					<svg width="13" height="7" viewBox="0 0 13 7" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M0.75 6L6.01498 0.749929L11.28 6" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</div>

				</div>
				<div class="content slide-down-content yekan-18 color-primary"><?php echo $product_components_text; ?></div>
			</div>
			<?php endif; ?>
			<?php
                    // Show product variation selector here for variable products.
                    if ( $product && $product->is_type( 'variable' ) ) {
                        $available_variations = $product->get_available_variations();
                        $attributes           = $product->get_variation_attributes();
                        $selected_attributes  = $product->get_default_attributes();
                        wc_get_template(
                            'single-product/add-to-cart/variable.php',
                            array(
                                'available_variations' => $available_variations,
                                'attributes'           => $attributes,
                                'selected_attributes'  => $selected_attributes,
                            )
                        );
                    }
            ?>
		</div>
		<div class="product-images flex-shrink-0">
			<div class="product-gallery-container flex gap-10">

				<!-- Swiper Main -->
				<div class="product-gallery-main flex">
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
							// Build a flat list of all gallery image IDs (main first)
							$all_gallery_ids = array();
							if ($main_image_id) {
								$all_gallery_ids[] = $main_image_id;
							}
							if ($attachment_ids) {
								$all_gallery_ids = array_merge($all_gallery_ids, $attachment_ids);
							}

							$total_thumbs = count($all_gallery_ids);
							$limit = 4;

							// Prepare lightbox data (full + thumb urls)
							$lightbox_items = array();
							foreach ($all_gallery_ids as $img_id) {
								$full_src = wp_get_attachment_image_src($img_id, 'large');
								$thumb_src = wp_get_attachment_image_src($img_id, 'thumbnail');
								$lightbox_items[] = array(
									'full' => $full_src ? $full_src[0] : '',
									'thumb' => $thumb_src ? $thumb_src[0] : '',
								);
							}

							// Render up to 4 thumb slides; if more than 4, make the 4th a lightbox button
							for ($i = 0; $i < min($limit, $total_thumbs); $i++) {
								$img_id = $all_gallery_ids[$i];
								$thumb_html = wp_get_attachment_image($img_id, 'thumbnail');

								if ($i === $limit - 1 && $total_thumbs > $limit) {
									$more_count = $total_thumbs - ($limit - 1);
									$lightbox_json = wp_json_encode($lightbox_items);
									echo '<div class="swiper-slide">';
									echo '<button type="button" class="thumb-item more-thumbs open-gallery-lightbox" data-gallery="' . esc_attr($lightbox_json) . '" data-more-count="' . esc_attr($more_count) . '" aria-label="مشاهده همه تصاویر">';
									// Show current thumb underneath overlay for context
									echo $thumb_html;
									echo '<span class="more-label yekan-14">+' . esc_html($more_count) . '</span>';
									echo '</button>';
									echo '</div>';
								} else {
									echo '<div class="swiper-slide">';
									echo '<div class="thumb-item">';
									echo $thumb_html;
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

	<?php
	// Prepare tabs context and include template part.
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
	$has_specs = false;
	foreach ($specs_fields as $spec) {
		if (!empty($spec)) {
			$has_specs = true;
			break;
		}
	}

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
	foreach ($technical_reviews as $review) {
		if (!empty($review)) {
			$has_technical_review = true;
			break;
		}
	}

	$product_size_images = get_field('product_size_images');
	$product_maintenance = get_field('product_maintenance');
	$production_process_video_link = get_field('production_process_video_link');
	$production_process_title = get_field('production_process_title');
	$production_process_video_poster = get_field('production_process_video_poster');

	$__product_tabs_ctx = array(
		'description' => $description,
		'has_specs' => $has_specs,
		'has_technical_review' => $has_technical_review,
		'product_cat' => $product_cat,
		'product_style' => $product_style,
		'product_group' => $product_group,
		'usage_material' => $usage_material,
		'material_of_bases' => $material_of_bases,
		'coating_material' => $coating_material,
		'wood_color' => $wood_color,
		'fabric_type' => $fabric_type,
		'fabric_color' => $fabric_color,
		'drawers_type' => $drawers_type,
		'general_product_specifications' => $general_product_specifications,
		'product_material_specifications' => $product_material_specifications,
		'functional_features_and_capabilities' => $functional_features_and_capabilities,
		'product_technical_specifications' => $product_technical_specifications,
		'product_installation_specifications' => $product_installation_specifications,
		'product_size_images' => $product_size_images,
		'product_maintenance' => $product_maintenance,
		'production_process_video_link' => $production_process_video_link,
		'production_process_title' => $production_process_title,
		'production_process_video_poster' => $production_process_video_poster,
	);
	set_query_var('product_tabs', $__product_tabs_ctx);
	get_template_part('template-parts/product/tabs');
	?>
	<?php
	$faqs = get_field('faqs');
	if ($faqs) :
		set_query_var('faqs', $faqs);
		get_template_part('template-parts/product/faqs');
	endif; ?>

	<?php get_template_part('template-parts/product/testimonials'); ?>

	<?php get_template_part('template-parts/product/help-cta'); ?>

	<div class="container mb-25 mt-25">
		<div class="flex justify-between gap-20">
			<?php

			get_template_part('template-parts/product/consult-cta', null, array(
				'label' => 'موجودی و نحوه ارسال محصول',
				'url' => '#',
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2 9V7C2 4 4 2 7 2H17C20 2 22 4 22 7V9" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
				<path d="M2 15V17C2 20 4 22 7 22H17C20 22 22 20 22 17V15" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
				<path d="M6.70312 9.25977L12.0031 12.3298L17.2631 9.27979" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
				<path d="M12 17.7698V12.3198" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
				<path d="M10.7622 6.29006L7.56218 8.07009C6.84218 8.47009 6.24219 9.48008 6.24219 10.3101V13.7001C6.24219 14.5301 6.83218 15.5401 7.56218 15.9401L10.7622 17.7201C11.4422 18.1001 12.5622 18.1001 13.2522 17.7201L16.4522 15.9401C17.1722 15.5401 17.7722 14.5301 17.7722 13.7001V10.3101C17.7722 9.48008 17.1822 8.47009 16.4522 8.07009L13.2522 6.29006C12.5622 5.90006 11.4422 5.90006 10.7622 6.29006Z" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
			</svg>',
			));

			get_template_part('template-parts/product/consult-cta', null, array(
				'label' => 'شرایط گارنتی محصولات',
				'url' => '#',
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10.4862 2.23055L5.49625 4.11055C4.34625 4.54055 3.40625 5.90055 3.40625 7.12055V14.5505C3.40625 15.7305 4.18625 17.2805 5.13625 17.9905L9.43625 21.2005C10.8462 22.2605 13.1663 22.2605 14.5763 21.2005L18.8762 17.9905C19.8262 17.2805 20.6063 15.7305 20.6063 14.5505V7.12055C20.6063 5.89055 19.6663 4.53055 18.5163 4.10055L13.5262 2.23055C12.6762 1.92055 11.3162 1.92055 10.4862 2.23055Z" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M9.04688 11.8697L10.6569 13.4797L14.9569 9.17969" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			',
			));
			?>
		</div>
	</div>

	<?php get_template_part('template-parts/product/products-suggustions'); ?>
	
	<div class="container mb-25 mt-25">
		<div class="flex justify-between gap-20">
			<?php

			get_template_part('template-parts/product/consult-cta', null, array(
				'label' => 'صفحه شرایط خرید نقدی و اقساطی',
				'url' => '#',
				'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3.67188 2.5V14.47C3.67188 15.45 4.13187 16.38 4.92188 16.97L10.1319 20.87C11.2419 21.7 12.7719 21.7 13.8819 20.87L19.0919 16.97C19.8819 16.38 20.3419 15.45 20.3419 14.47V2.5H3.67188Z" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10"/>
				<path d="M2 2.5H22" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"/>
				<path d="M8 8H16" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8 13H16" stroke="#333333" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>',
			));

			get_template_part('template-parts/product/consult-cta', null, array(
				'label' => 'مواد اولیه محصولات',
				'url' => '#',
				'icon' => '<svg width="20" height="22" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16.31 17.42L18.53 16.31V13.53M11.86 8.53L9.64 9.64L11.86 8.53ZM9.64 9.64L7.42 8.53L9.64 9.64ZM9.64 9.64V12.42V9.64ZM18.53 5.19L16.31 6.3L18.53 5.19ZM18.53 5.19L16.31 4.08L18.53 5.19ZM18.53 5.19V7.97V5.19ZM11.86 1.86L9.64 0.75L7.42 1.86H11.86ZM0.75 5.19L2.97 4.08L0.75 5.19ZM0.75 5.19L2.97 6.3L0.75 5.19ZM0.75 5.19V7.97V5.19ZM9.64 20.75L7.42 19.64L9.64 20.75ZM9.64 20.75L11.86 19.64L9.64 20.75ZM9.64 20.75V17.97V20.75ZM2.97 17.42L0.75 16.31V13.53L2.97 17.42Z" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>',
			));
			?>
		</div>
	</div>
</div>

<?php do_action('woocommerce_after_single_product'); ?>