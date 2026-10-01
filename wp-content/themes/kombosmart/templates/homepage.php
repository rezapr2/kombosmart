<?php

/**
 * The template for displaying the page
 *
 * template name: Homepage
 */

get_header();

$page_id  = get_the_ID();
$shop_url = get_permalink(wc_get_page_id('shop'));

/**
 * Build WP_Query args for a homepage product section.
 *
 * @param string $source   latest | sale | best_selling | featured | category
 * @param int    $category product_cat term id (optional filter)
 * @param int    $count
 */
$products_query = function ($source, $category, $count) {
    $query = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $count ?: 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'tax_query'      => [
            [
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => ['exclude-from-catalog'],
                'operator' => 'NOT IN',
            ],
        ],
    ];

    if ($category) {
        $query['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => (int) $category,
        ];
    }

    if ($source === 'sale') {
        $query['post__in'] = array_merge([0], wc_get_product_ids_on_sale());
    } elseif ($source === 'best_selling') {
        $query['meta_key'] = 'total_sales';
        $query['orderby']  = 'meta_value_num';
    } elseif ($source === 'featured') {
        $query['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => ['featured'],
        ];
    }

    return $query;
};

$new_products        = get_field('new_products', $page_id) ?: [];
$featured_categories = get_field('featured_categories', $page_id) ?: [];
$featured_products   = get_field('featured_products', $page_id) ?: [];

// Featured grid: fall back to best sellers when the chosen source has nothing to show.
$featured_source = $featured_products['source'] ?? 'sale';
$featured_query  = $products_query(
    $featured_source,
    $featured_source === 'category' ? ($featured_products['category'] ?? 0) : 0,
    (int) ($featured_products['count'] ?? 6)
);
if (!(new WP_Query($featured_query + ['fields' => 'ids', 'posts_per_page' => 1]))->have_posts()) {
    $featured_query = $products_query('best_selling', 0, (int) ($featured_products['count'] ?? 6));
}
?>

<div class="home-page wrapper" role="main">
    <h1 class="sr-only"><?php bloginfo('name'); ?> — <?php bloginfo('description'); ?></h1>

    <?php
    get_template_part('template-parts/home/hero', null, [
        'slides' => get_field('hero_slides', $page_id),
    ]);

    get_template_part('template-parts/home/promo-cards', null, [
        'cards' => get_field('promo_cards', $page_id),
    ]);

    get_template_part('template-parts/home/product-grid', null, [
        'eyebrow'  => 'تازه رسیده',
        'title'    => ($new_products['title'] ?? '') ?: 'جدیدترین <strong>محصولات</strong>',
        'subtitle' => ($new_products['subtitle'] ?? '') ?: 'تازه‌ترین تجهیزات خانه هوشمند، با ضمانت اصالت کالا و بهترین قیمت.',
        'link'     => !empty($new_products['link']['url']) ? $new_products['link'] : ['url' => $shop_url, 'title' => 'مشاهده همه محصولات'],
        'query'    => $products_query('latest', $new_products['category'] ?? 0, (int) ($new_products['count'] ?? 6)),
    ]);

    get_template_part('template-parts/home/category-bento', null, [
        'title'    => ($featured_categories['title'] ?? '') ?: 'دسته‌بندی‌های <strong>پیشنهادی</strong>',
        'subtitle' => ($featured_categories['subtitle'] ?? '') ?: 'از قفل و کلید لمسی تا پرده، دوربین و سنسور؛ هر چیزی که برای یک خانه هوشمند لازم داری.',
        'tiles'    => $featured_categories['tiles'] ?? [],
    ]);

    get_template_part('template-parts/home/product-grid', null, [
        'eyebrow'  => 'فرصت خرید',
        'title'    => ($featured_products['title'] ?? '') ?: 'پیشنهادهای <strong>ویژه</strong>',
        'subtitle' => ($featured_products['subtitle'] ?? '') ?: 'محصولات منتخب با تخفیف‌های محدود؛ قبل از تمام شدن موجودی انتخاب کن.',
        'link'     => !empty($featured_products['link']['url']) ? $featured_products['link'] : ['url' => $shop_url, 'title' => 'مشاهده همه پیشنهادها'],
        'query'    => $featured_query,
        'class'    => 'home-products--featured',
    ]);

    get_template_part('template-parts/home/cta-banner', null, get_field('cta_banner', $page_id) ?: []);
    ?>
</div>

<?php get_footer(); ?>
