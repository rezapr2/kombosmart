<?php

/**
 * The template for displaying the page
 *
 * template name: Homepage
 */


get_header(); ?>

<div class="home-page wrapper">
    <?php
    $page_id = get_the_ID();

    /* Stories */
    get_template_part('template-parts/home/stories');

    /* Hero Slider */
    get_template_part('template-parts/home/hero_slider');

    /* Features */
    get_template_part('template-parts/home/features');

    /* Product Categories */
    get_template_part('template-parts/home/product_categories');

    /* Banners */
    $args = [
        'banners' => get_field('banners_after_categories', $page_id),
    ];
    get_template_part('template-parts/home/banners', null, $args);

    /* Special Offers Products Slider */
    $special_offers_term = get_field('special_offers_term', $page_id);
    $args = [
        'title' => '<span class="yekan-20 color-white"><strong>فروش </strong><span class="thin">ویژه</span></span>',
        'button_link' => '#',
        'card' => 'product-card',
        'wrapper_class' => 'mb-40',
        'slidesPerView' => 3.5,
        'type' => 'special_offers',
        'color' => 'white',
        'query' => [
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $special_offers_term
                ]
            ]
        ]
    ];
    get_template_part('template-parts/slider/carousel_slider', null, $args);

    /* Banners */
    $args = [
        'banners' => get_field('banners_after_offer_sales', $page_id),
    ];
    get_template_part('template-parts/home/banners', null, $args);

    /* Soffa Products Slider */
    $soffa_term = get_field('soffa_term', $page_id);
    $args = [
        'title' => '<span class="yekan-20 color-black-80"><strong>مبلمان</strong></span>',
        'button_link' => '#',
        'card' => 'product-card',
        'wrapper_class' => 'mb-40',
        'slidesPerView' => 3.5,
        'query' => [
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $soffa_term
                ]
            ]
        ]
    ];
    get_template_part('template-parts/slider/carousel_slider', null, $args);

    /* Banners */
    $args = [
        'banners' => get_field('banners_after_soffa_products', $page_id),
    ];
    get_template_part('template-parts/home/banners', null, $args);

    /* Sleep Pack Products Slider */
    $sleep_pack_term = get_field('sleep_pack_term', $page_id);
    $args = [
        'title' => '<span class="yekan-20 color-black-80"><strong>سرویس </strong><span class="thin">خواب</span></span>',
        'button_link' => '#',
        'card' => 'product-card',
        'wrapper_class' => 'mb-40',
        'slidesPerView' => 3.5,
        'query' => [
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $sleep_pack_term
                ]
            ]
        ]
    ];
    get_template_part('template-parts/slider/carousel_slider', null, $args);

    /* Best Toshaks */
    get_template_part('template-parts/home/best_toshaks');

    /* About tanil */
    get_template_part('template-parts/home/about_tanil');

    /* Article Slider */
    $args = [
        'title' => '<span class="yekan-20 color-black-80"><strong>جدیدترین </strong><span class="thin">مقاله</span></span>',
        'button_link' => '#',
        'card' => 'article-card',
        'slidesPerView' => 2.2,
        'query' => [
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]
    ];
    get_template_part('template-parts/slider/carousel_slider', null, $args);
    ?>


</div>

<?php get_footer(); ?>