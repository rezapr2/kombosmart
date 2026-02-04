<?php
/**
 * Article Card Template
 * 
 * @package TanilChoob
 */
use TanilChoob\Theme\Helper;

// Get post data if not provided
$post_id = isset($args['post_id']) ? $args['post_id'] : get_the_ID();
$title = isset($args['title']) ? $args['title'] : get_the_title($post_id);
$excerpt = isset($args['excerpt']) ? $args['excerpt'] : get_the_excerpt($post_id);
$permalink = isset($args['permalink']) ? $args['permalink'] : get_permalink($post_id);
$image_url = isset($args['image_url']) ? $args['image_url'] : get_the_post_thumbnail_url($post_id, 'medium');
$read_more_text = isset($args['read_more_text']) ? $args['read_more_text'] : 'ادامه مطلب';
?>

<article class="article-card swiper-slide">
    <div class="article-card__inner flex flex-col md:flex-row">
        <?php if ($image_url): ?>
        <div class="article-card__image relative overflow-hidden h-auto flex-shrink-0 transition">
            <img class="transition object-cover" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
        </div>
        <?php endif; ?>
        <div class="article-card__content flex flex-col gap-07">
            <h3 class="article-card__title yekan-20 regular color-black-80">
                <?php echo esc_html($title); ?>
            </h3>
            <div class="article-card__excerpt yekan-16 color-black-50 text-justify">
                <?php echo wp_kses_post($excerpt); ?>
            </div>
            <a href="<?php echo esc_url($permalink); ?>" class="article-card__read-more transition md:w-fit w-100 color-white yekan-14 flex items-center gap-10">
                <span class="read-more-text"><?php echo esc_html($read_more_text); ?></span>
                <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-left.svg')); ?>
            </a>
        </div>
    </div>
</article>