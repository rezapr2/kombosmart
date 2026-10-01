<?php

/**
 * 404 — page not found.
 */

defined('ABSPATH') || exit;

get_header();
?>
<main class="page-404 container">
    <div class="page-404__card tone-violet flex flex-col items-center text-center">
        <span class="page-404__code" aria-hidden="true">۴۰۴</span>
        <h1 class="page-404__title">صفحه‌ای که دنبالش بودید پیدا نشد</h1>
        <p class="page-404__text">ممکن است آدرس اشتباه باشد یا این صفحه جابه‌جا شده باشد. از جستجو یا دسته‌بندی محصولات کمک بگیرید.</p>
        <form class="page-404__search flex items-center" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
            <input type="text" name="s" placeholder="جستجوی محصول..." aria-label="جستجو">
            <input type="hidden" name="post_type" value="product">
            <button type="submit" class="sw-btn sw-btn--primary">جستجو</button>
        </form>
        <a class="sw-btn sw-btn--light" href="<?php echo esc_url(home_url('/')); ?>">بازگشت به صفحه اصلی</a>
    </div>
</main>
<?php
get_footer();
