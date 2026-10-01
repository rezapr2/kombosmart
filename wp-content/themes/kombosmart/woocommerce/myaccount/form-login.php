<?php
/**
 * Login Form — OTP tabbed design (Login / Register).
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

$login_page = get_field('login_page', 'option');
$header_logo = isset($login_page['logo']['url']) ? $login_page['logo']['url'] : null;
$description = isset($login_page['description']) ? $login_page['description'] : '';
?>
<style>
	/* Hide default WooCommerce login/register forms */
	nav.breadcrumb {
		display: none;
	}
</style>
<div class="tc-auth-form tab-contents">
	<div class="logo">
		<?php if ( $header_logo ) : ?>
			<img src="<?php echo esc_url( $header_logo ); ?>" alt="<?php bloginfo('name'); ?>" />
		<?php else : ?>
			<span class="tc-auth-form__brand flex items-center justify-center gap-10">
				<?php get_template_part('template-parts/components/logo-mark'); ?>
				<span><?php bloginfo('name'); ?></span>
			</span>
		<?php endif; ?>
	</div>
	<div class="tc-auth-tabs">
		<div class="tab-item <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="tab-login">ورود</div>
		<?php if ( $show_register ) : ?>
		<div class="tab-item <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="tab-register">ثبت نام</div>
		<?php endif; ?>
	</div>

	<!-- ==================== Login ==================== -->
	<div class="tab-content-item <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="tab-login-content">
		<?php do_action( 'woocommerce_login_form_start' ); ?>
		<div class="otp-form" data-otp-type="login">

			<!-- Step 1: enter mobile -->
			<div class="otp-step" data-step="1">
				<div class="form-field">
					<label>شماره موبایل</label>
					<input
						type="tel"
						class="otp-mobile-input"
						placeholder="۰۹۱۲۳۴۵۶۷۸۹"
						dir="ltr"
						autocomplete="tel"
						maxlength="11"
					/>
				</div>
				<div class="auth-terms">
					<?php echo($description); ?>
				</div>
				<button type="button" class="auth-submit-btn otp-send-btn">ورود</button>
			</div>

			<!-- Step 2: enter OTP code -->
			<div class="otp-step otp-step--hidden" data-step="2">
				<p class="otp-info-text">کد ۶ رقمی به شماره <strong class="otp-mobile-display"></strong> ارسال شد.</p>
				<div class="form-field">
					<label>کد تأیید</label>
					<input
						type="tel"
						class="otp-code-input"
						placeholder="------"
						maxlength="6"
						dir="ltr"
						autocomplete="one-time-code"
					/>
				</div>
				<button type="button" class="auth-submit-btn otp-verify-btn">ورود</button>
				<div class="otp-resend-area">
					<span class="otp-timer">ارسال مجدد تا <span class="otp-countdown">02:00</span></span>
					<button type="button" class="otp-resend-btn otp-resend-btn--hidden">ارسال مجدد کد</button>
				</div>
				<button type="button" class="otp-back-btn">ویرایش شماره موبایل</button>
			</div>

			<div class="otp-messages"></div>
		</div>
		<?php do_action( 'woocommerce_login_form_end' ); ?>
	</div>

	<?php if ( $show_register ) : ?>
	<!-- ==================== Register ==================== -->
	<div class="tab-content-item <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="tab-register-content">
		<?php do_action( 'woocommerce_register_form_start' ); ?>
		<div class="otp-form" data-otp-type="register">

			<!-- Step 1: enter mobile -->
			<div class="otp-step" data-step="1">
				<div class="form-field">
					<label>شماره موبایل</label>
					<input
						type="tel"
						class="otp-mobile-input"
						placeholder="۰۹۱۲۳۴۵۶۷۸۹"
						dir="ltr"
						autocomplete="tel"
						maxlength="11"
					/>
				</div>
				<div class="auth-terms">
					<?php echo ($description); ?>
				</div>
				<button type="button" class="auth-submit-btn otp-send-btn">ثبت نام</button>
			</div>

			<!-- Step 2: enter OTP code -->
			<div class="otp-step otp-step--hidden" data-step="2">
				<p class="otp-info-text">کد ۶ رقمی به شماره <strong class="otp-mobile-display"></strong> ارسال شد.</p>
				<div class="form-field">
					<label>کد تأیید</label>
					<input
						type="tel"
						class="otp-code-input"
						placeholder="------"
						maxlength="6"
						dir="ltr"
						autocomplete="one-time-code"
					/>
				</div>
				<button type="button" class="auth-submit-btn otp-verify-btn">ثبت نام</button>
				<div class="otp-resend-area">
					<span class="otp-timer">ارسال مجدد تا <span class="otp-countdown">02:00</span></span>
					<button type="button" class="otp-resend-btn otp-resend-btn--hidden">ارسال مجدد کد</button>
				</div>
				<button type="button" class="otp-back-btn">ویرایش شماره موبایل</button>
			</div>

			<div class="otp-messages"></div>
		</div>
		<?php do_action( 'woocommerce_register_form_end' ); ?>
	</div>
	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
