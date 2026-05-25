<?php
/**
 * My Account — Orders (tabbed layout)
 *
 * @package TanilChoob
 */

defined( 'ABSPATH' ) || exit;

$current_user_id = get_current_user_id();

$tab_groups = [
	'active'    => [ 'label' => 'جاری',        'statuses' => [ 'pending', 'processing', 'on-hold' ] ],
	'delivered' => [ 'label' => 'تحویل شده',   'statuses' => [ 'completed' ] ],
	'cancelled' => [ 'label' => 'لغو شده',     'statuses' => [ 'cancelled', 'failed', 'refunded' ] ],
];

$active_tab = isset( $_GET['otab'] ) && array_key_exists( $_GET['otab'], $tab_groups )
	? sanitize_key( $_GET['otab'] )
	: 'active';

// Load orders for each group (count only for inactive tabs, full for active)
$orders_by_group = [];
foreach ( $tab_groups as $key => $group ) {
	$orders_by_group[ $key ] = wc_get_orders( [
		'customer' => $current_user_id,
		'status'   => $group['statuses'],
		'limit'    => -1,
		'orderby'  => 'date',
		'order'    => 'DESC',
	] );
}

$base_url = wc_get_account_endpoint_url( 'orders' );
?>

<div class="tc-orders">

	<!-- Tabs -->
	<div class="tc-orders__tabs" role="tablist">
		<?php foreach ( $tab_groups as $key => $group ) :
			$count    = count( $orders_by_group[ $key ] );
			$tab_url  = add_query_arg( 'otab', $key, $base_url );
			$is_active = $key === $active_tab;
		?>
		<a href="<?php echo esc_url( $tab_url ); ?>"
			class="tc-orders__tab <?php echo $is_active ? 'is-active' : ''; ?>"
			role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
			<?php echo esc_html( $group['label'] ); ?>
			<span class="tc-orders__tab-count">(<?php echo esc_html( $count ); ?>)</span>
		</a>
		<?php endforeach; ?>
	</div>

	<!-- Orders list -->
	<div class="tc-orders__list">
		<?php
		$orders = $orders_by_group[ $active_tab ];

		if ( empty( $orders ) ) :
			?>
			<div class="tc-orders__empty">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/no_orders.png" alt="Google Maps">
				<p>هیچ سفارشی در این بخش وجود ندارد.</p>
			</div>
		<?php else : ?>
			<?php foreach ( $orders as $order ) :
				$order_id       = $order->get_id();
				$order_number   = $order->get_order_number();
				$order_total    = $order->get_formatted_order_total();
				$order_url      = $order->get_view_order_url();
				$items          = $order->get_items();
				$item_count     = count( $items );

				$post_section = get_field( 'post_section', $order_id );

				// Shipping method label
				$shipping_label = $post_section ? $post_section['post_type'] : '';

				// Delivery slot meta (may be empty if no slot plugin is used)
				$delivery_slot = $post_section ? $post_section['post_time'] : '';
			?>
			<?php
					// First product image (used for mobile hero)
					$first_item    = reset( $items );
					$first_product = $first_item ? $first_item->get_product() : null;
					$first_img_id  = $first_product ? $first_product->get_image_id() : 0;
					$first_img_url = $first_img_id
						? wp_get_attachment_image_url( $first_img_id, 'medium' )
						: wc_placeholder_img_src( 'medium' );
					$first_img_alt = $first_product ? $first_product->get_name() : '';
					?>
				<div class="tc-order-card">
					<div class="tc-order-card__header">
						<span class="tc-order-card__code flex gap-10">
							<span class="color-black-80 yekan-16 md:yekan-22"><?php echo esc_html( 'TLC-' . $order_number ); ?></span><span class="color-black-50 yekan-16 md:yekan-22">:کد سفارش</span>
						</span>
						<span class="tc-order-card__total"><?php echo wp_kses_post( $order_total ); ?></span>
					</div>

					<img src="<?php echo esc_url( $first_img_url ); ?>" alt="<?php echo esc_attr( $first_img_alt ); ?>" class="tc-order-card__mobile-img">

					<div class="tc-order-card__body">
						<div class="tc-order-card__package-label">
							<span class="tc-order-card__item-count"><?php echo esc_html( $item_count ); ?> کالا</span>
						</div>

						<div class="tc-order-card__meta flex items-center gap-20 justify-between">
							<?php if ( $delivery_slot ) : ?>
							<div class="tc-order-card__meta-row">
								<span class="tc-order-card__meta-key">زمان ارسال :</span>
								<span class="tc-order-card__meta-val"><?php echo esc_html( $delivery_slot ); ?></span>
							</div>
							<?php endif; ?>

							<?php if ( $shipping_label ) : ?>
							<div class="tc-order-card__meta-row">
								<span class="tc-order-card__meta-key">نوع ارسال :</span>
								<span class="tc-order-card__meta-val"><?php echo esc_html( $shipping_label ); ?></span>
							</div>
							<?php endif; ?>
						</div>

						<div class="tc-order-card__footer flex justify-between items-end">
							<div class="tc-order-card__thumbs">
								<?php
								$shown = 0;
								foreach ( $items as $item ) {
									if ( $shown >= 3 ) break;
									$product   = $item->get_product();
									$image_id  = $product ? $product->get_image_id() : 0;
									$image_url = $image_id
										? wp_get_attachment_image_url( $image_id, 'thumbnail' )
										: wc_placeholder_img_src( 'thumbnail' );
									$name = $product ? $product->get_name() : $item->get_name();
									echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $name ) . '" class="tc-order-card__thumb">';
									$shown++;
								}
								if ( $item_count > 3 ) {
									echo '<span class="tc-order-card__thumb-more">+' . ( $item_count - 3 ) . '</span>';
								}
								?>
							</div>
							<a href="<?php echo esc_url( $order_url ); ?>" class="tc-order-card__details-link">
								جزئیات بیشتر
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

</div>
