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

		// Admin: Add a custom tab to manage price-affecting options
		add_filter('woocommerce_product_data_tabs', array($this, 'add_price_options_tab'));
		add_action('woocommerce_product_data_panels', array($this, 'render_price_options_panel'));
		add_action('woocommerce_process_product_meta', array($this, 'save_price_options_meta'));
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

	/**
	 * Add a new Product Data tab for price-affecting options
	 */
	public function add_price_options_tab($tabs)
	{
		$tabs['tc_price_options'] = array(
			'label'    => __('گزینه‌های قیمت', 'tanilchoob'),
			'target'   => 'tc_price_options_panel',
			'class'    => array(),
			'priority' => 80,
		);
		return $tabs;
	}

	/**
	 * Render the panel content for the custom price options tab
	 */
	public function render_price_options_panel()
	{
		global $post;
		$options = get_post_meta($post->ID, '_tc_price_options', true);
		if (!is_array($options)) { $options = array(); }
		?>
		<div id="tc_price_options_panel" class="panel woocommerce_options_panel">
			<div class="options-group">
				<p><?php _e('گزینه‌هایی که روی قیمت اثر می‌گذارند را اضافه کنید. مقدار می‌تواند مثبت یا منفی باشد.', 'tanilchoob'); ?></p>
				<table class="widefat wc_input_table" style="margin-top:10px;">
					<thead>
						<tr>
							<th style="width:40%;"><?php _e('عنوان گزینه', 'tanilchoob'); ?></th>
							<th style="width:25%;"><?php _e('مقدار (ریال)', 'tanilchoob'); ?></th>
							<th style="width:25%;"><?php _e('شناسه (اختیاری)', 'tanilchoob'); ?></th>
							<th style="width:10%;"></th>
						</tr>
					</thead>
					<tbody id="tc-price-options-rows">
						<?php if (!empty($options)) : foreach ($options as $opt) :
							$label = isset($opt['label']) ? $opt['label'] : '';
							$amount = isset($opt['amount']) ? floatval($opt['amount']) : 0;
							$id = isset($opt['id']) ? $opt['id'] : '';
						?>
						<tr>
							<td><input type="text" name="tc_option_label[]" value="<?php echo esc_attr($label); ?>" class="short" /></td>
							<td><input type="number" step="1" name="tc_option_amount[]" value="<?php echo esc_attr($amount); ?>" class="short" /></td>
							<td><input type="text" name="tc_option_id[]" value="<?php echo esc_attr($id); ?>" class="short" /></td>
							<td><button type="button" class="button remove_row"><?php _e('حذف', 'tanilchoob'); ?></button></td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4"><button type="button" class="button add_row"><?php _e('افزودن گزینه', 'tanilchoob'); ?></button></td>
						</tr>
					</tfoot>
				</table>
			</div>

			<script>
			(function($){
				function newRow(){
					return $('<tr>\n'
						+ '<td><input type="text" name="tc_option_label[]" class="short" /></td>\n'
						+ '<td><input type="number" step="1" name="tc_option_amount[]" class="short" /></td>\n'
						+ '<td><input type="text" name="tc_option_id[]" class="short" /></td>\n'
						+ '<td><button type="button" class="button remove_row"><?php echo esc_js(__('حذف', 'tanilchoob')); ?></button></td>\n'
					+ '</tr>');
				}
				$('#tc_price_options_panel').on('click', '.add_row', function(){
					$('#tc-price-options-rows').append(newRow());
				});
				$('#tc_price_options_panel').on('click', '.remove_row', function(){
					$(this).closest('tr').remove();
				});
			})(jQuery);
			</script>
		</div>
		<?php
	}

	/**
	 * Save price options to post meta
	 */
	public function save_price_options_meta($post_id)
	{
		$labels  = isset($_POST['tc_option_label']) ? (array) $_POST['tc_option_label'] : array();
		$amounts = isset($_POST['tc_option_amount']) ? (array) $_POST['tc_option_amount'] : array();
		$ids     = isset($_POST['tc_option_id']) ? (array) $_POST['tc_option_id'] : array();

		$options = array();
		$max = max(count($labels), count($amounts), count($ids));
		for ($i = 0; $i < $max; $i++) {
			$label = isset($labels[$i]) ? sanitize_text_field($labels[$i]) : '';
			$amount = isset($amounts[$i]) ? floatval($amounts[$i]) : 0.0;
			$id = isset($ids[$i]) ? sanitize_title($ids[$i]) : '';
			if ($label === '' && $amount === 0.0) { continue; }
			if ($id === '') { $id = 'opt_' . $i; }
			$options[] = array(
				'label' => $label,
				'amount' => $amount,
				'id' => $id,
			);
		}

		update_post_meta($post_id, '_tc_price_options', $options);
	}
}
