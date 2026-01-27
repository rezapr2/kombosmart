<?php

namespace TanilChoob\Theme\Ajax;

use TanilChoob\Theme\Abstracts\AjaxHandler;

class BlogPostsLoadMore extends AjaxHandler
{
    public function __construct()
    {
        parent::__construct('load_more_posts');
    }

    public function handler()
    {
        $this->handle_nonce();

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 4,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $page
        );

        $recent_posts = new \WP_Query($args);

        ob_start();

        if ($recent_posts->have_posts()) :
            while ($recent_posts->have_posts()) : $recent_posts->the_post();
                $post_id = get_the_ID();
                $title = get_the_title();
                $excerpt = get_the_excerpt();
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
        endif;
        
        $response = ob_get_clean();
        
        echo $response;
        die();
    }

    public function is_public() {
        return true;
    }
}

// Initialize handler
new BlogPostsLoadMore();
