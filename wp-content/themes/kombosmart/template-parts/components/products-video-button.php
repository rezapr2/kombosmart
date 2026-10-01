<?php

/**
 * "نمایش فیلم محصولات" trigger for product category / tag archives.
 *
 * Rendered only when the term has a value in the ACF `products_video_url` field.
 * Clicking it opens the shared video lightbox (js/partials/components/video-lightbox.js).
 *
 * @package TanilChoob
 *
 * @var array $args {
 *     @type WP_Term|null $term Term to read the video link from. Defaults to the queried term.
 * }
 */

defined('ABSPATH') || exit;

$video_term = isset($args['term']) && $args['term'] instanceof WP_Term ? $args['term'] : get_queried_object();

if (! $video_term instanceof WP_Term || ! function_exists('get_field')) {
    return;
}

$products_video_url = get_field('products_video_url', $video_term->taxonomy . '_' . $video_term->term_id);

if (empty($products_video_url)) {
    return;
}
?>
<button type="button" class="w-full md:w-auto flex justify-center show_products_video video-lightbox yekan-14 color-primary pointer transition" data-video-url="<?php echo esc_url($products_video_url); ?>">
    <span class="play-icon flex items-center justify-center">
        <svg viewBox="0 0 7 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M6.5 4L0.5 7.464V0.536L6.5 4Z" fill="currentColor" />
        </svg>
    </span>
    <span>نمایش ویدئو معرفی محصولات</span>
</button>
