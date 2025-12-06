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
				<div class="product-gallery-main flex relative">
						<div class="gallery-buttons flex flex-col gap-10 absolute z-index-5 items-start">
							<div class="button flex item-center pointer share-button" data-share-url="<?php echo esc_url( get_permalink() ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.9609 6.16992C18.9609 7.55992 20.3409 9.76992 20.6209 12.3199" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M3.49219 12.3697C3.75219 9.82973 5.11219 7.61973 7.09219 6.21973" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M8.1875 20.9404C9.3475 21.5304 10.6675 21.8604 12.0575 21.8604C13.3975 21.8604 14.6575 21.5604 15.7875 21.0104" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12.0613 7.69965C13.5966 7.69965 14.8413 6.455 14.8413 4.91965C14.8413 3.3843 13.5966 2.13965 12.0613 2.13965C10.5259 2.13965 9.28125 3.3843 9.28125 4.91965C9.28125 6.455 10.5259 7.69965 12.0613 7.69965Z" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M4.82687 19.9204C6.36223 19.9204 7.60688 18.6757 7.60688 17.1404C7.60688 15.605 6.36223 14.3604 4.82687 14.3604C3.29152 14.3604 2.04688 15.605 2.04688 17.1404C2.04688 18.6757 3.29152 19.9204 4.82687 19.9204Z" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M19.1706 19.9204C20.706 19.9204 21.9506 18.6757 21.9506 17.1404C21.9506 15.605 20.706 14.3604 19.1706 14.3604C17.6353 14.3604 16.3906 15.605 16.3906 17.1404C16.3906 18.6757 17.6353 19.9204 19.1706 19.9204Z" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
<span class="yekan-12">اشتراک گذاری</span>

						</div>
						<div class="button flex item-center pointer whish-button">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.62 20.8096C12.28 20.9296 11.72 20.9296 11.38 20.8096C8.48 19.8196 2 15.6896 2 8.68961C2 5.59961 4.49 3.09961 7.56 3.09961C9.38 3.09961 10.99 3.97961 12 5.33961C13.01 3.97961 14.63 3.09961 16.44 3.09961C19.51 3.09961 22 5.59961 22 8.68961C22 15.6896 15.52 19.8196 12.62 20.8096Z" stroke="#484848" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
<span class="yekan-12">افزودن به علاقه مندی ها</span>

						</div>
					</div>
					<div class="swiper-container gallery-main overflow-hidden">
						<div class="swiper-wrapper">
							<?php

							$attachment_ids = $product->get_gallery_image_ids();
							$main_image_id = $product->get_image_id();
							$video_gallery_url = get_field('video_gallery_url', $product_id);
							
							// Add main image to the beginning of the gallery
							if ($main_image_id) {
								echo '<div class="swiper-slide">';
								echo wp_get_attachment_image($main_image_id, 'large');
								echo '</div>';
							}

							// If video exists, show it as the next slide
							if ($video_gallery_url) {
								$video_url = is_array($video_gallery_url) && isset($video_gallery_url['url']) ? $video_gallery_url['url'] : $video_gallery_url;
								$video_url = is_string($video_url) ? trim($video_url) : '';
								if ($video_url) {
									$embed = wp_oembed_get($video_url);
									echo '<div class="swiper-slide video-slide">';
									echo '<div class="video-wrapper">';
									if ($embed) {
										echo $embed;
									} else {
										// Fallback to HTML5 video element
										echo '<video controls playsinline src="' . esc_url($video_url) . '"></video>';
									}
									echo '</div>';
									echo '</div>';
								}
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
							// Build images list (main first)
							$all_gallery_ids = array();
							if ($main_image_id) {
								$all_gallery_ids[] = $main_image_id;
							}
							if ($attachment_ids) {
								$all_gallery_ids = array_merge($all_gallery_ids, $attachment_ids);
							}

							// Determine if we have a video and compute a thumbnail src for it (use main image or first gallery)
							$has_video = false;
							$video_thumb_src = '';
							if (!empty($video_gallery_url)) {
								$video_url = is_array($video_gallery_url) && isset($video_gallery_url['url']) ? $video_gallery_url['url'] : $video_gallery_url;
								$video_url = is_string($video_url) ? trim($video_url) : '';
								if ($video_url) {
									$has_video = true;
									if ($main_image_id) {
										$thumb_src_arr = wp_get_attachment_image_src($main_image_id, 'thumbnail');
										$video_thumb_src = $thumb_src_arr ? $thumb_src_arr[0] : '';
									} elseif (!empty($attachment_ids)) {
										$first_id = reset($attachment_ids);
										$thumb_src_arr = wp_get_attachment_image_src($first_id, 'thumbnail');
										$video_thumb_src = $thumb_src_arr ? $thumb_src_arr[0] : '';
									}
								}
							}

							// Build ordered thumb items: main image, then video (if any), then other images
							$thumb_items = array();
							if ($main_image_id) {
								$thumb_items[] = array('type' => 'image', 'id' => $main_image_id);
							}
							if ($has_video) {
								$thumb_items[] = array('type' => 'video', 'src' => $video_thumb_src);
							}
							if ($attachment_ids) {
								foreach ($attachment_ids as $aid) {
									$thumb_items[] = array('type' => 'image', 'id' => $aid);
								}
							}

							$total_thumbs = count($thumb_items);
							$limit = 4;

							// Prepare lightbox data (images only)
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
								$item = $thumb_items[$i];
								$is_video = isset($item['type']) && $item['type'] === 'video';
								$thumb_html = '';
								if ($is_video) {
									$src = isset($item['src']) ? $item['src'] : '';
									$thumb_html = $src ? '<img src="' . esc_url($src) . '" alt="" />' : '';
								} else {
									$img_id = isset($item['id']) ? $item['id'] : 0;
									$thumb_html = $img_id ? wp_get_attachment_image($img_id, 'thumbnail') : '';
								}

								if ($i === $limit - 1 && $total_thumbs > $limit) {
									$more_count = $total_thumbs - ($limit - 1);
									$lightbox_json = wp_json_encode($lightbox_items);
									echo '<div class="swiper-slide">';
									echo '<button type="button" class="thumb-item more-thumbs open-gallery-lightbox" data-gallery="' . esc_attr($lightbox_json) . '" data-more-count="' . esc_attr($more_count) . '" aria-label="مشاهده همه تصاویر">';
									// Show current thumb underneath overlay for context
									echo $thumb_html;
									echo '<div class="more-label w-full color-white center absolute flex flex-col items-center gap-2 z-index-5"><span class="yekan-20">' . esc_html($more_count) . '+</span><span class="yekan-12">مشاهده همه</span></div>';
									echo '</button>';
									echo '</div>';
								} else {
									echo '<div class="swiper-slide">';
									if ($is_video) {
										echo '<div class="thumb-item video-thumb">';
										// image preview if available
										echo $thumb_html;
										// play icon overlay
										echo '<span class="video-icon absolute center z-index-5" aria-hidden="true">';
										echo '<svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
<foreignObject x="-9.96202" y="-9.96202" width="62.924" height="62.924"><div xmlns="http://www.w3.org/1999/xhtml" style="backdrop-filter:blur(4.98px);clip-path:url(#bgblur_0_1_1115_clip_path);height:100%;width:100%"></div></foreignObject><circle data-figma-bg-blur-radius="9.96202" cx="21.5" cy="21.5" r="21.5" fill="white" fill-opacity="0.62"/>
<path d="M31.1807 20.0795C32.1525 20.6405 32.1525 22.0431 31.1807 22.6042L17.5155 30.4938C16.5437 31.0549 15.3291 30.3535 15.3291 29.2315L15.3291 13.4522C15.3291 12.3301 16.5437 11.6288 17.5155 12.1898L31.1807 20.0795Z" fill="white"/>
<defs>
<clipPath id="bgblur_0_1_1115_clip_path" transform="translate(9.96202 9.96202)"><circle cx="21.5" cy="21.5" r="21.5"/>
</clipPath></defs>
</svg>';
										echo '</span>';
										echo '</div>';
									} else {
										echo '<div class="thumb-item">';
										echo $thumb_html;
										echo '</div>';
									}
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

	

	$product_size_images = get_field('product_size_images');
	$product_maintenance = get_field('product_maintenance');
	$production_process_video_link = get_field('production_process_video_link');
	$production_process_title = get_field('production_process_title');
	$production_process_video_poster = get_field('production_process_video_poster');
	$product_review = get_field('product_review');

	$__product_tabs_ctx = array(
		'description' => $description,
		'has_specs' => $has_specs,
		'product_review' => $product_review,
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