<?php

/**
 * Homepage promo cards (row of three under the hero).
 *
 * @var array $args ['cards' => ACF promo_cards rows]
 */

use TanilChoob\Theme\Helper;

$cards = !empty($args['cards']) ? $args['cards'] : [];

// Fallback: the three biggest product categories.
if (!$cards) {
    foreach (Helper::top_product_categories(3) as $term) {
        $cards[] = [
            'title'     => $term->name,
            'link'      => ['url' => get_term_link($term), 'title' => 'مشاهده محصولات', 'target' => ''],
            'image'     => ['ID' => (int) get_term_meta($term->term_id, 'thumbnail_id', true)],
            'tone'      => Helper::term_tone($term->term_id),
            'image_fit' => 'contain',
        ];
    }
}

if (!$cards) {
    return;
}
?>
<section class="home-promos container" aria-label="پیشنهادها">
    <div class="home-promos__grid">
        <?php foreach (array_slice($cards, 0, 3) as $card) :
            $link     = !empty($card['link']['url']) ? $card['link'] : ['url' => '#', 'title' => '', 'target' => ''];
            $image_id = !empty($card['image']['ID']) ? (int) $card['image']['ID'] : 0;
        ?>
            <a class="promo-card tone-<?php echo esc_attr($card['tone'] ?: 'violet'); ?> is-<?php echo esc_attr($card['image_fit'] ?: 'contain'); ?>"
                href="<?php echo esc_url($link['url']); ?>" <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>>
                <span class="promo-card__glow" aria-hidden="true"></span>
                <?php if ($image_id) : ?>
                    <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'promo-card__img', 'alt' => '']); ?>
                <?php endif; ?>
                <span class="promo-card__content">
                    <span class="promo-card__title"><?php echo Helper::title_html($card['title']); ?></span>
                    <?php if (!empty($link['title'])) : ?>
                        <span class="sw-btn sw-btn--dark sw-btn--sm promo-card__btn"><?php echo esc_html($link['title']); ?></span>
                    <?php endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
