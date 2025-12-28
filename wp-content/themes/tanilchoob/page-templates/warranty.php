<?php

/**
 * Template Name: Warranty
 * Description: A custom Warranty page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();
$top_boxes = get_field('top_boxes', $page_id) ?: [];
$boxes = isset($top_boxes['boxes']) ? $top_boxes['boxes'] : [];
?>
<div id="page-warranty" class="page-template-warranty">
    <section class="hero container flex flex-col items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-20"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?>
        </div>
    </section>
    <section class="container">
        <div class="top_boxes grid grid-cols-2 gap-10 mt-40">
            <?php if ($boxes): ?>
                <?php foreach ($boxes as $box): ?>
                    <div class="box flex-1 flex flex-col gap-07 bg-black-03">
                        <div class="title yekan-28 bold color-primary"><?php echo $box['title'] ?: ''; ?></div>
                        <div class="description yekan-20 color-black-70"><?php echo $box['description'] ?: ''; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <section class="container flex flex-col gap-20">
        <div class="page_blocks_title text-center yekan-28 bold color-primary mt-40">
            شرایط استفاده از گارانتی
        </div>

        <div class="warranty_terms flex items-center gap-20 yekan-22">
            <div class="text color-black-70">
                <?php echo get_field('warranty_terms', $page_id) ?: ''; ?>
            </div>
        </div>

    </section>
    <section >
       

        <div class="warranty_gallery mt-30">
            <?php if (get_field('warranty_gallery', $page_id)) : ?>
                <div class="swiper">
				<div class="swiper-wrapper">
					<?php foreach (get_field('warranty_gallery', $page_id) as $image) : ?>
							<div class="swiper-slide">
								<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
							</div>
							<?php endforeach; ?>
		    				</div>

					</div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    $form_id = get_field('form_id', $page_id) ?: [];
    if ($form_id):
        ?>
        <section class="container flex flex-col gap-20">
            <div class="page_blocks_title text-center yekan-28 bold color-primary mt-40">
                فرم ثبت درخواست گارانتی
            </div>
            <div class="contact-form">
                <?php
                if (shortcode_exists('contact-form-7')) {
                    echo do_shortcode('[contact-form-7 id="' . $form_id . '"]');
                }
                ?>
            </div>

        </section>
    <?php endif; ?>
    <?php
    $warranty_faqs = get_field('warranty_faqs', $page_id) ?: [];
    if ($warranty_faqs):
        ?>
        <section class="container flex flex-col gap-20">
            <div class="page_blocks_title text-center yekan-28 bold color-primary mt-40">
                سوالات متداول درباره گارانتی
            </div>

            <div class="warranty_faqs flex flex-col gap-20">
                <?php foreach ($warranty_faqs as $faq): ?>
                    <div class="faq-item slide-down-wrapper flex flex-col gap-20">
                        <div
                            class="faq-question slide-down-trigger yekan-24 color-black-80 flex justify-between items-center cursor-pointer">
                            <span><?php echo $faq['question']; ?></span>
                        </div>
                        <div class="faq-answer slide-down-content yekan-20 color-black-70" style="display: none;">
                            <?php echo $faq['answer']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>


</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */