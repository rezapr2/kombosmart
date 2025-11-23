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
<div id="reviews" class="woocommerce-Reviews customer-reviews flex flex-col items-center">
	<div class="submit-review flex items-center justify-between w-full">
		<div class="flex flex-col">
			<h3 class="yekan-18 color-black-60">شما هم درباره این کالا دیدگاه ثبت کنید.</h3>
			<p class="yekan-18 color-black-40">بدون نیاز به وارد شدن به حساب کاربری، نظر خود را در رابطه بااین کالا ثبت کنید و به نظرات دیگران امتیاز دهید.</p>
		</div>
		<div class="submit-button yekan-18 color-white bg-black">ثبت دیدگاه</div>
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

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper">
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
				$fields              = array(
					'author' => array(
						'label'        => __( 'Name', 'woocommerce' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					),
					'email'  => array(
						'label'        => __( 'Email', 'woocommerce' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<div class="comment-form-field flex flex-col comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '" class="yekan-18 color-black-80">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></div>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$icon = '<svg class="star-icon" width="20" height="20" viewBox="0 0 37 37" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M21.6086 2.63915L24.8511 9.12417C25.2933 10.0269 26.4724 10.8928 27.4673 11.0586L33.3443 12.0351C37.1027 12.6614 37.987 15.3881 35.2788 18.0779L30.7098 22.6469C29.936 23.4207 29.5122 24.913 29.7517 25.9815L31.0598 31.6375C32.0915 36.1144 29.7149 37.8462 25.7539 35.5064L20.2453 32.2455C19.2504 31.6559 17.6108 31.6559 16.5975 32.2455L11.0889 35.5064C7.14629 37.8462 4.75126 36.096 5.78297 31.6375L7.09102 25.9815C7.33053 24.913 6.90679 23.4207 6.13301 22.6469L1.56402 18.0779C-1.12579 15.3881 -0.259893 12.6614 3.49847 12.0351L9.37552 11.0586C10.352 10.8928 11.5311 10.0269 11.9732 9.12417L15.2157 2.63915C16.9844 -0.879715 19.8584 -0.879715 21.6086 2.63915Z" fill="currentColor"/></svg>';

					$comment_form['comment_field'] = '<div class="comment-form-rating flex flex-col items-center"><label for="rating" id="comment-form-rating-label" class="yekan-18 color-black-80">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label>'
						. '<p class="stars"><span role="group" aria-labelledby="comment-form-rating-label">'
							. '<a class="star-1" role="radio" aria-checked="false" href="#" tabindex="0">' . $icon . '<span class="sr-only">' . esc_html__( '1 out of 5 stars', 'woocommerce' ) . '</span></a>'
							. '<a class="star-2" role="radio" aria-checked="false" href="#" tabindex="0">' . $icon . '<span class="sr-only">' . esc_html__( '2 out of 5 stars', 'woocommerce' ) . '</span></a>'
							. '<a class="star-3" role="radio" aria-checked="false" href="#" tabindex="0">' . $icon . '<span class="sr-only">' . esc_html__( '3 out of 5 stars', 'woocommerce' ) . '</span></a>'
							. '<a class="star-4" role="radio" aria-checked="false" href="#" tabindex="0">' . $icon . '<span class="sr-only">' . esc_html__( '4 out of 5 stars', 'woocommerce' ) . '</span></a>'
							. '<a class="star-5" role="radio" aria-checked="false" href="#" tabindex="0">' . $icon . '<span class="sr-only">' . esc_html__( '5 out of 5 stars', 'woocommerce' ) . '</span></a>'
						. '</span></p>'
						. '<input type="hidden" name="rating" id="rating" ' . ( wc_review_ratings_required() ? 'data-required="1"' : '' ) . ' />'
						. ( wc_review_ratings_required() ? '<p class="rating-required-error yekan-16" style="display:none; color:#d00;">' . esc_html__( 'Please select a star rating.', 'woocommerce' ) . '</p>' : '' )
					. '</div>';
				}

				$comment_form['comment_field'] .= '<div class="comment-form-field flex flex-col comment-form-comment"><label for="comment" class="yekan-18 color-black-80">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></div>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
