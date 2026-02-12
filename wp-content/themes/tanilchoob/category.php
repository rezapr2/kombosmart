<?php
/**
 * Category Template
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

get_header();

// Breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}

// Sidebar Ads Logic
$sidebar_ads = get_field('sidebar_ads', 'option'); 
if(!$sidebar_ads) {
    // Fallback if needed, but for now we rely on global options
}

?>
<div class="page-template-blog-archive category-archive">
    
    <section class="flex flex-col-reverse md:flex-row container gap-10 mt-40 mb-40">
        <div class="tab-contents w-100 md:w-75">

            <div class="tab-content flex flex-col gap-20">
                <div class="tab-content-item active">
                    <main class="main_posts flex flex-col gap-20">
                        <?php if (have_posts()) : ?>
                            <?php while (have_posts()) : the_post(); 
                                $post_id = get_the_ID();
                                $title = get_the_title();
                                $excerpt = wp_trim_words(get_the_excerpt(), 40, '...');
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
                                        <h2 class="title regular yekan-22 md:yekan-28 color-black">
                                            <?php echo esc_html($title); ?>
                                        </h2>
                                        <div class="excerpt yekan-14 color-black-50">
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
                            <?php endwhile; ?>
                            
                            <?php
                                global $wp_query;
                                $current_paged = max(1, (int) get_query_var('paged'));
                                $max_pages     = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 1;
                            ?>
                            <?php if ($max_pages > $current_paged) : ?>
                            <div class="load-more-container flex justify-center mt-20">
                                <button id="load-more-category-posts" class="loadmore-btn yekan-18 transition color-black-80 flex items-center gap-10" data-page="<?php echo esc_attr($current_paged); ?>" data-max="<?php echo esc_attr($max_pages); ?>">
                                    مشاهده بیشتر
                                    <span class="spinner hidden"></span>
                                </button>
                            </div>
                            
                            <?php endif; ?>

                        <?php else : ?>
                            <div class="no-posts yekan-16 color-black-60 text-center py-20">
                                هیچ مقاله ای در این دسته بندی یافت نشد.
                            </div>
                        <?php endif; ?>
                    </main>
                </div>
            </div>
        </div>

        <aside class="sidebar w-100 md:w-25 flex flex-col gap-20">
            <div class="widget categories-widget flex flex-col gap-15">
                <h3 class="widget-title yekan-14 md:yekan-20 color-black-80 relative">دسته بندی مقالات</h3>
                <ul class="categories-list flex flex-col gap-10">
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'name',
                        'order'   => 'ASC'
                    ));
                    foreach( $categories as $category ) {
                        $category_link = get_category_link( $category->term_id );
                        $current_cat_id = get_queried_object_id();
                        $active_class = ($current_cat_id == $category->term_id) ? 'active color-primary' : 'color-black-40';
                        
                        echo '<li><a href="' . esc_url( $category_link ) . '" class="flex items-center justify-between ' . $active_class . ' transition yekan-14 md:yekan-20">' . esc_html( $category->name ) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            <?php if($sidebar_ads) : ?>
            <div class="widget ads-widget hidden md:flex flex-col gap-15">
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

</div>

<?php get_footer(); ?>
