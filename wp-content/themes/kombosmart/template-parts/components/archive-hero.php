<?php

/**
 * Colorful header banner for product archives (category, tag, shop, search).
 *
 * @var array $args ['title', 'eyebrow', 'count', 'tone', 'image_id', 'back_url']
 */

$tone     = !empty($args['tone']) ? $args['tone'] : 'violet';
$image_id = !empty($args['image_id']) ? (int) $args['image_id'] : 0;
$count    = isset($args['count']) ? (int) $args['count'] : null;
?>
<div class="container">
    <section class="archive-hero tone-<?php echo esc_attr($tone); ?><?php echo $image_id ? ' has-image' : ''; ?>">
        <span class="archive-hero__pattern" aria-hidden="true"></span>
        <div class="archive-hero__content flex flex-col">
            <?php if (!empty($args['back_url'])) : ?>
                <a class="archive-hero__back flex items-center" href="<?php echo esc_url($args['back_url']); ?>">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14.43 5.93 20.5 12l-6.07 6.07M3.5 12h16.83" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?php echo esc_html($args['back_label'] ?? 'بازگشت'); ?>
                </a>
            <?php endif; ?>
            <?php if (!empty($args['eyebrow'])) : ?>
                <span class="archive-hero__eyebrow"><?php echo esc_html($args['eyebrow']); ?></span>
            <?php endif; ?>
            <h1 class="archive-hero__title"><?php echo esc_html($args['title'] ?? ''); ?></h1>
            <?php if ($count !== null) : ?>
                <span class="archive-hero__count"><?php echo esc_html(sprintf('%s محصول', number_format_i18n($count))); ?></span>
            <?php endif; ?>
        </div>
        <?php if ($image_id) : ?>
            <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'archive-hero__img', 'alt' => '', 'fetchpriority' => 'high']); ?>
        <?php endif; ?>
    </section>
</div>
