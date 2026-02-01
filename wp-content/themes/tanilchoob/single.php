<?php
/**
 * Single Post Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use TanilChoob\Theme\Helper;

get_header();

$page_id = get_queried_object_id();
// Sidebar Ads
$sidebar_ads = get_field('sidebar_ads', 'option'); 
if(!$sidebar_ads) {
    $sidebar_ads = get_field('sidebar_ads', $page_id);
}

 if ( function_exists('woocommerce_breadcrumb') ): ?>
        <?php woocommerce_breadcrumb(); ?>
<?php endif; ?>
<div class="single-post-container">
            <!-- Post Header -->
            <header class="post-header relative overflow-hidden flex items-end">
                        <div class="post-title-wrapper z-index-1 relative flex flex-col gap-10">
                            <h1 class="yekan-16 md:yekan-34 color-white"><?php the_title(); ?></h1>
                            <div class="post-meta flex items-center">
                                <span class="date yekan-18 color-black-80">
                                    <?php echo get_the_date(); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('full', ['class' => 'w-100 h-auto object-cover']); ?>
                            </div>
                        <?php endif; ?>
            </header>
    
    <div class="container flex flex-col md:flex-row gap-10 mt-40 items-start">
        
        <!-- Main Content -->
        <main class="main-content overflow-hidden w-100">
            <?php 
                // Get content and filter it
                $content = get_the_content();
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);

                // Pattern to match h2 and h3 tags
                $pattern = '/<h([2-3])(.*?)>(.*?)<\/h\1>/i';
                
                $headings = [];
                
                // Callback function to replace headings with id attribute and collect them
                $content_with_ids = preg_replace_callback($pattern, function($matches) use (&$headings) {
                    $level = $matches[1];
                    $attrs = $matches[2];
                    $text = strip_tags($matches[3]);
                    $id = sanitize_title($text);
                    
                    // Add to headings array
                    $headings[] = [
                        'level' => $level,
                        'text' => $text,
                        'id' => $id
                    ];
                    
                    // Return the heading with ID
                    return "<h$level id=\"$id\"$attrs>$matches[3]</h$level>";
                }, $content);
            ?>

            <?php if(!empty($headings)): ?>
            <div class="post-headings flex flex-col mb-10">
                <div class="title-wrapper flex items-center justify-between">
                    <p class="yekan-24 color-white">فهرست مطالب</p>
                    <svg  viewBox="0 0 18 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.59 0.75L10.07 7.27C9.3 8.04 8.04 8.04 7.27 7.27L0.75 0.75" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <ul class="headings-list flex flex-col gap-10 m-0 p-0">
                    <?php foreach($headings as $heading): ?>
                    <li class="flex items-center gap-10">
                         <a href="#<?php echo esc_attr($heading['id']); ?>" class="yekan-18 color-white-50 transition">
                             <?php echo esc_html($heading['text']); ?>
                         </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php 
            $top_video = get_field('top_video');
            if (isset($top_video['video_link'])): ?>
                <div class="hero_video relative w-100 mb-10">
                    <div class="dark-overlay z-index-1"></div>
                    <a class=" relative video-lightbox" data-video-url="<?php echo esc_url($top_video['video_link']); ?>">
                        <img class="poster object-cover flex w-100" src="<?php echo esc_url($top_video['video_imge']['url']); ?>"
                            alt="featured video">
                        <div class="play-icon absolute center z-index-5">
                            <img class="" src="<?php echo Helper::getAssetUri('images/play_icon.svg'); ?>" alt="Play Icon">
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <!-- Post Content -->
                    <div class="post-content yekan-22 color-black-70">
                        <?php echo $content_with_ids; ?>
                    </div>

                    <!-- Related Products -->
                    <?php 
                    $related_products = get_field('related_products');

                    if( !empty($related_products) ): 
                    ?>
                    <div class="related-products mb-40 relative w-100">
                        
                        <?php

                        $query_args = [
                            'post_type'           => 'product',
                            'post_status'         => 'publish',
                            'posts_per_page'      => count($related_products),
                            'ignore_sticky_posts' => true,
                            'post__in'            => $related_products,
                        ];

                        $args = [
                            'card'          => 'product-card',
                            'slidesPerView' => 2.5,
                            'query'         => $query_args,
                        ];
                        ?>
                        <div class="carousel_slider-wrapper z-index-1 relative" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
                        <div class="slider_header flex justify-between items-center m-0">
                            <div class="title_btn flex items-center">
                                <h3 class="title yekan-26 regular color-white m-0">محصولات مرتبط با مقاله</h3>
                            </div>
                        <div class="flex gap-10">
                            <!-- Navigation buttons -->
                            <div class="button-prev circle-radius white hidden md:flex item-center pointer transition">
                            <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
                            </div>
                            <div class="button-next circle-radius white hidden md:flex item-center pointer transition">
                            <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
                            </div>
                        </div>
                        </div>
                        <?php
                            get_template_part('template-parts/slider/carousel_slider', null, $args);
                        ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Post categories -->
                    <?php
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) :
                    ?>
                        <div class="post-categories flex items-center gap-10 mt-30 mb-30">
                            <span class="yekan-16 color-black">دسته بندی:</span>
                            <div class="flex flex-wrap gap-10">
                                <?php foreach ( $categories as $category ) : ?>
                                    <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" 
                                       class="tag yekan-14 transition bg-black-03 color-black-60">
                                        <?php echo esc_html( $category->name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Post tags -->
                    <?php
                        $tags = get_the_tags();
                        if ( ! empty( $tags ) ) :
                    ?>
                        <div class="post-tags flex items-center gap-10 mt-10 mb-30">
                            <span class="yekan-16 color-black">تگ:</span>
                            <div class="flex flex-wrap gap-10">
                                <?php foreach ( $tags as $tag ) : ?>
                                    <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" 
                                       class="tag yekan-14 transition bg-black-03 color-black-60">
                                        <?php echo esc_html( $tag->name ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Comments -->
                    <div class="comments-wrapper mt-40">
                        <?php 
                        if ( comments_open() || get_comments_number() ) :
                            comments_template();
                        endif;
                        ?>
                    </div>

                </article>
            <?php endwhile; ?>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar flex flex-col flex-shrink-0 gap-20">
            
            <!-- Readable Articles -->
            <div class="widget related-posts-widget bg-white border">
                <h3 class="widget-title yekan-30 color-black mb-25">مطالب خواندنی</h3>
                <div class="flex flex-col gap-30">
                    <?php
                    $readable_posts = get_field('readable_posts');
                    if(empty($readable_posts)){
                        $readable_posts = get_field('readable_posts', 'option');
                    }
                    
                    $sidebar_posts = new WP_Query([
                        'post_type' => 'post',
                        'posts_per_page' => count($readable_posts),
                        'post__not_in' => [get_the_ID()],
                        'post__in' => $readable_posts,
                    ]);
                    if($sidebar_posts->have_posts()):
                        while($sidebar_posts->have_posts()): $sidebar_posts->the_post();
                    ?>
                        <a href="<?php the_permalink(); ?>" class="sidebar-post-item flex items-start gap-15">
                            
                            <div class="content flex flex-col gap-5">
                                <h4 class="yekan-20 color-black-70 transition"><?php the_title(); ?></h4>
                                <span class="date yekan-20 color-black-30"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' پیش'; ?></span>
                            </div>
                            <?php if(has_post_thumbnail()): ?>
                                <div class="thumb w-60 h-60 flex-shrink-0 rounded-10 overflow-hidden relative">
                                    <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" class="w-100 h-100 object-cover transition group-hover:scale-110" alt="<?php the_title(); ?>">
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>

            <!-- Ads -->
            <?php if($sidebar_ads && isset($sidebar_ads['ads']) && is_array($sidebar_ads['ads'])): ?>
            <div class="widget ads-widget flex flex-col gap-15">
                 <?php foreach( $sidebar_ads['ads'] as $ad ) : ?>
                    <?php 
                        $link = isset($ad['link']['url']) ? $ad['link']['url'] : '#';
                        $image = isset($ad['image']['url']) ? $ad['image']['url'] : '';
                        if($image):
                    ?>
                    <a href="<?php echo esc_url($link); ?>" target="_blank" class="block w-full rounded-10 overflow-hidden hover-shadow transition">
                        <img src="<?php echo esc_url($image); ?>" class="w-100 h-auto block" alt="Ad">
                    </a>
                    <?php endif; ?>
                 <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </aside>

    </div>

    <!-- Bottom Related Posts -->
    <section class="bottom-related-posts container mt-40">
         <div class="section-header mb-25">
             <h3 class="section-title yekan-30 color-black">مطالب مرتبط</h3>
         </div>
         <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <?php

            $related_posts = get_field('ralated_posts');
            
            if( empty($related_posts) ){
                $categories = get_the_category();
                $cat_ids = $categories ? array_map(function($c){return $c->term_id;}, $categories) : [];
                
                $related_query = new WP_Query([
                    'category__in' => $cat_ids,
                    'post__not_in' => [get_the_ID()],
                    'post_status' => 'publish',
                    'posts_per_page' => 4
                ]);
            }else{
                $related_query = new WP_Query([
                    'post__in' => $related_posts,
                    'post__not_in' => [get_the_ID()],
                    'post_status' => 'publish',
                    'posts_per_page' => 4
                ]);
            }
            

            if(!$related_query->have_posts()){
                $related_query = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 4,
                    'post__not_in' => [get_the_ID()]
                ]);
            }

            if($related_query->have_posts()):
                while($related_query->have_posts()): $related_query->the_post();
            ?>
                <article class="related-post-card flex flex-col gap-10 bg-black-03">
                    <a href="<?php the_permalink(); ?>" class="thumb w-100 relative overflow-hidden block">
                        <?php if(has_post_thumbnail()): ?>
                        <img src="<?php the_post_thumbnail_url('medium'); ?>" class="w-100 h-100 object-cover transition" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </a>
                    <div class="content flex flex-col gap-10">
                        <h4 class="title yekan-16 transition ">
                            <a class="color-black-80" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <div class="excerpt yekan-14 color-black-50 line-clamp-3">
                            <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                        </div>
                    </div>
                </article>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
         </div>
    </section>

</div>

<?php get_footer(); ?>
