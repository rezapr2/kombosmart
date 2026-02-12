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
    <section class="hero container flex flex-col gap-10 items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-12 md:yekan-20 text-center"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?>
        </div>
    </section>
    <section class="container">
        <div class="top_boxes grid grid-cols-1 md:grid-cols-2 gap-10 mt-40">
            <?php if ($boxes): ?>
                <?php foreach ($boxes as $box): ?>
                    <div class="box flex-1 flex flex-col gap-07 bg-black-03">
                        <div class="title yekan-20 md:yekan-28 bold color-primary text-center md:text-right"><?php echo $box['title'] ?: ''; ?></div>
                        <div class="description yekan-16 md:yekan-20 color-black-70 text-center md:text-right"><?php echo $box['description'] ?: ''; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <section class="container flex flex-col gap-20">
        <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40">
            شرایط استفاده از گارانتی
        </h2>

        <div class="warranty_terms flex items-center gap-20 yekan-16 text-center md:text-right md:yekan-22">
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
								<?php
									$img_url   = isset($image['url']) ? $image['url'] : '';
									$img_alt   = isset($image['alt']) ? $image['alt'] : '';
									$thumb_url = isset($image['sizes']['thumbnail']) ? $image['sizes']['thumbnail'] : $img_url;
									$story_payload = array(
										'title'         => $img_alt,
										'subtitle'      => '',
										'content_type'  => 'image',
										'image'         => array('url' => $img_url),
										'thumbnail_url' => $thumb_url,
									);
								?>
								<a href="#" class="story-item" data-story-content="<?php echo esc_attr(wp_json_encode($story_payload)); ?>">
									<img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>">
								</a>
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
            <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-40">
                فرم ثبت درخواست گارانتی
            </h2>
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
            <h2 class="page_blocks_title text-center yekan-18 md:yekan-28 bold color-primary mt-20 md:mt-40">
                سوالات متداول درباره گارانتی
            </h2>

            <div class="warranty_faqs flex flex-col gap-20">
                <?php foreach ($warranty_faqs as $faq): ?>
                    <div class="faq-item slide-down-wrapper flex flex-col gap-04 md:gap-20">
                        <div
                            class="faq-question slide-down-trigger  flex justify-between items-center cursor-pointer">
                            <h3 class="regular yekan-14 md:yekan-24 color-black-80"><?php echo $faq['question']; ?></h3>
                        </div>
                        <div class="faq-answer slide-down-content yekan-12 md:yekan-20 text-center md:text-right color-black-70" style="display: none;">
                            <p><?php echo $faq['answer']; ?></p>
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
