<?php

/**
 * Single variation cart button
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined('ABSPATH') || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button h-100">
	<?php do_action('woocommerce_before_add_to_cart_button'); ?>



	<div class="single_add_to_cart_button add-to-cart flex items-center h-100 px-40 bg-black gap-10">
		<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M2.10156 2.10449H3.93219C5.06844 2.10449 5.96271 3.08293 5.86802 4.20866L4.99479 14.6874C4.8475 16.4023 6.20468 17.8752 7.9301 17.8752H19.1348C20.6498 17.8752 21.9754 16.6338 22.0911 15.1293L22.6593 7.23866C22.7855 5.49221 21.4599 4.07188 19.7029 4.07188H6.12053" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M17.0964 23.1458C17.8227 23.1458 18.4115 22.557 18.4115 21.8307C18.4115 21.1044 17.8227 20.5156 17.0964 20.5156C16.37 20.5156 15.7812 21.1044 15.7812 21.8307C15.7812 22.557 16.37 23.1458 17.0964 23.1458Z" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M8.68229 23.1458C9.4086 23.1458 9.9974 22.557 9.9974 21.8307C9.9974 21.1044 9.4086 20.5156 8.68229 20.5156C7.95598 20.5156 7.36719 21.1044 7.36719 21.8307C7.36719 22.557 7.95598 23.1458 8.68229 23.1458Z" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M9.46875 8.41699H22.0938" stroke="white" stroke-width="1.57812" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
		<span class="yekan-22 color-white">افزودن به سبد خرید</span>
	</div>
	<?php do_action('woocommerce_after_add_to_cart_button'); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>