<?php
/**
 * Product Questions & Answers
 *
 * Displays customer questions (stored as posts of type 'product_question') and answers
 * (child posts of those questions), along with a question submission form.
 */

defined('ABSPATH') || exit;

$post_id = get_the_ID();

// Fetch published top-level questions for this product from CPT.
$questions_query = new WP_Query([
    'post_type'      => 'product_questions',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        [
            'key'     => 'product',
            'value'   => $post_id,
            'compare' => '=',
            'type'    => 'NUMERIC',
        ],
    ],
]);

$questions = $questions_query->posts;
?>
<div class="product-qa flex flex-col items-center">
    <div class="submit-question flex items-center justify-between w-full">
        <div class="flex flex-col">
            <h3 class="yekan-18 color-black-60">سوالتان درباره این محصول را بپرسید.</h3>
            <p class="yekan-18 color-black-40">بدون نیاز به ورود، سوالات خود را ثبت کنید و پاسخ‌ها را مشاهده کنید.</p>
        </div>
        <a href="#qa_form_wrapper" class="submit-button yekan-18 color-white bg-black">ثبت پرسش</a>
    </div>

    <div class="qa_list w-full">
        <?php if (!empty($questions)) : ?>
            <ul class="qa-list py-25 flex flex-col gap-07">
                <?php foreach ($questions as $question) : ?>
                    <li id="question-<?php echo (int) $question->ID; ?>" class="qa-item bg-white flex items-stretch">
                        <div class="qa-question-icon flex item-center">
                            <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24.082 26.1091H18.4154L12.1112 30.3024C11.1762 30.9257 9.91536 30.26 9.91536 29.1266V26.1091C5.66536 26.1091 2.83203 23.2758 2.83203 19.0258V10.5257C2.83203 6.27572 5.66536 3.44238 9.91536 3.44238H24.082C28.332 3.44238 31.1654 6.27572 31.1654 10.5257V19.0258C31.1654 23.2758 28.332 26.1091 24.082 26.1091Z" stroke="#5D0E87" stroke-width="2.125" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17.0001 16.0928V15.7953C17.0001 14.832 17.5951 14.322 18.1901 13.9111C18.7709 13.5145 19.3517 13.0045 19.3517 12.0695C19.3517 10.7662 18.3034 9.71777 17.0001 9.71777C15.6967 9.71777 14.6484 10.7662 14.6484 12.0695" stroke="#5D0E87" stroke-width="2.125" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.9949 19.4788H17.0077" stroke="#5D0E87" stroke-width="2.125" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="qa-question flex-1 flex items-center yekan-18 color-black-70">
                            <?php echo wp_kses_post($question->post_content); ?>
                        </div>
                        <div class="yekan-14 flex items-center color-black-40 qa-date px-25">
                            <?php echo esc_html(get_the_date('', $question)); ?>
                        </div>
                    </li>
                    <?php
                        $answer_text = get_field('answer_text', $question->ID);
                        if (!empty($answer_text)) :
                    ?>
                    <li id="answer-<?php echo (int) $question->ID; ?>" class="qa-item bg-white flex items-stretch mb-10">
                        <div class="qa-answer-icon flex item-center">
                            <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.4136 24.8057V23.2107C8.25109 21.2995 5.65234 17.5732 5.65234 13.6132C5.65234 6.80698 11.9086 1.47198 18.9761 3.01198C22.0836 3.69948 24.8061 5.76198 26.2223 8.60823C29.0961 14.3832 26.0711 20.5157 21.6298 23.197V24.792C21.6298 25.1907 21.7811 26.112 20.3098 26.112H12.7336C11.2211 26.1257 11.4136 25.5345 11.4136 24.8057Z" stroke="black" stroke-width="2.0625" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.6875 30.2504C14.8362 29.3566 18.1637 29.3566 21.3125 30.2504" stroke="black" stroke-width="2.0625" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="qa-question flex-1 flex items-center yekan-18 color-black-50">
                            <?php echo wp_kses_post($answer_text); ?>
                        </div>
                        <div class="px-25">
                        </div>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="yekan-18 color-black-60 w-full">هنوز سوالی ثبت نشده است.</p>
        <?php endif; ?>
    </div>

    <div id="qa_form_wrapper">
        <form id="product-qa-form" class="qa-form">
            <input type="hidden" name="product_id" value="<?php echo (int) $post_id; ?>" />
            <?php if ( is_user_logged_in() ) :
                $current_user = wp_get_current_user();
                $display_name = $current_user->display_name ?: $current_user->user_login;
                $user_email   = $current_user->user_email;
            ?>
                <div class="qa-form-field flex flex-col">
                    <label class="yekan-18 color-black-80">ارسال با حساب</label>
                    <div class="yekan-18 color-black-60">
                        <?php echo esc_html( $display_name ); ?>
                        <span class="yekan-16 color-black-40">(<?php echo esc_html( $user_email ); ?>)</span>
                    </div>
                </div>
                <input type="hidden" name="qa_name" value="<?php echo esc_attr( $display_name ); ?>" />
                <input type="hidden" name="qa_email" value="<?php echo esc_attr( $user_email ); ?>" />
            <?php else : ?>
                <div class="qa-form-field flex flex-col">
                    <label for="qa_name" class="yekan-18 color-black-80">نام <span class="required">*</span></label>
                    <input id="qa_name" name="qa_name" type="text" autocomplete="name" required />
                </div>
                <div class="qa-form-field flex flex-col">
                    <label for="qa_email" class="yekan-18 color-black-80">ایمیل</label>
                    <input id="qa_email" name="qa_email" type="email" autocomplete="email" />
                </div>
            <?php endif; ?>
            <div class="qa-form-field flex flex-col">
                <label for="qa_question" class="yekan-18 color-black-80">سوال شما <span class="required">*</span></label>
                <textarea id="qa_question" name="qa_question" cols="45" rows="6" required></textarea>
            </div>
            <div class="form-submit">
                <button type="submit" class="submit yekan-18">ثبت پرسش</button>
            </div>
            <div class="qa-form-message yekan-16" style="margin-top:0.8rem;"></div>
        </form>
    </div>

    <div class="clear"></div>
</div>