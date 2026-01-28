<?php

/**
 * Template Name: Blog
 * Description: A custom Blog page template with info and form.
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

$top_posts = get_field('top_posts', $page_id);
$hot_tags = get_field('hot_tags', $page_id);
$top_ads = get_field('top_ads', $page_id);
$most_popular_posts = get_field('most_popular_posts', $page_id);
$sidebar_ads = get_field('sidebar_ads', $page_id);
$bottom_tabs = get_field('bottom_tabs', $page_id);
?>
<div id="page-blog-archive" class="page-template-blog-archive">
    <?php if($top_posts) : 
        $highlighted_post_id = $top_posts[0];
        $grid_post_ids = array_slice($top_posts, 1, 4);
    ?>
    <section class="top_posts container flex flex-col items-center">
        <div class="flex w-100 gap-20">
            <div class="highted_post post relative w-100">
                <?php
                    $h_img = get_the_post_thumbnail_url($highlighted_post_id, 'large');
                    $h_title = get_the_title($highlighted_post_id);
                    $h_link = get_permalink($highlighted_post_id);
                ?>
                <a href="<?php echo esc_url($h_link); ?>" class="flex flex-col gap-10">
                    <?php if($h_img): ?>
                        <img class="transition object-cover w-100 h-auto" src="<?php echo esc_url($h_img); ?>" alt="<?php echo esc_attr($h_title); ?>">
                    <?php endif; ?>
                        <h2 class="yekan-16 md:yekan-24 color-white">
                            <?php echo esc_html($h_title); ?>
                        </h2>
                </a>
            </div>
            <div class="grid grid-cols-2 w-100 gap-20">
                <?php foreach($grid_post_ids as $pid): 
                    $p_img = get_the_post_thumbnail_url($pid, 'medium');
                    $p_title = get_the_title($pid);
                    $p_link = get_permalink($pid);
                ?>
                <a href="<?php echo esc_url($p_link); ?>" class="post relative flex flex-col gap-10">
                    <?php if($p_img): ?>
                        <img class="transition object-cover w-100 h-100 absolute inset-0" src="<?php echo esc_url($p_img); ?>" alt="<?php echo esc_attr($p_title); ?>">
                    <?php endif; ?>
                    <h3 class="yekan-16 md:yekan-24 color-white" >
                        <?php echo esc_html($p_title); ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; if($hot_tags): ?>
    <section class="hot_tags container mt-40">
        <div class="wrapper flex flex-col gap-10">
            <span class="tags_label yekan-14 md:yekan-16 color-black flex-shrink-0">برچسب های داغ:</span>
            <div class="tags_list flex items-center gap-10 overflow-x-auto no-scrollbar flex-grow-1">
                <?php  foreach($hot_tags as $tag): 
                ?>
                    <a href="<?php echo esc_url($tag['tag']['url']); ?>" class="tag_item yekan-14 color-black-60 bg-black-03 transition">
                        <?php echo esc_html($tag['tag']['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif;  ?>
    <?php if($top_ads): ?>
        <section class="top_ads container flex items-center gap-20 mt-30">
            <?php foreach( $top_ads['ads'] as $ad ) : ?>
                            <?php if(isset($ad['link']['url'])): ?>
                            <a href="<?php echo esc_url( $ad['link']['url'] ); ?>" target="_blank" class="flex w-full object-cover">
                            <?php endif; if(isset($ad['image']['url'])): ?>    
                            <img src="<?php echo esc_url( $ad['image']['url'] ); ?>" alt="<?php echo esc_attr( $ad['image']['title'] ); ?>">
                            <?php endif; if(isset($ad['link']['url'])): ?> </a><?php endif; ?> 
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <section class="flex container gap-10 mt-40">
        <div class="tab-contents w-75">
            <div class="tabs flex w-full relative mb-10">
                    <div id="tab-recent" class="tab-item yekan-14 color-black-30 pointer active">جدیدترین مطالب</div>
                    <?php if($most_popular_posts): ?>
                        <div id="tab-popular" class="tab-item yekan-14 color-black-30 pointer">پربازدیدترین</div>
                    <?php endif; ?>
            </div>
            <div class="tab-content flex flex-col gap-20">
                    <div id="tab-recent-content" class="tab-content-item yekan-18 color-black-60 active">
                        <main class="main_posts  flex flex-col gap-20">
                            <?php
                            $recent_posts = new WP_Query(array(
                                'post_type' => 'post',
                                'posts_per_page' => 4,
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ));

                            if ($recent_posts->have_posts()) :
                                while ($recent_posts->have_posts()) : $recent_posts->the_post();
                                    $post_id = get_the_ID();
                                    $title = get_the_title();
                                    $excerpt = wp_trim_words(get_the_excerpt(), 45, '...');
                                    $permalink = get_permalink();
                                    $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                            ?>
                            <article class="blog-card flex flex-col md:flex-row gap-20 bg-black-03 transition">
                                <?php if($image_url): ?>
                                <a href="<?php echo esc_url($permalink); ?>" class="image-box relative flex-shrink-0">
                                    <img class="w-100 h-100 object-cover absolute inset-0 transition" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url($permalink); ?>" class="content flex flex-col justify-between flex-grow-1 gap-10">
                                    <div class="top-content flex flex-col gap-10">
                                        <h2 class="title yekan-18 md:yekan-28 color-black">
                                            <?php echo esc_html($title); ?>
                                        </h2>
                                        <div class="excerpt yekan-20 color-black-50">
                                            <?php echo wp_kses_post($excerpt); ?>
                                        </div>
                                    </div>
                                    <div class="bottom-content flex items-center">
                                        <span class="date color-black-50 yekan-16 flex items-center gap-10">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M15.7109 15.1798L12.6109 13.3298C12.0709 13.0098 11.6309 12.2398 11.6309 11.6098V7.50977" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' پیش'; ?>
                                        </span>
                                    </div>
                                </a>
                            </article>
                            <?php 
                                endwhile;
                                wp_reset_postdata();
                            else: 
                            ?>
                            <div class="no-posts yekan-16 color-black-60 text-center py-20">
                                هیچ مقاله ای یافت نشد.
                            </div>
                            <?php endif; ?>
                            <div class="load-more-container flex justify-center mt-20">
                                <button id="load-more-posts" class="yekan-18 transition color-black-80 flex items-center gap-10" data-page="1" data-max="<?php echo $recent_posts->max_num_pages; ?>">
                                    مشاهده بیشتر
                                    <span class="spinner hidden"></span>
                                </button>
                            </div>
                        </main>
                    </div>
                    <?php if($most_popular_posts): ?>

                    <div id="tab-popular-content" class="tab-content-item">
                        <main class="main_posts  flex flex-col gap-20">
                            <?php
                            $most_popular_posts_loop = new WP_Query(array(
                                'post_type' => 'post',
                                'posts_per_page' => count($most_popular_posts),
                                'post_status' => 'publish',
                                'post__in' => $most_popular_posts,
                                'orderby' => 'post__in'
                            ));

                            if ($most_popular_posts_loop->have_posts()) :
                                while ($most_popular_posts_loop->have_posts()) : $most_popular_posts_loop->the_post();
                                    $post_id = get_the_ID();
                                    $title = get_the_title();
                                    $excerpt = wp_trim_words(get_the_excerpt(), 45, '...');
                                    $permalink = get_permalink();
                                    $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                            ?>
                            <article class="blog-card flex flex-col md:flex-row gap-20 bg-black-03 transition">
                                <?php if($image_url): ?>
                                <a href="<?php echo esc_url($permalink); ?>" class="image-box relative flex-shrink-0">
                                    <img class="w-100 h-100 object-cover absolute inset-0 transition" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url($permalink); ?>" class="content flex flex-col justify-between flex-grow-1 gap-10">
                                    <div class="top-content flex flex-col gap-10">
                                        <h2 class="title yekan-18 md:yekan-28 color-black">
                                            <?php echo esc_html($title); ?>
                                        </h2>
                                        <div class="excerpt yekan-20 color-black-50">
                                            <?php echo wp_kses_post($excerpt); ?>
                                        </div>
                                    </div>
                                    <div class="bottom-content flex items-center">
                                        <span class="date color-black-50 yekan-16 flex items-center gap-10">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M15.7109 15.1798L12.6109 13.3298C12.0709 13.0098 11.6309 12.2398 11.6309 11.6098V7.50977" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' پیش'; ?>
                                        </span>
                                    </div>
                                </a>
                            </article>
                            <?php 
                                endwhile;
                                wp_reset_postdata();
                            ?>
                            
                            <?php endif; ?>
                        </main>
                    </div>
                                        <?php endif; ?>

            </div>
        </div>
        
        <aside class="sidebar w-25 flex flex-col gap-20">
            <div class="widget categories-widget flex flex-col gap-15">
                <h3 class="widget-title yekan-20 color-black-80 relative">دسته بندی مقالات</h3>
                <ul class="categories-list flex flex-col gap-10">
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'name',
                        'order'   => 'ASC'
                    ));
                    foreach( $categories as $category ) {
                        $category_link = get_category_link( $category->term_id );
                        echo '<li><a href="' . esc_url( $category_link ) . '" class="flex items-center justify-between color-black-40 transition yekan-20">' . esc_html( $category->name ) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <?php if($sidebar_ads) : ?>
            <div class="widget ads-widget flex flex-col gap-15">
                <div class="ads-list flex flex-col gap-10">
                    <?php foreach( $sidebar_ads['ads'] as $ad ) : ?>
                        <?php if(isset($ad['link']['url'])): ?>
                        <a href="<?php echo esc_url( $ad['link']['url'] ); ?>" target="_blank" class="block w-full h-200 object-cover rounded-10 transition hover-shadow">
                        <?php endif; if(isset($ad['image']['url'])): ?>    
                        <img src="<?php echo esc_url( $ad['image']['url'] ); ?>" alt="<?php echo esc_attr( $ad['image']['title'] ); ?>">
                        <?php endif; if(isset($ad['link']['url'])): ?> </a><?php endif; ?> 
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </section>
    <section class="flex container mt-40">
        <div class="tab-contents w-full">
            <div class="tabs flex w-full relative">
                    <?php if(isset($bottom_tabs['new_posts'])): ?>
                    <div id="tab-mp" class="tab-item yekan-14 color-black-30 pointer active">جدیدترین مطالب</div>
                    <?php endif; ?>
                    <?php if(isset($bottom_tabs['selected_posts'])): ?>
                        <div id="tab-selected" class="tab-item yekan-14 color-black-30 pointer">منتخب سردبیر</div>
                    <?php endif; ?>
                    <?php if(isset($bottom_tabs['most_popular'])): ?>
                        <div id="tab-views" class="tab-item yekan-14 color-black-30 pointer">پربازدیدترین</div>
                    <?php endif; ?>
            </div>
            <div class="tab-content flex flex-col gap-20 mt-30">
                    <?php if(isset($bottom_tabs['new_posts'])): 
                        $highlighted_post_id = $bottom_tabs['new_posts'][0];
                        $grid_post_ids = array_slice($bottom_tabs['new_posts'], 1, 4);
                    ?>
                    <div id="tab-mp-content" class="tab-content-item yekan-18 color-black-60 relative active">
                        <?php if(isset($bottom_tabs['new_posts_all'])): ?>
                            <a href="<?php echo esc_url($bottom_tabs['new_posts_all']); ?>" class="flex items-center gap-20 all-link absolute">
                                <span class="yekan-20 color-black-50">
                                مشاهده همه 
                                </span>
                                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.19531 10.6699L1.11211 6.58672C0.629893 6.1045 0.629893 5.31542 1.11211 4.8332L5.19531 0.75" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        <div class="top_posts video_posts flex flex-col items-center">
                            <div class="flex w-100 gap-20">
                                <div class="highted_post post relative w-100">
                                    <?php
                                        $h_img = get_the_post_thumbnail_url($highlighted_post_id, 'large');
                                        $h_title = get_the_title($highlighted_post_id);
                                        $h_link = get_permalink($highlighted_post_id);
                                    ?>
                                    <a href="<?php echo esc_url($h_link); ?>" class="flex flex-col gap-10">
                                        <?php if($h_img): ?>
                                            <img class="transition object-cover w-100 h-auto" src="<?php echo esc_url($h_img); ?>" alt="<?php echo esc_attr($h_title); ?>">
                                        <?php endif; ?>
                                            <h2 class="yekan-16 md:yekan-24 color-white">
                                                <?php echo esc_html($h_title); ?>
                                            </h2>
                                    </a>
                                </div>
                                <div class="grid grid-cols-2 w-100 gap-20">
                                    <?php foreach($grid_post_ids as $pid): 
                                        $p_img = get_the_post_thumbnail_url($pid, 'medium');
                                        $p_title = get_the_title($pid);
                                        $p_link = get_permalink($pid);
                                    ?>
                                    <a href="<?php echo esc_url($p_link); ?>" class="post relative flex flex-col gap-10">
                                        <?php if($p_img): ?>
                                            <img class="transition object-cover w-100 h-100 absolute inset-0" src="<?php echo esc_url($p_img); ?>" alt="<?php echo esc_attr($p_title); ?>">
                                        <?php endif; ?>
                                        <h3 class="yekan-16 md:yekan-24 color-white" >
                                            <?php echo esc_html($p_title); ?>
                                        </h3>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php endif; ?>
                    <?php if(isset($bottom_tabs['selected_posts'])): 
                        $highlighted_post_id = $bottom_tabs['selected_posts'][0];
                        $grid_post_ids = array_slice($bottom_tabs['selected_posts'], 1, 4);
                    ?>

                    <div id="tab-selected-content" class="tab-content-item relative">
                        <?php if(isset($bottom_tabs['selected_posts_all'])): ?>
                            <a href="<?php echo esc_url($bottom_tabs['selected_posts_all']); ?>" class="flex items-center gap-20 all-link absolute">
                                <span class="yekan-20 color-black-50">
                                مشاهده همه 
                                </span>
                                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.19531 10.6699L1.11211 6.58672C0.629893 6.1045 0.629893 5.31542 1.11211 4.8332L5.19531 0.75" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                       <div class="top_posts video_posts flex flex-col items-center">
                            <div class="flex w-100 gap-20">
                                <div class="highted_post post relative w-100">
                                    <?php
                                        $h_img = get_the_post_thumbnail_url($highlighted_post_id, 'large');
                                        $h_title = get_the_title($highlighted_post_id);
                                        $h_link = get_permalink($highlighted_post_id);
                                    ?>
                                    <a href="<?php echo esc_url($h_link); ?>" class="flex flex-col gap-10">
                                        <?php if($h_img): ?>
                                            <img class="transition object-cover w-100 h-auto" src="<?php echo esc_url($h_img); ?>" alt="<?php echo esc_attr($h_title); ?>">
                                        <?php endif; ?>
                                            <h2 class="yekan-16 md:yekan-24 color-white">
                                                <?php echo esc_html($h_title); ?>
                                            </h2>
                                    </a>
                                </div>
                                <div class="grid grid-cols-2 w-100 gap-20">
                                    <?php foreach($grid_post_ids as $pid): 
                                        $p_img = get_the_post_thumbnail_url($pid, 'medium');
                                        $p_title = get_the_title($pid);
                                        $p_link = get_permalink($pid);
                                    ?>
                                    <a href="<?php echo esc_url($p_link); ?>" class="post relative flex flex-col gap-10">
                                        <?php if($p_img): ?>
                                            <img class="transition object-cover w-100 h-100 absolute inset-0" src="<?php echo esc_url($p_img); ?>" alt="<?php echo esc_attr($p_title); ?>">
                                        <?php endif; ?>
                                        <h3 class="yekan-16 md:yekan-24 color-white" >
                                            <?php echo esc_html($p_title); ?>
                                        </h3>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($bottom_tabs['most_popular'])): 
                        $highlighted_post_id = $bottom_tabs['most_popular'][0];
                        $grid_post_ids = array_slice($bottom_tabs['most_popular'], 1, 4);
                    ?>
                    <div id="tab-views-content" class="tab-content-item relative">
                        <?php if(isset($bottom_tabs['selected_posts_all'])): ?>
                            <a href="<?php echo esc_url($bottom_tabs['selected_posts_all']); ?>" class="flex items-center gap-20 all-link absolute">
                                <span class="yekan-20 color-black-50">
                                مشاهده همه 
                                </span>
                                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.19531 10.6699L1.11211 6.58672C0.629893 6.1045 0.629893 5.31542 1.11211 4.8332L5.19531 0.75" stroke="black" stroke-opacity="0.5" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                       <div class="top_posts video_posts flex flex-col items-center">
                            <div class="flex w-100 gap-20">
                                <div class="highted_post post relative w-100">
                                    <?php
                                        $h_img = get_the_post_thumbnail_url($highlighted_post_id, 'large');
                                        $h_title = get_the_title($highlighted_post_id);
                                        $h_link = get_permalink($highlighted_post_id);
                                    ?>
                                    <a href="<?php echo esc_url($h_link); ?>" class="flex flex-col gap-10">
                                        <?php if($h_img): ?>
                                            <img class="transition object-cover w-100 h-auto" src="<?php echo esc_url($h_img); ?>" alt="<?php echo esc_attr($h_title); ?>">
                                        <?php endif; ?>
                                            <h2 class="yekan-16 md:yekan-24 color-white">
                                                <?php echo esc_html($h_title); ?>
                                            </h2>
                                    </a>
                                </div>
                                <div class="grid grid-cols-2 w-100 gap-20">
                                    <?php foreach($grid_post_ids as $pid): 
                                        $p_img = get_the_post_thumbnail_url($pid, 'medium');
                                        $p_title = get_the_title($pid);
                                        $p_link = get_permalink($pid);
                                    ?>
                                    <a href="<?php echo esc_url($p_link); ?>" class="post relative flex flex-col gap-10">
                                        <?php if($p_img): ?>
                                            <img class="transition object-cover w-100 h-100 absolute inset-0" src="<?php echo esc_url($p_img); ?>" alt="<?php echo esc_attr($p_title); ?>">
                                        <?php endif; ?>
                                        <h3 class="yekan-16 md:yekan-24 color-white" >
                                            <?php echo esc_html($p_title); ?>
                                        </h3>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

            </div>
        </div>
        
    </section>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */