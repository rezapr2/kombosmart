<?php

/**
 * SnappPayAPI class.
 *
 * The parent class for the different requests.
 */
class SnappPayAPI
{

    /**
     * Send sales tax as separate item (US merchants).
     *
     * @var bool
     */
    private $settings = false;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');
    }

    public function get_settings()
    {
        return apply_filters('snapppay_settings', $this->settings);
    }

    /**
     * Gets SnappPay API URL base.
     */
    public function get_api_base_url()
    {
        return substr($this->settings['base_url'], -1) === '/' ? $this->settings['base_url'] : $this->settings['base_url'] . '/';
    }

    /**
     * Gets SnappPay API request basic token.
     *
     * @return array
     */
    protected function get_request_basic_token()
    {
        $token = 'Basic ' . base64_encode($this->settings['client_id'] . ':' . str_replace('amp;', '', $this->settings['client_secret']));
        return array(
            'Authorization' => $token,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        );
    }

    /**
     * Gets SnappPay API request bearer token.
     *
     * @return array
     */
    protected function get_request_bearer_token()
    {
        $bearer_token = get_transient('snapppay_bearer_token');
        if (!$bearer_token) {
            $response = $this->request(
                'api/online/v1/oauth/token',
                'POST',
                'Basic',
                [
                    'grant_type' => 'password',
                    'scope' => 'online-merchant',
                    'username' => $this->settings['client_username'],
                    'password' => str_replace('amp;', '', $this->settings['client_password']),
                ]
            );


            if (is_wp_error($response)) {
                return false;
            }
            $bearer_token = $response['access_token'];
            $ttl = $response['expires_in'];
            set_transient('snapppay_bearer_token', $bearer_token, $ttl);
        }

        return array(
            'Authorization' => 'Bearer ' . $bearer_token,
            'Content-Type'  => 'application/json',
        );
    }

    public function is_merchant_eligible($amount)
    {
        if (empty($this->settings['client_username']) || empty($this->settings['client_password']) || empty($this->settings['client_id']) || empty($this->settings['client_secret']) || empty($this->settings['base_url'])) {
            return new WP_Error('401', 'خطای تنظیمات پلاگین درگاه پرداخت اسنپ! پی');
        }

        $response = $this->request('api/online/offer/v1/eligible', 'GET', 'Bearer', ['amount' => $amount]);

        if (is_wp_error($response)) {
            return new WP_Error('401', 'خطای درگاه ' . $response->get_error_message());
        } else {
            return $response;
        }
    }

    public function get_payment_token(WC_Order $order, $transactionId)
    {
        $order_id = $order->get_id();
        $amount = get_order_amount_in_iranian_rial($order);

        do_action('WC_SnappPay_Gateway_Payment', $order_id);

        $callBackUrl = add_query_arg('wc_order', $order_id, WC()->api_request_url(strtolower('WC_Gateway_SnappPay')));

        $data = array(
            "amount" => $amount,
            "paymentMethodTypeDto" => 'INSTALLMENT',
            "returnURL" => $callBackUrl,
            "transactionId" => $transactionId,
        );

        $mobile = snapppay_mobile($order->get_billing_phone());

        if ($mobile) {
            $data['mobile'] = $mobile;
        }

        $data['cartList'][] = $this->build_cart_list($order);
        $data['discountAmount'] = false === empty($order->get_data()['discount_total']) ? get_amount_by_currency($order->get_data()['discount_total'], $order->get_currency()) : 0;

        return $this->request('api/online/payment/v1/token', 'POST', 'Bearer', $data);
    }

    private function get_product_categories($product_id)
    {
        $terms = get_the_terms($product_id, 'product_cat');

        $commission_type = null;
        $category_name = null;

        if (is_wp_error($terms)) {
            return [$commission_type, $category_name];
        }

        if (empty($terms)) {
            $product_parent_id = wc_get_product($product_id)->get_parent_id();
            $terms = get_the_terms($product_parent_id, 'product_cat');
        }

        if (empty($terms)) {
            return [$commission_type, $category_name];
        }

        $main_category = null;

        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $main_category = $term;
            }
            $term_commision_type = get_term_meta($term->term_id, 'snp_commission_type', true);
            $term_name = $term->name;
        }

        if (!is_null($main_category)) {
            $main_category_commision_type = get_term_meta($main_category->term_id, 'snp_commission_type', true);
        }

        $commission_type = $term_commision_type ?: $main_category_commision_type ?: '';
        $category_name = $term_name ?: $main_category->name ?: '';

        return [$commission_type, $category_name];
    }

    private function build_cart_list(WC_Order $order)
    {
        $order_currency = $order->get_currency();
        $cart_list = [
            'cartId' => $order->get_id(),
            'totalAmount' => get_order_amount_in_iranian_rial($order),
        ];
        $items = array();

        /** @var WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item_id => $item) {

            /** @var WC_Product $product */
            $product = $item->get_product();

            list($commission_type, $category_name) = $this->get_product_categories($product->get_id());

            $item_params = array(
                "name" => $item->get_name(),
                "count" => $item->get_quantity(),
                // "amount" => get_amount_by_currency($item->get_total(), $order_currency),
                "amount" => get_amount_by_currency($product->get_price('edit'), $order_currency),
                "id" => $product->get_id(),
                "category" => $category_name,
            );

            if (snapppay_has_comission()) {
                $item_params['commissionType'] = $commission_type;
            }

            $items[] = $item_params;
        }
        $cart_list['cartItems'] = $items;

        $order_data = $order->get_data();

        $cart_list['taxAmount']          = false === empty($order_data['total_tax']) ? get_amount_by_currency($order_data['total_tax'], $order_currency) : 0;
        $cart_list['shippingAmount']     = false === empty($order_data['shipping_total']) ? get_amount_by_currency($order_data['shipping_total'], $order_currency) : 0;
        $cart_list['isShipmentIncluded'] = @$order_data['shipping_total'] > 0 ? true : false;
        $cart_list['isTaxIncluded']      = @$order_data['total_tax'] > 0 ? true : false;

        return $cart_list;
    }

    public function verify($order_spp_token)
    {        
        return $this->request('api/online/payment/v1/verify', 'POST', 'Bearer', ['paymentToken' => $order_spp_token]);
    }

    public function settle($order_spp_token)
    {
        return $this->request('api/online/payment/v1/settle', 'POST', 'Bearer', ['paymentToken' => $order_spp_token]);
    }

    public function cancel($paymentToken)
    {
        return $this->request('api/online/payment/v1/cancel', 'POST', 'Bearer', ['paymentToken' => $paymentToken]);
    }

    public function status($paymentToken)
    {
        return $this->request('api/online/payment/v1/status', 'GET', 'Bearer', ['paymentToken' => $paymentToken]);
    }

    public function update(WC_Order $order, string $paymentToken)
    {
        $amount = get_order_amount_in_iranian_rial($order);
        $data = array(
            "amount" => $amount,
            "paymentMethodTypeDto" => 'INSTALLMENT',
            "paymentToken" => $paymentToken,
        );

        $data['cartList'][] = $this->build_cart_list($order);
        $data['discountAmount'] = false === empty($order->get_data()['discount_total']) ? get_amount_by_currency($order->get_data()['discount_total'], $order->get_currency()) : 0;

        return $this->request('api/online/payment/v1/update', 'POST', 'Bearer', $data);
    }

    /**
     * Checks response for any error.
     *
     * @param object $response The response.
     * @param array  $request_args The request args.
     * @param string $request_url The request URL.
     * @return object|array
     */
    public function process_response($response, $request_args = array(), $request_url = '')
    {
        // Check if response is a WP_Error, and return it back if it is.
        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $is_json = snapppay_is_json($response_body);
        // Check the status code, if its not between 200 and 299 then its an error.
        if (!$is_json) {
            return new \WP_Error($response['response']['code'], $response['response']['message']);
        }

        return json_decode($response_body, true);
    }

    /**
     * Custom remote request wrapper.
     */
    private function request($endpoint, $method = 'GET', $token = 'Basic', $args = [], $user = null, $url = null)
    {
        $request = array(
            'method' => $method
        );

        if (!$user) {
            $user = wp_get_current_user();
        }

        if (!$url) {
            $url = $this->get_api_base_url() . $endpoint;
        }

        $request['headers'] = $this->prepare_request_headers($token);        

        if ($method == 'GET' && !empty($args) && is_array($args)) {
            $url = add_query_arg($args, $url);
        } else {
            if ($token === 'Basic') {
                $request['body'] = http_build_query($args);
            } else {
                $request['body'] = json_encode($args);
            }
        }

        $response = wp_remote_request($url, $request);

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code && $response_code == 401) {
            delete_transient('snapppay_bearer_token');
            $request['headers'] = $this->prepare_request_headers($token);        
            $response = wp_remote_request($url, $request);
        }

        //file_put_contents(ABSPATH . 'snapppay.log', date('Y-m-d H:i:s') . ' === ' . json_encode(['req' => $request, 'res' => $response, 'url' => $url], true) . PHP_EOL . PHP_EOL, FILE_APPEND);
        //chmod(ABSPATH . 'snapppay.log', 0600);
        return $this->process_response($response, $request, $url);
    }

    private function prepare_request_headers($token) {

        $headers = array();

        if ($token === 'Basic') {
            $headers = $this->get_request_basic_token();
        } else {
            $headers = $this->get_request_bearer_token();
        }

        // Add custom user-agent to request.
        $headers['user-agent'] = "SnappPay, " . $this->settings['client_id'];

        return $headers;

    }
}
