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
<div id="page-contact-us" class="container page-template-contact-us ">
    <div class="bg-black-03 py-25 px-30 mb-25">
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
    <div class="bg-black-03 py-25 px-30 mb-25">
        <div class="contact-hero flex">
            <h1 class="yekan-34 color-black bold">آدرس های ما</h1>
        </div>
        <?php
            $our_addresses = get_field('our_addresses', $page_id);
            if($our_addresses):
                foreach($our_addresses as $address):
        ?>
        <div class="address-item flex flex-col items-center mb-40">
            <div class="address-title yekan-28 color-black-80 mb-40"><?php echo $address['title']; ?></div>
            <div class="flex gap-20">
                <div class="address-text yekan-22 color-black-80">
                    <?php echo $address['address']; ?>
                </div>
                <div class="address-on-apps flex items-center gap-30">
                    <label class="yekan-22 color-black-80 flex-shrink-0" for="address-on-apps">مسیریابی با:</label>
                    <div class="apps flex items-center justify-between w-100">
                        <?php if($address['google_map']): ?>
                        <a href="<?php echo $address['google_map']; ?>" target="_blank" class="app-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_googlemaps.png" alt="Google Maps">
                        </a>
                        <?php endif; 
                        if($address['neshan']):
                        ?>
                        <a href="<?php echo $address['neshan']; ?>" target="_blank" class="app-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_neshan.png" alt="Neshan">
                        </a>
                        <?php endif; 
                        if($address['waze']):
                        ?>
                        <a href="<?php echo $address['waze']; ?>" target="_blank" class="app-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_waze.png" alt="Waze">
                        </a>
                        <?php endif; 
                        if($address['balad']):
                        ?>
                        <a href="<?php echo $address['balad']; ?>" target="_blank" class="app-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/frontend/dist/images/icon_balad.png" alt="Balad">
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */