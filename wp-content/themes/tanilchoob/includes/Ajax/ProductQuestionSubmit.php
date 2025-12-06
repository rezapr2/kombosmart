<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Abstracts\AjaxHandler;

class ProductQuestionSubmit extends AjaxHandler
{
    public function __construct()
    {
        parent::__construct('product_question_submit');
    }

    public function handler()
    {
        $this->handle_nonce();
        $this->validate_recaptcha();

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $author     = isset($_POST['qa_name']) ? sanitize_text_field($_POST['qa_name']) : '';
        $email      = isset($_POST['qa_email']) ? sanitize_email($_POST['qa_email']) : '';
        $question   = isset($_POST['qa_question']) ? sanitize_textarea_field($_POST['qa_question']) : '';

        if ($product_id <= 0 || get_post_type($product_id) !== 'product') {
            wp_send_json_error(['message' => __('شناسه محصول معتبر نیست.', 'tanilchoob')]);
        }

        if ($author === '' || $question === '') {
            wp_send_json_error(['message' => __('نام و متن سوال الزامی است.', 'tanilchoob')]);
        }

        // Create a new Product Question post (pending for moderation).
        $title_seed = wp_strip_all_tags($question);
        if (function_exists('mb_strlen') && mb_strlen($title_seed) > 60) {
            $title_seed = mb_substr($title_seed, 0, 60) . '…';
        } elseif (strlen($title_seed) > 60) {
            $title_seed = substr($title_seed, 0, 60) . '…';
        }
        $post_title = !empty($title_seed) ? $title_seed : __('پرسش درباره محصول', 'tanilchoob');

        $postarr = [
            'post_type'    => 'product_question',
            'post_status'  => 'pending',
            'post_parent'  => 0,
            'post_title'   => $post_title,
            'post_content' => $question,
        ];

        $question_post_id = wp_insert_post($postarr, true);

        if (is_wp_error($question_post_id) || !$question_post_id) {
            wp_send_json_error(['message' => __('ارسال پرسش با خطا مواجه شد.', 'tanilchoob')]);
        }

        // Save metadata to associate with product and optional contact details.
        update_post_meta($question_post_id, 'product_id', $product_id);
        if (!empty($author)) {
            update_post_meta($question_post_id, 'qa_name', $author);
        }
        if (!empty($email)) {
            update_post_meta($question_post_id, 'qa_email', $email);
        }

        wp_send_json_success(['message' => __('سوال شما دریافت شد و پس از بررسی منتشر می‌شود.', 'tanilchoob')]);
    }
}

// Initialize handler
new ProductQuestionSubmit();