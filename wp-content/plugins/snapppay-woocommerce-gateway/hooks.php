<?php

// plugin activation
register_activation_hook(SNAPP_PAY_PLUGIN_FILE, 'snapppay_wc_gateway_activate');
function snapppay_wc_gateway_activate()
{

    $old_settings = get_option('woocommerce_snapppay_settings', null);

    if (!is_null($old_settings)) {
        update_option('woocommerce_WC_Gateway_SnappPay_settings', $old_settings);
    }

    snapppay_get_ip();
    
    track_event('activate');

    @unlink(ABSPATH . 'snapppay.log');
}

// plugin deactivation
register_deactivation_hook(SNAPP_PAY_PLUGIN_FILE, 'snapppay_wc_gateway_deactivate');
function snapppay_wc_gateway_deactivate()
{
    delete_option('snapppay-web-server-ip');
    delete_option('snapppay-web-server-ip-show');
    @unlink(ABSPATH . 'snapppay.log');

    track_event('deactivate');
}

// plugin uninstallation
register_uninstall_hook(SNAPP_PAY_PLUGIN_FILE, 'snapppay_wc_gateway_uninstall');
function snapppay_wc_gateway_uninstall()
{
    @unlink(ABSPATH . 'snapppay.log');
    delete_option('snapppay-web-server-ip');
    delete_option('snapppay-web-server-ip-show');
    // delete_option( 'woocommerce_WC_Gateway_SnappPay_settings' );
}

add_action('save_post_shop_order',  'snapppay_save_post_shop_order', 21, 3);
function snapppay_save_post_shop_order($post_id, $post, $update)
{

    if (did_action('woocommerce_order_status_changed')) {
        return;
    }

    if (did_action('snapppay_save_post_shop_order')) {
        return;
    }


    if (!$update) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (empty($_POST['_payment_method'])) {
        return;
    }

    if (false === in_array($_POST['_payment_method'], array('WC_Gateway_SnappPay', 'snapppay'))) {
        return;
    }

    $backward_order_status = in_array($_POST['original_post_status'], array('wc-on-hold', 'wc-pending'));
    $order_status = in_array($_POST['original_order_status'], array('on-hold', 'pending'));

    if (false === $backward_order_status && false === $order_status) {
        return;
    }

    if (false === in_array($_POST['order_status'], array('wc-completed', 'wc-processing'))) {
        return;
    }


    /** @var WC_Order $order */
    $order = wc_get_order($post_id);

    $order_payment_token = get_order_payment_token($order);

    if (!$order_payment_token) {
        return;
    }

    $response = (new SnappPayAPI)->update($order, $order_payment_token);

    handle_response($order, $response, 'update');

    do_action('snapppay_save_post_shop_order');
}

add_action('woocommerce_order_status_changed', 'snapppay_handle_status_change_for_hpos_and_legacy', 10, 3);

//compatible with hpos
function snapppay_handle_status_change_for_hpos_and_legacy($order_id, $old_status_slug, $new_status_slug)
{
   
    if ( ! is_admin() ) {
        return;
    }

    static $processed = [];
    if (isset($processed[$order_id])) return;
    $processed[$order_id] = true;

    $order = wc_get_order($order_id);
    if (!$order) return;

    if (!in_array($order->get_payment_method(), ['WC_Gateway_SnappPay', 'snapppay'], true)) return;

    $old_status = ( strpos( $old_status_slug, 'wc-' ) === 0 ) ? $old_status_slug : 'wc-' . $old_status_slug;
    $new_status = ( strpos( $new_status_slug, 'wc-' ) === 0 ) ? $new_status_slug : 'wc-' . $new_status_slug;

    if (!in_array($old_status, ['wc-pending', 'wc-on-hold'])) return;
    if (!in_array($new_status, ['wc-processing', 'wc-completed'])) return;

    $token = get_order_payment_token($order);
    if (!$token) return;

    $response = (new SnappPayAPI())->update($order, $token);
    handle_response($order, $response, 'update');
}

add_filter('woocommerce_available_payment_gateways',  'show_snapppay_payment_gateway_based_on_cart_amount', PHP_INT_MAX, 1);

function show_snapppay_payment_gateway_based_on_cart_amount($available_gateways)
{

    if (!is_checkout()) {
        return $available_gateways;
    }

    if (did_action('woocommerce_checkout_before_customer_details')) {
        return $available_gateways;
    }

    if (isset($_POST["_wp_http_referer"])) {
        return $available_gateways;
    }

    $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');

    if (!$snapppay_settings) {
        unset($available_gateways['WC_Gateway_SnappPay']);
        return $available_gateways;
    }

    if ($snapppay_settings['enabled'] != 'yes') {
        unset($available_gateways['WC_Gateway_SnappPay']);
        return $available_gateways;
    }

    WC()->cart->calculate_fees();
    $amount = WC()->cart->get_total('edit');

    $order = null;
    if (isset($_GET['key'])) {
        $order = new WC_Order(wc_get_order_id_by_order_key($_GET['key']));
    }

    if (!$amount) {
        if ($order) {
            $amount = $order->get_total();
        }
    }

    $currency = get_woocommerce_currency();

    $final_amount = get_amount_by_currency($amount, $currency);

    $is_eligible = (new SnappPayAPI())->is_merchant_eligible($final_amount);

    if (is_wp_error($is_eligible)) {
        // print eligibilty error.
        wc_print_notice($is_eligible->get_error_message(), 'error');
        unset($available_gateways['WC_Gateway_SnappPay']);
        return $available_gateways;
    }

    // check eligible
    if (isset($is_eligible['response']) && $is_eligible['response']['eligible']) {
        $available_gateways['WC_Gateway_SnappPay']->title = $is_eligible['response']['title_message'];
        $available_gateways['WC_Gateway_SnappPay']->description = $is_eligible['response']['description'];
        $available_gateways['WC_Gateway_SnappPay']->chosen = $snapppay_settings['default_gateway'] == 'yes' ? true : false;
    } else {
        unset($available_gateways['WC_Gateway_SnappPay']);
    }

    return $available_gateways;
}

add_filter('woocommerce_checkout_fields', 'snapppay_checkout_fields', 99);
function snapppay_checkout_fields($fields)
{
    $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');

    if ($snapppay_settings['postal_enabled'] != 'yes') {
        unset($fields['billing']['billing_postcode']['validate']);
        unset($fields['billing']['billing_postcode']['required']);
        unset($fields['shipping']['shipping_postcode']['validate']);
        unset($fields['billing']['shipping_postcode']['required']);
    }

    if ($snapppay_settings['mobile_enabled'] != 'yes') {
        unset($fields['billing']['billing_phone']['validate']);
        unset($fields['billing']['billing_phone']['required']);
        unset($fields['shipping']['shipping_phone']['validate']);
        unset($fields['shipping']['shipping_phone']['required']);
    }

    return $fields;
}

add_filter('plugin_action_links_' . SNAPP_PAY_PLUGIN,  'snapppay_plugin_action_links');
function snapppay_plugin_action_links($links)
{
    $params = array(
        'page'    => 'wc-settings',
        'tab'     => 'checkout',
        'section' => 'WC_Gateway_SnappPay',
    );

    $admin_url = add_query_arg($params, 'admin.php');
    $plugin_links = array(
        "<a href='$admin_url'>Settings</a>",
        "<a href='https://snapppay.ir/'>Support</a>",
    );

    return array_merge($plugin_links, $links);
}


add_action('woocommerce_order_status_changed',  'snapppay_order_status_changed', PHP_INT_MAX, 3);
function snapppay_order_status_changed($order_id, $from_status, $next_status)
{

    if (false === in_array($next_status, array('cancelled', 'refunded', 'processing'))) {
        return;
    }

    $snapppay_settings = get_option('woocommerce_WC_Gateway_SnappPay_settings');

    if (!$snapppay_settings) {
        return;
    }

    if ($snapppay_settings['enabled'] != 'yes') {
        return;
    }

    if ($next_status == 'cancelled' || $next_status == 'refunded') {
        return cancel_snapppay_order($order_id, $from_status);
    }

    // if ($next_status == 'processing') {
    //     verify_snapppay_order($order_id);
    // }
}

add_action('save_post_shop_order',  'snapppay_save_post_shop_order', 21, 3);


if (snapppay_has_comission()) {
    add_action('product_cat_add_form_fields', 'add_commission_type_new_field', 10, 2);
    add_action('product_cat_edit_form_fields', 'add_commission_type_edit_field', 10, 2);
    add_action('edited_product_cat', 'save_taxonomy_commission_type', 10, 2);
    add_action('create_product_cat', 'save_taxonomy_commission_type', 10, 2);
}

function add_commission_type_new_field()
{
    // this will add the custom meta field to the add new term page
?>
    <div class="form-field">
        <label for="snp_commission_type">نوع کمیسیون</label>
        <input type="text" name="snp_commission_type" id="snp_commission_type" value="">
        <p class="description">نوع کمیسیون مشخص شده برای این دسته بندی را وارد کنید</p>
    </div>
<?php
}

function add_commission_type_edit_field($term)
{

    // put the term ID into a variable
    $t_id = $term->term_id;

    // retrieve the existing value(s) for this meta field. This returns an array
    $snp_commission_type = get_term_meta($t_id, 'snp_commission_type', true); ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="snp_commission_type">نوع کمیسیون</label></th>
        <td>
            <input type="text" name="snp_commission_type" id="snp_commission_type" value="<?php echo esc_attr($snp_commission_type) ? esc_attr($snp_commission_type) : ''; ?>">
            <p class="description">نوع کمیسیون مشخص شده برای این دسته بندی را وارد کنید</p>
        </td>
    </tr>
    <?php
}

function save_taxonomy_commission_type($term_id)
{
    update_term_meta($term_id, 'snp_commission_type', wc_clean(wp_unslash($_POST['snp_commission_type'])));
}

add_action('http_api_curl', 'snapppay_http_api_curl', PHP_INT_MAX, 1);
function snapppay_http_api_curl($handle)
{
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
}


add_action('admin_init', 'snapppay_check_if_woocommerce_installed');
function snapppay_check_if_woocommerce_installed()
{
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('woocommerce/woocommerce.php')) {
        add_action('admin_notices', 'snapppay_woo_active_notice');

        // Deactivate this plugin
        deactivate_plugins(SNAPP_PAY_PLUGIN);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

add_action('admin_notices', 'snapppay_admin_notice_server_ip_notice');
function snapppay_admin_notice_server_ip_notice()
{
    $show_ip = get_option('snapppay-web-server-ip-show');
    $ip = get_option('snapppay-web-server-ip');
    if ($ip && $show_ip === 'yes') {
        delete_option('snapppay-web-server-ip-show');
    ?>
        <div class="updated notice is-dismissible">
            <p style="display: flex; align-items:center;">
                <img src="https://snapppay.ir/wp-content/uploads/2021/09/logo-default-snapp-pay-1.png" alt="" style="height:30px;margin-left: 10px;"> افزونه اسنپ پی با موفقیت نصب شد. آدرس IP شما <strong style="color:#008efa;font-weight: bolder;">&nbsp;<?php echo $ip; ?> </strong>.
            </p>
        </div>
<?php
    }
}

add_filter('https_ssl_verify', '__return_false');

if (snapppay_has_pdp()) {
    add_action('woocommerce_single_variation', 'snapppay_variation_product_page_widget_price', 31);
    add_action('woocommerce_single_product_summary', 'snapppay_product_page_widget_price', 31);
}

function snapppay_variation_product_page_widget_price()
{
    global $product;

    if ($product->is_type('variable') && !$product->is_in_stock()) {
        return;
    }

    $price_tmp = $product->get_price();
    if (!validate_pdp_amount($price_tmp)) {
        return;
    }

    $snapp_pay_price = $price_tmp / 4;
    $snapp_pay_price = wc_price($snapp_pay_price);

    $logo = get_pdp_logo();

    echo "
        <div class='snapppay-widget'>
        <img src='$logo' alt='snapppay' class='snapppay-widget-image' />
            <div class='snapppay-widget-content'>
                <div class='snapppay-widget-title'>هر قسط با اسنپ‌پی: $snapp_pay_price</div>
                <div class='snapppay-widget-description'>۴ قسط ماهانه. بدون سود، چک و ضامن.</div>
            </div>
        </div>
    ";

    inject_pdp_styles();
}

function snapppay_product_page_widget_price()
{
    global $product;

    $price_tmp = $product->get_price();

    if (!validate_pdp_amount($price_tmp)) {
        return;
    }

    $snapp_pay_price_simple = $price_tmp / 4;
    $snapp_pay_price_simple = wc_price($snapp_pay_price_simple);

    $logo = get_pdp_logo();

    // Check if product is a simple product and in stock
    if ($product->is_type('simple') && $product->is_in_stock()) {

        echo "
            <div class='snapppay-widget'>
            <img src='$logo' alt='snapppay' class='snapppay-widget-image' />
                <div class='snapppay-widget-content'>
                <div class='snapppay-widget-title'>هر قسط با اسنپ‌پی: $snapp_pay_price_simple</div>
                    <div class='snapppay-widget-description'>۴ قسط ماهانه. بدون سود، چک و ضامن.</div>
                </div>
            </div>
        ";

        inject_pdp_styles();
    }
}

function validate_pdp_amount($amount)
{
    $amount = get_amount_by_currency($amount);
    $min_amount = get_amount_by_currency(50000);
    $max_amount = get_amount_by_currency(100000000);
    return $amount >= $min_amount && $amount <= $max_amount;
}

function get_pdp_logo()
{
    if (snapppay_dark_pdp()) {
        $logo = apply_filters('WC_SnappPay_Logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/images/logo.png');
    } else {
        $logo = apply_filters('WC_SnappPay_Logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/images/logo-bg.png');
    }
    return $logo;
}

function inject_pdp_styles()
{
    if (snapppay_dark_pdp()) {
        $widget_content_color = "#ffffff";
    } else {
        $widget_content_color = "#1a1c23";
    }


    echo "
    <style>
    @font-face {
        font-family: IRANSansMobile-fa;
        font-style: normal;
        font-weight: 300;
        src: url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Light.eot');
        src: url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Light.eot?#iefix')
            format('embedded-opentype'),
            /* IE6-8 */
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Light.woff2')
            format('woff2'),
            /* FF39+,Chrome36+, Opera24+*/
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Light.woff')
            format('woff'),
            /* FF3.6+, IE9, Chrome6+, Saf5.1+*/
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Light.ttf')
            format('truetype');
        font-display: fallback;
        }

        /* 700 */
        @font-face {
        font-family: IRANSansMobile-fa;
        font-style: normal;
        font-weight: bold;
        src: url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Bold.eot');
        src: url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Bold.eot?#iefix')
            format('embedded-opentype'),
            /* IE6-8 */
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Bold.woff2')
            format('woff2'),
            /* FF39+,Chrome36+, Opera24+*/
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Bold.woff')
            format('woff'),
            /* FF3.6+, IE9, Chrome6+, Saf5.1+*/
            url('https://web-cdn.snapp.ir/fintech/fonts/iran-sans-fa/IRANSansWeb_Bold.ttf')
            format('truetype');
        font-display: fallback;
        }
        .snapppay-widget {
            border-radius: 16px;
            border: 0.5px solid var(--Gray-Ash, #ebecf2);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        .snapppay-widget-image {
            width: 40px;
            border-radius:15px;
        }
        .snapppay-widget-content {
            color: $widget_content_color;
        }
        .snapppay-widget-title {
            font-family: IRANSansMobile-fa;
            font-size: 14px;
            font-weight: bold;
            line-height: 180%;
            margin-bottom: 2px;
        }
        .snapppay-widget-description {
            font-family: IRANSansMobile-fa;
            font-weight: 300;
            font-size: 12px;
            line-height: 180%;
            letter-spacing: -0.36px;
        }
    </style>

    ";
}
