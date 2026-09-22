<?php

use Composer\Installers\TuskInstaller;

function cancel_snapppay_order($order_id, $from_status)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return; // Ensure the order exists
    }

    $order_payment_method = $order->get_payment_method(); // Use getter method

    // Check if the payment method is SnappPay
    if (!in_array($order_payment_method, ['WC_Gateway_SnappPay', 'snapppay'], true)) {
        return;
    }

    $order_payment_token = get_order_payment_token($order);
    if (!$order_payment_token) {
        return; // Check if the payment token exists
    }

    $snappPayAPI = new SnappPayAPI(); // Create an instance outside the conditions

    if ('pending' === $from_status) {
        $status_response = $snappPayAPI->status($order_payment_token);
        //log_snapppay_response('cancel_snapppay_order pending status', $status_response);

        if (is_wp_error($status_response) || !$status_response['successful']) {

            return handle_error_status($order, $from_status);
        }

        if (isset($status_response['response'])) {
            $response_status = $status_response['response']['status'];
            return handle_status_response($order, $response_status, $snappPayAPI, $order_payment_token, $from_status);
        }
    } else {
        $response = $snappPayAPI->cancel($order_payment_token);
        handle_response($order, $response);
    }
}

function log_snapppay_response($message, $response)
{
    $log_message = $message . ' - ' . json_encode($response) . PHP_EOL;
    file_put_contents(ABSPATH . 'snapppay.log', $log_message, FILE_APPEND);
}

function handle_error_status($order, $from_status)
{
    $snapppay_check_status = $order->get_meta('_wc_snapppay_check_status', true);

    if ('yes' === $snapppay_check_status) {
        $order->update_status('failed');
        return false;
    } else {
        $order->update_meta_data('_wc_snapppay_check_status', 'yes');
        $order->update_status($from_status);
        return false;
    }
}

function handle_status_response($order, $status, $snappPayAPI, $token, $from_status)
{
    switch ($status) {
        case 'REVERT':
            $order->update_status('failed');
            return true;

        case 'VERIFY':
            return process_verification($order, $snappPayAPI, $token, $from_status);

        case 'SETTLE':
            $order->update_status('processing');
            return true;

        case 'PENDING':
            return process_pending($order, $snappPayAPI, $token);

        default:
            return false; // Handle unexpected status
    }
}

function process_verification($order, $snappPayAPI, $token, $from_status = 'pending')
{
    $settle_response = $snappPayAPI->settle($token);
    if (isset($settle_response['successful']) && $settle_response['successful']) {
        $order->update_status('processing');
        return true;
    } else {
        $order->update_status($from_status);
        return false;
    }
}

function process_pending($order, $snappPayAPI, $token)
{
    $verify_response = $snappPayAPI->verify($token);
    if (isset($verify_response['successful']) && $verify_response['successful']) {
        return process_verification($order, $snappPayAPI, $token);
    } else {
        $order->update_status('failed');
        return false;
    }
    
}

function verify_snapppay_order($order_id)
{
    /** @var WC_Order $order */
    $order = wc_get_order($order_id);

    $order_payment_method = $order->payment_method;

    // backward compatible
    if (false === in_array($order_payment_method, array('WC_Gateway_SnappPay', 'snapppay'))) {
        return;
    }

    $order_payment_token = get_order_payment_token($order);

    if (!$order_payment_token) {
        return;
    }

    $response = (new SnappPayAPI)->verify($order_payment_token);

    handle_response($order, $response, 'status');
}

function get_order_payment_token($order)
{
    $order_payment_token = $order->order_spp_token;

    if (!$order_payment_token) {
        $order_payment_token = $order->paymentToken;
    }

    if (!$order_payment_token) {
        $order_payment_token = $order->get_meta('_order_spp_token');
    }

    if (!$order_payment_token) {
        $order_payment_token = $order->get_meta('_paymentToken');
    }

    if (!$order_payment_token) {
        $order_payment_token = $order->get_meta('order_spp_token');
    }

    if (!$order_payment_token) {
        $order_payment_token = $order->get_meta('paymentToken');
    }

    if (!$order_payment_token) {
        $order_payment_token = get_post_meta($order->id, 'order_spp_token', true);
    }

    if (!$order_payment_token) {
        $order_payment_token = get_post_meta($order->id, '_order_spp_token', true);
    }

    if (!$order_payment_token) {
        $order_payment_token = get_post_meta($order->id, 'paymentToken', true);
    }

    if (!$order_payment_token) {
        $order_payment_token = get_post_meta($order->id, '_paymentToken', true);
    }

    if (!$order_payment_token) {
        return false;
    } else {
        return $order_payment_token;
    }
}


function handle_response($order, $response, $type = 'cancel')
{
    $order->add_order_note(prepare_message($response, $type));
}

function prepare_message($response, $type)
{
    $order_type = $type == 'cancel' ? 'لغو' : 'بروزرسانی';

    if ($type == 'status') {
        $order_type = 'بررسی وضعیت';
    }

    $message = "اسنپ پی: عملیات <strong style='color:green'>$order_type سفارش با موفقیت انجام شد</strong>.";

    if (is_wp_error($response)) {
        $message = "اسنپ پی : عملیات <strong style='color:red'>$order_type سفارش ناموفق بود: " . $response->get_error_message() . "</strong>";
        return $message;
    }

    if (!$response["successful"]) {
        $response_error_message = '';
        if (!empty($response['errorData']['message'])) {
            $response_error_message = $response['errorData']['message'];
        }
        $message = "اسنپ پی: عملیات <strong style='color:red'>$order_type سفارش ناموفق بود.  $response_error_message</strong>";
        return $message;
    }

    return $message;
}

if (!function_exists("snapppay_mobile")) {
    function snapppay_mobile(string $mobile)
    {
        $mobile = snapppay_num_to_en($mobile);

        if (preg_match("@^9809@", $mobile) && strlen($mobile) == 13) {
            return "+98" . substr($mobile, 3);
        }


        if (preg_match("@^\+9898@", $mobile) && strlen($mobile) > 13) {

            if (preg_match("@^\+98980@", $mobile)) {
                return "+98" . substr($mobile, 6);
            }

            return "+98" . substr($mobile, 5);
        }

        if (preg_match("@^\+980@", $mobile) && strlen($mobile) == 14) {
            return "+98" . substr($mobile, 4);
        }

        if (preg_match("@^\+98@", $mobile)) {
            return $mobile;
        }

        if (preg_match("@^98@", $mobile)) {
            return "+" . $mobile;
        }

        if (preg_match("@^09@", $mobile) && strlen($mobile) == 11) {
            return "+98" . substr($mobile, 1);
        }
        
        if (preg_match("@^098@", $mobile)) {
            return "+98" . substr($mobile, 3);
        }

        if (preg_match("@^9@", $mobile) && strlen($mobile) == 10) {
            return "+98" . $mobile;
        }

        return false;
    }
}

if (!function_exists("snapppay_num_to_en")) {
    function snapppay_num_to_en($string)
    {
        $persian = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
        $arabic = ["٩", "٨", "٧", "٦", "٥", "٤", "٣", "٢", "١", "٠"];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        return str_replace($arabic, $num, $convertedPersianNums);
    }
}

if (!function_exists("snapppay_is_json")) {
    function snapppay_is_json($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists("get_order_amount_in_iranian_rial")) {
    function get_order_amount_in_iranian_rial(WC_Order $order)
    {
        $currency = $order->get_currency();
        $currency = apply_filters('WC_SnappPay_Currency', $currency, $order->get_id());
        $amount = intval($order->get_total());
        $amount = apply_filters('woocommerce_order_amount_total_iranian_gateways_before_check_currency', $amount, $currency);

        $amount = get_amount_by_currency($amount, $currency);

        $amount = apply_filters('woocommerce_order_amount_total_iranian_gateways_after_check_currency', $amount, $currency);
        $amount = apply_filters('woocommerce_order_amount_total_iranian_gateways_irr', $amount, $currency);
        $amount = apply_filters('woocommerce_order_amount_total_snapppay_gateway', $amount, $currency);

        return $amount;
    }
}

if (!function_exists("get_amount_by_currency")) {
    function get_amount_by_currency($amountVal, $currency = null)
    {
	$amount = (int) $amountVal;    

        if(null === $currency) {
            $currency = get_woocommerce_currency();
        }

        if (
            strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN')
            || strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN')
            || strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN')
            || strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN')
            || strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران')
        ) {
            $amount = $amount * 10;
        } elseif (strtolower($currency) == strtolower('IRHT')) {
            $amount = $amount * 1000 * 10;
        } elseif (strtolower($currency) == strtolower('IRHR')) {
            $amount = $amount * 1000;
        }

        return $amount;
    }
}

if (!function_exists('snapppay_has_comission')) {
    function snapppay_has_comission()
    {
        $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');
        return $snapppay_settings['has_comission'] == 'yes' ? true : false;
    }
}

if (!function_exists('snapppay_has_pdp')) {
    function snapppay_has_pdp()
    {
        //$snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');
        //return $snapppay_settings['has_pdp'] == 'yes' ? true : false;
	$settings = get_option('woocommerce_WC_Gateway_SnappPay_settings', []);
        return ($settings['has_pdp'] ?? 'no') === 'yes';
    }
}

if (!function_exists('snapppay_dark_pdp')) {
    function snapppay_dark_pdp()
    {
        $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');
        return $snapppay_settings['dark_pdp'] == 'yes' ? true : false;
    }
}

if (!function_exists('snapppay_direct_payment')) {
    function snapppay_direct_payment()
    {
        $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');
        return $snapppay_settings['direct_payment'] == 'yes' ? true : false;
    }
}

function snapppay_woo_active_notice()
{
    echo '<div class="notice notice-error is-dismissible">';
    echo 'WooCommerce is not activated, please activate it to use <b>Snapp Pay Gateway</b>';
    echo '</div>';
}

function snapppay_get_ip()
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://whatisip.snapppay.ir/whatis/ip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    // save the IP address
    $result = explode(":", $result)[1];
    update_option('snapppay-web-server-ip', $result);
    update_option('snapppay-web-server-ip-show', 'yes');
}

function track_event($eventname) {
    $site_data = gather_site_data($eventname);

    log_error("Site Data:");
    log_error($site_data);

    $result = send_to_google_sheets($site_data);
    
    // Store activation data for later checking
    $success = !is_wp_error($result);
    $response_data = $success ? json_decode(wp_remote_retrieve_body($result), true) : null;
    
    update_option('activation_tracker_data', array(
        'site_data' => $site_data,
        'sent_at' => current_time('mysql'),
        'success' => $success,
        'response' => $response_data,
        'error' => is_wp_error($result) ? $result->get_error_message() : null
    ));
    
    update_option('activation_tracker_activated', true);
}

function gather_site_data($eventname) {
    
    $additional_info = array(
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => phpversion(),
        'locale' => get_locale(),
        'timezone' => wp_timezone_string(),
        'plugin_version' => '1.0.2',
        'action' => $eventname
    );
    
    return array(
        'sitename' => sanitize_text_field(get_bloginfo('name')),
        'siteurl' => esc_url_raw(get_site_url()),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'additional_info' => $additional_info
    );
}

/**
 * Send data to Google Sheets via Apps Script
 */
function send_to_google_sheets($data) {

    $google_script_url = "https://script.google.com/macros/s/AKfycbwzSSUqPescvvu7rO7fyBnTTH2LSJXzXiQq8LiEXHnKZS9pPGGoOHobog4WptcnRtOI/exec";

    $args = array(
        'body' => wp_json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'timeout' => 30,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'data_format' => 'body'
    );
    
    // Make the POST request
    $response = wp_remote_post($google_script_url, $args);
    
    // Log the response for debugging
    if (is_wp_error($response)) {
        log_error('Activation Tracker Error: ' . $response->get_error_message());
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        log_error("Activation Tracker Response: Code {$response_code} - {$response_body}");
    }
    
    return $response;
}

function log_error($message) {

    file_put_contents(ABSPATH . 'snapppay.log', date('Y-m-d H:i:s') . ' === ' . json_encode(['message' => $message], true) . PHP_EOL . PHP_EOL, FILE_APPEND);
    
}
