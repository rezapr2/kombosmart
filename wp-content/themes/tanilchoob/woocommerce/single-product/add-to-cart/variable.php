<?php

/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined('ABSPATH') || exit;

global $product;

$attribute_keys  = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart flex flex-col w-full gap-04" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. 
																																																																							?>">
	<?php do_action('woocommerce_before_variations_form'); ?>

	<?php if (empty($available_variations) && false !== $available_variations) : ?>
		<p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
	<?php else : ?>
		<?php foreach ($attributes as $attribute_name => $options) : ?>

			<div class="accordion-box variation-box slide-down-wrapper flex flex-col gap-10">
				<div class="box-title flex items-center justify-between">
					<div class="label yekan-18 color-black-60"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); // WPCS: XSS ok. 
																																			?>:</label></div>
					<div class="slide-down-trigger transition" role="button" aria-expanded="false">
						<svg width="13" height="7" viewBox="0 0 13 7" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M0.75 6L6.01498 0.749929L11.28 6" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</svg>
					</div>

				</div>
				<div class="content slide-down-content yekan-18 color-primary">
					<?php
					// Render radios for this attribute and keep a hidden select for WooCommerce JS compatibility.
					$sanitized_attr = sanitize_title( $attribute_name );
					$selected_value = isset( $_REQUEST[ 'attribute_' . $sanitized_attr ] )
						? wc_clean( wp_unslash( $_REQUEST[ 'attribute_' . $sanitized_attr ] ) )
						: $product->get_variation_default_attribute( $attribute_name );

					$is_taxonomy = taxonomy_exists( $attribute_name );
					$radio_items = array();

					if ( $is_taxonomy ) {
						foreach ( $options as $opt_slug ) {
							$term = get_term_by( 'slug', $opt_slug, $attribute_name );
							if ( $term && ! is_wp_error( $term ) ) {
								$item_array = array( 'value' => $opt_slug, 'label' => $term->name, 'term_id' => $term->term_id, );
								// load color_code for this taxonomy term
								$color_code = get_field('color_code', $term->taxonomy . '_' . $term->term_id);
								if($color_code){
									// Add color_code to item array
									$item_array['color_code'] = $color_code;
								}

								// load patern_image for this taxonomy term
								$patern_image = get_field('patern_image', $term->taxonomy . '_' . $term->term_id);
								if($patern_image){
									// Add patern_image to item array
									$item_array['patern_image'] = $patern_image;
								}

								if(isset($item_array['color_code']) || isset($item_array['patern_image'])){
									// Add has-color-patern to item array
									$item_array['has-color-patern'] = true;
								}

								$radio_items[] = $item_array;
							}
						}
					} else {
						foreach ( $options as $opt_val ) {
							$radio_items[] = array( 'value' => $opt_val, 'label' => $opt_val );
						}
					}
					?>
					<div class="variations-radio-group<?php echo ($attribute_name === 'pa_attribute_wood_color' || $attribute_name === 'pa_attribute_cloth_color') ? ' flex flex-wrap gap-20' : ''; ?>" data-attribute="<?php echo esc_attr( $attribute_name ); ?>">
						<?php foreach ( $radio_items as $item ) :
							$input_id = 'var-' . $sanitized_attr . '-' . sanitize_title( $item['value'] );
							// Check if checked 
							$is_checked = ( $selected_value === $item['value'] );
						?>
							<label class="variation-radio-item flex items-center gap-05 pointer <?php echo $is_checked ? 'checked' : ''; echo isset($item['has-color-patern']) ? ' has-color-patern' : ''; ?>" for="<?php echo esc_attr( $input_id ); ?>">
								<input
									id="<?php echo esc_attr( $input_id ); ?>"
									type="radio"
									class="tanil-variation-radio"
									data-attribute="<?php echo esc_attr( $attribute_name ); ?>"
									name="<?php echo esc_attr( 'tanil_attribute_' . $sanitized_attr ); ?>"
									value="<?php echo esc_attr( $item['value'] ); ?>"
									<?php checked( sanitize_title( $selected_value ), sanitize_title( $item['value'] ) ); ?>
								/>
								
								<?php if(isset($item['color_code']) && $item['color_code']){ ?>
									<span class="color-dot circle-radius" style="background-color:<?php echo $item['color_code']; ?>"></span>
								<?php } ?>
								<?php if(isset($item['patern_image']) && $item['patern_image']){ ?>
									<img class="patern-image circle-radius" src="<?php echo $item['patern_image']['url']; ?>" alt="<?php echo $item['label']; ?>" />
								<?php } ?>
								<span class="yekan-18 color-black-50"><?php echo esc_html( $item['label'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>

					<?php
					// Native dropdown rendered hidden to preserve behavior.
					ob_start();
					wc_dropdown_variation_attribute_options(
						array(
							'options'   => $options,
							'attribute' => $attribute_name,
							'product'   => $product,
							'selected'  => $selected_value,
							'class'     => 'tanil-hidden-select',
						)
					);
					$hidden_select = ob_get_clean();
					echo '<div style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">' . $hidden_select . '</div>';

					?>
				</div>
			</div>

		<?php endforeach; ?>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action('woocommerce_after_variations_table'); ?>

		<div class="single_variation_wrap buttons-wrapper flex flex-row-reverse gap-07">
			<div class="tanil-variation-price product-price h-100 px-20 flex items-center justify-center flex-1 yekan-22 color-primary bold">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
			<?php
			/**
			 * Hook: woocommerce_before_single_variation.
			 */
			do_action('woocommerce_before_single_variation');

			/**
			 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
			 *
			 * @since 2.4.0
			 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
			 */
			do_action('woocommerce_single_variation');

			/**
			 * Hook: woocommerce_after_single_variation.
			 */
			do_action('woocommerce_after_single_variation');
			?>
		</div>
	<?php endif; ?>

	<?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php
do_action('woocommerce_after_add_to_cart_form');
