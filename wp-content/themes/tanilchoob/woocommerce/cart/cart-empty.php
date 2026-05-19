<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;


?>

<div class="flex flex-col items-center py-40 mt-40 mb-40">
	<img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/no_orders.png" alt="Google Maps">
	<p>سبد خرید شما در حال حاضر خالی است.</p>
</div>