<?php
if (!defined('ABSPATH')) {
    exit;
}

function load_snapp_pay_gateway()
{
    // check if woocommerce is not available.
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    if (class_exists('WC_Payment_Gateway') && !class_exists('WC_Gateway_SnappPay') && !function_exists('woocommerce_add_snapppay_gateway')) {

        add_filter('woocommerce_payment_gateways', 'woocommerce_add_snapppay_gateway');

        // woocommerce_WC_Gateway_SnappPay_settings
        function snapppay_settings_after_save($old_value, $new_value)
        {
            $keys = ['client_id', 'client_secret', 'client_username', 'client_password'];
	    $delete_token_transient = false;
            foreach ($keys as $key) {
                if ($old_value[$key] != $new_value[$key]) {
                    $delete_token_transient = true;
                    break;
                }
            }
            $delete_token_transient && delete_transient('snapppay_bearer_token');
        }
        add_action('update_option_woocommerce_WC_Gateway_SnappPay_settings', 'snapppay_settings_after_save', 10, 2);




        function woocommerce_add_snapppay_gateway($methods)
        {
            $methods[] = 'WC_Gateway_SnappPay';
            return $methods;
        }
        class WC_Gateway_SnappPay extends WC_Payment_Gateway
        {
            protected $order_id = 0;
            protected $current_order = 0;
            protected $redirect_url;
            protected $api;

            public function __construct()
            {
                $this->redirect_url = wc_get_checkout_url();
                $this->api = new SnappPayAPI();
                $this->author = 'snapppay';
                $this->id = 'WC_Gateway_SnappPay';
                $this->method_title = 'اسنپ پی';
                $this->method_description = 'تنظیمات درگاه پرداخت اسنپ پی برای افزونه فروشگاه ساز ووکامرس';
                $this->icon = apply_filters('WC_SnappPay_Logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/images/pay_logo.png');
                $this->has_fields = false;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = 'پرداخت اقساطیِ اسنپ پی';
                $this->description = $this->settings['description'];

                // Trim whitespace from credential fields to prevent issues
                $this->client_id = isset($this->settings['client_id']) ? trim($this->settings['client_id']) : '';
                $this->client_secret = isset($this->settings['client_secret']) ? trim($this->settings['client_secret']) : '';
                $this->client_username = isset($this->settings['client_username']) ? trim($this->settings['client_username']) : '';
                $this->client_password = isset($this->settings['client_password']) ? trim($this->settings['client_password']) : '';
                $this->base_url = $this->settings['base_url'];

                $this->success_message = $this->settings['success_message'];
                $this->failed_message = $this->settings['failed_message'];
                $this->cancelled_message = $this->settings['cancelled_message'];

                if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                } else {
                    add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
                }

                add_action('woocommerce_receipt_' . $this->id . '', array($this, 'process_payment_request'));
                add_action('woocommerce_api_' . strtolower(get_class($this)) . '', array($this, 'process_payment_verify'));
                add_action('woocommerce_before_thankyou', array($this, 'snapppay_before_thankyou'), 99);
                add_action('woocommerce_before_cart', array($this, 'snapppay_before_thankyou'), 10);
                add_action('woocommerce_before_checkout_form', array($this, 'snapppay_before_thankyou'), 10);
                add_action('before_woocommerce_pay', array($this, 'snapppay_before_thankyou'), 10);
            }

            public function admin_options()
            {
                $action = $this->author;
                do_action('WC_Gateway_Payment_Actions', $action);
                parent::admin_options();
            }

            public function fields()
            {
                $ip = get_option('snapppay-web-server-ip');

                return apply_filters(
                    'WC_SnappPay_Config',
                    array(
                        'base_config' => array(
                            'title'       =>  'تنظیمات پایه ای',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'base_server_ip' => array(
                            'title'       =>  $ip,
                            'type'        => 'title',
                            'description' => 'آدرس IP سرور شما',
                            'css'         => 'color:#008efa;font-weight:bold;'
                        ),
                        'description' => array(
                            'title'       =>  'توضیحات درگاه',
                            'type'        => 'text',
                            'desc_tip'    => true,
                            'description' =>  'توضیحاتی که در طی عملیات پرداخت برای درگاه نمایش داده خواهد شد',
                            'default'     =>  'پرداخت اقساطی اسنپ پی'
                        ),
                        'enabled' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی',
                            'type'    => 'checkbox',
                            'label'   =>  'فعالسازی درگاه اسنپ پی',
                            'description' =>  'برای فعالسازی درگاه پرداخت اسنپ پی باید چک باکس را تیک بزنید',
                            'default' => 'yes',
                            'desc_tip'    => true,
                        ),
                        'mobile_enabled' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی',
                            'type'    => 'checkbox',
                            'label'   =>  'موبایل اجباری باشد',
                            'description' =>  'برای اجباری بودن موبایل چک باکس را تیک بزنید',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),

                        'postal_enabled' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی',
                            'type'    => 'checkbox',
                            'label'   =>  'کدپستی اجباری باشد',
                            'description' =>  'برای اجباری بودن کدپستی چک باکس را تیک بزنید',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),
                        'account_config' => array(
                            'title'       =>  'تنظیمات حساب اسنپ پی',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'default_gateway' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی اسنپ پی به عنوان درگاه پیش فرض',
                            'type'    => 'checkbox',
                            'description' =>  'در صورتی که تمایل دارید درگاه پرداخت اسنپ پی پیشفرض باشد باید چک باکس را تیک بزنید',
                            'default' => 'yes',
                            'desc_tip'    => true,
                        ),
                        'has_comission' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی نوع کمیسیون برای دسته بندی ها',
                            'type'    => 'checkbox',
                            'description' =>  'برای فعالسازی نوع کمیسیون ها باید چک باکس را تیک بزنید',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),
                        'has_pdp' => array(
                            'title'   =>  'فعالسازی/غیرفعالسازی ویجت اسنپ پی در صفحه محصول',
                            'type'    => 'checkbox',
                            'description' =>  'برای فعالسازی نمایش ویجت اسنپ پی در صفحه محصول تیک بزنید',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),
                        'dark_pdp' => array(
                            'title'   =>  'استفاده از تم تیره ویجت اسنپ پی در صفحه محصول',
                            'type'    => 'checkbox',
                            'description' =>  'با انتخاب تم تیره، نوشته‌ها با رنگ سفید نمایش داده می‌شود و در پس‌زمینهٔ روشن خوانا نیست.',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),
                        'direct_payment' => array(
                            'title'   =>  'انتقال مستقیم به درگاه',
                            'type'    => 'checkbox',
                            'default' => 'no',
                            'desc_tip'    => true,
                        ),
                        'base_url' => array(
                            'title'       =>  'آدرس درگاه',
                            'type'        => 'text',
                            'description' =>  'آدرس API درگاه اسنپ پی',
                            'default'     => 'https://api.snapppay.ir/',
                            'desc_tip'    => true,
                            'required'    => true,
                        ),
                        'client_id' => array(
                            'title'       =>  'شماره مشتری',
                            'type'        => 'text',
                            'default'     => '',
                            'required'    => true,
                            'desc_tip'    => true
                        ),
                        'client_secret' => array(
                            'title'       =>  'کد امنیتی مشتری',
                            'type'        => 'text',
                            'default'     => '',
                            'required'    => true,
                            'desc_tip'    => true
                        ),
                        'client_username' => array(
                            'title'       =>  'نام کاربری مشتری',
                            'type'        => 'text',
                            'default'     => '',
                            'required'    => true,
                            'desc_tip'    => true
                        ),
                        'client_password' => array(
                            'title'       =>  'رمزعبور مشتری',
                            'type'        => 'text',
                            'default'     => '',
                            'required'    => true,
                            'desc_tip'    => true
                        ),
                        'payment_config' => array(
                            'title'       =>  'تنظیمات عملیات پرداخت',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'success_message' => array(
                            'title'       =>  'پیام پرداخت موفق',
                            'type'        => 'textarea',
                            'description' =>  'متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {refrenceID} برای نمایش کد رهگیری ( کد مرجع تراکنش ) و از شرت کد استفاده نمایید .',
                            'default'     =>  'با تشکر از شما . سفارش شما با موفقیت پرداخت شد .',
                        ),
                        'failed_message' => array(
                            'title'       =>  'پیام پرداخت ناموفق',
                            'type'        => 'textarea',
                            'description' =>  'متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت اسنپ پی ارسال میگردد .',
                            'default'     =>  'پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .',
                        ),
                        'cancelled_message' => array(
                            'title'       =>  'پیام انصراف از پرداخت',
                            'type'        => 'textarea',
                            'description' =>  'متن پیامی که میخواهید بعد از انصراف کاربر از پرداخت نمایش دهید را وارد نمایید . این پیام بعد از بازگشت از بانک نمایش داده خواهد شد .',
                            'default'     =>  'پرداخت به دلیل انصراف شما ناتمام باقی ماند .',
                        ),
                        'shortcodes'        => array(
                            'transactionId' => 'شماره تراکنش',
                            'paymentToken' => 'توکن پرداخت',
                        )
                    )
                );
            }

            public function init_form_fields()
            {
                $fields = $this->fields();
                array_pop($fields);
                $this->form_fields = $fields;
            }

            protected function redirect($url)
            {
                wp_redirect($url);
                exit;
            }

            public function process_payment($order)
            {
                $order = $this->get_order($order);

                return array(
                    'result'   => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }

            public function snapppay_before_thankyou()
            {
                $order_id = isset($_GET['wc_order']) && !empty($_GET['wc_order']) ? $_GET['wc_order'] : false;

                if ($order_id) {
                    /** @var \WC_Order $order */
                    $order = wc_get_order($order_id);

                    if ($order) {
                        $order_status = $order->get_status();
                        $notice = $order->wc_snapppay_order_notice;
                        $notice =  $notice . ' <a href="' . get_permalink(wc_get_page_id('shop')) . '">بازگشت به فروشگاه</a> <a href="' . wc_get_cart_url() . '"> بازگشت به سبد خرید </a>';
                        $order_status = $order_status == 'completed' || $order_status == 'processing' ? 'success' : 'error';

                        wc_print_notice($notice, $order_status);
                    }
                }
            }

            public function process_payment_request($order_id)
            {
                global $woocommerce;

                $this->order_id = $order_id;

                /** @var WC_Order $order */
                $order = wc_get_order($order_id);

                $is_direct = snapppay_direct_payment();

                if (!$is_direct) {
                    $action = $this->author;
                    do_action('WC_Gateway_Payment_Actions', $action);

                    $form = '<form action="" method="POST" class="snapppay-checkout-form" id="snapppay-checkout-form">
						<input type="submit" name="snapppay_submit" class="button alt" id="snapppay-payment-button" value="' . 'پرداخت' . '"/>
						<a class="button cancel" href="' . wc_get_checkout_url() . '">' .  'بازگشت' . '</a>
					 </form><br/>';

                    $form = apply_filters('WC_SnappPay_Form', $form, $order_id, $woocommerce);

                    do_action('WC_SnappPay_Gateway_Before_Form', $order_id, $woocommerce);
                    echo $form;
                    do_action('WC_SnappPay_Gateway_After_Form', $order_id, $woocommerce);
                }

                if (isset($_POST["snapppay_submit"]) || $is_direct) {

                    $transactionId = time() . '-' . $order_id;

                    $order->update_meta_data('_transactionId', $transactionId);

                    //track_event('get_payment_token');
                    $response = $this->api->get_payment_token($order, $transactionId);

                    if (!$response['successful']) {
                        $notice = 'در هنگام اتصال به درگاه خطایی رخ داده است : ' . $response['errorData']['message'];
                        $order->update_meta_data('wc_snapppay_order_notice', $notice);
                        // wc_print_notice($notice, 'error');
                        $this->set_message('failed', $notice, true, $this->redirect_url);
                        $order->save();
                    } else {
                        $payment_token = $response['response']['paymentToken'];

                        $order->update_meta_data('_order_spp_token', $payment_token);

                        $order->update_meta_data('_paymentToken', $payment_token);

                        $order->add_order_note('انتقال به درگاه با توکن پرداخت:<br>' . $payment_token . '<br>شناسه تراکنش:<br>' . $transactionId, 1);

                        $order->save();

                        $this->current_order = $order;

                        $this->redirect($response['response']['paymentPageUrl']);
                    }
                }
            }

            public function process_payment_verify()
            {

                $order_id = isset($_GET['wc_order']) && !empty($_GET['wc_order']) ? $_GET['wc_order'] : $this->session('get', 'order_id');

                if (empty($order_id)) {
                    $this->set_message('failed', 'شماره سفارش وجود ندارد.', true, $this->redirect_url);
                }

                $status = '';
                if ($_POST['state'] !== 'OK') {
                    $this->set_message('failed', 'خطای درگاه پرداخت', true, $this->redirect_url);
                } else {
                    $status = 'completed';
                }

                $order = $this->get_order($order_id);

                if (!$this->needs_payment($order)) {
                    $this->set_message('failed', 'وضعیت تراکنش قبلا مشخص شده است.', true, $this->redirect_url, true);
                }

                $this->order_id = $order_id;

                $payment_token = get_order_payment_token($order);

                if (!$payment_token) {
                    $this->set_message('failed', 'خطای سفارش: توکن پرداخت نامعتبر است.', true, $this->redirect_url, true);
                    exit;
                }

                $response = $this->api->verify($payment_token);

                if (!$response['successful']) {
                    // wc_print_notice($response['errorData']['message'], 'error');

                    $this->set_message('failed', 'خطای درگاه: ' . $response['errorData']['message'], true, $this->redirect_url, true);
                    exit;
                } else {

                    if ($status == 'completed') {
                        $redirect = $this->get_return_url($order);

                        // Settle payment and store status in order meta for future use.
                        $settle_response = $this->api->settle($payment_token);
                        if ($settle_response['successful']) {
                            $order->payment_complete();
                            $this->empty_cart();
                            $order->update_meta_data('order_spp_status', 'SETTLE');
                        } else {
                            $order->update_meta_data('order_spp_status', 'VERIFY');
                        }

                        $order->save();

                        $shortcodes = $this->get_shortcodes_values();
                        $note       = array('تراکنش موفق بود.');
                        foreach ($this->fields_shortcodes() as $key => $value) {
                            $key    = trim($key, '\{\}');
                            $note[] = "$value :";
                            $note[] = "{$shortcodes[$key]}";
                        }
                        $notice = implode("<br>", $note);
                        // $order->add_order_note($notice, 1);
                        $this->set_message('success', $notice, true, $redirect);
                    } else {
                        $error = 'در حین پرداخت خطایی رخ داده است.';
                        // $order->add_order_note(sprintf('در هنگام بازگشت از درگاه %s خطای زیر رخ داده است.', $this->title) . "<br>{$error}", 1);
                        $error = sprintf('در هنگام بازگشت از درگاه %s خطای زیر رخ داده است.', $this->title) . "<br>{$error}";
                        $this->set_message('failed', $error, true, $this->redirect_url);
                    }
                }
            }

            protected function empty_cart()
            {
                if (function_exists('wc_empty_cart')) {
                    wc_empty_cart();
                } elseif (function_exists('WC') && !empty(WC()->cart) && method_exists(WC()->cart, 'empty_cart')) {
                    WC()->cart->empty_cart();
                } else {
                    global $woocommerce;
                    $woocommerce->cart->empty_cart();
                }
            }

            protected function order_id($order)
            {
                if (is_numeric($order)) {
                    $order_id = $order;
                } elseif (method_exists($order, 'get_id')) {
                    $order_id = $order->get_id();
                } elseif (!($order_id = absint(get_query_var('order-pay')))) {
                    $order_id = $order->id;
                }

                if (!empty($order_id)) {
                    $this->order_id = $order_id;
                }

                return $order_id;
            }

            protected function get_order($order = 0): \WC_Order
            {
                if (empty($order)) {
                    $order = $this->order_id;
                }

                if (empty($order)) {
                    $order = $_GET['wc_order'];
                }

                if (is_numeric($order)) {
                    $this->order_id = $order;

                    $order = wc_get_order($order);
                }

                return $order;
            }

            protected function get_order_props($prop, $default = '')
            {
                if (empty($this->order_id)) {
                    return '';
                }

                $order = $this->get_order();

                $method = 'get_' . $prop;

                if (method_exists($order, $method)) {
                    $prop = $order->$method();
                } elseif (!empty($order->{$prop})) {
                    $prop = $order->{$prop};
                } else {
                    $prop = '';
                }

                return !empty($prop) ? $prop : $default;
            }

            protected function needs_payment($order = 0)
            {
                if (empty($order) && empty($this->order_id)) {
                    return true;
                }

                $order = $this->get_order($order);

                if (method_exists($order, 'needs_payment')) {
                    return $order->needs_payment();
                }

                if (empty($this->order_id) && !empty($order)) {
                    $this->order_id = $this->order_id($order);
                }

                return !in_array($this->get_order_props('status'), array('completed', 'processing'));
            }


            protected function session($action, $name, $value = '')
            {
                global $woocommerce;

                $name = $this->id . '_' . $name;

                $wc_session = function_exists('WC') && !empty(WC()->session);

                if ($action == 'set') {
                    if ($wc_session && method_exists(WC()->session, 'set')) {
                        WC()->session->set($name, $value);
                    } else {
                        $woocommerce->session->{$name} = $value;
                    }
                } elseif ($action == 'get') {
                    if ($wc_session && method_exists(WC()->session, 'get')) {
                        $value = WC()->session->get($name);
                        unset(WC()->session->{$name});
                    } else {
                        $value = $woocommerce->session->{$name};
                        unset($woocommerce->session->{$name});
                    }

                    return $value;
                }

                return '';
            }

            protected function option($name)
            {
                $option = '';
                if (method_exists($this, 'get_option')) {
                    $option = $this->get_option($name);
                } elseif (!empty($this->settings[$name])) {
                    $option = $this->settings[$name];
                }

                if (in_array(strtolower($option), array('yes', 'on', 'true'))) {
                    $option = '1';
                }
                if (in_array(strtolower($option), array('no', 'off', 'false'))) {
                    $option = false;
                }

                return $option;
            }

            protected function fields_shortcodes($fields = array())
            {
                $fields = !empty($fields) ? $fields : $this->fields();

                return !empty($fields['shortcodes']) && is_array($fields['shortcodes']) ? $fields['shortcodes'] : array();
            }

            protected function get_shortcodes_values()
            {
                $shortcodes = array();
                $order = new WC_Order($this->order_id);

                if(!$order){
                    return $shortcodes;
                }

                foreach ($this->fields_shortcodes() as $key => $value) {
                    $key                = trim($key, '\{\}');
                    $shortcodes[$key] = $order->get_meta( '_' . $key, true);

                    if(!$shortcodes[$key]){

                        $shortcodes[$key] = get_post_meta($this->order_id, '_' . $key, true);
                    }    
                }

                return $shortcodes;
            }

            protected function set_message($status, $error = '', $notice = true, $redirect = false, $failed_note = true)
            {
                $redirect_status = $status;

                if ($redirect_status == 'failed') {
                    $redirect_status = 'error';
                }

                if (!in_array($status, array('success', 'cancelled', 'failed'))) {
                    $status = 'failed';
                }

                $order = $this->get_order();

                if (!empty($error) && $failed_note && !empty($order)) {
                    $order->add_order_note('اسنپ پی: ' . $error, 1);
                }

                $shortcodes = array_merge($this->get_shortcodes_values(), array('{fault}' => $error));

                $message = $this->option($status . '_massage');
                $find    = array_map(function ($value) {
                    return '{' . trim($value, '\{\}') . '}';
                }, array_keys($shortcodes));
                $message = str_ireplace($find, array_values($shortcodes), $message);
                $message = wpautop(wptexturize(trim($message)));

                $message = $message ?: $error;
                if ($notice) {
                    // wc_print_notice($message, $status == 'completed' ? 'success' : 'error');
                    wc_add_notice($message, $status == 'completed' ? 'success' : 'error');
                    // update_post_meta($this->order_id, '_wc_snapppay_order_notice', $message);
                    $order->update_meta_data('wc_snapppay_order_notice', $message);
                }

                if ($redirect) {
                    wp_redirect(add_query_arg(['wc_status' => $redirect_status, 'wc_order' => $order->get_id()], $redirect));
                    exit;
                } else {
                    return $message;
                }
            }
        }
    }
}
add_action('plugins_loaded', 'load_snapp_pay_gateway', 0);
