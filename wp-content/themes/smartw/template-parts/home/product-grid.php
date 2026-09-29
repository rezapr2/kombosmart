<?php

/**
 * Homepage product grid section (title, 3-column grid of product cards, "see all" button).
 *
 * @var array $args ['title', 'subtitle', 'eyebrow', 'query' => WP_Query args, 'link' => ACF link array, 'class']
 */

use TanilChoob\Theme\Helper;

if (empty($args['query'])) {
    return;
}

$query = new WP_Query($args['query']);
if (!$query->have_posts()) {
    return;
}

$link = !empty($args['link']['url']) ? $args['link'] : null;
?>
<section class="home-products sw-section container <?php echo esc_attr($args['class'] ?? ''); ?>">
    <div class="sw-section-head">
        <?php if (!empty($args['eyebrow'])) : ?>
            <span class="sw-section-head__eyebrow"><?php echo esc_html($args['eyebrow']); ?></span>
        <?php endif; ?>
        <h2 class="sw-section-head__title"><?php echo Helper::title_html($args['title'] ?? ''); ?></h2>
        <?php if (!empty($args['subtitle'])) : ?>
            <p class="sw-section-head__subtitle"><?php echo esc_html($args['subtitle']); ?></p>
        <?php endif; ?>
    </div>

    <div class="home-products__grid grid grid-cols-2 md:grid-cols-3">
        <?php
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/cards/product-card', null, ['product_id' => get_the_ID()]);
        }
        wp_reset_postdata();
        ?>
    </div>

    <?php if ($link) : ?>
        <div class="home-products__more flex justify-center">
            <a href="<?php echo esc_url($link['url']); ?>" class="sw-btn sw-btn--outline sw-btn--lg">
                <?php echo esc_html($link['title'] ?: 'مشاهده همه محصولات'); ?>
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9.57 5.93 3.5 12l6.07 6.07M20.5 12H3.67" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    <?php endif; ?>
</section>
