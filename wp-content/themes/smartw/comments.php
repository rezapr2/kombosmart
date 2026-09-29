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
<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="comments-area customer-reviews flex flex-col items-center parent-wrapper">
	<div class="submit-review flex flex-col gap-20 items-center justify-between w-full">
		<div class="flex flex-col">
			<?php $subject_label = is_singular('product') ? 'کالا' : 'مطلب'; ?>
			<h3 class="yekan-18 color-black-60">شما هم درباره این <?php echo esc_html($subject_label); ?> دیدگاه ثبت کنید.</h3>
			<p class="yekan-18 color-black-40">بدون نیاز به وارد شدن به حساب کاربری، نظر خود را در رابطه با این <?php echo esc_html($subject_label); ?> ثبت کنید و به نظرات دیگران امتیاز دهید.</p>
		</div>
		<div id="submit-review" class="reviews-submit submit-button self-end yekan-18 color-white bg-black pointer">ثبت دیدگاه</div>
		<div class="comments-title-wrapper w-100 flex flex-col gap-10">
				<h2 class="comments-title yekan-20 regular color-black">
					
					<?php
					$tanilchoob_comment_count = get_comments_number();

					echo "نظرات کاربران ({$tanilchoob_comment_count} نفر)";
					
					
					?>
				</h2><!-- .comments-title -->
				<?php if(!have_comments()): ?>
					<p class="no-comments yekan-16 color-black-70">هیچ دیدگاهی برای این مطلب نوشته نشده است.</p>
				<?php endif; ?>
		</div>
	</div>
		<div id="review_form_wrapper" class="submit-form mt-40 hidden">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'woocommerce' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title yekan-24 color-black-80 text-center" role="heading" aria-level="3">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => 'ثبت نظر',
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
                // Remove email field from the review form; keep author only.
                $fields              = array(
                    'author' => array(
                        'label'        => __( 'Name', 'woocommerce' ),
                        'type'         => 'text',
                        'value'        => $commenter['comment_author'],
                        'required'     => $name_email_required,
                        'autocomplete' => 'name',
                    ),
                );

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<div class="comment-form-field flex flex-col comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '" class="yekan-18 color-black-80">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">(اجباری)</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></div>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}


				$comment_form['comment_field'] .= '<div class="comment-form-field flex flex-col comment-form-comment"><label for="comment" class="yekan-18 color-black-80">' . 'دیدگاه شما' . '&nbsp;<span class="required">(اجباری)</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></div>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<div id="comments" class="reviews_list w-full">
		
		<?php if ( have_comments() ) : ?>
			<ul class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ul>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews yekan-18 color-black-60 w-full"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>

	

	<div class="clear"></div>
</div>
