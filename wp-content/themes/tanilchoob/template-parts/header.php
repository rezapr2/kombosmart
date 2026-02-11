<?php

use TanilChoob\Theme\Helper;

$header_logo = get_field('header_logo', 'options');
?>
<header id="main_header" class="header bg-white">
    <div class="top-row flex items-center justify-between container">
        <div class="top-row__right flex items-center">
            <a class="logo hidden md:flex" href="<?php echo home_url(); ?>">
                <img src="<?php echo isset($header_logo['url']) ? $header_logo['url'] : ''; ?>"
                    alt="<?php bloginfo('name'); ?>">
            </a>
            <div class="search">
                <form class="search-form flex items-center" action="<?php echo home_url(); ?>" method="get">
                    <input class="transition" type="text" name="s" placeholder="جستجو در تانیل چوب">
                    <button type="submit" class="btn btn--primary flex items-center">
                        <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/search-normal.svg')); ?>
                    </button>
                </form>
            </div>
        </div>
        <div class="top-row__left hidden md:flex ">
            <div class="bascket-btn flex item-center transition pointer">
                <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/shopping-cart.svg')); ?>
            </div>
            <div class="sign-up-btn flex items-center transition pointer">
                <div href="<?php echo home_url(); ?>/sign-up" class="btn btn--primary color-primary">ورود/ثبت نام</div>
            </div>
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
</header>
<div class="products-menu-overlay"></div>
