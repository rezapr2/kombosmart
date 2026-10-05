<?php
/**
 * Simple product add to cart
 *
 * Mirrors the variable-product layout (variable.php): price box + add-to-cart button.
 * The form is submitted over AJAX by js/partials/templates/single-product/add_to_cart.js.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Out of stock: "unavailable" box, whether or not a price is set. Checked first because
// a product without a price is not purchasable and would otherwise render nothing.
if ( ! $product->is_in_stock() ) {
	?>
	<div class="buttons-wrapper flex flex-col md:flex-row-reverse gap-07">
		<div class="product-price is-unavailable h-100 px-20 flex items-center justify-center flex-1 yekan-20 bold">ناموجود</div>
	</div>
	<?php
	return;
}

$contact_mode = \TanilChoob\Theme\Helper::get_options_field( 'contact_mode' );
$price_html   = preg_replace( '/<\/?(ins)[^>]*>/', '', $product->get_price_html() );

// In stock but no price set yet: "call for price" box with a phone button.
if ( $product->get_price() === '' ) {
	$phone_link = \TanilChoob\Theme\Helper::store_phone_link();
	?>
	<div class="buttons-wrapper flex flex-col md:flex-row-reverse gap-07">
		<div class="product-price is-on-request h-100 px-20 flex items-center justify-center flex-1 yekan-20 bold">استعلام قیمت</div>
		<?php if ( $phone_link ) : ?>
			<div class="variations_button h-100 flex-grow-1">
				<a href="<?php echo esc_url( $phone_link, [ 'tel' ] ); ?>" class="add-to-cart w-full flex items-center justify-center h-100 gap-10 pointer transition">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21.97 18.33c0 .36-.08.73-.25 1.09-.17.36-.39.7-.68 1.02-.49.54-1.03.93-1.64 1.18-.6.25-1.25.38-1.95.38-1.02 0-2.11-.24-3.26-.73s-2.3-1.15-3.44-1.98a28.75 28.75 0 0 1-3.28-2.8 28.42 28.42 0 0 1-2.79-3.27c-.82-1.14-1.48-2.28-1.96-3.41C2.24 8.67 2 7.58 2 6.54c0-.68.12-1.33.36-1.93.24-.61.62-1.17 1.15-1.67C4.15 2.31 4.85 2 5.59 2c.28 0 .56.06.81.18.26.12.49.3.67.56l2.32 3.27c.18.25.31.48.4.7.09.21.14.42.14.61 0 .24-.07.48-.21.71-.13.23-.32.47-.56.71l-.76.79c-.11.11-.16.24-.16.4 0 .08.01.15.03.23.03.08.06.14.08.2.18.33.49.76.93 1.28.45.52.93 1.05 1.45 1.58.54.53 1.06 1.02 1.59 1.47.52.44.95.74 1.29.92.05.02.11.05.18.08.08.03.16.04.25.04.17 0 .3-.06.41-.17l.76-.75c.25-.25.49-.44.72-.56.23-.14.46-.21.71-.21.19 0 .39.04.61.13.22.09.45.22.7.39l3.31 2.35c.26.18.44.39.55.64.1.25.16.5.16.78Z" stroke="currentColor" stroke-width="1.6" stroke-miterlimit="10"/></svg>
					<span class="yekan-20">تماس برای استعلام قیمت</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return;
}

if ( ! $product->is_purchasable() ) {
	return;
}
?>

<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<div class="buttons-wrapper flex flex-col md:flex-row-reverse gap-07">
		<div class="tanil-variation-price product-price h-100 px-20 flex items-center justify-center flex-1 yekan-22 color-primary bold">
			<?php echo wp_kses_post( $price_html ); ?>
		</div>

		<div class="variations_button h-100 flex-grow-1">
			<?php if ( $contact_mode ) : ?>
				<a href="#help_cta" class="add-to-cart flex items-center justify-center h-100 gap-10 pointer transition">
					<span class="yekan-20 color-white">تماس با ما</span>
				</a>
			<?php else : ?>
				<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button add-to-cart w-full flex items-center justify-center h-100 gap-10 pointer transition">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 2h1.74c1.08 0 1.93.93 1.84 2l-.83 9.96A2.8 2.8 0 0 0 7.54 17h10.65c1.44 0 2.7-1.18 2.81-2.61l.54-7.5A2.77 2.77 0 0 0 18.73 3.9H5.82M16.25 22a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM8.25 22a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM9 8h12" stroke="currentColor" stroke-width="1.6" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<span class="yekan-20"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
