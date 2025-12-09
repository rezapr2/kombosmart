<?php

/**
 * Template Name: Contact Us
 * Description: A custom Contact Us page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();

$form = get_field('form', $page_id);

?>
<div class="container mb-25">
    <div class="bg-black-03 py-25 px-30">
        <div class="contact-hero flex">
            <h1 class="yekan-34 color-black bold"><?php the_title(); ?></h1>
        </div>
        <div class="flex">
            <div class="flex flex-col">

            </div>
            <div class="contact-form">
                <?php
                // Render Contact Form 7 if a form field is provided.
                $form_id = 0;
                if (is_numeric($form)) {
                    $form_id = absint($form);
                } elseif (is_object($form) && isset($form->ID)) {
                    $form_id = absint($form->ID);
                }

                if ($form_id && shortcode_exists('contact-form-7')) :
                    echo do_shortcode('[contact-form-7 id="' . $form_id . '" title="فرم تماس با ما"]');
                elseif ($form_id) :
                    // CF7 not active; show a basic fallback message.
                    echo '<p class="yekan-16 color-black-60">' . esc_html__('Contact form plugin is not active.', 'tanilchoob') . '</p>';
                else :
                    echo '<p class="yekan-16 color-black-60">' . esc_html__('No contact form selected for this page.', 'tanilchoob') . '</p>';
                endif;
                ?>
            </div>
        </div>
    </div>

</div>

<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */