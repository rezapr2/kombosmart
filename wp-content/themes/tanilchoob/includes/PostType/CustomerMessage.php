<?php

namespace TanilChoob\Theme\PostType;

use TanilChoob\Theme\Abstracts\PostType;

class CustomerMessage extends PostType {

	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes',        [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_tc_message',  [ $this, 'save_meta' ], 10, 2 );
		add_action( 'wp_ajax_tc_mark_message_read', [ $this, 'handle_mark_read' ] );
	}

	public function register() {
		$labels = [
			'name'               => 'پیام‌ها',
			'singular_name'      => 'پیام',
			'add_new'            => 'پیام جدید',
			'add_new_item'       => 'افزودن پیام جدید',
			'edit_item'          => 'ویرایش پیام',
			'new_item'           => 'پیام جدید',
			'view_item'          => 'مشاهده پیام',
			'search_items'       => 'جستجو پیام‌ها',
			'not_found'          => 'پیامی یافت نشد',
			'not_found_in_trash' => 'پیامی در زباله‌دان یافت نشد',
			'menu_name'          => 'پیام‌ها',
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
	}

	public function render_recipient_meta_box( $post ) {
		wp_nonce_field( 'tc_message_meta', 'tc_message_meta_nonce' );

		$user_id = get_post_meta( $post->ID, '_tc_message_user_id', true );

		$users = get_users( [
			'role__in' => [ 'customer', 'subscriber' ],
			'number'   => 500,
			'orderby'  => 'display_name',
		] );

		echo '<label for="tc_message_user_id" style="display:block;margin-bottom:6px">مشتری گیرنده:</label>';
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

		$read = get_post_meta( $post->ID, '_tc_message_read', true );
		echo '<p style="margin-top:10px">';
		echo '<strong>وضعیت خواندن:</strong> ';
		echo $read ? '<span style="color:green">خوانده‌شده</span>' : '<span style="color:#999">خوانده‌نشده</span>';
		echo '</p>';
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

		if ( isset( $_POST['tc_message_user_id'] ) ) {
			$user_id = absint( $_POST['tc_message_user_id'] );
			update_post_meta( $post_id, '_tc_message_user_id', $user_id );
		}
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

		$recipient = (int) get_post_meta( $message_id, '_tc_message_user_id', true );
		if ( $recipient !== get_current_user_id() ) {
			wp_send_json_error();
		}

		update_post_meta( $message_id, '_tc_message_read', 1 );
		wp_send_json_success();
	}

	// ── Query helper (used in MyAccount) ──────────────────────────────────

	public static function get_for_user( int $user_id ): array {
		return get_posts( [
			'post_type'      => 'tc_message',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => [
				[
					'key'   => '_tc_message_user_id',
					'value' => $user_id,
					'type'  => 'NUMERIC',
				],
			],
		] );
	}
}

new CustomerMessage();
