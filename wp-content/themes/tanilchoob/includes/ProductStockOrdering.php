<?php

namespace TanilChoob\Theme;

/**
 * Orders product listings so available products (in_stock, in_produce)
 * always appear before unavailable ones (out_of_stock_temporary, out_of_stock),
 * regardless of the selected sort option.
 *
 * Applies to the main archive/taxonomy queries and the AJAX load-more query.
 */
class ProductStockOrdering
{
	public function __construct()
	{
		add_filter('posts_clauses', array($this, 'prioritize_available_products'), 20, 2);
	}

	/**
	 * Prepend a stock-priority key to the ORDER BY of product queries.
	 *
	 * @param array     $clauses
	 * @param \WP_Query $query
	 * @return array
	 */
	public function prioritize_available_products($clauses, $query)
	{
		if (!$this->is_product_listing_query($query)) {
			return $clauses;
		}

		global $wpdb;

		// Join the product status meta (missing meta counts as available,
		// matching the back-office default of 'in_stock').
		$join = " LEFT JOIN {$wpdb->postmeta} tc_stock_status ON ({$wpdb->posts}.ID = tc_stock_status.post_id AND tc_stock_status.meta_key = '_product_status') ";
		if (strpos($clauses['join'], 'tc_stock_status') === false) {
			$clauses['join'] .= $join;
		}

		$priority = "CASE WHEN tc_stock_status.meta_value IN ('out_of_stock_temporary','out_of_stock') THEN 1 ELSE 0 END ASC";

		if (!empty($clauses['orderby'])) {
			$clauses['orderby'] = $priority . ', ' . $clauses['orderby'];
		} else {
			$clauses['orderby'] = $priority;
		}

		return $clauses;
	}

	/**
	 * Whether this query lists products on the frontend.
	 *
	 * @param \WP_Query $query
	 * @return bool
	 */
	private function is_product_listing_query($query)
	{
		// Skip admin screens, but allow admin-ajax (load-more requests).
		if (is_admin() && !wp_doing_ajax()) {
			return false;
		}

		$post_type = $query->get('post_type');
		if ($post_type === 'product' || (is_array($post_type) && in_array('product', $post_type, true))) {
			return true;
		}

		// Main query on product taxonomy archives has no explicit post_type.
		if ($query->is_main_query() && ($query->is_tax('product_cat') || $query->is_tax('product_tag') || $query->is_post_type_archive('product'))) {
			return true;
		}

		return false;
	}
}
