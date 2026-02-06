<?php
// Access the tabs context passed from the parent template.
$ctx = get_query_var('product_tabs');

$description = $ctx['description'] ?? '';

// get product categories
$product_cats = get_the_terms(get_the_ID(), 'product_cat');


$has_specs = false;

$product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;
$attributes = $product->get_attributes();
if (($product && (!empty($attributes)) || $product_cats)) {
    $has_specs = true;
}

$product_review = $ctx['product_review'] ?? '';

$product_size_images = $ctx['product_size_images'] ?? [];
$product_maintenance = $ctx['product_maintenance'] ?? '';

$production_process_video_link = $ctx['production_process_video_link'] ?? '';
$production_process_title = $ctx['production_process_title'] ?? '';
$production_process_video_poster = $ctx['production_process_video_poster'] ?? null;

// Theme asset helper replacement for play icon while preserving classes.
$play_icon_src = function_exists('get_theme_file_uri')
    ? get_theme_file_uri('assets/frontend/dist/images/play_icon.svg')
    : (get_stylesheet_directory_uri() . '/assets/frontend/dist/images/play_icon.svg');
?>

<div class="container mb-40">
    <div class="tab-contents">
        <div class="tabs hidden md:flex w-full">
            <?php if ($description) : ?>
                <div id="tab-desc" class="tab-item yekan-14 color-black-30 pointer active">توضیحات محصول</div>
            <?php endif;
            if ($has_specs) : ?>
                <div id="tab-specs" class="tab-item yekan-14 color-black-30 pointer">مشخصات کلی</div>
            <?php endif; ?>
            <?php if ($product_review) : ?>
                <div id="tab-technical-review" class="tab-item yekan-14 color-black-30 pointer">بررسی تخصصی</div>
            <?php endif; ?>
            <?php if ($product_size_images) : ?>
                <div id="tab-dimensions" class="tab-item yekan-14 color-black-30 pointer">ابعاد محصول</div>
            <?php endif; ?>
            <div id="tab-faqs" class="tab-item yekan-14 color-black-30 pointer">پرسش و پاسخ</div>
            <div id="tab-reviews" class="tab-item yekan-14 color-black-30 pointer">نظرات مشتریان</div>
            <?php if ($product_maintenance) : ?>
                <div id="tab-maintenance" class="tab-item yekan-14 color-black-30 pointer">نحوه نگهداری محصول</div>
            <?php endif; ?>
            <?php if ($production_process_video_link) : ?>
                <div id="tab-production" class="tab-item yekan-14 color-black-30 pointer">روند تولید</div>
            <?php endif; ?>
        </div>
        <div class="tab-content flex flex-col gap-20">
            <?php if ($description) : ?>
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer active" data-target="tab-desc-content">توضیحات محصول <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-desc-content" class="tab-content-item yekan-14 md:yekan-18 px-15 md:px-40 py-25 color-black-60 bg-black-03 active">
                        <?php
                        // Height-based clamp with bottom fade (no substring truncation)
                        $desc_text = trim($description);
                        $should_collapse = mb_strlen($desc_text, 'UTF-8') > 250; // Only show controls if likely long
                        if (!$should_collapse) {
                            echo nl2br($desc_text);
                        } else { ?>
                            <div class="desc-readmore slide-down-wrapper flex flex-col gap-10">
                                <div class="desc-full yekan-14 md:yekan-18 color-black-60"><?php echo nl2br($desc_text); ?></div>
                                <div class="desc-toggle desc-toggle-more slide-down-trigger yekan-14 md:yekan-16 color-primary pointer transition items-center" role="button" aria-expanded="false">نمایش بیشتر متن</div>
                                <div class="desc-toggle desc-toggle-less slide-down-trigger yekan-14 md:yekan-16 color-primary pointer transition items-center" role="button" aria-expanded="true">نمایش کمتر متن</div>
                            </div>
                        <?php }
                        ?>

                        <?php
                        $product_id = get_the_ID();
                        if ($product_id) {
                            $tag_list = wc_get_product_tag_list($product_id, '');
                            if (!empty($tag_list)) {
                                echo '<div class="product-tags yekan-12 md:yekan-14 flex items-center flex-wrap gap-05 md:gap-10 mt-25"><span class="yekan-14 md:yekan-16 color-black">برچسب‌ها: </span>' . $tag_list . '</div>';
                            }
                            $cat_list = wc_get_product_category_list($product_id, '');
                            if (!empty($cat_list)) {
                                echo '<div class="product-cats yekan-12 md:yekan-14 flex items-center flex-wrap gap-05 md:gap-10 mt-10"><span class="yekan-14 md:yekan-16 color-black">دسته‌ها: </span>' . $cat_list . '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endif;
            if ($has_specs) : ?>
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-specs-content">مشخصات کلی <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-specs-content" class="tab-content-item px-15 md:px-0 pb-20 md:py-20">
                        <div class="items grid grid-cols-1 md:grid-cols-2 gap-07">
                            <?php if ($product_cats) : ?>
                                <div class="spec-item flex flex-col gap-05 py-10 md:py-20 px-15 md:px-40 bg-black-03">
                                    <div class="spec-name yekan-16 md:yekan-20 color-black-80">دسته بندی محصول</div>
                                    <div class="spec-value yekan-14 md:yekan-18 color-black-50">
                                        <?php
                                        $cat_names = array();
                                        foreach ($product_cats as $cat) {
                                            $cat_names[] = $cat->name;
                                        }
                                        echo implode(' | ', $cat_names);
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php 
                            if ($product) {
                                if (!empty($attributes)) {
                                    foreach ($attributes as $attribute) {
                                        if (!is_object($attribute) || !method_exists($attribute, 'get_visible') || !$attribute->get_visible()) {
                                            continue;
                                        }

                                        $label = function_exists('wc_attribute_label') ? wc_attribute_label($attribute->get_name()) : $attribute->get_name();
                                        $valueParts = [];

                                        if ($attribute->is_taxonomy()) {
                                            $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                                            if (!empty($terms)) {
                                                foreach ($terms as $t) { $valueParts[] = esc_html($t); }
                                            }
                                        } else {
                                            $options = $attribute->get_options();
                                            if (!empty($options)) {
                                                foreach ($options as $opt) { $valueParts[] = esc_html(wc_clean($opt)); }
                                            }
                                        }

                                        if (empty($valueParts)) { continue; }
                                        $valueStr = implode(' | ', $valueParts);
                                        ?>
                                        <div class="spec-item flex flex-col gap-05 py-10 md:py-20 px-15 md:px-40 bg-black-03">
                                            <div class="spec-name yekan-16 md:yekan-20 color-black-80"><?php echo esc_html($label); ?></div>
                                            <div class="spec-value yekan-14 md:yekan-18 color-black-50"><?php echo esc_html($valueStr); ?></div>
                                        </div>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
            <?php if ($product_review) : ?>
                
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-technical-review-content">بررسی تخصصی <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-technical-review-content" class="tab-content-item px-15 md:px-25 py-25 bg-black-03">
                        <div class="flex flex-col gap-20">
                            <?php
                            $x = 0;
                            foreach ($product_review as $review) :
                                $x++;
                            ?>
                            <div class="technical-review-item slide-down-wrapper flex flex-col gap-10 <?php echo $x == 1 ? 'active' : ''; ?>">
                                    <div class="technical-review-title slide-down-trigger yekan-18 md:yekan-24 color-primary flex gap-10 items-center cursor-pointer">
                                        <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span><?php echo $review['product_review_title'] ?: ' ' ; ?></span>
                                    </div>
                                    <div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80">
                                        <?php if($review['items']) : foreach ($review['items'] as $spec) : ?>
                                            <div class="spec-item flex flex-col md:flex-row md:gap-10">
                                                <div class="spec-name"><?php echo $spec['label']; ?></div>
                                                <div class="spec-value"><?php echo $spec['value']; ?></div>
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($product_size_images) : ?>
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-dimensions-content">ابعاد محصول <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-dimensions-content" class="tab-content-item  bg-black-03 py-20">
                        <div class="flex flex-col md:flex-row items-center justify-center w-full">
                            <?php foreach ($product_size_images as $image) : ?>
                                <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
            <?php if ($product_maintenance) : ?>
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-maintenance-content">نحوه نگهداری محصول <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-maintenance-content" class="tab-content-item yekan-14 md:yekan-18 px-15 md:px-40 py-25 color-black-60 bg-black-03">
                        <?php echo ($product_maintenance); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($production_process_video_link) : ?>
                <div class="accordion-wrapper">
                    <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-production-content">روند تولید <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div id="tab-production-content" class="tab-content-item  px-25 py-25 bg-black-03">
                        <div class="flex flex-col items-center gap-20">
                            <div class="title yekan-14 md:yekan-26 color-black-80">
                                <?php echo ($production_process_title); ?>
                            </div>
                            <div class="production-process-video relative w-full">
                                <a class="video relative video-lightbox" data-video-url="<?php echo esc_url($production_process_video_link); ?>">
                                    <img class=" flex" src="<?php echo esc_url($production_process_video_poster['url'] ?? ''); ?>" alt="<?php echo esc_attr($production_process_title); ?>">
                                    <div class="absolute center z-index-5">
                                        <img class="transform" src="<?php echo esc_url($play_icon_src); ?>" alt="Play Icon">
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="accordion-wrapper">
                <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-faqs-content">پرسش و پاسخ <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div id="tab-faqs-content" class="tab-content-item px-15 md:px-25 py-25 bg-black-03">
                    <?php
                    // Render Product Questions & Answers inside this tab.
                    // Custom template shows questions (comments with type 'question') and their answers.
                    get_template_part('template-parts/product/questions-answers');
                    ?>
                </div>
            </div>
            <div class="accordion-wrapper">
                <div class="accordion-tab-trigger flex items-center justify-between md:hidden yekan-16 color-black-80 pointer" data-target="tab-reviews-content">نظرات مشتریان <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.710938 5.69824L5.71094 0.712406L10.7109 5.69824" stroke="#292D32" stroke-width="1.42451" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div id="tab-reviews-content" class="tab-content-item  px-15 md:px-25 py-25 bg-black-03">
                    <?php
                    // Render WooCommerce product reviews (list + form) inside this tab.
                    // WooCommerce hooks into comments_template() to load single-product-reviews.php for products.
                    comments_template();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>