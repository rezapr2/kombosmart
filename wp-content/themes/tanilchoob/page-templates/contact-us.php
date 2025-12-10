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
<div id="page-contact-us" class="page-template-contact-us">
    <div class="container mb-25">
        <div class="bg-black-03 py-25 px-30">
            <div class="contact-hero flex">
                <h1 class="yekan-34 color-black bold"><?php the_title(); ?></h1>
            </div>
            <div class="flex gap-20">
                <div class="flex flex-col gap-20">
                    <div class="contact-info grid grid-cols-2">
                        <?php
                        $working_hours = get_field('working_hours', $page_id);
                        if ($working_hours):
                        ?>
                            <div class="info-item flex flex-col">
                                <h3 class="yekan-22 color-black-80">ساعت کاری:</h3>
                                <p class="yekan-20 color-black-50"><?php echo $working_hours; ?></p>
                            </div>
                        <?php endif;
                        $central_branch = get_field('central_branch', $page_id);
                        if ($central_branch):
                        ?>
                            <div class="info-item flex flex-col">
                                <h3 class="yekan-22 color-black-80">فروشگاه مرکزی:</h3>
                                <p class="yekan-20 color-black-50"><?php echo $central_branch; ?></p>
                            </div>
                        <?php endif;
                        $contact_ways = get_field('contact_ways', $page_id);
                        if ($contact_ways):
                        ?>
                            <div class="info-item flex flex-col">
                                <h3 class="yekan-22 color-black-80">راه های ارتباطی:</h3>
                                <p class="yekan-20 color-black-50"><?php echo $contact_ways; ?></p>
                            </div>
                        <?php endif;
                        $factory_address = get_field('factory_address', $page_id);
                        if ($factory_address):
                        ?>
                            <div class="info-item flex flex-col">
                                <h3 class="yekan-22 color-black-80">نشانی کارخانه:</h3>
                                <p class="yekan-20 color-black-50"><?php echo $factory_address; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $boxarea_text = get_field('boxarea_text', $page_id);
                    if ($boxarea_text):
                    ?>
                        <div class="contact-desc yekan-20 text-center">
                            <?php echo $boxarea_text; ?>
                        </div>
                    <?php endif; ?>
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
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */