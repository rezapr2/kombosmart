<?php
/**
 * Edit account form — custom layout
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

$user = wp_get_current_user();
$first_name = $user->first_name;
$last_name = $user->last_name;
$email = $user->user_email;
$phone = get_user_meta($user->ID, 'billing_phone', true);
$national_id = get_user_meta($user->ID, 'billing_national_id', true);
$newsletter = get_user_meta($user->ID, 'tc_newsletter', true);

do_action('woocommerce_before_edit_account_form'); ?>

<div class="tc-edit-account" dir="rtl">

	<!-- ── View mode ─────────────────────────────────────────── -->
	<div class="tc-account-info" id="tc-account-info-view">
		<div class="tc-account-info__grid grid grid-cols-3 gap-40">

			<div class="tc-account-info__field flex items-center justify-between gap-30">
				<div class="flex flex-col">
					<span class="tc-account-info__label yekan-20 color-black-50">نام و نام خانوادگی</span>
					<strong
						class="tc-account-info__value yekan-20 color-black"><?php echo esc_html(trim($first_name . ' ' . $last_name) ?: '—'); ?></strong>
				</div>
				<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account"
					aria-label="ویرایش">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z"
							stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
							stroke-linejoin="round" />
						<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32"
							stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10"
							stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>

			<div class="tc-account-info__field flex items-center justify-between gap-30">
				<div class="flex flex-col">
					<span class="tc-account-info__label yekan-20 color-black-50">پست الکترونیکی</span>
					<strong
						class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($email ?: '—'); ?></strong>
				</div>
				<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account"
					aria-label="ویرایش">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z"
							stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
							stroke-linejoin="round" />
						<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32"
							stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10"
							stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>

			<div class="tc-account-info__field flex items-center justify-between gap-30">
				<div class="flex flex-col">
					<span class="tc-account-info__label yekan-20 color-black-50">کد ملی</span>
					<strong
						class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($national_id ?: '—'); ?></strong>
				</div>
				<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account"
					aria-label="ویرایش">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z"
							stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
							stroke-linejoin="round" />
						<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32"
							stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10"
							stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>

			<div class="tc-account-info__field flex items-center justify-between gap-30">
				<div class="flex flex-col">
					<span class="tc-account-info__label yekan-20 color-black-50">شماره موبایل</span>
					<strong
						class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($phone ?: '—'); ?></strong>
				</div>
				<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account"
					aria-label="ویرایش">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z"
							stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
							stroke-linejoin="round" />
						<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32"
							stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10"
							stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>

			<div class="tc-account-info__field flex items-center justify-between gap-30">
				<div class="flex flex-col">
					<span class="tc-account-info__label yekan-20 color-black-50">دریافت خبر نامه</span>
					<strong
						class="tc-account-info__value yekan-20 color-black"><?php echo $newsletter ? 'بله' : 'خیر'; ?></strong>
				</div>
				<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account"
					aria-label="ویرایش">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z"
							stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
							stroke-linejoin="round" />
						<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32"
							stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
						<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10"
							stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>

		</div>

		<button type="button" class="tc-btn tc-btn--outline-primary tc-open-edit-account main-btn">
			ویرایش اطلاعات
		</button>
	</div>

	<!-- ── Edit form ──────────────────────────────────────────── -->
	<form class="tc-account-edit-form woocommerce-EditAccountForm edit-account" id="tc-account-edit-form" action=""
		method="post" style="display:none;">

		<?php do_action('woocommerce_edit_account_form_start'); ?>

		<div class="tc-form-grid">

			<div class="tc-form-field">
				<label for="account_first_name">نام <span class="required">*</span></label>
				<input type="text" id="account_first_name" name="account_first_name"
					value="<?php echo esc_attr($first_name); ?>" placeholder="نام">
			</div>

			<div class="tc-form-field">
				<label for="account_last_name">نام خانوادگی <span class="required">*</span></label>
				<input type="text" id="account_last_name" name="account_last_name"
					value="<?php echo esc_attr($last_name); ?>" placeholder="نام خانوادگی">
			</div>

			<div class="tc-form-field">
				<label for="account_email">پست الکترونیکی <span class="required">*</span></label>
				<input type="email" id="account_email" name="account_email" value="<?php echo esc_attr($email); ?>"
					dir="ltr" placeholder="ایمیل">
			</div>

			<div class="tc-form-field">
				<label for="billing_national_id">کد ملی</label>
				<input type="text" id="billing_national_id" name="billing_national_id"
					value="<?php echo esc_attr($national_id); ?>" dir="ltr" inputmode="numeric" maxlength="10"
					placeholder="کد ملی ۱۰ رقمی">
			</div>

			<div class="tc-form-field">
				<label for="billing_phone">شماره موبایل</label>
				<input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr($phone); ?>"
					dir="ltr" placeholder="09xxxxxxxxx">
			</div>

			<div class="tc-form-field tc-form-field--full tc-form-field--checkbox">
				<label>
					<input type="checkbox" name="tc_newsletter" value="1" <?php checked($newsletter, '1'); ?>>
					<span>دریافت خبرنامه</span>
				</label>
			</div>

		</div>

		<hr class="tc-account-edit-form__divider">

		<div class="tc-form-grid">

			<div class="tc-form-field">
				<label for="password_current">رمز عبور فعلی</label>
				<input type="password" id="password_current" name="password_current" dir="ltr"
					placeholder="در صورت تغییر رمز عبور وارد کنید">
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

		<?php do_action('woocommerce_edit_account_form'); ?>

		<div class="tc-account-edit-form__footer">
			<button type="submit" class="tc-btn tc-btn--primary" name="save_account_details"
				value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>">
				ذخیره تغییرات
			</button>
			<button type="button" class="tc-btn tc-btn--outline tc-cancel-edit-account">
				انصراف
			</button>
		</div>

		<?php wp_nonce_field('save_account_details', 'woocommerce-edit-account-nonce'); ?>
		<input type="hidden" name="action" value="save_account_details">

		<?php do_action('woocommerce_edit_account_form_end'); ?>
	</form>

</div>

<script>
	(function ($) {
		$(document).on('click', '.tc-open-edit-account', function () {
			$('#tc-account-info-view').hide();
			$('#tc-account-edit-form').show();
		});
		$(document).on('click', '.tc-cancel-edit-account', function () {
			$('#tc-account-edit-form').hide();
			$('#tc-account-info-view').show();
		});
	})(jQuery);
</script>

<?php do_action('woocommerce_after_edit_account_form'); ?>