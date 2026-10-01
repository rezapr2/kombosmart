<?php

/**
 * Footer
 *
 * The footer template.
 *
 * @since   1.0.0
 * @package WP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use TanilChoob\Theme\Helper;

$footer_logo     = get_field('footer_logo', 'options');
$about           = get_field('about_tanil', 'options');
$newsletter_form = get_field('newsletter_form', 'options');
$socials         = array_filter((array) get_field('social_networks', 'options'));
$phone_numbers   = get_field('phone_numbers', 'options');
$address         = get_field('footer_address', 'options');
$location_link   = get_field('location_link', 'options');
$copyright       = get_field('footer_copyright_text', 'options');
$ga_id           = get_field('ga_measurement_id', 'options');
$badges          = array_filter((array) get_field('footer_badges', 'options'), fn($badge) => !empty($badge['image']['ID']));

$social_labels = [
    'instagram_link' => 'اینستاگرام',
    'whatsapp_link'  => 'واتس‌اپ',
    'telegram_link'  => 'تلگرام',
    'facebook_link'  => 'فیس‌بوک',
];

// Link columns from theme options; the first falls back to top product categories.
$columns = [];
foreach (['list_1', 'list_2'] as $list_key) {
    $list = get_field($list_key, 'options');
    if (!empty($list['list'])) {
        $columns[] = [
            'title' => $list['title'] ?? '',
            'links' => array_filter(array_column($list['list'], 'link')),
        ];
    }
}
if (!$columns) {
    $links = [];
    foreach (Helper::top_product_categories(6) as $term) {
        $links[] = ['url' => get_term_link($term), 'title' => $term->name];
    }
    if ($links) {
        $columns[] = ['title' => 'دسته‌بندی‌ها', 'links' => $links];
    }
}
?>
<footer id="main_footer" class="footer">
    <span class="footer__glow footer__glow--1" aria-hidden="true"></span>
    <span class="footer__glow footer__glow--2" aria-hidden="true"></span>
    <div class="footer__mark" aria-hidden="true"><?php get_template_part('template-parts/components/logo-mark'); ?></div>

    <div class="container relative">
        <div class="footer__top">
            <div class="footer__brand flex flex-col">
                <a class="footer__logo flex items-center" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (!empty($footer_logo['url'])) : ?>
                        <img src="<?php echo esc_url($footer_logo['url']); ?>" width="<?php echo esc_attr($footer_logo['width']); ?>" height="<?php echo esc_attr($footer_logo['height']); ?>" alt="<?php bloginfo('name'); ?>">
                    <?php else : ?>
                        <?php get_template_part('template-parts/components/logo-mark'); ?>
                        <span><?php bloginfo('name'); ?></span>
                    <?php endif; ?>
                </a>
                <p class="footer__about">
                    <?php echo $about ? esc_html($about) : esc_html(get_bloginfo('description')); ?>
                </p>

                <?php if ($newsletter_form) : ?>
                    <div class="footer__newsletter">
                        <div class="footer__newsletter-title">عضویت در خبرنامه</div>
                        <p class="footer__newsletter-text">از تخفیف‌ها و محصولات جدید زودتر از همه باخبر شوید.</p>
                        <?php echo do_shortcode(wp_kses_post($newsletter_form)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php foreach ($columns as $column) : ?>
                <nav class="footer__col" aria-label="<?php echo esc_attr($column['title']); ?>">
                    <div class="footer__col-title"><?php echo esc_html($column['title']); ?></div>
                    <ul class="footer__links">
                        <?php foreach ($column['links'] as $link) : ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endforeach; ?>

            <?php if ($phone_numbers || $address || $location_link) : ?>
                <div class="footer__col footer__contact">
                    <div class="footer__col-title">ارتباط با ما</div>
                    <ul class="footer__links">
                        <?php if ($phone_numbers) : ?>
                            <li class="footer__contact-item flex">
                                <span class="footer__contact-icon flex item-center flex-shrink-0"><?php echo Helper::svg_icon('call'); ?></span>
                                <div class="footer__contact-text"><?php echo wp_kses_post($phone_numbers); ?></div>
                            </li>
                        <?php endif; ?>
                        <?php if ($address) : ?>
                            <li class="footer__contact-item flex">
                                <span class="footer__contact-icon flex item-center flex-shrink-0"><?php echo Helper::svg_icon('map'); ?></span>
                                <div class="footer__contact-text"><?php echo esc_html($address); ?></div>
                            </li>
                        <?php endif; ?>
                        <?php if ($location_link) : ?>
                            <li class="footer__contact-item flex">
                                <span class="footer__contact-icon flex item-center flex-shrink-0"><?php echo Helper::svg_icon('location'); ?></span>
                                <a class="footer__contact-text" href="<?php echo esc_url($location_link); ?>" target="_blank" rel="noopener noreferrer">مسیریابی روی نقشه</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($badges) : ?>
            <ul class="footer__badges flex items-center">
                <?php foreach ($badges as $badge) : ?>
                    <?php
                    $badge_title = $badge['title'] ?? '';
                    $badge_img   = wp_get_attachment_image($badge['image']['ID'], 'medium', false, [
                        'class' => 'footer__badge-img',
                        'alt'   => $badge_title,
                    ]);
                    ?>
                    <li>
                        <?php if (!empty($badge['link'])) : ?>
                            <?php // No "noreferrer": eNamad and similar registries check the referring origin. ?>
                            <a class="footer__badge flex items-center" href="<?php echo esc_url($badge['link']); ?>" target="_blank" rel="noopener" referrerpolicy="origin"<?php echo $badge_title ? ' title="' . esc_attr($badge_title) . '"' : ''; ?>>
                                <?php echo $badge_img; ?>
                            </a>
                        <?php else : ?>
                            <span class="footer__badge flex items-center"><?php echo $badge_img; ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="footer__bottom flex items-center justify-between">
            <div class="footer__copyright">
                <?php echo $copyright ? esc_html($copyright) : '© ' . esc_html(wp_date('Y')) . ' ' . esc_html(get_bloginfo('name')) . ' — تمامی حقوق محفوظ است.'; ?>
            </div>
            <?php if ($socials) : ?>
                <div class="footer__socials flex items-center">
                    <?php foreach ($socials as $key => $url) : ?>
                        <a class="footer__social flex items-center" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo Helper::svg_icon($key); ?>
                            <span><?php echo esc_html($social_labels[$key] ?? ucfirst(str_replace('_link', '', $key))); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php get_template_part('template-parts/mobile-bottom-nav'); ?>

<?php get_template_part('template-parts/components/whatsapp-float'); ?>

<?php wp_footer(); ?>

<?php if ($ga_id) : ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', <?php echo wp_json_encode($ga_id); ?>);
    </script>
<?php endif; ?>

</body>

</html>
