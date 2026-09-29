<?php

namespace TanilChoob\Theme\OptionPage;

use TanilChoob\Theme\Abstracts\OptionPage;
use TanilChoob\Theme\Helper;

class GeneralOption extends OptionPage {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_head', [ $this, 'add_google_analytics' ] );
		add_action( 'acf/init', [ $this, 'google_map_api_key_init' ] );
	}

	public function register() {
		acf_add_options_page( [
			'position'   => '4',
			'page_title' => 'تنظیمات کلی',
			'menu_title' => 'تنظیمات کلی',
			'menu_slug'  => 'general-options',
			'redirect'   => true,
		] );
	}

	public function add_google_analytics() {
		$google_analytics_api_key = Helper::get_options_field( 'google_analytics_measurement_id' );

		if ( $google_analytics_api_key ) {
			?>
            <script async
                    src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_analytics_api_key; ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());
                gtag('config', '<?php echo $google_analytics_api_key; ?>');
            </script>
		<?php }
	}

	// For ACF Map
	public function google_map_api_key_init() {
		$map_api_key = Helper::get_options_field( 'google_map_api_key' );
		if ( $map_api_key ) {
			acf_update_setting( 'google_api_key', $map_api_key );
		}
	}
}

new GeneralOption();