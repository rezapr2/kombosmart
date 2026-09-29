<?php

$header_logo        = get_field('header_logo', 'option');
$header_logo_mobile = get_field('header_logo_mobile', 'option');
$account_url        = get_permalink(get_option('woocommerce_myaccount_page_id'));
$cart_count         = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<header id="main_header" class="header">
    <div class="header__bar container">
        <div class="top-row flex items-center justify-between">
            <div class="top-row__right flex items-center">
                <button id="mobile-main-menu-trigger" class="hamburger md:hidden flex flex-col items-center justify-between" type="button" aria-label="باز کردن منو">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                <a class="logo flex items-center" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (!empty($header_logo['url'])) : ?>
                        <img class="<?php echo !empty($header_logo_mobile['url']) ? 'hidden md:flex' : 'flex'; ?>" src="<?php echo esc_url($header_logo['url']); ?>"
                            width="<?php echo esc_attr($header_logo['width'] ?? ''); ?>" height="<?php echo esc_attr($header_logo['height'] ?? ''); ?>"
                            alt="<?php bloginfo('name'); ?>">
                        <?php if (!empty($header_logo_mobile['url'])) : ?>
                            <img class="flex md:hidden" src="<?php echo esc_url($header_logo_mobile['url']); ?>"
                                width="<?php echo esc_attr($header_logo_mobile['width'] ?? ''); ?>" height="<?php echo esc_attr($header_logo_mobile['height'] ?? ''); ?>"
                                alt="<?php bloginfo('name'); ?>">
                        <?php endif; ?>
                    <?php else : ?>
                        <?php get_template_part('template-parts/components/logo-mark'); ?>
                        <span class="logo__text"><?php bloginfo('name'); ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="search flex-1">
                <form class="search-form flex items-center relative" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
                    <input class="transition" type="text" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="جستجوی لپ‌تاپ، موبایل، ساعت هوشمند..." aria-label="جستجو">
                    <input type="hidden" name="post_type" value="product">
                    <button type="submit" class="search-form__btn flex items-center justify-center" aria-label="جستجو">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M11.5 21a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19ZM22 22l-2-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </form>
            </div>

            <div class="top-row__left items-center gap-10 hidden md:flex">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('wishlist')); ?>" class="sw-icon-btn minicart__icon-wish" aria-label="لیست علاقه‌مندی‌ها">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12.62 20.81c-.34.12-.9.12-1.24 0C8.48 19.82 2 15.69 2 8.69 2 5.6 4.49 3.1 7.56 3.1c1.82 0 3.43.88 4.44 2.24 1.01-1.36 2.63-2.24 4.44-2.24C19.51 3.1 22 5.6 22 8.69c0 7-6.48 11.13-9.38 12.12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>

                <div class="minicart-wrapper relative">
                    <button type="button" class="sw-icon-btn relative" id="tc-minicart-trigger" aria-expanded="false" aria-controls="tc-minicart-dropdown" aria-label="سبد خرید">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M2 2h1.74c1.08 0 1.93.93 1.84 2l-.83 9.96A2.8 2.8 0 0 0 7.54 17h10.65c1.44 0 2.7-1.18 2.81-2.61l.54-7.5A2.77 2.77 0 0 0 18.73 3.9H5.82M16.25 22a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM8.25 22a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM9 8h12" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span id="tc-minicart-badge-wrap"><?php if ($cart_count > 0) : ?><span class="minicart-badge absolute flex item-center bg-primary"><?php echo esc_html($cart_count); ?></span><?php endif; ?></span>
                    </button>
                    <div class="minicart absolute bg-white" id="tc-minicart" role="dialog" aria-label="سبد خرید" dir="rtl">
                        <div class="minicart__header">
                            <div class="minicart__title-row">
                                <span class="minicart__title yekan-20 bold">سبد خرید شما</span>
                                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="minicart__view-cart inline-block color-primary yekan-14">مشاهده سبد خرید</a>
                            </div>
                        </div>
                        <?php echo \TanilChoob\Theme\Frontend::render_minicart(); ?>
                    </div>
                    <div class="minicart-overlay" id="tc-minicart-overlay"></div>
                </div><!-- /.minicart-wrapper -->

                <div class="profile-wrapper relative">
                    <?php if (is_user_logged_in()) :
                        $current_user = wp_get_current_user();
                        $display      = trim($current_user->first_name . ' ' . $current_user->last_name);
                    ?>
                        <button type="button" id="tc-profile-trigger" class="sign-up-btn sw-btn sw-btn--soft" aria-expanded="false" aria-controls="tc-profile-dropdown">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12.16 10.87c-.1-.01-.22-.01-.33 0a4.42 4.42 0 0 1-4.27-4.43C7.56 3.99 9.54 2 12 2a4.44 4.44 0 0 1 .16 8.87ZM7.16 14.56c-2.42 1.62-2.42 4.26 0 5.87 2.75 1.84 7.26 1.84 10.01 0 2.42-1.62 2.42-4.26 0-5.87-2.74-1.83-7.25-1.83-10.01 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <?php echo esc_html($display ?: $current_user->display_name); ?>
                        </button>
                        <div class="profile-dropdown absolute bg-white" id="tc-profile-dropdown" role="dialog" aria-label="حساب کاربری" dir="rtl">
                            <ul class="profile-dropdown__list flex flex-col m-0 p-0">
                                <li class="profile-dropdown__item">
                                    <a href="<?php echo esc_url($account_url); ?>" class="profile-dropdown__link flex items-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12.16 10.87c-.1-.01-.22-.01-.33 0a4.42 4.42 0 0 1-4.27-4.43C7.56 3.99 9.54 2 12 2a4.44 4.44 0 0 1 .16 8.87ZM7.16 14.56c-2.42 1.62-2.42 4.26 0 5.87 2.75 1.84 7.26 1.84 10.01 0 2.42-1.62 2.42-4.26 0-5.87-2.74-1.83-7.25-1.83-10.01 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        مدیریت حساب کاربری
                                    </a>
                                </li>
                                <li class="profile-dropdown__item">
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="profile-dropdown__link flex items-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M7.5 7.67V6.7c0-2.25 1.81-4.46 4.06-4.67A4.5 4.5 0 0 1 16.5 6.51v1.38M9 22h6c4.02 0 4.74-1.61 4.95-3.57l.75-6c.27-2.44-.43-4.43-4.7-4.43H8c-4.27 0-4.97 1.99-4.7 4.43l.75 6C4.26 20.39 4.98 22 9 22Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        سفارش‌های من
                                    </a>
                                </li>
                                <li class="profile-dropdown__item profile-dropdown__item--logout">
                                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="profile-dropdown__link flex items-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M8.9 7.56c.31-3.6 2.16-5.07 6.21-5.07h.13c4.47 0 6.26 1.79 6.26 6.26v6.52c0 4.47-1.79 6.26-6.26 6.26h-.13c-4.02 0-5.87-1.45-6.2-4.99M15 12H3.62M5.85 8.65 2.5 12l3.35 3.35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        خروج از حساب کاربری
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="profile-overlay" id="tc-profile-overlay"></div>
                    <?php else : ?>
                        <a href="<?php echo esc_url($account_url); ?>" class="sign-up-btn sw-btn sw-btn--dark">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12.16 10.87c-.1-.01-.22-.01-.33 0a4.42 4.42 0 0 1-4.27-4.43C7.56 3.99 9.54 2 12 2a4.44 4.44 0 0 1 .16 8.87ZM7.16 14.56c-2.42 1.62-2.42 4.26 0 5.87 2.75 1.84 7.26 1.84 10.01 0 2.42-1.62 2.42-4.26 0-5.87-2.74-1.83-7.25-1.83-10.01 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            ورود / ثبت‌نام
                        </a>
                    <?php endif; ?>
                </div><!-- /.profile-wrapper -->
            </div>
        </div>

        <div class="header__menu hidden md:flex items-center">
            <div class="products-menu-dropdown flex items-center relative pointer">
                <div class="icon flex">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 10h2c2 0 3-1 3-3V5c0-2-1-3-3-3H5C3 2 2 3 2 5v2c0 2 1 3 3 3ZM17 10h2c2 0 3-1 3-3V5c0-2-1-3-3-3h-2c-2 0-3 1-3 3v2c0 2 1 3 3 3ZM17 22h2c2 0 3-1 3-3v-2c0-2-1-3-3-3h-2c-2 0-3 1-3 3v2c0 2 1 3 3 3ZM5 22h2c2 0 3-1 3-3v-2c0-2-1-3-3-3H5c-2 0-3 1-3 3v2c0 2 1 3 3 3Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="products-menu-dropdown__title">دسته‌بندی محصولات</div>
                <div class="arrow flex transition">
                    <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <div class="products-menu-dropdown__content">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'product_categories_menu',
                        'menu_class'     => 'flex flex-col m-0 p-0',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'walker'         => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
                    ]);
                    ?>
                </div>
            </div>
            <nav class="menu" aria-label="منوی اصلی">
                <?php
                wp_nav_menu([
                    'theme_location' => 'main_menu',
                    'menu_class'     => 'flex items-center m-0 p-0',
                    'fallback_cb'    => false,
                    'walker'         => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
                ]);
                ?>
            </nav>
        </div>
    </div>

    <div id="mobile-main-menu-dropdown" class="mobile-main-menu-dropdown bg-white transition absolute md:hidden">
        <div class="mobile-main-menu-dropdown__content">
            <?php
            wp_nav_menu([
                'theme_location' => 'main_menu',
                'menu_class'     => 'mobile-main-menu-list m-0 p-0',
                'container'      => false,
                'fallback_cb'    => false,
                'walker'         => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
            ]);
            ?>
        </div>
    </div>
</header>
<div class="products-menu-overlay"></div>
