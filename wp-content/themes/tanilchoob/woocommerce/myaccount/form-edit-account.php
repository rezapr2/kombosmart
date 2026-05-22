<?php
/**
 * Edit account form — custom layout
 *
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

$user        = wp_get_current_user();
$first_name  = $user->first_name;
$last_name   = $user->last_name;
$email       = $user->user_email;
$phone       = get_user_meta($user->ID, 'billing_phone', true);
$national_id = get_user_meta($user->ID, 'billing_national_id', true);
$newsletter  = get_user_meta($user->ID, 'tc_newsletter', true);

do_action('woocommerce_before_edit_account_form'); ?>

<div class="tc-edit-account" dir="rtl">

	<!-- ── View mode ─────────────────────────────────────────── -->
	<div class="tc-account-info" id="tc-account-info-view">
		<div class="tc-account-info__grid grid grid-cols-3 gap-40">

			<div class="tc-account-info__field" data-group="name" data-label="نام و نام خانوادگی">
				<div class="tc-account-info__field-inner flex items-center justify-between gap-30">
					<div class="flex flex-col">
						<span class="tc-account-info__label yekan-20 color-black-50">نام و نام خانوادگی</span>
						<strong class="tc-account-info__value yekan-20 color-black"><?php echo esc_html(trim($first_name . ' ' . $last_name) ?: '—'); ?></strong>
					</div>
					<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account" aria-label="ویرایش">
						<svg class="tc-edit-icon--pencil" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<svg class="tc-edit-icon--chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>

			<div class="tc-account-info__field" data-group="email" data-label="پست الکترونیکی">
				<div class="tc-account-info__field-inner flex items-center justify-between gap-30">
					<div class="flex flex-col">
						<span class="tc-account-info__label yekan-20 color-black-50">پست الکترونیکی</span>
						<strong class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($email ?: '—'); ?></strong>
					</div>
					<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account" aria-label="ویرایش">
						<svg class="tc-edit-icon--pencil" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<svg class="tc-edit-icon--chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>

			<div class="tc-account-info__field" data-group="national_id" data-label="کد ملی">
				<div class="tc-account-info__field-inner flex items-center justify-between gap-30">
					<div class="flex flex-col">
						<span class="tc-account-info__label yekan-20 color-black-50">کد ملی</span>
						<strong class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($national_id ?: '—'); ?></strong>
					</div>
					<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account" aria-label="ویرایش">
						<svg class="tc-edit-icon--pencil" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<svg class="tc-edit-icon--chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>

			<div class="tc-account-info__field" data-group="newsletter" data-label="دریافت خبرنامه">
				<div class="tc-account-info__field-inner flex items-center justify-between gap-30">
					<div class="flex flex-col">
						<span class="tc-account-info__label yekan-20 color-black-50">دریافت خبر نامه</span>
						<strong class="tc-account-info__value yekan-20 color-black"><?php echo $newsletter ? 'بله' : 'خیر'; ?></strong>
					</div>
					<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account" aria-label="ویرایش">
						<svg class="tc-edit-icon--pencil" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<svg class="tc-edit-icon--chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>

			<div class="tc-account-info__field" data-group="phone" data-label="شماره موبایل">
				<div class="tc-account-info__field-inner flex items-center justify-between gap-30">
					<div class="flex flex-col">
						<span class="tc-account-info__label yekan-20 color-black-50">شماره موبایل</span>
						<strong class="tc-account-info__value yekan-20 color-black"><?php echo esc_html($phone ?: '—'); ?></strong>
					</div>
					<button type="button" class="tc-account-info__edit-icon flex item-center tc-open-edit-account" aria-label="ویرایش">
						<svg class="tc-edit-icon--pencil" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.2594 3.60022L5.04936 12.2902C4.73936 12.6202 4.43936 13.2702 4.37936 13.7202L4.00936 16.9602C3.87936 18.1302 4.71936 18.9302 5.87936 18.7302L9.09936 18.1802C9.54936 18.1002 10.1794 17.7702 10.4894 17.4302L18.6994 8.74022C20.1194 7.24022 20.7594 5.53022 18.5494 3.44022C16.3494 1.37022 14.6794 2.10022 13.2594 3.60022Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M11.8906 5.0498C12.3206 7.8098 14.5606 9.9198 17.3406 10.1998" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 22H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<svg class="tc-edit-icon--chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>

		</div>

		<button type="button" class="tc-btn tc-btn--outline-primary tc-open-edit-account main-btn">
			ویرایش اطلاعات
		</button>
	</div>

	<!-- ── Edit form ──────────────────────────────────────────── -->
	<form class="tc-account-edit-form woocommerce-EditAccountForm edit-account" id="tc-account-edit-form" action=""
		method="post" style="display:none;">

		<!-- Mobile group section header (shown only when a specific group is open on mobile) -->
		<div class="tc-mobile-group-header" id="tc-mobile-group-header">
			<button type="button" class="tc-mobile-group-back" id="tc-mobile-back-to-list" aria-label="بازگشت">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<span class="tc-mobile-group-title" id="tc-mobile-group-title"></span>
		</div>

		<?php do_action('woocommerce_edit_account_form_start'); ?>

		<div class="tc-form-grid">

			<div class="tc-form-group" data-group="name">
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
			</div>

			<div class="tc-form-group" data-group="email">
				<div class="tc-form-field">
					<label for="account_email">پست الکترونیکی</label>
					<input type="email" id="account_email" name="account_email" value="<?php echo esc_attr($email); ?>"
						dir="ltr" placeholder="ایمیل">
				</div>
			</div>

			<div class="tc-form-group" data-group="national_id">
				<div class="tc-form-field">
					<label for="billing_national_id">کد ملی</label>
					<input type="text" id="billing_national_id" name="billing_national_id"
						value="<?php echo esc_attr($national_id); ?>" dir="ltr" inputmode="numeric" maxlength="10"
						placeholder="کد ملی ۱۰ رقمی">
				</div>
			</div>

			<div class="tc-form-group" data-group="newsletter">
				<div class="tc-form-field tc-form-field--full tc-form-field--checkbox">
					<label>
						<input type="checkbox" name="tc_newsletter" value="1" <?php checked($newsletter, '1'); ?>>
						<span>دریافت خبرنامه</span>
					</label>
				</div>
			</div>

			<div class="tc-form-group" data-group="phone">
				<div class="tc-form-field">
					<label for="billing_phone">شماره موبایل</label>
					<input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr($phone); ?>"
						dir="ltr" placeholder="09xxxxxxxxx">
				</div>
			</div>

		</div>

		<?php do_action('woocommerce_edit_account_form'); ?>

		<div class="tc-account-edit-form__footer">
			<button type="submit" class="tc-btn tc-btn--primary" name="save_account_details"
				value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>">
				<span class="tc-save-label--desktop">ذخیره تغییرات</span>
				<span class="tc-save-label--mobile">ثبت اطلاعات</span>
			</button>
			<button type="button" class="tc-btn tc-btn--outline tc-cancel-edit-account tc-desktop-cancel">
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
		var isMobile = function () { return window.innerWidth <= 768; };

		// Open edit — desktop: show all fields; mobile: show only the tapped group
		$(document).on('click', '.tc-open-edit-account', function () {
			var $field = $(this).closest('.tc-account-info__field');
			var group  = $field.data('group');
			var label  = $field.data('label');

			$('#tc-account-info-view').hide();
			$('#tc-account-edit-form').show();

			if (isMobile() && group) {
				$('.tc-form-group').hide();
				$('.tc-form-group[data-group="' + group + '"]').show();
				$('#tc-mobile-group-title').text(label);
				$('#tc-mobile-group-header').show();
				$('.tc-account-edit-form__footer').addClass('tc-footer--mobile-group');
			} else {
				$('.tc-form-group').show();
				$('#tc-mobile-group-header').hide();
				$('.tc-account-edit-form__footer').removeClass('tc-footer--mobile-group');
			}
		});

		// "ویرایش اطلاعات" main button — show all fields
		$(document).on('click', '.main-btn.tc-open-edit-account', function () {
			$('#tc-account-info-view').hide();
			$('#tc-account-edit-form').show();
			$('.tc-form-group').show();
			$('#tc-mobile-group-header').hide();
			$('.tc-account-edit-form__footer').removeClass('tc-footer--mobile-group');
		});

		// Cancel — go back to view
		$(document).on('click', '.tc-cancel-edit-account, #tc-mobile-back-to-list', function () {
			$('#tc-account-edit-form').hide();
			$('#tc-account-info-view').show();
		});
	})(jQuery);
</script>

<?php do_action('woocommerce_after_edit_account_form'); ?>
