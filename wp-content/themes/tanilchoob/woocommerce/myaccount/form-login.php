<?php
/**
 * Login Form — Custom tabbed design (Login / Register).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_register = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$active_tab    = ( isset( $_GET['register'] ) || ( isset( $_POST['register'] ) && wc_notice_count( 'error' ) > 0 ) ) ? 'register' : 'login';

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="tc-auth-form tab-contents">

	<div class="tc-auth-tabs">
		<div class="tab-item <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="tab-login">ورود</div>
		<?php if ( $show_register ) : ?>
		<div class="tab-item <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="tab-register">ثبت نام</div>
		<?php endif; ?>
	</div>

	<!-- ==================== Login ==================== -->
	<div class="tab-content-item <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="tab-login-content">
		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<div class="form-field">
				<label for="username">شماره موبایل یا ایمیل</label>
				<input
					type="text"
					name="username"
					id="username"
					autocomplete="username"
					placeholder="۰۹۱۲۳۴۵۶۷۸۹"
					value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
					required
					aria-required="true"
				/>
			</div>

			<div class="form-field">
				<label for="password">رمز عبور</label>
				<input
					type="password"
					name="password"
					id="password"
					autocomplete="current-password"
					placeholder="••••••••"
					required
					aria-required="true"
				/>
			</div>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="auth-terms">
				ورود | ثبت نام شما به معنای پذیرش
				<?php
				$terms_page_id = wc_get_page_id( 'terms' );
				$terms_url     = $terms_page_id > 0 ? get_permalink( $terms_page_id ) : home_url( '/purchasing-rules/' );
				$privacy_url   = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : home_url( '/privacy-policy/' );
				?>
				<a href="<?php echo esc_url( $terms_url ); ?>">قوانین و مقررات</a>
				و
				<a href="<?php echo esc_url( $privacy_url ); ?>">حریم خصوصی کاربران</a>
				تانیل چوب است.
			</p>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<input type="hidden" name="rememberme" value="forever" />

			<button type="submit" class="auth-submit-btn" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
				ورود
			</button>

			<p class="auth-lost-password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">رمز عبور را فراموش کرده‌اید؟</a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
	</div>

	<?php if ( $show_register ) : ?>
	<!-- ==================== Register ==================== -->
	<div class="tab-content-item <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="tab-register-content">
		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
			<div class="form-field">
				<label for="reg_username">نام کاربری</label>
				<input
					type="text"
					name="username"
					id="reg_username"
					autocomplete="username"
					value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
					required
					aria-required="true"
				/>
			</div>
			<?php endif; ?>

			<div class="form-field">
				<label for="reg_email">شماره موبایل</label>
				<input
					type="email"
					name="email"
					id="reg_email"
					autocomplete="email"
					placeholder="۰۹۱۲۳۴۵۶۷۸۹"
					value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
					required
					aria-required="true"
				/>
			</div>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
			<div class="form-field">
				<label for="reg_password">رمز عبور</label>
				<input
					type="password"
					name="password"
					id="reg_password"
					autocomplete="new-password"
					placeholder="••••••••"
					required
					aria-required="true"
				/>
			</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="auth-terms">
				ورود | ثبت نام شما به معنای پذیرش
				<a href="<?php echo esc_url( $terms_url ); ?>">قوانین و مقررات</a>
				و
				<a href="<?php echo esc_url( $privacy_url ); ?>">حریم خصوصی کاربران</a>
				تانیل چوب است.
			</p>

			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

			<button type="submit" class="auth-submit-btn" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
				ثبت نام
			</button>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>
	</div>
	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
