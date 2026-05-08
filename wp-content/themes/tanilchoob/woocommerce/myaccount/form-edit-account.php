<?php
/**
 * Edit account form — custom layout
 *
 * @package TanilChoob
 */

defined( 'ABSPATH' ) || exit;

$user        = wp_get_current_user();
$first_name  = $user->first_name;
$last_name   = $user->last_name;
$email       = $user->user_email;
$phone       = get_user_meta( $user->ID, 'billing_phone', true );
$national_id = get_user_meta( $user->ID, 'billing_national_id', true );
$newsletter  = get_user_meta( $user->ID, 'tc_newsletter', true );

do_action( 'woocommerce_before_edit_account_form' ); ?>

<div class="tc-edit-account" dir="rtl">

	<!-- ── View mode ─────────────────────────────────────────── -->
	<div class="tc-account-info" id="tc-account-info-view">
		<div class="tc-account-info__grid">

			<div class="tc-account-info__field">
				<span class="tc-account-info__label">نام و نام خانوادگی</span>
				<strong class="tc-account-info__value"><?php echo esc_html( trim( $first_name . ' ' . $last_name ) ?: '—' ); ?></strong>
				<button type="button" class="tc-account-info__edit-icon tc-open-edit-account" aria-label="ویرایش">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6L5.05 12.29c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.17.72 1.97 1.88 1.77l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21-.14-5.3C16.37 1.37 14.7 2.1 13.26 3.6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.89 5.05c.43 2.76 2.67 4.87 5.45 5.15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 22h18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<div class="tc-account-info__field">
				<span class="tc-account-info__label">پست الکترونیکی</span>
				<strong class="tc-account-info__value"><?php echo esc_html( $email ?: '—' ); ?></strong>
				<button type="button" class="tc-account-info__edit-icon tc-open-edit-account" aria-label="ویرایش">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6L5.05 12.29c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.17.72 1.97 1.88 1.77l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21-.14-5.3C16.37 1.37 14.7 2.1 13.26 3.6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.89 5.05c.43 2.76 2.67 4.87 5.45 5.15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 22h18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<div class="tc-account-info__field">
				<span class="tc-account-info__label">کد ملی</span>
				<strong class="tc-account-info__value"><?php echo esc_html( $national_id ?: '—' ); ?></strong>
				<button type="button" class="tc-account-info__edit-icon tc-open-edit-account" aria-label="ویرایش">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6L5.05 12.29c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.17.72 1.97 1.88 1.77l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21-.14-5.3C16.37 1.37 14.7 2.1 13.26 3.6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.89 5.05c.43 2.76 2.67 4.87 5.45 5.15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 22h18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<div class="tc-account-info__field">
				<span class="tc-account-info__label">شماره موبایل</span>
				<strong class="tc-account-info__value" dir="ltr"><?php echo esc_html( $phone ?: '—' ); ?></strong>
				<button type="button" class="tc-account-info__edit-icon tc-open-edit-account" aria-label="ویرایش">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6L5.05 12.29c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.17.72 1.97 1.88 1.77l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21-.14-5.3C16.37 1.37 14.7 2.1 13.26 3.6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.89 5.05c.43 2.76 2.67 4.87 5.45 5.15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 22h18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<div class="tc-account-info__field">
				<span class="tc-account-info__label">دریافت خبر نامه</span>
				<strong class="tc-account-info__value"><?php echo $newsletter ? 'بله' : 'خیر'; ?></strong>
				<button type="button" class="tc-account-info__edit-icon tc-open-edit-account" aria-label="ویرایش">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.26 3.6L5.05 12.29c-.31.33-.61.98-.67 1.43l-.37 3.24c-.13 1.17.72 1.97 1.88 1.77l3.22-.55c.45-.08 1.08-.41 1.39-.75l8.21-8.69c1.42-1.5 2.06-3.21-.14-5.3C16.37 1.37 14.7 2.1 13.26 3.6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.89 5.05c.43 2.76 2.67 4.87 5.45 5.15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 22h18" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

		</div>

		<button type="button" class="tc-btn tc-btn--outline-primary tc-open-edit-account">
			ویرایش اطلاعات
		</button>
	</div>

	<!-- ── Edit form ──────────────────────────────────────────── -->
	<form class="tc-account-edit-form woocommerce-EditAccountForm edit-account" id="tc-account-edit-form"
		action="" method="post" style="display:none;">

		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<div class="tc-form-grid">

			<div class="tc-form-field">
				<label for="account_first_name">نام <span class="required">*</span></label>
				<input type="text" id="account_first_name" name="account_first_name"
					value="<?php echo esc_attr( $first_name ); ?>" placeholder="نام">
			</div>

			<div class="tc-form-field">
				<label for="account_last_name">نام خانوادگی <span class="required">*</span></label>
				<input type="text" id="account_last_name" name="account_last_name"
					value="<?php echo esc_attr( $last_name ); ?>" placeholder="نام خانوادگی">
			</div>

			<div class="tc-form-field">
				<label for="account_email">پست الکترونیکی <span class="required">*</span></label>
				<input type="email" id="account_email" name="account_email"
					value="<?php echo esc_attr( $email ); ?>" dir="ltr" placeholder="ایمیل">
			</div>

			<div class="tc-form-field">
				<label for="billing_national_id">کد ملی</label>
				<input type="text" id="billing_national_id" name="billing_national_id"
					value="<?php echo esc_attr( $national_id ); ?>" dir="ltr" inputmode="numeric" maxlength="10" placeholder="کد ملی ۱۰ رقمی">
			</div>

			<div class="tc-form-field">
				<label for="billing_phone">شماره موبایل</label>
				<input type="tel" id="billing_phone" name="billing_phone"
					value="<?php echo esc_attr( $phone ); ?>" dir="ltr" placeholder="09xxxxxxxxx">
			</div>

			<div class="tc-form-field tc-form-field--full tc-form-field--checkbox">
				<label>
					<input type="checkbox" name="tc_newsletter" value="1" <?php checked( $newsletter, '1' ); ?>>
					<span>دریافت خبرنامه</span>
				</label>
			</div>

		</div>

		<hr class="tc-account-edit-form__divider">

		<div class="tc-form-grid">

			<div class="tc-form-field">
				<label for="password_current">رمز عبور فعلی</label>
				<input type="password" id="password_current" name="password_current" dir="ltr" placeholder="در صورت تغییر رمز عبور وارد کنید">
			</div>

			<div class="tc-form-field">
				<label for="password_1">رمز عبور جدید</label>
				<input type="password" id="password_1" name="password_1" dir="ltr" placeholder="رمز عبور جدید">
			</div>

			<div class="tc-form-field">
				<label for="password_2">تکرار رمز عبور جدید</label>
				<input type="password" id="password_2" name="password_2" dir="ltr" placeholder="تکرار رمز عبور جدید">
			</div>

		</div>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<div class="tc-account-edit-form__footer">
			<button type="submit" class="tc-btn tc-btn--primary" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>">
				ذخیره تغییرات
			</button>
			<button type="button" class="tc-btn tc-btn--outline tc-cancel-edit-account">
				انصراف
			</button>
		</div>

		<?php wp_nonce_field( 'save_account_details', 'woocommerce-edit-account-nonce' ); ?>
		<input type="hidden" name="action" value="save_account_details">

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>

</div>

<script>
(function($){
	$(document).on('click', '.tc-open-edit-account', function(){
		$('#tc-account-info-view').hide();
		$('#tc-account-edit-form').show();
	});
	$(document).on('click', '.tc-cancel-edit-account', function(){
		$('#tc-account-edit-form').hide();
		$('#tc-account-info-view').show();
	});
})(jQuery);
</script>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
