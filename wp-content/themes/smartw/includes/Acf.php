<?php

namespace TanilChoob\Theme;
use TanilChoob\Theme\Helper;

class ACF
{
    public function __construct()
    {
        //        // use default language for acf options page
//        add_filter('acf/settings/current_language', function () {
//            return false;
//        });

        //        add_action( 'admin_init', [$this, 'force_redirect_to_the__all__version_of_global_options']);

        add_filter('acf/load_field/type=select', [$this, 'maybe_load_pattern_choices']);
        add_filter('acf/load_field/key=field_69fa23cdd6be1', [$this, 'display_user_credit_message']);
    }

    /**
     * Display user credit in an ACF Message field, with transient caching.
     *
     * @param array $field ACF field array.
     * @return array
     */
    public function display_user_credit_message($field)
    {
        // Default loading text
        $field['message'] = '<p>در حال بارگذاری اعتبار...</p>';

        $api_key = Helper::get_options_field('sms_api_key');
        $sms_base_url = 'https://edge.ippanel.com/v1';

        if (empty($api_key)) {
            $field['message'] = '<p style="color:red;">کلید API تنظیم نشده است.</p>';
            return $field;
        }

        // 1. Check for a cached version
        $cache_key = 'ippanel_credit_display';
        $cached_html = get_transient($cache_key);

        if (false !== $cached_html) {
            $field['message'] = $cached_html;
            return $field;
        }

        // 2. No cache – call the API
        $url = rtrim($sms_base_url, '/') . '/api/payment/credit/mine';

        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $api_key,
            ],
        ]);

        if (is_wp_error($response)) {
            $field['message'] = '<p style="color:red;">خطا در دریافت اعتبار: ' . esc_html($response->get_error_message()) . '</p>';
            return $field;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if (200 !== $http_code) {
            $field['message'] = '<p style="color:red;">خطای سرور (کد ' . intval($http_code) . ')</p>';
            return $field;
        }

        $data = json_decode($body, true);

        // 3. Extract the credit from the nested data
        $credit = isset($data['data']['credit'])
            ? (float) $data['data']['credit']
            : null;

        if (null === $credit) {
            $field['message'] = '<p style="color:red;">اعتبار دریافت نشد.</p>';
            return $field;
        }

        // Format the credit nicely (you can adjust decimals as needed)
        $formatted_credit = number_format($credit, 0, '.', ','); // e.g., 6,084,722,932.71

        $field['message'] = '<p>اعتبار فعلی شما: <strong>' . $formatted_credit . ' ریال</strong></p>';

        // 4. Store in transient for 15 minutes
        set_transient($cache_key, $field['message'], 15 * MINUTE_IN_SECONDS);

        return $field;
    }
    public function maybe_load_pattern_choices($field)
    {
        // Only run on your specific fields
        $target_fields = ['otp_login_pattern', 'otp_signup_pattern']; // field names
        if (!in_array($field['name'], $target_fields)) {
            return $field;
        }

        // Now call your main logic (or just put it here)
        return $this->otp_login_patterns__choices($field);
    }

    public function otp_login_patterns__choices($field)
    {
        $field['choices'] = [];
        $api_key = Helper::get_options_field('sms_api_key');
        $sms_base_url = 'https://edge.ippanel.com/v1';

        if (empty($api_key)) {
            error_log("Please Set API KEY");
            // Return field with an empty choices array (or an info place-holder)
            $field['choices'][''] = 'ابتدا کلید API را تنظیم کنید';
            return $field;
        } else {
            $field['choices'][''] = 'پترن را از لیست انتخاب کنید';
        }

        $url = rtrim($sms_base_url, '/') . '/api/patterns?page=1&per_page=100';

        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $api_key,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('[TanilChoob OTP] SMS error: ' . $response->get_error_message());
            $field['choices'][''] = 'خطا در دریافت الگوها';
            return $field;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if (200 !== $http_code) {
            error_log('[TanilChoob OTP] API returned status: ' . $http_code);
            $field['choices'][''] = 'خطا از سرور (کد ' . $http_code . ')';
            return $field;
        }

        $data = json_decode($body, true);

        if (empty($data['data'])) {
            $field['choices'][''] = 'هیچ الگویی یافت نشد';
            return $field;
        }

        // Build the choices
        foreach ($data['data'] as $pattern) {
            // Use pattern_code as the value; pattern_message as the label
            $value = $pattern['pattern_code'];
            $label = $pattern['pattern_message'];

            // Optionally show active status in the label
            if ('active' !== $pattern['pattern_status']) {
                $label .= ' (غیرفعال)';
            }

            // Or, if you only want active patterns:
            // if ( 'active' === $pattern['pattern_status'] ) {
            //     $field['choices'][ $value ] = $label;
            // }

            $field['choices'][$value] = $label;
        }

        return $field;
    }
    public function force_redirect_to_the__all__version_of_global_options()
    {
        // correct page
        global $pagenow;
        if ($pagenow === "admin.php" && isset($_GET['page']) && $_GET['page'] === "acf-options-general-options") { // global-options is the menu_slug you defined in acf_add_options_page
            // lang not 'all'?
            if (ICL_LANGUAGE_CODE !== 'all') {
                // manipulate query (set lang to "all")
                $query = $_GET;
                $query['lang'] = 'all';
                $query_result = http_build_query($query);
                // redirect and die
                wp_redirect(get_admin_url() . 'admin.php?' . $query_result);
                die();
            }
        }
    }
}
