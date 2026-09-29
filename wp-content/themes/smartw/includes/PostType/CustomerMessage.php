<?php

namespace TanilChoob\Theme\PostType;

use TanilChoob\Theme\Abstracts\PostType;
use TanilChoob\Theme\Helper;

class CustomerMessage extends PostType {

	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes',        [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_tc_message',  [ $this, 'save_meta' ], 10, 2 );
		add_action( 'wp_ajax_tc_mark_message_read', [ $this, 'handle_mark_read' ] );
		add_action( 'tc_send_sms',           [ $this, 'handle_send_sms' ], 10, 3 );
		add_action( 'wp_insert_comment',              [ $this, 'maybe_send_review_reply_sms' ], 10, 2 );
		// Priority 20 so ACF has already persisted fields (ACF saves at priority 10)
		add_action( 'admin_menu',            [ $this, 'add_submenu' ] );
		add_action( 'admin_post_tc_send_bulk_sms', [ $this, 'handle_bulk_sms_form' ] );
	}

	public function register() {
		$labels = [
			'name'               => 'اطلاع رسانی',
			'singular_name'      => 'پیام',
			'add_new'            => 'پیام جدید',
			'add_new_item'       => 'افزودن پیام جدید',
			'edit_item'          => 'ویرایش پیام',
			'new_item'           => 'پیام جدید',
			'view_item'          => 'مشاهده پیام',
			'search_items'       => 'جستجو پیام‌ها',
			'not_found'          => 'پیامی یافت نشد',
			'not_found_in_trash' => 'پیامی در زباله‌دان یافت نشد',
			'menu_name'          => 'اطلاع رسانی',
		];

		register_post_type( 'tc_message', [
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 26,
			'supports'            => [ 'title', 'editor' ],
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
		] );
	}

	// ── Admin meta boxes ───────────────────────────────────────────────────

	public function add_meta_boxes() {
		add_meta_box(
			'tc_message_recipient',
			'گیرنده پیام',
			[ $this, 'render_recipient_meta_box' ],
			'tc_message',
			'side',
			'high'
		);
		add_meta_box(
			'tc_message_sms',
			'ارسال پیامک',
			[ $this, 'render_sms_meta_box' ],
			'tc_message',
			'side',
			'default'
		);
	}

	public function render_recipient_meta_box( $post ) {
		wp_nonce_field( 'tc_message_meta', 'tc_message_meta_nonce' );

		$user_id     = get_post_meta( $post->ID, '_tc_message_user_id', true );
		$group_id    = get_post_meta( $post->ID, '_tc_message_group_id', true );
		$all_flag    = get_post_meta( $post->ID, '_tc_message_recipient_all', true );

		if ( $group_id ) {
			$mode = 'group';
		} elseif ( $user_id ) {
			$mode = 'user';
		} elseif ( $all_flag ) {
			$mode = 'all';
		} else {
			$mode = 'all'; // default for new posts
		}

		echo '<p style="margin-bottom:8px">';
		echo '<label style="margin-left:12px"><input type="radio" name="tc_message_recipient_mode" value="all" ' . checked( $mode, 'all', false ) . '> همه مشتریان</label>';
		echo '<label style="margin-left:12px"><input type="radio" name="tc_message_recipient_mode" value="user" ' . checked( $mode, 'user', false ) . '> مشتری</label>';
		echo '<label><input type="radio" name="tc_message_recipient_mode" value="group" ' . checked( $mode, 'group', false ) . '> گروه مشتریان</label>';
		echo '</p>';

		// Individual customer
		$users = get_users( [
			'role__in' => [ 'customer', 'subscriber' ],
			'number'   => 500,
			'orderby'  => 'display_name',
		] );

		echo '<div id="tc-recipient-user" style="margin-bottom:10px;' . ( \in_array( $mode, [ 'group', 'all' ], true ) ? 'display:none' : '' ) . '">';
		echo '<label for="tc_message_user_id" style="display:block;margin-bottom:4px">مشتری گیرنده:</label>';
		echo '<select name="tc_message_user_id" id="tc_message_user_id" style="width:100%">';
		echo '<option value="">— انتخاب کنید —</option>';
		foreach ( $users as $user ) {
			printf(
				'<option value="%d" %s>%s (%s)</option>',
				$user->ID,
				selected( $user->ID, $user_id, false ),
				esc_html( $user->display_name ),
				esc_html( $user->user_email )
			);
		}
		echo '</select>';
		echo '</div>';

		// Customer group
		$groups = get_terms( [ 'taxonomy' => 'customer_group', 'hide_empty' => false ] );

		echo '<div id="tc-recipient-group" style="' . ( \in_array( $mode, [ 'user', 'all' ], true ) ? 'display:none' : '' ) . '">';
		echo '<label for="tc_message_group_id" style="display:block;margin-bottom:4px">گروه گیرنده:</label>';
		echo '<select name="tc_message_group_id" id="tc_message_group_id" style="width:100%">';
		echo '<option value="">— انتخاب کنید —</option>';
		foreach ( $groups as $group ) {
			printf(
				'<option value="%d" %s>%s</option>',
				$group->term_id,
				selected( $group->term_id, $group_id, false ),
				esc_html( $group->name )
			);
		}
		echo '</select>';
		echo '</div>';

		$read = get_post_meta( $post->ID, '_tc_message_read', true );
		echo '<p style="margin-top:12px">';
		echo '<strong>وضعیت خواندن:</strong> ';
		echo $read ? '<span style="color:green">خوانده‌شده</span>' : '<span style="color:#999">خوانده‌نشده</span>';
		echo '</p>';
		?>
		<script>
		(function(){
			document.querySelectorAll('input[name="tc_message_recipient_mode"]').forEach(function(radio){
				radio.addEventListener('change', function(){
					document.getElementById('tc-recipient-user').style.display  = this.value === 'user'  ? '' : 'none';
					document.getElementById('tc-recipient-group').style.display = this.value === 'group' ? '' : 'none';
				});
			});
		})();

		</script>
		<?php
	}

	public function render_sms_meta_box( $post ) {
		wp_nonce_field( 'tc_message_sms', 'tc_message_sms_nonce' );

		$sms_text   = get_post_meta( $post->ID, '_tc_sms_text', true );
		$sms_status = get_post_meta( $post->ID, '_tc_sms_status', true );
		?>
		<label for="tc_sms_text" style="display:block;margin-bottom:4px">متن پیامک:</label>
		<textarea name="tc_sms_text" id="tc_sms_text" rows="4" style="width:100%;resize:vertical"><?php echo esc_textarea( $sms_text ); ?></textarea>

		<label style="display:flex;align-items:center;gap:6px;margin-top:8px">
			<input type="checkbox" name="tc_send_sms" value="1">
			ارسال پیامک هنگام ذخیره
		</label>

		<?php if ( $sms_status ) : ?>
		<p style="margin-top:10px">
			<strong>وضعیت آخرین ارسال:</strong>
			<?php if ( $sms_status === 'sent' ) : ?>
				<span style="color:green">ارسال شد ✓</span>
			<?php else : ?>
				<span style="color:red">خطا: <?php echo esc_html( $sms_status ); ?></span>
			<?php endif; ?>
		</p>
		<?php endif; ?>
		<?php
	}

	public function save_meta( $post_id, $post ) {
		if (
			! isset( $_POST['tc_message_meta_nonce'] ) ||
			! wp_verify_nonce( $_POST['tc_message_meta_nonce'], 'tc_message_meta' )
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$mode = isset( $_POST['tc_message_recipient_mode'] ) ? sanitize_key( $_POST['tc_message_recipient_mode'] ) : 'all';

		if ( $mode === 'all' ) {
			update_post_meta( $post_id, '_tc_message_recipient_all', 1 );
			delete_post_meta( $post_id, '_tc_message_user_id' );
			delete_post_meta( $post_id, '_tc_message_group_id' );
		} elseif ( $mode === 'group' ) {
			$group_id = absint( $_POST['tc_message_group_id'] ?? 0 );
			update_post_meta( $post_id, '_tc_message_group_id', $group_id );
			delete_post_meta( $post_id, '_tc_message_user_id' );
			delete_post_meta( $post_id, '_tc_message_recipient_all' );
		} else {
			$user_id = absint( $_POST['tc_message_user_id'] ?? 0 );
			update_post_meta( $post_id, '_tc_message_user_id', $user_id );
			delete_post_meta( $post_id, '_tc_message_group_id' );
			delete_post_meta( $post_id, '_tc_message_recipient_all' );
		}

		// SMS
		if (
			isset( $_POST['tc_message_sms_nonce'] ) &&
			wp_verify_nonce( $_POST['tc_message_sms_nonce'], 'tc_message_sms' )
		) {
			$sms_text = sanitize_textarea_field( $_POST['tc_sms_text'] ?? '' );
			update_post_meta( $post_id, '_tc_sms_text', $sms_text );

			if ( ! empty( $_POST['tc_send_sms'] ) && $sms_text ) {
				$this->dispatch_sms( $post_id, $mode, $sms_text );
			}
		}
	}

	private function dispatch_sms( int $post_id, string $mode, string $text ): void {
		$phones = [];

		if ( $mode === 'all' ) {
			$users = get_users( [
				'role__in' => [ 'customer', 'subscriber' ],
				'fields'   => 'ID',
			] );
			foreach ( $users as $uid ) {
				$phone = get_user_meta( $uid, 'billing_phone', true );
				if ( $phone ) {
					$phones[] = $phone;
				}
			}
		} elseif ( $mode === 'group' ) {
			$group_id = (int) get_post_meta( $post_id, '_tc_message_group_id', true );
			if ( $group_id ) {
				$users = get_users( [
					'meta_key'   => '_tc_customer_group_id',
					'meta_value' => $group_id,
					'fields'     => 'ID',
				] );
				foreach ( $users as $uid ) {
					$phone = get_user_meta( $uid, 'billing_phone', true );
					if ( $phone ) {
						$phones[] = $phone;
					}
				}
			}
		} else {
			$uid   = (int) get_post_meta( $post_id, '_tc_message_user_id', true );
			$phone = $uid ? get_user_meta( $uid, 'billing_phone', true ) : '';
			if ( $phone ) {
				$phones[] = $phone;
			}
		}

		if ( empty( $phones ) ) {
			update_post_meta( $post_id, '_tc_sms_status', 'شماره‌ای یافت نشد' );
			return;
		}

		/**
		 * Fire this action to integrate your SMS gateway.
		 *
		 * @param string[] $phones  Recipient phone numbers.
		 * @param string   $text    SMS body.
		 * @param int      $post_id Message post ID.
		 */
		do_action( 'tc_send_sms', $phones, $text, $post_id );

		update_post_meta( $post_id, '_tc_sms_status', 'sent' );
	}

	// ── AJAX: mark as read ─────────────────────────────────────────────────

	public function handle_mark_read() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error();
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$message_id = absint( $_POST['message_id'] ?? 0 );
		if ( ! $message_id ) {
			wp_send_json_error();
		}

		$user_id          = get_current_user_id();
		$direct_recipient = (int) get_post_meta( $message_id, '_tc_message_user_id', true );
		$group_id         = (int) get_post_meta( $message_id, '_tc_message_group_id', true );
		$is_all           = (bool) get_post_meta( $message_id, '_tc_message_recipient_all', true );

		if ( $is_all ) {
			$read_ids = (array) get_user_meta( $user_id, '_tc_read_messages', true );
			if ( ! \in_array( $message_id, $read_ids, true ) ) {
				$read_ids[] = $message_id;
				update_user_meta( $user_id, '_tc_read_messages', $read_ids );
			}
		} elseif ( $group_id ) {
			$user_group_id = (int) get_user_meta( $user_id, '_tc_customer_group_id', true );
			if ( $user_group_id !== $group_id ) {
				wp_send_json_error();
			}
			$read_ids = (array) get_user_meta( $user_id, '_tc_read_messages', true );
			if ( ! \in_array( $message_id, $read_ids, true ) ) {
				$read_ids[] = $message_id;
				update_user_meta( $user_id, '_tc_read_messages', $read_ids );
			}
		} else {
			if ( $direct_recipient !== $user_id ) {
				wp_send_json_error();
			}
			update_post_meta( $message_id, '_tc_message_read', 1 );
		}

		wp_send_json_success();
	}

	// ── Query helper (used in MyAccount) ──────────────────────────────────

	public static function get_for_user( int $user_id ): array {
		$user_group_id = (int) get_user_meta( $user_id, '_tc_customer_group_id', true );

		$meta_query = [
			'relation' => 'OR',
			[
				'key'   => '_tc_message_user_id',
				'value' => $user_id,
				'type'  => 'NUMERIC',
			],
			[
				'key'   => '_tc_message_recipient_all',
				'value' => '1',
			],
		];

		if ( $user_group_id ) {
			$meta_query[] = [
				'key'   => '_tc_message_group_id',
				'value' => $user_group_id,
				'type'  => 'NUMERIC',
			];
		}

		return get_posts( [
			'post_type'      => 'tc_message',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => $meta_query,
		] );
	}

	// ── SMS gateway ───────────────────────────────────────────────────────────

	/**
	 * @param string[] $phones  Numbers in 09xxxxxxxxx format.
	 * @param string   $text    Message body.
	 * @param int      $post_id Source message post ID (used to persist status).
	 */
	public function handle_send_sms( array $phones, string $text, int $post_id ): void {
		$status = $this->send_sms_request( $phones, $text );
		if ( $post_id ) {
			update_post_meta( $post_id, '_tc_sms_status', $status );
		}
	}

	/**
	 * Performs the actual IPPanel API call and returns 'sent' or an error string.
	 *
	 * @param string[] $phones Raw phone numbers.
	 * @param string   $text   SMS body.
	 * @return string 'sent' on success, error message on failure.
	 */
	private function send_sms_request( array $phones, string $text ): string {
		$api_key     = Helper::get_options_field( 'sms_api_key' );
		$from_number = Helper::get_options_field( 'sms_from_number' );
		$base_url    = 'https://edge.ippanel.com/v1';

		$recipients = array_map( [ $this, 'to_international' ], $phones );
		$recipients = array_values( array_filter( $recipients ) );

		if ( empty( $recipients ) ) {
			return 'شماره‌ای برای ارسال وجود ندارد';
		}

		if ( empty( $api_key ) ) {
			error_log( '[TanilChoob SMS] phones=' . implode( ',', $recipients ) . ' text=' . $text );
			return 'sent';
		}

		$payload = wp_json_encode( [
			'sending_type' => 'webservice',
			'from_number'  => $from_number,
			'message'      => $text,
			'params'       => [ 'recipients' => $recipients ],
		] );

		$response = wp_remote_post( rtrim( $base_url, '/' ) . '/api/send', [
			'timeout' => 10,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => $api_key,
			],
			'body'    => $payload,
		] );

		if ( is_wp_error( $response ) ) {
			error_log( '[TanilChoob SMS] error: ' . $response->get_error_message() );
			return $response->get_error_message();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $body['meta']['status'] ?? false ) {
			return 'sent';
		}

		$msg = $body['meta']['message'] ?? wp_remote_retrieve_response_code( $response );
		error_log( "[TanilChoob SMS] failed: {$msg}" );
		return (string) $msg;
	}

	private function to_international( string $phone ): string {
		$phone = preg_replace( '/[^0-9]/', '', $phone );
		if ( str_starts_with( $phone, '0' ) ) {
			return '+98' . substr( $phone, 1 );
		}
		if ( str_starts_with( $phone, '98' ) ) {
			return '+' . $phone;
		}
		return $phone ? '+' . $phone : '';
	}

	// ── Bulk SMS submenu ──────────────────────────────────────────────────────

	public function add_submenu(): void {
		add_submenu_page(
			'edit.php?post_type=tc_message',
			'ارسال SMS',
			'ارسال SMS',
			'manage_options',
			'tc-send-sms',
			[ $this, 'render_sms_page' ]
		);
	}

	public function render_sms_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'دسترسی غیرمجاز' ) );
		}

		$sent  = isset( $_GET['tc_sms_sent'] );
		$error = isset( $_GET['tc_sms_error'] ) ? sanitize_text_field( urldecode( $_GET['tc_sms_error'] ) ) : '';
		?>
		<style>
		.tc-sms-wrap {
			direction: rtl;
			max-width: 780px;
			margin: 30px 20px 0;
			font-family: inherit;
		}
		.tc-sms-header {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 28px;
		}
		.tc-sms-header .tc-sms-icon {
			width: 44px;
			height: 44px;
			background: #2271b1;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.tc-sms-header .tc-sms-icon .dashicons {
			color: #fff;
			font-size: 24px;
			width: 24px;
			height: 24px;
		}
		.tc-sms-header h1 {
			margin: 0;
			padding: 0;
			font-size: 22px;
			font-weight: 600;
			color: #1d2327;
		}
		.tc-sms-header p {
			margin: 2px 0 0;
			color: #646970;
			font-size: 13px;
		}
		.tc-sms-notice {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 14px 16px;
			border-radius: 8px;
			margin-bottom: 24px;
			font-size: 14px;
			font-weight: 500;
		}
		.tc-sms-notice.success {
			background: #edfaef;
			border: 1px solid #00a32a;
			color: #1a6629;
		}
		.tc-sms-notice.error {
			background: #fcf0f1;
			border: 1px solid #d63638;
			color: #8a1f1f;
		}
		.tc-sms-notice .dashicons { font-size: 18px; width: 18px; height: 18px; }
		.tc-sms-card {
			background: #fff;
			border: 1px solid #dcdcde;
			border-radius: 10px;
			overflow: hidden;
			box-shadow: 0 1px 3px rgba(0,0,0,.06);
		}
		.tc-sms-card-header {
			padding: 16px 22px;
			border-bottom: 1px solid #f0f0f1;
			background: #f6f7f7;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.tc-sms-card-header .dashicons {
			color: #2271b1;
			font-size: 18px;
			width: 18px;
			height: 18px;
		}
		.tc-sms-card-header span {
			font-size: 14px;
			font-weight: 600;
			color: #1d2327;
		}
		.tc-sms-card-body {
			padding: 22px;
		}
		.tc-sms-field {
			margin-bottom: 22px;
		}
		.tc-sms-field:last-child { margin-bottom: 0; }
		.tc-sms-field label {
			display: block;
			font-size: 13px;
			font-weight: 600;
			color: #1d2327;
			margin-bottom: 8px;
		}
		.tc-sms-field label .required {
			color: #d63638;
			margin-right: 3px;
		}
		.tc-sms-field textarea {
			width: 100%;
			border: 1px solid #dcdcde;
			border-radius: 6px;
			padding: 10px 12px;
			font-size: 14px;
			line-height: 1.6;
			color: #1d2327;
			background: #fff;
			resize: vertical;
			transition: border-color .15s, box-shadow .15s;
			box-sizing: border-box;
			direction: rtl;
			font-family: inherit;
		}
		.tc-sms-field textarea:focus {
			border-color: #2271b1;
			box-shadow: 0 0 0 1px #2271b1;
			outline: none;
		}
		.tc-sms-field .tc-hint {
			margin-top: 6px;
			font-size: 12px;
			color: #646970;
			display: flex;
			align-items: center;
			gap: 4px;
		}
		.tc-sms-field .tc-hint .dashicons {
			font-size: 14px;
			width: 14px;
			height: 14px;
			color: #a7aaad;
		}
		.tc-sms-counter {
			margin-top: 6px;
			font-size: 12px;
			color: #646970;
			display: flex;
			justify-content: space-between;
		}
		.tc-sms-counter span { color: #2271b1; font-weight: 600; }
		.tc-sms-divider {
			height: 1px;
			background: #f0f0f1;
			margin: 0 0 22px;
		}
		.tc-sms-footer {
			padding: 16px 22px;
			border-top: 1px solid #f0f0f1;
			background: #f6f7f7;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
		.tc-sms-footer .tc-tip {
			font-size: 12px;
			color: #646970;
			display: flex;
			align-items: center;
			gap: 5px;
		}
		.tc-sms-footer .tc-tip .dashicons {
			font-size: 14px;
			width: 14px;
			height: 14px;
		}
		.tc-sms-submit {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			background: #2271b1;
			color: #fff !important;
			border: none;
			border-radius: 6px;
			padding: 9px 20px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: background .15s, transform .1s;
			text-decoration: none;
		}
		.tc-sms-submit:hover { background: #135e96; }
		.tc-sms-submit:active { transform: scale(.98); }
		.tc-sms-submit .dashicons {
			font-size: 17px;
			width: 17px;
			height: 17px;
		}
		</style>

		<div class="tc-sms-wrap">

			<div class="tc-sms-header">
				<div class="tc-sms-icon">
					<span class="dashicons dashicons-smartphone"></span>
				</div>
				<div>
					<h1>ارسال SMS</h1>
					<p>ارسال پیامک مستقیم به شماره‌های دلخواه</p>
				</div>
			</div>

			<?php if ( $sent ) : ?>
			<div class="tc-sms-notice success">
				<span class="dashicons dashicons-yes-alt"></span>
				پیامک‌ها با موفقیت ارسال شدند.
			</div>
			<?php elseif ( $error ) : ?>
			<div class="tc-sms-notice error">
				<span class="dashicons dashicons-warning"></span>
				<?php echo esc_html( $error ); ?>
			</div>
			<?php endif; ?>

			<div class="tc-sms-card">
				<div class="tc-sms-card-header">
					<span class="dashicons dashicons-edit-page"></span>
					<span>محتوای پیامک</span>
				</div>
				<div class="tc-sms-card-body">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="tc-sms-form">
						<?php wp_nonce_field( 'tc_bulk_sms', 'tc_bulk_sms_nonce' ); ?>
						<input type="hidden" name="action" value="tc_send_bulk_sms">

						<div class="tc-sms-field">
							<label for="tc_bulk_phones">
								شماره‌های گیرنده
								<span class="required">*</span>
							</label>
							<textarea
								name="tc_bulk_phones"
								id="tc_bulk_phones"
								rows="6"
								placeholder="09123456789&#10;09876543210&#10;09111111111"
								required
							></textarea>
							<div class="tc-hint">
								<span class="dashicons dashicons-info-outline"></span>
								هر شماره را در یک خط جداگانه وارد کنید.
							</div>
							<div class="tc-sms-counter">
								<span></span>
								<span id="tc-phone-count">۰ شماره</span>
							</div>
						</div>

						<div class="tc-sms-divider"></div>

						<div class="tc-sms-field">
							<label for="tc_bulk_message">
								متن پیامک
								<span class="required">*</span>
							</label>
							<textarea
								name="tc_bulk_message"
								id="tc_bulk_message"
								rows="5"
								placeholder="متن پیامک را اینجا بنویسید..."
								required
							></textarea>
							<div class="tc-sms-counter">
								<span></span>
								<span id="tc-char-count">۰ کاراکتر</span>
							</div>
						</div>
					</form>
				</div>
				<div class="tc-sms-footer">
					<div class="tc-tip">
						<span class="dashicons dashicons-shield-alt"></span>
						پیامک‌ها از طریق درگاه IPPanel ارسال می‌شوند.
					</div>
					<button type="submit" form="tc-sms-form" class="tc-sms-submit">
						<span class="dashicons dashicons-controls-forward"></span>
						ارسال پیامک
					</button>
				</div>
			</div>

		</div>

		<script>
		(function () {
			function toPersianNum(n) {
				return String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
			}

			var phonesEl  = document.getElementById('tc_bulk_phones');
			var msgEl     = document.getElementById('tc_bulk_message');
			var phoneCount = document.getElementById('tc-phone-count');
			var charCount  = document.getElementById('tc-char-count');

			function updatePhoneCount() {
				var lines = phonesEl.value.split(/\n/).filter(function(l){ return l.trim() !== ''; });
				phoneCount.textContent = toPersianNum(lines.length) + ' شماره';
			}

			function updateCharCount() {
				charCount.textContent = toPersianNum(msgEl.value.length) + ' کاراکتر';
			}

			phonesEl.addEventListener('input', updatePhoneCount);
			msgEl.addEventListener('input', updateCharCount);
		})();
		</script>
		<?php
	}

	public function handle_bulk_sms_form(): void {
		if (
			! isset( $_POST['tc_bulk_sms_nonce'] ) ||
			! wp_verify_nonce( $_POST['tc_bulk_sms_nonce'], 'tc_bulk_sms' )
		) {
			wp_die( 'نانس نامعتبر است.' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی غیرمجاز' );
		}

		$raw_phones = sanitize_textarea_field( $_POST['tc_bulk_phones'] ?? '' );
		$message    = sanitize_textarea_field( $_POST['tc_bulk_message'] ?? '' );

		if ( ! $raw_phones || ! $message ) {
			wp_redirect( add_query_arg(
				[ 'tc_sms_error' => rawurlencode( 'شماره یا متن پیامک خالی است.' ) ],
				admin_url( 'edit.php?post_type=tc_message&page=tc-send-sms' )
			) );
			exit;
		}

		$lines  = preg_split( '/[\r\n]+/', $raw_phones );
		$phones = array_values( array_filter( array_map( 'trim', $lines ) ) );

		if ( empty( $phones ) ) {
			wp_redirect( add_query_arg(
				[ 'tc_sms_error' => rawurlencode( 'هیچ شماره‌ای وارد نشده است.' ) ],
				admin_url( 'edit.php?post_type=tc_message&page=tc-send-sms' )
			) );
			exit;
		}

		$status   = $this->send_sms_request( $phones, $message );
		$redirect = admin_url( 'edit.php?post_type=tc_message&page=tc-send-sms' );

		if ( $status === 'sent' ) {
			wp_redirect( add_query_arg( [ 'tc_sms_sent' => '1' ], $redirect ) );
		} else {
			wp_redirect( add_query_arg( [ 'tc_sms_error' => rawurlencode( $status ) ], $redirect ) );
		}
		exit;
	}

	// ── Review reply SMS ──────────────────────────────────────────────────────

	public function maybe_send_review_reply_sms( int $comment_id, \WP_Comment $comment ): void {
		if ( ! Helper::get_options_field( 'send_sms_on_review_response' ) ) {
			return;
		}

		// Only replies to WooCommerce product reviews
		if ( get_post_type( (int) $comment->comment_post_ID ) !== 'product' ) {
			return;
		}

		$parent_id = (int) $comment->comment_parent;
		if ( ! $parent_id ) {
			return;
		}

		$sms_text = Helper::get_options_field( 'send_sms_on_review_response_text' );
		if ( ! $sms_text ) {
			return;
		}

		$parent = get_comment( $parent_id );
		if ( ! $parent ) {
			return;
		}

		// Get phone from parent commenter's user account
		$phone = '';
		if ( $parent->user_id ) {
			$phone = get_user_meta( (int) $parent->user_id, 'billing_phone', true );
		}

		if ( ! $phone ) {
			return;
		}

		do_action( 'tc_send_sms', [ $phone ], $sms_text, 0 );
	}
}

new CustomerMessage();
