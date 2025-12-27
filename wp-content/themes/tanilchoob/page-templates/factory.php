<?php

/**
 * Template Name: Factory
 * Description: A custom Factory page template with info and form.
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

$about_factory_text = get_field('about_factory_text', $page_id);
$about_factory_video_poster = get_field('about_factory_video_poster', $page_id);
$about_factory_video_url = get_field('about_factory_video_url', $page_id);
$page_blocks = get_field('page_blocks', $page_id);
?>
<div id="page-factory" class="page-template-factory">
    <section class="hero container flex flex-col items-center">
        <h1 class="title color-primary bold relative"><?php the_title(); ?></h1>
        <div class="hero_subtitle color-black-70 yekan-20"><?php echo get_field('hero_subtitle', $page_id) ?: ''; ?></div>
    </section>
    <section class="flex flex-col gap-07">
        <div class="content-box tanil-story flex items-center gap-20">
            <div class="story-box flex-1 flex flex-col gap-07">
                <h2 class="title color-black regular yekan-28 ">درباره کارخانه تانیل چوب</h2>
                <div class="description yekan-18 color-black-50">
                    <?php echo $about_factory_text ? $about_factory_text : ''; ?>
                </div>
            </div>
            <?php if (isset($about_factory_video_url)): ?>
            <div class="hero_video relative ">
                <div class="dark-overlay z-index-1"></div>
                <a class=" relative video-lightbox" data-video-url="<?php echo esc_url($about_factory_video_url); ?>">
                    <img class="poster object-cover flex w-100" src="<?php echo esc_url($about_factory_video_poster['url']); ?>"
                        alt="<?php echo esc_attr($page_title); ?>">
                    <div class="absolute center z-index-5">
                        <img class="" src="<?php echo Helper::getAssetUri('images/play_icon.svg'); ?>" alt="Play Icon">
                    </div>
                </a>
            </div>
        <?php endif; ?>
        </div>
        
    </section>
    <section class="container flex flex-col gap-20">
        <div class="page_blocks_title text-center yekan-28 bold color-primary mt-30">
            بخش هاى مختلف كارخانه تانيل چوب
        </div>
        <?php
        if ($page_blocks) :
            foreach ($page_blocks as $index => $block) :
                // Apply reverse flex direction on odd items (1-based indexing)
                $is_odd = (($index + 1) % 2 === 1);
                $reverse_class = $is_odd ? 'flex-row-reverse' : ''; ?>
                <div class="content-box flex items-center gap-20 <?php echo $reverse_class; ?>">
                    <div class="story-box flex-1 flex flex-col gap-07">
                        <h2 class="title color-primary yekan-25 ">
                            <?php
                            if (isset($block['title_url'])) {
                                echo '<a class="color-primary" href="' . $block['title_url'] . '" target="_blank">' . $block['title'] . '</a>';
                            } else {
                                echo $block['title'] ?: '';
                            }
                            ?>
                        </h2>
                        <div class="description yekan-16 color-black-70">
                            <?php echo $block['text'] ?: ''; ?>
                        </div>
                    </div>
                    <div class="image-box">
                        <?php if ($block['image']) : ?>
                            <img class="block h-100 object-cover" src="<?php echo $block['image']['url']; ?>" alt="<?php echo $block['image']['alt'] ?: 'tanil choob story image'; ?>">
                        <?php endif; ?>
                    </div>
                </div>
        <?php endforeach;
        endif;
        ?>
    </section>
    <section class="container flex flex-col gap-20">
        <div class="page_blocks_title text-center yekan-28 bold color-primary mt-30">
            ارتباط با کارخانه
        </div>
        <?php
        $address = get_field('factory_address', $page_id);
        ?>
        <div class="flex gap-20">
                <div class="address-text yekan-18 color-black-80">
                    <?php echo $address['address']; ?>
                </div>
                <div class="address-on-apps flex flex-shrink-0 items-center gap-30">
                    <label class="yekan-18 color-black-80 flex-shrink-0" for="address-on-apps">مسیریابی با:</label>
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
    </section>


</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */