<?php

use TanilChoob\Theme\Helper;

$header_logo = get_field('header_logo', 'option');
$header_logo_mobile = get_field('header_logo_mobile', 'option');
?>
<header id="main_header" class="header bg-white">
    <div class="top-row flex items-center justify-between container">
        <div class="top-row__right flex flex-row-reverse md:flex-row items-center">
            <a class="logo" href="<?php echo home_url(); ?>">
                <img class="hidden md:flex" src="<?php echo isset($header_logo['url']) ? $header_logo['url'] : ''; ?>"
                    alt="<?php bloginfo('name'); ?>">
                <?php if (isset($header_logo_mobile['url']) && $header_logo_mobile['url'] !== '') : ?>
                <img class="flex md:hidden" src="<?php echo isset($header_logo_mobile['url']) ? $header_logo_mobile['url'] : ''; ?>"
                    alt="<?php bloginfo('name'); ?>">
                <?php endif; ?>
            </a>
            <div class="search">
                <form class="search-form flex items-center" action="<?php echo home_url(); ?>" method="get">
                    <input class="transition" type="text" name="s" placeholder="جستجو در تانیل چوب">
                    <button type="submit" class="btn btn--primary flex items-center">
                        <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/search-normal.svg')); ?>
                    </button>
                </form>
            </div>

            <button id="mobile-main-menu-trigger" class="hamburger md:hidden flex flex-col items-center justify-between bg-black-05" type="button" aria-label="باز کردن منو">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
        <div class="top-row__left hidden md:flex ">
            <div class="minicart-wrapper">
                <button type="button" class="bascket-btn flex item-center transition pointer" id="tc-minicart-trigger" aria-expanded="false" aria-controls="tc-minicart-dropdown">
                    <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/shopping-cart.svg')); ?>
                    <?php
                    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                    if ( $cart_count > 0 ) : ?>
                    <span class="minicart-badge"><?php echo esc_html( $cart_count ); ?></span>
                    <?php endif; ?>
                </button>
                <div class="minicart" id="tc-minicart" role="dialog" aria-label="سبد خرید" dir="rtl">
                    <div class="minicart__header">
                        <div class="minicart__icons">
                            <span class="minicart__icon-cart is-active">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M2 2h1.74c1.08 0 1.93.93 1.84 2l-.83 9.96a2.796 2.796 0 002.79 3.03H18.19c1.45 0 2.73-1.07 2.85-2.51l.54-7.5c.13-1.64-1.14-2.98-2.79-2.98H5.82" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.25 22a1.25 1.25 0 100-2.5 1.25 1.25 0 000 2.5zM8.25 22a1.25 1.25 0 100-2.5 1.25 1.25 0 000 2.5z" fill="currentColor"/><path d="M9 8h11" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url('wishlist') ); ?>" class="minicart__icon-wish">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12.62 20.81c-.34.12-.9.12-1.24 0C8.48 19.82 2 15.69 2 8.69 2 5.6 4.49 3.1 7.56 3.1c1.82 0 3.43.88 4.44 2.24 1.01-1.36 2.63-2.24 4.44-2.24C19.51 3.1 22 5.6 22 8.69c0 7-6.48 11.13-9.38 12.12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                        <div class="minicart__title-row">
                            <span class="minicart__title">سبد خرید شما</span>
                            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="minicart__view-cart">مشاهده سبد خرید</a>
                        </div>
                    </div>
                    <?php echo \TanilChoob\Theme\Frontend::render_minicart(); ?>
                </div>
                <div class="minicart-overlay" id="tc-minicart-overlay"></div>
            </div>
            </div><!-- /.minicart-wrapper -->
            <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="sign-up-btn flex items-center transition pointer color-primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.1625 10.87C12.0625 10.86 11.9425 10.86 11.8325 10.87C9.4525 10.79 7.5625 8.84 7.5625 6.44C7.5625 3.99 9.5425 2 12.0025 2C14.4525 2 16.4425 3.99 16.4425 6.44C16.4325 8.84 14.5425 10.79 12.1625 10.87Z" stroke="#5D0E87" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7.15875 14.56C4.73875 16.18 4.73875 18.82 7.15875 20.43C9.90875 22.27 14.4188 22.27 17.1688 20.43C19.5888 18.81 19.5888 16.17 17.1688 14.56C14.4288 12.73 9.91875 12.73 7.15875 14.56Z" stroke="#5D0E87" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <?php if ( is_user_logged_in() ) :
                    $current_user = wp_get_current_user();
                    $first_name   = $current_user->first_name;
                    $last_name    = $current_user->last_name;
                    $display      = trim( "$first_name $last_name" );
                    echo esc_html( $display ?: $current_user->display_name );
                else : ?>
                ورود/ثبت نام
                <?php endif; ?>
            </a>
        </div>

    </div>
    <div class="header__menu hidden md:flex items-center container">
        <div class="products-menu-dropdown flex items-center relative pointer">
            <div class="icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 7H21" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M3 12H21" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M3 17H21" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="products-menu-dropdown__title yekan-16 color-black">
                دسته بندی محصولات
            </div>
            <div class="arrow transition">
                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L5 5L9 1" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <div class="products-menu-dropdown__content">
                <?php 
                wp_nav_menu([
                    'theme_location' => 'product_categories_menu', 
                    'menu_class' => 'flex flex-col m-0 p-0',
                    'container' => false,
                    'walker' => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
                ]); 
                ?>
            </div>
        </div>
        <nav class="menu">
            <?php 
            wp_nav_menu([
                'theme_location' => 'main_menu', 
                'menu_class' => 'flex items-center m-0 p-0',
                'walker' => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
            ]); 
            ?>
        </nav>
    </div>
    <div id="mobile-main-menu-dropdown" class="mobile-main-menu-dropdown bg-white transition absolute md:hidden">
    <div class="mobile-main-menu-dropdown__content">
        <?php 
        wp_nav_menu([
            'theme_location' => 'main_menu', 
            'menu_class' => 'mobile-main-menu-list m-0 p-0',
            'container' => false,
            'walker' => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
        ]); 
        ?>
    </div>
</div>
</header>
<div class="products-menu-overlay"></div>
