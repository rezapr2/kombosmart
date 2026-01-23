<?php

/**
 * Template Name: About Us
 * Description: A custom About Us page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


$page_id = get_queried_object_id();

$tanilchoob_story = get_field('tanilchoob_story', $page_id);
$tanilchoob_story_image = get_field('tanilchoob_story_image', $page_id);
$page_blocks = get_field('page_blocks', $page_id);
?>
<div id="page-about-us" class="page-template-about-us md:mt-30">
    <div class="container">
<section class="flex flex-col gap-07 md:gap-20">
        <div class="content-box tanil-story flex flex-col-reverse md:flex-row items-center gap-20 md:gap-07">
            <div class="story-box flex-1 flex flex-col gap-07">
                <h2 class="title color-primary yekan-26 text-center md:text-right">داستان تانیل چوپ</h2>
                <div class="description yekan-18 color-black-80 text-center md:text-right">
                    <?php echo $tanilchoob_story ? $tanilchoob_story : ''; ?>
                </div>
            </div>
            <div class="image-box">
                <?php if ($tanilchoob_story_image) : ?>
                    <img class="block h-100 object-cover" src="<?php echo $tanilchoob_story_image['url']; ?>" alt="<?php echo $tanilchoob_story_image['alt'] ?: 'tanil choob story image'; ?>">
                <?php endif; ?>
            </div>
        </div>
        <?php
        if ($page_blocks) :
            foreach ($page_blocks as $index => $block) :
                // Apply reverse flex direction on odd items (1-based indexing)
                $is_odd = (($index + 1) % 2 === 1);
                $reverse_class = $is_odd ? 'md:flex-row-reverse' : 'md:flex-row'; ?>
                <div class="content-box flex flex-col-reverse items-center md:gap-20 <?php echo $reverse_class; ?>">
                    <div class="story-box flex-1 flex flex-col gap-07">
                        <h2 class="title color-primary yekan-25 text-center md:text-right">
                            <?php
                            if (isset($block['title_url'])) {
                                echo '<a class="color-primary" href="' . $block['title_url'] . '" target="_blank">' . $block['title'] . '</a>';
                            } else {
                                echo $block['title'] ?: '';
                            }
                            ?>
                        </h2>
                        <div class="description yekan-16 color-black-70 text-center md:text-right">
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
    <section class="positive_of_tanil flex flex-col mt-60 md:px-80">
        <div class="title yekan-22 md:yekan-24 color-black thin text-center color-black-80">
            <strong class="color-primary">ویژگی های مثبت </strong> تانیل چوب
        </div>
        <?php
        $positive_subtitle_text = get_field('positive_subtitle_text', $page_id);
        ?>
        <div class="description yekan-14 md:yekan-16 color-black-60 text-center">
            <?php echo $positive_subtitle_text ? $positive_subtitle_text : ''; ?>
        </div>
        <div class="positive_items_list flex flex-col mt-30">
            <?php
            $positive_items_list = get_field('positive_items_list', $page_id);
            if ($positive_items_list) :
                foreach ($positive_items_list as $item) :
            ?>
                    <div class="positive_item flex items-center justify-between">
                        <div class="title yekan-14 md:yekan-30 color-black-70">
                            <?php echo $item['title'] ?: ''; ?>
                        </div>
                        <div class="icon">
                            <?php if ($item['icon']) : ?>
                                <img class="block h-100 object-cover" src="<?php echo $item['icon']['url']; ?>" alt="<?php echo $item['icon']['alt'] ?: 'tanil choob positive item icon'; ?>">
                            <?php endif; ?>
                        </div>

                    </div>
            <?php endforeach;
            endif;
            ?>
        </div>
    </section>
    <section class="honors_of_tanil flex flex-col mt-60 md:px-80">
        <div class="title yekan-22 md:yekan-24 color-black thin text-center color-black-80">
            <strong class="color-primary">افتخارات و گواهی های </strong> تانیل چوب
        </div>
        <?php
        $honors_subtitle_text = get_field('honors_subtitle_text', $page_id);
        ?>
        <div class="description yekan-14 md:yekan-16 color-black-60 text-center">
            <?php echo $honors_subtitle_text ? $honors_subtitle_text : ''; ?>
        </div>
        <div class="honors_items_list flex gap-05 md:gap-15 mt-30">
            <?php
            $honors = get_field('honors', $page_id);
            if ($honors) :
                foreach ($honors as $item) :
            ?>
                    <div class="honor_item flex flex-col items-center gap-20 flex-1">

                        <div class="image flex">
                            <?php if ($item['image']) : ?>
                                <img class="block object-cover" src="<?php echo $item['image']['url']; ?>" alt="<?php echo $item['image']['alt'] ?: 'tanil choob positive item image'; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="title hidden md:flex yekan-22 color-black-70 text-center mb-10">
                            <?php echo $item['title'] ?: ''; ?>
                        </div>

                    </div>
            <?php endforeach;
            endif;
            ?>
        </div>
    </section>
    </div>
        <div class="container-right md:container">

    <section class="top_products_of_tanil flex flex-col mt-60">
        <div class="title yekan-22 md:yekan-24 color-black thin text-center color-black-80">
            <strong class="color-primary">محصولات برتر </strong> تانیل چوب
        </div>
        <?php
        $top_products_subtitle_text = get_field('top_products_subtitle_text', $page_id);
        ?>
        <div class="description yekan-14 md:yekan-16 color-black-60 text-center">
            <?php echo $top_products_subtitle_text ? $top_products_subtitle_text : ''; ?>
        </div>
        <div class="top_products_items_list mt-30">
            <?php
            $top_products = get_field('top_products', $page_id);

            if ($top_products) :
                $args = [
                    'query' => [
                        'post_type' => 'product',
                        'post__in' => $top_products,
                    ],
                    'card' => 'product-card',
                ];
            ?>
                <div class="carousel_slider-wrapper" data-slidesPerView="3">

                    <?php
                    get_template_part('template-parts/slider/carousel_slider', null, $args);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
            </div>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */