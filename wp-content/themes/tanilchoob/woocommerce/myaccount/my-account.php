<?php
/**
 * My Account page — custom two-column layout.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$menu_items    = wc_get_account_menu_items();
$section_label = '';

$first_name   = trim( $current_user->first_name );
$last_name    = trim( $current_user->last_name );
$full_name    = trim( "$first_name $last_name" );
$greeting_name = $full_name ?: get_user_meta( $current_user->ID, 'billing_phone', true );

foreach ( $menu_items as $endpoint => $label ) {
	if ( in_array( $endpoint, [ 'dashboard', 'customer-logout' ], true ) ) {
		continue;
	}
	if ( wc_is_current_account_menu_item( $endpoint ) ) {
		$section_label = $label;
		break;
	}
}

$is_sub_page = ! empty( $section_label );
?>

<div class="wc-my-account<?php echo $is_sub_page ? ' wc-my-account--sub-page' : ''; ?>">

	<?php if ( $is_sub_page ) : ?>
	<div class="wc-my-account__mobile-header">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="wc-my-account__mobile-back" aria-label="<?php esc_attr_e( 'بازگشت به حساب کاربری', 'woocommerce' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
		<span class="wc-my-account__mobile-title"><?php echo esc_html( $section_label ); ?></span>
	</div>
	<?php endif; ?>

	<p class="wc-my-account__greeting">
		خوش آمدی<br class="wc-my-account__greeting-break"> <span class="wc-my-account__greeting-name"><?php echo esc_html( $greeting_name ); ?>!</span> 👋
	</p>

	<div class="wc-my-account__body flex flex-row-reverse">

		<div class="wc-my-account__content bg-black-03 woocommerce-MyAccount-content">
			<?php do_action( 'woocommerce_account_content' ); ?>
		</div>

		<div class="wc-my-account__sidebar">
			<?php do_action( 'woocommerce_account_navigation' ); ?>
		</div>

	</div>

</div>
