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
$sidebar_ads = get_field('sidebar_ads', $page_id);
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
    <section class="hot_tags container flex items-center gap-10 mt-40">
        <span class="tags_label yekan-14 md:yekan-16 color-black flex-shrink-0">برچسب های داغ:</span>
        <div class="tags_list flex items-center gap-10 overflow-x-auto no-scrollbar flex-grow-1">
            <?php  foreach($hot_tags as $tag_id): 
                $tag = get_term($tag_id);
                if(!$tag || is_wp_error($tag)) continue;
            ?>
                <a href="<?php echo get_term_link($tag); ?>" class="tag_item yekan-14 color-black-60 transition">
                    <?php echo esc_html($tag->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif;  ?>
    <?php if(isset($top_ads['ads'])): ?>
    <section class="top_ads container flex items-center gap-20 mt-30">
        <?php foreach( $top_ads['ads'] as $ad ) : ?>
                        <?php if(isset($ad['link']['url'])): ?>
                        <a href="<?php echo esc_url( $ad['link']['url'] ); ?>" target="_blank" class="block w-full h-200 object-cover rounded-10 transition hover-shadow">
                        <?php endif; if(isset($ad['image']['url'])): ?>    
                        <img src="<?php echo esc_url( $ad['image']['url'] ); ?>" alt="<?php echo esc_attr( $ad['image']['title'] ); ?>">
                        <?php endif; if(isset($ad['link']['url'])): ?> </a><?php endif; ?> 
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    <section class="flex container gap-10 mt-40">
        
        <main class="main_posts w-75 flex flex-col gap-20">
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
                    $excerpt = get_the_excerpt();
                    $permalink = get_permalink();
                    $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                    $author_name = get_the_author();
                    $date = get_the_date('j F Y');
                    $read_time = '5 دقیقه مطالعه'; // Static for now or use a helper if available
            ?>
            <article class="blog-card flex flex-col md:flex-row gap-20 bg-white p-15 rounded-15 border border-gray-100 transition hover-shadow">
                <?php if($image_url): ?>
                <a href="<?php echo esc_url($permalink); ?>" class="image-box relative overflow-hidden rounded-10 flex-shrink-0 w-100 md:w-30">
                    <img class="w-100 h-100 object-cover absolute inset-0 transition" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                </a>
                <?php endif; ?>
                <div class="content flex flex-col justify-between flex-grow-1 gap-10">
                    <div class="top-content flex flex-col gap-10">
                        <div class="meta flex items-center gap-15 color-black-40 yekan-12">
                            <span class="author flex items-center gap-5">
                                <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/user.svg')); ?>
                                <?php echo esc_html($author_name); ?>
                            </span>
                            <span class="date flex items-center gap-5">
                                <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/calendar.svg')); ?>
                                <?php echo esc_html($date); ?>
                            </span>
                        </div>
                        <h2 class="title yekan-18 md:yekan-22 bold color-black-80">
                            <a href="<?php echo esc_url($permalink); ?>" class="transition hover-color-primary">
                                <?php echo esc_html($title); ?>
                            </a>
                        </h2>
                        <div class="excerpt yekan-14 color-black-60 text-justify line-clamp-2">
                            <?php echo wp_kses_post($excerpt); ?>
                        </div>
                    </div>
                    <div class="bottom-content flex items-center justify-between border-t border-gray-100 pt-15 mt-5">
                         <span class="read-time color-black-40 yekan-12 flex items-center gap-5">
                            <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/clock.svg')); ?>
                            <?php echo esc_html($read_time); ?>
                         </span>
                         <a href="<?php echo esc_url($permalink); ?>" class="read-more color-primary yekan-14 flex items-center gap-5 transition hover-gap-10">
                            ادامه مطلب
                            <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-left-primary.svg')); ?>
                         </a>
                    </div>
                </div>
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
        </main>
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
                    <?php foreach( $sidebar_ads as $ad ) : ?>
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
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */