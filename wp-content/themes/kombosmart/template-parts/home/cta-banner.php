<?php

/**
 * Homepage closing banner (wide rounded banner with a centered headline and button).
 *
 * @var array $args ACF cta_banner group
 */

use TanilChoob\Theme\Helper;

$title    = !empty($args['title']) ? $args['title'] : 'با کومبو اسمارت، <strong>خانه‌ات</strong> را یک قدم هوشمندتر کن';
$link     = !empty($args['link']['url']) ? $args['link'] : ['url' => get_permalink(wc_get_page_id('shop')), 'title' => 'شروع خرید', 'target' => ''];
$tone     = !empty($args['tone']) ? $args['tone'] : 'ink';
$fit      = !empty($args['image_fit']) ? $args['image_fit'] : 'contain';
$image_id = !empty($args['image']['ID']) ? (int) $args['image']['ID'] : 0;
?>
<section class="home-cta sw-section container">
    <div class="home-cta__inner tone-<?php echo esc_attr($tone); ?> is-<?php echo esc_attr($fit); ?><?php echo $image_id ? ' has-image' : ''; ?>">
        <span class="home-cta__decor" aria-hidden="true"></span>
        <?php if ($image_id) : ?>
            <?php echo wp_get_attachment_image($image_id, 'full', false, ['class' => 'home-cta__img', 'alt' => '', 'loading' => 'lazy']); ?>
        <?php endif; ?>
        <div class="home-cta__content flex flex-col items-center">
            <h2 class="home-cta__title"><?php echo Helper::title_html($title); ?></h2>
            <a href="<?php echo esc_url($link['url']); ?>" class="sw-btn sw-btn--light sw-btn--lg" <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>>
                <?php echo esc_html($link['title'] ?: 'شروع خرید'); ?>
            </a>
        </div>
    </div>
</section>
