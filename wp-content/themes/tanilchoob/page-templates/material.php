<?php

/**
 * Template Name: Material
 * Description: A custom Material page template with info and form.
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
$why_material_text = get_field('why_material_text', $page_id);
$why_material_image = get_field('why_material_image', $page_id);
$page_blocks = get_field('page_blocks', $page_id);
?>
<div id="page-material" class="page-template-material">
    <section class="hero container flex flex-col items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-20"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?></div>
    </section>
    <section class="container flex flex-col gap-20">
        <div class="page_blocks_title text-center yekan-28 bold color-primary mt-30">
            چرا متريال اهميت دارد؟
        </div>

        <div class="why_material flex items-center gap-20">
                <div class="text color-black-70 yekan-22">
                    <?php echo $why_material_text ?: ''; ?>
                </div>
                <div class="image-box flex-shrink-0">
                    <?php if ($why_material_image) : ?>
                        <img class="block h-100 object-cover" src="<?php echo $why_material_image['url']; ?>" alt="<?php echo $why_material_image['alt'] ?: 'why material image'; ?>">
                    <?php endif; ?>
                </div>
        </div>
    </section>
    <section class="flex flex-col gap-40 mt-40">
        
        <?php
        if ($page_blocks) :
            foreach ($page_blocks as $index => $block) : ?>
                <div class="content-box bg-black-03">
                    <div class="container flex items-center gap-20">
                        <div class="story-box flex-1 flex flex-col gap-20">
                            <div class="title_wrapper flex items-center gap-07">
                                <h2 class="title color-primary yekan-28 ">
                                    <?php
                                    if (isset($block['title_url'])) {
                                        echo '<a class="color-primary" href="' . $block['title_url'] . '" target="_blank">' . $block['title'] . '</a>';
                                    } else {
                                        echo $block['title'] ?: '';
                                    }

                                    
                                    ?>
                                </h2>
                                <?php if(isset($block['subtitle'])) { ?>
                                    <span class="color-primary yekan-28">|</span>
                                    <div class="subtitle yekan-18 color-black-60">
                                        <?php echo $block['subtitle'] ?: ''; ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="description yekan-24 color-black-70">
                                <?php echo $block['text'] ?: ''; ?>
                            </div>

                                <?php
                                $features = isset($block['features']) ? $block['features'] : [];
                                if ($features) : ?>
                                    <div class="features mt-25">
                                        <ul class="flex items-center gap-10">
                                            <?php foreach ($features as $feature) : ?>
                                                <li class="feature yekan-14 color-primary"><?php echo $feature['item_text'] ?: ''; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                        </div>
                        <div class="image-box relative">
                            <?php if ($block['image']) : ?>
                                <img class="block h-100 object-cover" src="<?php echo $block['image']['url']; ?>" alt="<?php echo $block['image']['alt'] ?: 'tanil choob story image'; ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        <?php endforeach;
        endif;
        ?>
    </section>
    <section class="call-us flex flex-col items-center mt-40">
        <div class="title yekan-30 bold color-primary">
            برای انتخاب بهتر، با مشاوران ما تماس بکیريد
        </div>
        <div class="subtitle yekan-24 color-black-60">
            ما آماده ايم تا متريال هاى مختلف را به شما نشان دهيم ودر انتخاب بهترين كزينه راهنمایى تان كنيم.
        </div>
        <div class="button mt-25">
            <a href="<?php echo get_field('button_url', $page_id) ?: '#'; ?>" class="yekan-20 color-white bg-primary block">
                <?php echo get_field('button_text', $page_id) ?: 'تماس با ما'; ?>
            </a>
        </div>
    </section>


</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */