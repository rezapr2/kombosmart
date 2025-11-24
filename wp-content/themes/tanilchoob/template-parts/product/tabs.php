<?php
// Access the tabs context passed from the parent template.
$ctx = get_query_var('product_tabs');

$description = $ctx['description'] ?? '';
$has_specs = !empty($ctx['has_specs']);
$product_review = $ctx['product_review'] ?? '';

$product_cat = $ctx['product_cat'] ?? null;
$product_style = $ctx['product_style'] ?? null;
$product_group = $ctx['product_group'] ?? null;
$usage_material = $ctx['usage_material'] ?? null;
$material_of_bases = $ctx['material_of_bases'] ?? null;
$coating_material = $ctx['coating_material'] ?? null;
$wood_color = $ctx['wood_color'] ?? null;
$fabric_type = $ctx['fabric_type'] ?? null;
$fabric_color = $ctx['fabric_color'] ?? null;
$drawers_type = $ctx['drawers_type'] ?? null;


$product_size_images = $ctx['product_size_images'] ?? [];
$product_maintenance = $ctx['product_maintenance'] ?? '';

$production_process_video_link = $ctx['production_process_video_link'] ?? '';
$production_process_title = $ctx['production_process_title'] ?? '';
$production_process_video_poster = $ctx['production_process_video_poster'] ?? null;

// Theme asset helper replacement for play icon while preserving classes.
$play_icon_src = function_exists('get_theme_file_uri')
    ? get_theme_file_uri('images/play_icon.svg')
    : (get_stylesheet_directory_uri() . '/images/play_icon.svg');
?>

<div class="container mb-40">
    <div class="tab-contents">
        <div class="tabs flex w-full">
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
                <div id="tab-desc-content" class="tab-content-item yekan-18 px-40 py-25 color-black-60 bg-black-03 active">
                    <?php echo nl2br($description); ?>
                </div>
            <?php endif;
            if ($has_specs) : ?>
                <div id="tab-specs-content" class="tab-content-item py-20">
                    <div class="items grid grid-cols-2 gap-07">
                        <?php if ($product_cat) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">دسته بندی محصول</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($product_cat as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product_style) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">سبک محصول</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($product_style as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product_group) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">گروه محصول</div>
                                <div class="spec-value yekan-18 color-black-50"><?php echo $product_group; ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($usage_material) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">متریال مصرفی</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($usage_material as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($material_of_bases) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">جنس پایه‌ها (و ستون‌ها)</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($material_of_bases as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($coating_material) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">جنس روکش</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($coating_material as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($wood_color) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">رنگ چوب</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($wood_color as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($fabric_type) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">جنس پارچه</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($fabric_type as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($fabric_color) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">رنگ پارچه</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($fabric_color as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($drawers_type) : ?>
                            <div class="spec-item flex flex-col py-20 px-40 bg-black-03">
                                <div class="spec-name yekan-20 color-black-80">نوع کشوها</div>
                                <div class="spec-value yekan-18 color-black-50">
                                    <?php
                                    $cat_names = array();
                                    foreach ($drawers_type as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo implode(' | ', $cat_names);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endif; ?>
            <?php if ($product_review) : ?>
                
                <div id="tab-technical-review-content" class="tab-content-item px-25 py-25 bg-black-03">
                    <div class="flex flex-col gap-20">
                        <?php
                        $x = 0;
                        foreach ($product_review as $review) :
                            $x++;
                        ?>
                        <div class="technical-review-item slide-down-wrapper flex flex-col gap-10 <?php echo $x == 1 ? 'active' : ''; ?>">
                                <div class="technical-review-title slide-down-trigger yekan-24 color-primary flex gap-10 items-center cursor-pointer">
                                    <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.10156 1.09961L8.60156 8.57836L16.1016 1.09961" stroke="#5D0E87" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span><?php echo $review['product_review_title'] ?: ' ' ; ?></span>
                                </div>
                                <div class="technical-review-content slide-down-content flex flex-col gap-07 yekan-14 color-black-80">
                                    <?php if($review['items']) : foreach ($review['items'] as $spec) : ?>
                                        <div class="spec-item flex gap-10">
                                            <div class="spec-name"><?php echo $spec['label']; ?></div>
                                            <div class="spec-value"><?php echo $spec['value']; ?></div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($product_size_images) : ?>
                <div id="tab-dimensions-content" class="tab-content-item  bg-black-03 py-20">
                    <div class="flex items-center justify-center w-full">
                        <?php foreach ($product_size_images as $image) : ?>
                            <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endif; ?>
            <?php if ($product_maintenance) : ?>
                <div id="tab-maintenance-content" class="tab-content-item yekan-18 px-40 py-25 color-black-60 bg-black-03">
                    <?php echo ($product_maintenance); ?>
                </div>
            <?php endif; ?>
            <?php if ($production_process_video_link) : ?>
                <div id="tab-production-content" class="tab-content-item  px-25 py-25 bg-black-03">
                    <div class="flex flex-col items-center gap-20">
                        <div class="title yekan-26 color-black-80">
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
            <?php endif; ?>
            <div id="tab-reviews-content" class="tab-content-item  px-25 py-25 bg-black-03">
                <?php
                // Render WooCommerce product reviews (list + form) inside this tab.
                // WooCommerce hooks into comments_template() to load single-product-reviews.php for products.
                comments_template();
                ?>
            </div>
        </div>
    </div>
</div>