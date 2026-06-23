<?php

use TanilChoob\Theme\Helper;

$page_id = get_the_ID();
$features = get_field('features', $page_id);
$comod_types = get_field('comod_types', $page_id);
$soffa = get_field('soffa', $page_id);
$tv_desk = get_field('tv_desk', $page_id);
$console_desk = get_field('console_desk', $page_id);
$sleep_products = get_field('sleep_products', $page_id);
$food_products = get_field('food_products', $page_id);
$sleep_pack = get_field('sleep_pack', $page_id);
$soffa_desk = get_field('soffa_desk', $page_id);
$categories_section_subtitle = get_field('categories_section_subtitle', $page_id);

$arrow = Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-left-btn.svg'));
?>
<section class="home-product-categories flex flex-col container">
    <div class="title yekan-16 md:yekan-24 color-black thin text-center color-black-80">
        <strong class="color-primary">دسته بندی</strong> محصولات
    </div>
    <?php if(isset($categories_section_subtitle) && $categories_section_subtitle): ?>
    <div class="description yekan-12 md:yekan-16 color-black-60 text-center">
        <?php echo $categories_section_subtitle; ?>
    </div>
    <?php endif; ?>
    <div class="categories gap-04 md:gap-07 flex flex-col-reverse md:flex-row">
        <div class="category-pack gap-04 md:gap-07 flex flex-col">
            <div class="row gap-04 md:gap-07 flex h-100">
                <?php
                $img = $comod_types['image'] ?: null;
                $link = $comod_types['link'] ?: null;
                ?>
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'انواع کمد') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'انواع کمد' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php
                $img = $soffa['image'] ?: null;
                $link = $soffa['link'] ?: null;
                ?>
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'soffa') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'مبلمان' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="row gap-04 md:gap-07 flex flex-row-reverse md:flex-row h-100">
            <?php
            $img = $tv_desk['image'] ?: null;
            $link = $tv_desk['link'] ?: null;
            ?>
            <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                <?php
                if ($img) {
                    echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'tv desk') . '" />';
                }
                ?>
                <div class="content">
                    <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'tv desk' ?></div>
                    <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                        <div class="arrow flex">
                            <?php echo $arrow; ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php
            $img = $console_desk['image'] ?: null;
            $link = $console_desk['link'] ?: null;
            ?>
            <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item md:hidden relative" >
                <?php
                if ($img) {
                    echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'console desk') . '" />';
                }
                ?>
                <div class="content">
                    <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'console desk' ?></div>
                    <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                        <div class="arrow flex">
                            <?php echo $arrow; ?>
                        </div>
                    </div>
                </div>
            </a>
            </div>
        </div>
        <?php
        $img = $console_desk['image'] ?: null;
        $link = $console_desk['link'] ?: null;
        ?>
        <div class="category-pack gap-07 hidden md:flex flex-wrap">
            <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative" >
                <?php
                if ($img) {
                    echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'console desk') . '" />';
                }
                ?>
                <div class="content">
                    <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'console desk' ?></div>
                    <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                        <div class="arrow flex">
                            <?php echo $arrow; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php
        $img = $sleep_products['image'] ?: null;
        $link = $sleep_products['link'] ?: null;
        ?>
        <div class="category-pack gap-04 md:gap-07 flex flex-col">
            <div class="row gap-04 md:gap-07 flex h-100">
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'sleep products') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'sleep pack' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php
                $img = $sleep_pack['image'] ?: null;
                $link = $sleep_pack['link'] ?: null;
                ?>
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'sleep pack') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'sleep pack' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php
            $img = $food_products['image'] ?: null;
            $link = $food_products['link'] ?: null;
            ?>
            <div class="row gap-04 md:gap-07 flex flex-row-reverse md:flex-row h-100">
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'food products') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'food products' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php
                $img = $soffa_desk['image'] ?: null;
                $link = $soffa_desk['link'] ?: null;
                ?>
                <a href="<?php echo $link['url'] ?: '#' ?>" class="category-item relative">
                    <?php
                    if ($img) {
                        echo '<img class="w-100 h-100 object-cover" fetchpriority=high src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'soffa desk') . '" />';
                    }
                    ?>
                    <div class="content">
                        <div class="title yekan-14 md:yekan-20 color-white-80 bold"><?php echo $link['title'] ?: 'soffa desk' ?></div>
                        <div class="more w-fit yekan-15 color-white hidden md:flex items-center transition"> بیشتر
                            <div class="arrow flex">
                                <?php echo $arrow; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</section>