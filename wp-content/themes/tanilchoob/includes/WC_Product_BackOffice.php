<?php


namespace TanilChoob\Theme;


class WC_Product_BackOffice
{
	/**
	 * WC_Product_BackOffice constructor.
	 */
	public function __construct()
	{
		// Add product status field to general tab
		add_action('woocommerce_product_options_general_product_data', array($this, 'add_product_status_field'));
		
		// Save product status field
		add_action('woocommerce_process_product_meta', array($this, 'save_product_status_field'));
	}

	/**
	 * Add product status field to general tab
	 */
	public function add_product_status_field()
	{
		global $post;
		
		// Get current value or set default to 'in_stock'
		$product_status = get_post_meta($post->ID, '_product_status', true);
		if (empty($product_status)) {
			$product_status = 'in_stock';
		}
		
		// Status options
		$options = array(
			'' => __('وضعیت محصول', 'tanilchoob'),
			'in_stock' => __('موجود و آماده ارسال', 'tanilchoob'),
			'in_produce' => __('در حال تولید', 'tanilchoob'),
			'out_of_stock_temporary' => __('توقف موقت تولید', 'tanilchoob'),
			'out_of_stock' => __('توقف کامل تولید', 'tanilchoob'),
		);
		
		// Output the field
		woocommerce_wp_select(array(
			'id' => '_product_status',
			'label' => __('وضعیت محصول', 'tanilchoob'),
			'description' => __('وضعیت فعلی محصول را انتخاب کنید.', 'tanilchoob'),
			'desc_tip' => true,
			'options' => $options,
			'value' => $product_status,
		));
	}

	/**
	 * Save product status field
	 */
	public function save_product_status_field($post_id)
	{
		$product_status = isset($_POST['_product_status']) ? sanitize_text_field($_POST['_product_status']) : 'in_stock';
		update_post_meta($post_id, '_product_status', $product_status);
	}
}
