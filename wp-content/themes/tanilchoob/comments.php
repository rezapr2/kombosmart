<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @package TanilChoob
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area bg-white rounded-10 p-30 border border-gray-200">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) :
		?>
		<h2 class="comments-title yekan-20 bold color-black mb-30 border-b border-gray-100 pb-15">
			<?php
			$tanilchoob_comment_count = get_comments_number();
			if ( '1' === $tanilchoob_comment_count ) {
				printf(
					/* translators: 1: title. */
					esc_html__( 'یک دیدگاه برای &ldquo;%1$s&rdquo;', 'tanilchoob' ),
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			} else {
				printf( 
					/* translators: 1: comment count number, 2: title. */
					esc_html( _nx( '%1$s دیدگاه برای &ldquo;%2$s&rdquo;', '%1$s دیدگاه برای &ldquo;%2$s&rdquo;', $tanilchoob_comment_count, 'comments title', 'tanilchoob' ) ),
					number_format_i18n( $tanilchoob_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			}
			?>
		</h2><!-- .comments-title -->

		<?php the_comments_navigation(); ?>

		<ol class="comment-list list-none p-0 m-0 flex flex-col gap-20">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 60,
					'callback'   => function($comment, $args, $depth) {
						$GLOBALS['comment'] = $comment;
						?>
						<li <?php comment_class('flex flex-col gap-10 bg-gray-50 p-20 rounded-10'); ?> id="li-comment-<?php comment_ID(); ?>">
							<div class="comment-body flex flex-col gap-10 w-100">
								<div class="comment-meta flex items-center justify-between">
									<div class="author-info flex items-center gap-10">
										<div class="avatar rounded-full overflow-hidden w-50 h-50 flex-shrink-0">
											<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
										</div>
										<div class="flex flex-col">
											<b class="fn yekan-16 color-black"><?php echo get_comment_author_link(); ?></b>
											<span class="date yekan-12 color-black-40">
												<?php
													/* translators: 1: date, 2: time */
													printf( esc_html__( '%1$s در %2$s', 'tanilchoob' ), get_comment_date(), get_comment_time() );
												?>
											</span>
										</div>
									</div>
									
									<?php comment_reply_link( array_merge( $args, array( 
										'depth' => $depth, 
										'max_depth' => $args['max_depth'],
										'reply_text' => '<span class="yekan-12 color-primary border border-primary rounded-5 px-10 py-5 hover-bg-primary hover-color-white transition">پاسخ</span>'
									) ) ); ?>
								</div>

								<?php if ( '0' == $comment->comment_approved ) : ?>
								<p class="comment-awaiting-moderation yekan-14 color-orange"><?php esc_html_e( 'دیدگاه شما در انتظار بررسی است.', 'tanilchoob' ); ?></p>
								<?php endif; ?>

								<div class="comment-content yekan-14 color-black-60 leading-24 text-justify">
									<?php comment_text(); ?>
								</div>
							</div>
						<!-- </li> is closed by WordPress -->
						<?php
					}
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_navigation();

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() ) :
			?>
			<p class="no-comments yekan-14 color-black-40 text-center mt-20"><?php esc_html_e( 'دیدگاه‌ها بسته شده‌اند.', 'tanilchoob' ); ?></p>
			<?php
		endif;

	endif; // Check for have_comments().

	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$fields =  array(
		'author' =>
			'<div class="comment-form-author flex flex-col gap-5 w-100 md:w-50">' .
			'<label for="author" class="yekan-14 color-black-60">' . __( 'نام', 'tanilchoob' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
			'<input id="author" name="author" type="text" class="w-100 border border-gray-200 rounded-5 p-10 yekan-14 focus-border-primary transition" value="' . esc_attr( $commenter['comment_author'] ) .
			'" size="30"' . $aria_req . ' /></div>',

		'email' =>
			'<div class="comment-form-email flex flex-col gap-5 w-100 md:w-50">' .
			'<label for="email" class="yekan-14 color-black-60">' . __( 'ایمیل', 'tanilchoob' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
			'<input id="email" name="email" type="text" class="w-100 border border-gray-200 rounded-5 p-10 yekan-14 focus-border-primary transition" value="' . esc_attr(  $commenter['comment_author_email'] ) .
			'" size="30"' . $aria_req . ' /></div>',
	);

	comment_form( array(
		'fields' => $fields,
		'class_form' => 'comment-form flex flex-wrap gap-20 mt-40',
		'title_reply' => '<span class="yekan-20 bold color-black">' . __( 'ارسال دیدگاه', 'tanilchoob' ) . '</span>',
		'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title w-100 mb-20 border-b border-gray-100 pb-15">',
		'title_reply_after' => '</h3>',
		'comment_field' => '<div class="comment-form-comment w-100 flex flex-col gap-5">' .
			'<label for="comment" class="yekan-14 color-black-60">' . _x( 'دیدگاه', 'noun', 'tanilchoob' ) . '</label>' .
			'<textarea id="comment" name="comment" cols="45" rows="8" class="w-100 border border-gray-200 rounded-5 p-10 yekan-14 focus-border-primary transition" aria-required="true"></textarea>' .
			'</div>',
		'submit_button' => '<button name="%1$s" type="submit" id="%2$s" class="%3$s bg-primary color-white border-none rounded-5 py-10 px-30 yekan-16 pointer hover-bg-dark-primary transition mt-10">%4$s</button>',
		'class_submit' => 'submit',
	) );
	?>

</div><!-- #comments -->
