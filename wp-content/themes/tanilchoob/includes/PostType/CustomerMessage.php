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
	}

	public function render_recipient_meta_box( $post ) {
		wp_nonce_field( 'tc_message_meta', 'tc_message_meta_nonce' );

		$user_id  = get_post_meta( $post->ID, '_tc_message_user_id', true );
		$group_id = get_post_meta( $post->ID, '_tc_message_group_id', true );

		$mode = $group_id ? 'group' : 'user';

		echo '<p style="margin-bottom:8px">';
		echo '<label style="margin-left:12px"><input type="radio" name="tc_message_recipient_mode" value="user" ' . checked( $mode, 'user', false ) . '> مشتری</label>';
		echo '<label><input type="radio" name="tc_message_recipient_mode" value="group" ' . checked( $mode, 'group', false ) . '> گروه مشتریان</label>';
		echo '</p>';

		// Individual customer
		$users = get_users( [
			'role__in' => [ 'customer', 'subscriber' ],
			'number'   => 500,
			'orderby'  => 'display_name',
		] );

		echo '<div id="tc-recipient-user" style="margin-bottom:10px;' . ( $mode === 'group' ? 'display:none' : '' ) . '">';
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

		echo '<div id="tc-recipient-group" style="' . ( $mode === 'user' ? 'display:none' : '' ) . '">';
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

		$mode = isset( $_POST['tc_message_recipient_mode'] ) ? sanitize_key( $_POST['tc_message_recipient_mode'] ) : 'user';

		if ( $mode === 'group' ) {
			$group_id = absint( $_POST['tc_message_group_id'] ?? 0 );
			update_post_meta( $post_id, '_tc_message_group_id', $group_id );
			delete_post_meta( $post_id, '_tc_message_user_id' );
		} else {
			$user_id = absint( $_POST['tc_message_user_id'] ?? 0 );
			update_post_meta( $post_id, '_tc_message_user_id', $user_id );
			delete_post_meta( $post_id, '_tc_message_group_id' );
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

		$user_id          = get_current_user_id();
		$direct_recipient = (int) get_post_meta( $message_id, '_tc_message_user_id', true );
		$group_id         = (int) get_post_meta( $message_id, '_tc_message_group_id', true );

		if ( $group_id ) {
			$user_group_id = (int) get_user_meta( $user_id, '_tc_customer_group_id', true );
			if ( $user_group_id !== $group_id ) {
				wp_send_json_error();
			}
			$read_ids = (array) get_user_meta( $user_id, '_tc_read_messages', true );
			if ( ! in_array( $message_id, $read_ids, true ) ) {
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
}

new CustomerMessage();
