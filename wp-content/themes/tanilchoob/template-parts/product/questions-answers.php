<?php
/**
 * Product Questions & Answers
 *
 * Displays customer questions (stored as posts of type 'product_question') and answers
 * (child posts of those questions), along with a question submission form.
 */

defined('ABSPATH') || exit;

global $product;

$post_id = get_the_ID();

// Fetch published top-level questions for this product from CPT.
$questions_query = new WP_Query([
    'post_type'      => 'product_question',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'post_parent'    => 0,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        [
            'key'     => 'product_id',
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
            <ul class="qa-list">
                <?php foreach ($questions as $question) : ?>
                    <li id="question-<?php echo (int) $question->ID; ?>" class="qa-item">
                        <div class="qa-item-header flex items-center justify-between">
                            <div class="yekan-16 color-black-60 qa-author">
                                <?php
                                $qa_name = get_post_meta($question->ID, 'qa_name', true);
                                echo esc_html($qa_name ?: __('کاربر', 'tanilchoob'));
                                ?>
                            </div>
                            <div class="yekan-14 color-black-40 qa-date">
                                <?php echo esc_html(get_the_date('', $question)); ?>
                            </div>
                        </div>
                        <div class="qa-question yekan-18 color-black-80">
                            <?php echo wp_kses_post($question->post_content); ?>
                        </div>

                        <?php
                        // Fetch published answers (child posts) for this question.
                        $answers_query = new WP_Query([
                            'post_type'      => 'product_question',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'post_parent'    => $question->ID,
                            'orderby'        => 'date',
                            'order'          => 'ASC',
                        ]);
                        $answers = $answers_query->posts;
                        if (!empty($answers)) :
                        ?>
                            <div class="qa-answers">
                                <?php foreach ($answers as $answer) : ?>
                                    <div id="answer-<?php echo (int) $answer->ID; ?>" class="qa-answer-item">
                                        <div class="qa-answer-header flex items-center justify-between">
                                            <div class="yekan-16 color-black-60 qa-author">
                                                <?php
                                                $answer_author = get_the_author_meta('display_name', $answer->post_author);
                                                echo esc_html($answer_author ?: __('پاسخ', 'tanilchoob'));
                                                ?>
                                            </div>
                                            <div class="yekan-14 color-black-40 qa-date">
                                                <?php echo esc_html(get_the_date('', $answer)); ?>
                                            </div>
                                        </div>
                                        <div class="qa-answer yekan-18 color-black-80">
                                            <?php echo wp_kses_post($answer->post_content); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="yekan-18 color-black-60 w-full">هنوز سوالی ثبت نشده است.</p>
        <?php endif; ?>
    </div>

    <div id="qa_form_wrapper">
        <form id="product-qa-form" class="qa-form">
            <input type="hidden" name="product_id" value="<?php echo (int) $post_id; ?>" />
            <div class="qa-form-field flex flex-col">
                <label for="qa_name" class="yekan-18 color-black-80">نام <span class="required">*</span></label>
                <input id="qa_name" name="qa_name" type="text" autocomplete="name" required />
            </div>
            <div class="qa-form-field flex flex-col">
                <label for="qa_email" class="yekan-18 color-black-80">ایمیل</label>
                <input id="qa_email" name="qa_email" type="email" autocomplete="email" />
            </div>
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