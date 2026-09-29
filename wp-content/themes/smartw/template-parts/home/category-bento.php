<?php

/**
 * Homepage "recommended categories" bento grid: one tall tile, two stacked tiles, one wide tile.
 *
 * @var array $args ['title', 'subtitle', 'tiles' => ACF tiles rows]
 */

use TanilChoob\Theme\Helper;

$tiles = !empty($args['tiles']) ? $args['tiles'] : [];

// Fallback: the four biggest product categories.
if (!$tiles) {
    foreach (Helper::top_product_categories(4) as $term) {
        $tiles[] = [
            'title'     => $term->name,
            'link'      => ['url' => get_term_link($term), 'title' => 'مشاهده همه', 'target' => ''],
            'image'     => ['ID' => (int) get_term_meta($term->term_id, 'thumbnail_id', true)],
            'tone'      => Helper::term_tone($term->term_id),
            'image_fit' => 'contain',
        ];
    }
}

if (!$tiles) {
    return;
}

$tiles    = array_slice($tiles, 0, 4);
$variants = ['big', 'small', 'small', 'wide'];
?>
<section class="home-bento sw-section container">
    <div class="sw-section-head">
        <h2 class="sw-section-head__title"><?php echo Helper::title_html($args['title'] ?? ''); ?></h2>
        <?php if (!empty($args['subtitle'])) : ?>
            <p class="sw-section-head__subtitle"><?php echo esc_html($args['subtitle']); ?></p>
        <?php endif; ?>
    </div>

    <div class="home-bento__grid count-<?php echo count($tiles); ?>">
        <?php foreach ($tiles as $i => $tile) :
            $link     = !empty($tile['link']['url']) ? $tile['link'] : ['url' => '#', 'title' => '', 'target' => ''];
            $image_id = !empty($tile['image']['ID']) ? (int) $tile['image']['ID'] : 0;
        ?>
            <a class="bento-tile bento-tile--<?php echo esc_attr($variants[$i]); ?> tone-<?php echo esc_attr($tile['tone'] ?: 'violet'); ?> is-<?php echo esc_attr($tile['image_fit'] ?: 'contain'); ?>"
                href="<?php echo esc_url($link['url']); ?>" <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>>
                <span class="bento-tile__pattern" aria-hidden="true"></span>
                <?php if ($image_id) : ?>
                    <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'bento-tile__img', 'alt' => '', 'loading' => 'lazy']); ?>
                <?php endif; ?>
                <span class="bento-tile__content">
                    <span class="bento-tile__title"><?php echo Helper::title_html($tile['title']); ?></span>
                    <?php if (!empty($link['title'])) : ?>
                        <span class="sw-btn sw-btn--light sw-btn--sm bento-tile__btn">
                            <?php echo esc_html($link['title']); ?>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 17 7 7M7 7v9.5M7 7h9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    <?php endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
