<?php

$current_product_id = get_the_ID();

$complementary_products = get_field('complementary_products', $current_product_id);
$next_to_it_products = get_field('next_to_it_products', $current_product_id);
?>

<div class="container-right md:container mb-25">
    <div class="tab-contents">
        <div class="tabs flex w-full">
            <div id="tab-suggustions" class="tab-item yekan-12 md:yekan-14 color-black-30 pointer active">محصولات مشابه</div>
            <?php if($complementary_products): ?>
            <div id="tab-complementary" class="tab-item yekan-12 md:yekan-14 color-black-30 pointer ">محصولات مکمل</div>
            <?php endif; ?>
            <?php if($next_to_it_products): ?>
            <div id="tab-related" class="tab-item yekan-12 md:yekan-14 color-black-30 pointer ">در کنارش خریداری شده</div>
            <?php endif; ?>
        </div>
        <div class="tab-content flex flex-col gap-20"> 
            <div id="tab-suggustions-content" class="tab-content-item pt-10 active">
                <?php
                    /* Suggustions Products Slider */
                    $product_terms      = get_the_terms( $current_product_id, 'product_cat' );
                    $product_term_ids   = $product_terms ? wp_list_pluck( $product_terms, 'term_id' ) : [];
                    if ( ! empty( $product_terms ) ) {
                        $parent_ids = [];
                        foreach ( $product_terms as $term ) {
                            $ancestors = get_ancestors( $term->term_id, 'product_cat' );
                            if ( ! empty( $ancestors ) ) {
                                $parent_ids = array_merge( $parent_ids, $ancestors );
                            }
                        }
                        $parent_ids = array_unique( $parent_ids );
                        $leaf_ids = array_values( array_diff( $product_term_ids, $parent_ids ) );
                        if ( ! empty( $leaf_ids ) ) {
                            $product_term_ids = $leaf_ids;
                        }
                    }

                    $query_args = [
                        'post_type'           => 'product',
                        'post_status'         => 'publish',
                        'posts_per_page'      => 10,
                        'ignore_sticky_posts' => true,
                        'post__not_in'        => [ $current_product_id ],
                    ];

                    if ( ! empty( $product_term_ids ) ) {
                        $query_args['tax_query'] = [
                            [
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $product_term_ids,
                                'operator' => 'IN',
                            ],
                        ];
                    }

                    $args = [
                        'card'          => 'product-card',
                        'slidesPerView' => 3.5,
                        'query'         => $query_args,
                    ];
                    ?>
                    <div class="carousel_slider-wrapper" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
                    <?php
                        get_template_part('template-parts/slider/carousel_slider', null, $args);
                    ?>
                    </div>
            </div>
            <?php if($complementary_products): ?>
            <div id="tab-complementary-content" class="tab-content-item pt-10">
                <?php
                    /* Complementary Products Slider */
                    $args = [
                        'card'          => 'product-card',
                        'slidesPerView' => 3.5,
                        'query'         => [
                            'post_type'           => 'product',
                            'post_status'         => 'publish',
                            'ignore_sticky_posts' => true,
                            'post__in'            => $complementary_products,
                        ],
                    ];
                    ?>
                    <div class="carousel_slider-wrapper" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
                    <?php
                        get_template_part('template-parts/slider/carousel_slider', null, $args);
                    ?>
                    </div>
            </div>
            <?php endif; ?>
            <?php if($next_to_it_products): ?>
            <div id="tab-related-content" class="tab-content-item pt-10">
                <?php
                    /* Related Products Slider */
                    $args = [
                        'card'          => 'product-card',
                        'slidesPerView' => 3.5,
                        'query'         => [
                            'post_type'           => 'product',
                            'post_status'         => 'publish',
                            'ignore_sticky_posts' => true,
                            'post__in'            => $next_to_it_products,
                        ],
                    ];
                    ?>
                    <div class="carousel_slider-wrapper" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
                    <?php
                        get_template_part('template-parts/slider/carousel_slider', null, $args);
                    ?>
                    </div>
            </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>
