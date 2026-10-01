<?php

/**
 * Homepage hero slider.
 *
 * @var array $args ['slides' => ACF hero_slides rows]
 */

use TanilChoob\Theme\Helper;

$slides = !empty($args['slides']) ? $args['slides'] : [];

if (!$slides) {
    $slides = [[
        'title'       => 'خانه‌ای که <strong>هر روزت</strong> را هوشمندتر می‌کند',
        'description' => 'قفل دیجیتال، کلید لمسی، پنل کنترل و دوربین هوشمند با ضمانت اصالت و ارسال سریع به سراسر کشور.',
        'link'        => ['url' => get_permalink(wc_get_page_id('shop')), 'title' => 'مشاهده محصولات', 'target' => ''],
        'tone'        => 'violet',
        'image'       => null,
        'image_fit'   => 'contain',
    ]];
}
$has_many = count($slides) > 1;
?>
<section class="home-hero container" aria-label="اسلایدر اصلی">
    <div class="home-hero__slider swiper">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $i => $slide) :
                $tone  = !empty($slide['tone']) ? $slide['tone'] : 'violet';
                $fit   = !empty($slide['image_fit']) ? $slide['image_fit'] : 'contain';
                $image = !empty($slide['image']['ID']) ? $slide['image'] : null;
                $link  = !empty($slide['link']['url']) ? $slide['link'] : null;
            ?>
                <div class="swiper-slide home-hero__slide tone-<?php echo esc_attr($tone); ?> is-<?php echo esc_attr($fit); ?>">
                    <div class="home-hero__decor" aria-hidden="true">
                        <span class="home-hero__blob home-hero__blob--1"></span>
                        <span class="home-hero__blob home-hero__blob--2"></span>
                        <span class="home-hero__ring"></span>
                    </div>
                    <?php if ($image) : ?>
                        <?php echo wp_get_attachment_image($image['ID'], 'full', false, [
                            'class'         => 'home-hero__img',
                            'loading'       => $i === 0 ? 'eager' : 'lazy',
                            'fetchpriority' => $i === 0 ? 'high' : 'auto',
                            'alt'           => wp_strip_all_tags($slide['title'] ?? ''),
                        ]); ?>
                    <?php endif; ?>
                    <div class="home-hero__content">
                        <h2 class="home-hero__title"><?php echo Helper::title_html($slide['title'] ?? ''); ?></h2>
                        <?php if (!empty($slide['description'])) : ?>
                            <p class="home-hero__desc"><?php echo esc_html($slide['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($link) : ?>
                            <a href="<?php echo esc_url($link['url']); ?>" class="sw-btn sw-btn--light sw-btn--lg home-hero__cta" <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>>
                                <?php echo esc_html($link['title'] ?: 'خرید کنید'); ?>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9.57 5.93 3.5 12l6.07 6.07M20.5 12H3.67" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($has_many) : ?>
            <div class="home-hero__nav flex items-center">
                <div class="home-hero__pagination"></div>
                <button type="button" class="home-hero__prev sw-icon-btn" aria-label="اسلاید قبلی">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M14.43 5.93 20.5 12l-6.07 6.07M3.5 12h16.83" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="home-hero__next sw-icon-btn sw-icon-btn--dark" aria-label="اسلاید بعدی">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9.57 5.93 3.5 12l6.07 6.07M20.5 12H3.67" stroke="currentColor" stroke-width="1.8" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>
