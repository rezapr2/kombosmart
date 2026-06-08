<?php

namespace TanilChoob\Theme\Ajax;

class Checkout {

	public function __construct() {
		add_action( 'wp_ajax_tc_cart_update_qty',   [ $this, 'handle_cart_update_qty' ] );
		add_action( 'wp_ajax_tc_cart_remove_item',  [ $this, 'handle_cart_remove_item' ] );
		add_action( 'wp_ajax_tc_address_save',      [ $this, 'handle_address_save' ] );
		add_action( 'wp_ajax_tc_address_delete',    [ $this, 'handle_address_delete' ] );
		add_action( 'wp_ajax_tc_place_order',       [ $this, 'handle_place_order' ] );
		add_action( 'wp_ajax_tc_apply_coupon',      [ $this, 'handle_apply_coupon' ] );
		add_action( 'wp_ajax_tc_remove_coupon',     [ $this, 'handle_remove_coupon' ] );
		add_action( 'template_redirect',            [ $this, 'maybe_redirect_checkout' ] );
	}

	public function maybe_redirect_checkout() {
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_user_logged_in() ) {
			wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
			exit;
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() && function_exists( 'WC' ) && WC()->cart->is_empty() ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}

	// ── Nonce check ─────────────────────────────────────────

	private function check_nonce() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( [ 'message' => 'درخواست نامعتبر است.' ] );
		}
	}

	// ── Cart ────────────────────────────────────────────────

	public function handle_cart_update_qty() {
		$this->check_nonce();

		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0;

		if ( ! $key ) {
			wp_send_json_error( [ 'message' => 'آیتم سبد خرید یافت نشد.' ] );
		}

		WC()->cart->set_quantity( $key, $qty );
		WC()->cart->calculate_totals();

		$item           = WC()->cart->get_cart_item( $key );
		$line_total     = $item ? $item['line_total'] : 0;

		wp_send_json_success( [
			'line_total'   => number_format( $line_total, 0, '.', ',' ),
			'cart_total'   => number_format( (float) WC()->cart->get_total( '' ), 0, '.', ',' ),
			'is_empty'     => WC()->cart->is_empty(),
		] );
	}

	public function handle_cart_remove_item() {
		$this->check_nonce();

		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

		if ( ! $key ) {
			wp_send_json_error( [ 'message' => 'آیتم سبد خرید یافت نشد.' ] );
		}

		WC()->cart->remove_cart_item( $key );
		WC()->cart->calculate_totals();

		wp_send_json_success( [
			'cart_total' => number_format( (float) WC()->cart->get_total( '' ), 0, '.', ',' ),
			'is_empty'   => WC()->cart->is_empty(),
		] );
	}

	// ── Addresses ────────────────────────────────────────────

	public function handle_address_save() {
		$this->check_nonce();

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'برای ذخیره آدرس باید وارد شوید.' ] );
		}

		$id = isset( $_POST['address_id'] ) && $_POST['address_id']
			? sanitize_text_field( wp_unslash( $_POST['address_id'] ) )
			: wp_generate_uuid4();

		$data = [
			'id'           => $id,
			'first_name'   => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
			'last_name'    => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
			'phone'        => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'fixedphone'   => sanitize_text_field( wp_unslash( $_POST['fixedphone'] ?? '' ) ),
			'postcode'     => sanitize_text_field( wp_unslash( $_POST['postcode'] ?? '' ) ),
			'nationalcode' => sanitize_text_field( wp_unslash( $_POST['nationalcode'] ?? '' ) ),
			'city'         => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
			'address_1'    => sanitize_textarea_field( wp_unslash( $_POST['address_1'] ?? '' ) ),
		];

		if ( ! $data['first_name'] || ! $data['last_name'] || ! $data['phone'] || ! $data['address_1'] ) {
			wp_send_json_error( [ 'message' => 'لطفاً فیلدهای الزامی را پر کنید.' ] );
		}

		$this->save_address( $data );
		wp_send_json_success( [ 'address' => $data ] );
	}

	public function handle_address_delete() {
		$this->check_nonce();

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'برای حذف آدرس باید وارد شوید.' ] );
		}

		$id = isset( $_POST['address_id'] ) ? sanitize_text_field( wp_unslash( $_POST['address_id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'آدرس یافت نشد.' ] );
		}

		$this->delete_address( $id );
		wp_send_json_success( [] );
	}

	// ── Place order ──────────────────────────────────────────

	public function handle_place_order() {
		$this->check_nonce();

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'برای ثبت سفارش باید وارد شوید.' ] );
		}

		if ( WC()->cart->is_empty() ) {
			wp_send_json_error( [ 'message' => 'سبد خرید شما خالی است.' ] );
		}

		$address_id     = isset( $_POST['address_id'] )     ? sanitize_text_field( wp_unslash( $_POST['address_id'] ) )     : '';
		$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';
		$notes          = isset( $_POST['notes'] )          ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) )       : '';

		if ( ! $address_id )     { wp_send_json_error( [ 'message' => 'لطفاً یک آدرس انتخاب کنید.' ] ); }
		if ( ! $payment_method ) { wp_send_json_error( [ 'message' => 'لطفاً شیوه پرداخت را انتخاب کنید.' ] ); }

		$address = $this->get_address_by_id( $address_id );
		if ( ! $address ) {
			wp_send_json_error( [ 'message' => 'آدرس انتخابی یافت نشد.' ] );
		}

		$order = wc_create_order( [
			'customer_id' => get_current_user_id(),
			'status'      => 'pending',
		] );

		if ( is_wp_error( $order ) ) {
			wp_send_json_error( [ 'message' => 'خطا در ایجاد سفارش.' ] );
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			$order_item = $order->add_product( $item['data'], $item['quantity'], [
				'subtotal'     => $item['line_subtotal'],
				'total'        => $item['line_total'],
				'subtotal_tax' => $item['line_subtotal_tax'],
				'total_tax'    => $item['line_tax'],
				'variation_id' => $item['variation_id'] ?? 0,
				'variation'    => $item['variation'] ?? [],
			] );

			if ( ! $order_item ) {
				continue;
			}

			$item_id = is_object( $order_item ) ? $order_item->get_id() : (int) $order_item;

			// Custom option adjustments — written directly to DB so admin always sees them
			if ( $item_id && ! empty( $item['tc_option_adjustments'] ) && is_array( $item['tc_option_adjustments'] ) ) {
				foreach ( $item['tc_option_adjustments'] as $opt ) {
					$amount = isset( $opt['amount'] ) ? (float) $opt['amount'] : 0.0;
					$sign   = $amount >= 0 ? '+' : '-';
					wc_add_order_item_meta(
						$item_id,
						$opt['label'] ?? 'گزینه',
						$sign . ' ' . number_format( abs( $amount ), 0, '.', ',' ) . ' تومان'
					);
				}
			}
		}

		$gateways       = WC()->payment_gateways()->payment_gateways();
		$gateway_obj    = $gateways[ $payment_method ] ?? null;

		if ( ! $gateway_obj ) {
			wp_send_json_error( [ 'message' => 'شیوه پرداخت انتخابی معتبر نیست.' ] );
		}

		$addr_data = [
			'first_name' => $address['first_name'],
			'last_name'  => $address['last_name'],
			'phone'      => $address['phone'],
			'address_1'  => $address['address_1'],
			'city'       => $address['city'] ?? '',
			'postcode'   => $address['postcode'] ?? '',
			'country'    => 'IR',
		];

		$order->set_address( $addr_data, 'billing' );
		$order->set_address( $addr_data, 'shipping' );
		$order->set_payment_method( $payment_method );
		$order->set_payment_method_title( $gateway_obj->get_title() );

		foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$order->apply_coupon( $coupon_code );
		}

		if ( $notes ) {
			$order->add_order_note( $notes, true );
		}

		$order->calculate_totals();
		$order->update_meta_data( '_tc_payment_note', $notes );
		if ( ! empty( $address['fixedphone'] ) )   $order->update_meta_data( '_tc_billing_fixedphone',   $address['fixedphone'] );
		if ( ! empty( $address['nationalcode'] ) ) $order->update_meta_data( '_tc_billing_nationalcode', $address['nationalcode'] );
		$order->save();

		// For gateways that handle payment themselves (e.g. online bank redirect),
		// call process_payment() to get the redirect URL.
		if ( $gateway_obj->id !== 'cod' && $gateway_obj->id !== 'bacs' && $gateway_obj->id !== 'cheque' ) {
			$result = $gateway_obj->process_payment( $order->get_id() );
			if ( isset( $result['result'] ) && $result['result'] === 'success' && ! empty( $result['redirect'] ) ) {
				WC()->cart->empty_cart();
				wp_send_json_success( [ 'redirect_url' => $result['redirect'] ] );
			}
		}

		WC()->cart->empty_cart();

		$created    = $order->get_date_created();
		$order_date = $created
			? \TanilChoob\Theme\Helper::jalali_date($created->getTimestamp())
			: \TanilChoob\Theme\Helper::jalali_date(time());

		wp_send_json_success( [
			'order_id'       => $order->get_id(),
			'order_number'   => $order->get_order_number(),
			'order_date'     => $order_date,
			'payment_title'  => $gateway_obj->get_title(),
			'address'        => $address,
			'notes'          => $notes,
			'total'          => number_format( (float) $order->get_total(), 0, '.', ',' ),
			'items'          => $this->get_order_items_data( wc_get_order( $order->get_id() ) ),
		] );
	}

	// ── Coupons ──────────────────────────────────────────────

	public function handle_apply_coupon() {
		$this->check_nonce();

		$code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

		if ( ! $code ) {
			wp_send_json_error( [ 'message' => 'کد تخفیف را وارد کنید.' ] );
		}

		wc_clear_notices();

		$result = WC()->cart->apply_coupon( $code );

		if ( ! $result ) {
			$notices = wc_get_notices( 'error' );
			$msg     = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : 'کد تخفیف معتبر نیست.';
			wc_clear_notices();
			wp_send_json_error( [ 'message' => $msg ] );
		}

		wc_clear_notices();
		WC()->cart->calculate_totals();

		wp_send_json_success( $this->coupon_response() );
	}

	public function handle_remove_coupon() {
		$this->check_nonce();

		$code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

		if ( ! $code ) {
			wp_send_json_error( [ 'message' => 'کد تخفیف معتبر نیست.' ] );
		}

		WC()->cart->remove_coupon( $code );
		WC()->cart->calculate_totals();

		wp_send_json_success( $this->coupon_response() );
	}

	private function coupon_response(): array {
		return [
			'cart_total'      => number_format( (float) WC()->cart->get_total( '' ), 0, '.', ',' ),
			'discount_total'  => number_format( (float) WC()->cart->get_discount_total(), 0, '.', ',' ),
			'applied_coupons' => WC()->cart->get_applied_coupons(),
		];
	}

	// ── Helpers ──────────────────────────────────────────────

	private function get_user_addresses() {
		$addresses = get_user_meta( get_current_user_id(), 'tc_saved_addresses', true );
		return \is_array( $addresses ) ? $addresses : [];
	}

	private function save_address( array $data ) {
		$addresses = $this->get_user_addresses();
		$found = false;
		foreach ( $addresses as &$addr ) {
			if ( $addr['id'] === $data['id'] ) {
				$addr  = $data;
				$found = true;
				break;
			}
		}
		unset( $addr );
		if ( ! $found ) {
			$addresses[] = $data;
		}
		update_user_meta( get_current_user_id(), 'tc_saved_addresses', $addresses );
	}

	private function delete_address( string $id ) {
		$addresses = array_values( array_filter(
			$this->get_user_addresses(),
			fn( $a ) => $a['id'] !== $id
		) );
		update_user_meta( get_current_user_id(), 'tc_saved_addresses', $addresses );
	}

	private function get_address_by_id( string $id ) {
		foreach ( $this->get_user_addresses() as $addr ) {
			if ( $addr['id'] === $id ) return $addr;
		}
		return null;
	}

	private function get_order_items_data( \WC_Order $order ): array {
		$items = [];
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}
			$qty = $item->get_quantity();

			$customizations = [];
			foreach ( $item->get_formatted_meta_data( '_', true ) as $meta ) {
				$customizations[] = [
					'key'   => wp_strip_all_tags( $meta->display_key ),
					'value' => wp_strip_all_tags( $meta->display_value ),
				];
			}

			$items[] = [
				'name'           => $item->get_name(),
				'qty'            => $qty,
				'price'          => number_format( $qty > 0 ? (float) $item->get_subtotal() / $qty : 0, 0, '.', ',' ),
				'total'          => number_format( (float) $item->get_subtotal(), 0, '.', ',' ),
				'customizations' => $customizations,
			];
		}
		return $items;
	}
}

new Checkout();
