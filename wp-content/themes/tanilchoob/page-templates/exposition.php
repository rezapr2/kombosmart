<?php

/**
 * Template Name: Exposition
 * Description: A custom Exposition page template with info and form.
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

$page_title = get_field('page_title', $page_id);

$top_video = get_field('top_video', $page_id);
$content_boxes = get_field('content_boxes', $page_id);
$exposition_boxes = get_field('exposition_boxes', $page_id);
?>
<div id="page-exposition" class="page-template-exposition mt-25">
    <section class="flex flex-col">
        <h1 class="yekan-34 bold text-center color-black-80"><?php echo $page_title ? $page_title : ''; ?></h1>
        <?php if (isset($top_video['video_link'])): ?>
            <div class="hero_video relative mt-25 w-100">
                <div class="dark-overlay z-index-1"></div>
                <a class=" relative video-lightbox" data-video-url="<?php echo esc_url($top_video['video_link']); ?>">
                    <img class="poster object-cover flex w-100" src="<?php echo esc_url($top_video['poster']['url']); ?>"
                        alt="<?php echo esc_attr($page_title); ?>">
                    <div class="absolute center z-index-5">
                        <img class="" src="<?php echo Helper::getAssetUri('images/play_icon.svg'); ?>" alt="Play Icon">
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </section>
    <section class="container content_boxes flex flex-col gap-20 mt-25">
        <?php
        if ($content_boxes):
            foreach ($content_boxes as $index => $block): ?>
                <div class="content-box flex flex-col  gap-10 bg-black-03">
                    <h2 class="title color-black-80 yekan-24 ">
                        <?php
                        echo $block['title'] ?: '';
                        ?>
                    </h2>
                    <div class="divider"></div>
                    <div class="content yekan-20 color-black-60">
                        <?php echo $block['content'] ?: ''; ?>
                    </div>
                </div>
            <?php endforeach;
        endif;
        ?>
    </section>
    <?php if ($exposition_boxes): ?>
        <section class="container exposition_boxes flex flex-col gap-20 mt-25">
            <?php
            if ($exposition_boxes):
                foreach ($exposition_boxes as $index => $block): ?>
                    <div class="exposition_box flex flex-col">
                        <h2 class="title color-primary yekan-28 text-center regular">
                            <?php
                            echo $block['title'] ?: '';
                            ?>
                        </h2>
                        <div class="flex content-box gap-20 ">
                            <?php
                                if(isset($block['image']['url'])):
                            ?>
                            <img class="object-cover box-image" src="<?php echo $block['image']['url']; ?>" alt="<?php echo $block['image']['alt']; ?>">
                            <?php endif; ?>
                            <div class="flex flex-col gap-10 mt-10">
                                <div class="subtitle yekan-24 bold color-black-80">
                                    <?php echo $block['subtitle'] ?: ''; ?>
                                </div>
                                <div class="content yekan-20 color-black-70">
                                    <?php echo $block['content'] ?: ''; ?>
                                </div>
                                <?php if(isset($block['button'])): ?>
                                <a href="<?php echo $block['button']['url']; ?>" class="link yekan-18 color-black mt-10">
                                    <?php echo $block['button']['title']; ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach;
            endif;
            ?>
        </section>
    <?php endif; ?>


</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */