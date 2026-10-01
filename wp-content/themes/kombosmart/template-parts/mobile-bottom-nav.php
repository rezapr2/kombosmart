<?php 
$contact_mode = \TanilChoob\Theme\Helper::get_options_field('contact_mode');
?>
<div class="mobile-bottom-nav md:hidden fixed bottom-0 left-0 right-0 bg-white px-15 py-15 ">
    <div class="flex flex-row-reverse items-center justify-between">
        <!-- Cart -->
        <?php
        $cart_count_mobile = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        ?>
        <a href="<?php echo $contact_mode ? '#' : wc_get_cart_url(); ?>" class="flex flex-col items-center color-black relative">
            <span class="relative">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.66406 1.66699H3.11407C4.01407 1.66699 4.7224 2.44199 4.6474 3.33366L3.95573 11.6337C3.83906 12.992 4.91406 14.1587 6.28072 14.1587H15.1557C16.3557 14.1587 17.4057 13.1753 17.4974 11.9837L17.9474 5.73366C18.0474 4.35033 16.9974 3.22532 15.6057 3.22532H4.8474" stroke="#2F2F2F" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.5417 18.3333C14.117 18.3333 14.5833 17.867 14.5833 17.2917C14.5833 16.7164 14.117 16.25 13.5417 16.25C12.9664 16.25 12.5 16.7164 12.5 17.2917C12.5 17.867 12.9664 18.3333 13.5417 18.3333Z" stroke="#2F2F2F" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.8776 18.3333C7.4529 18.3333 7.91927 17.867 7.91927 17.2917C7.91927 16.7164 7.4529 16.25 6.8776 16.25C6.30231 16.25 5.83594 16.7164 5.83594 17.2917C5.83594 17.867 6.30231 18.3333 6.8776 18.3333Z" stroke="#2F2F2F" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.5 6.66699H17.5" stroke="#2F2F2F" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span id="tc-minicart-badge-wrap-mobile"><?php if ( $cart_count_mobile > 0 ) : ?><span class="minicart-badge absolute flex item-center bg-primary"><?php echo esc_html( $cart_count_mobile ); ?></span><?php endif; ?></span>
            </span>
            <span class="yekan-11">سبد خرید</span>
        </a>

        <!-- Search -->
        <a href="#" class="flex flex-col items-center color-black search-trigger-mobile">
            <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.61059 17.5003C13.995 17.5003 17.5493 13.9559 17.5493 9.58366C17.5493 5.2114 13.995 1.66699 9.61059 1.66699C5.22616 1.66699 1.67188 5.2114 1.67188 9.58366C1.67188 13.9559 5.22616 17.5003 9.61059 17.5003Z" stroke="#292D32" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18.3822 18.3337L16.7109 16.667" stroke="#292D32" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class=" yekan-11">جستجو</span>
        </a>

        <!-- Home -->
        <a href="<?php echo home_url(); ?>" class="flex flex-col items-center color-black">
            <svg viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.53002 2.37003L3.0321 5.87491C2.28106 6.45905 1.67188 7.70245 1.67188 8.64542V14.829C1.67188 16.765 3.24907 18.3506 5.18509 18.3506H14.8485C16.7845 18.3506 18.3617 16.765 18.3617 14.8374V8.76225C18.3617 7.75252 17.6858 6.45905 16.8597 5.88325L11.7025 2.2699C10.5342 1.45209 8.65658 1.49382 7.53002 2.37003Z" stroke="#2F2F2F" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.0156 15.0123V12.5088" stroke="#2F2F2F" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class=" yekan-11">خانه</span>
        </a>

        <!-- Categories -->
        <a href="#" id="mobile-categories-trigger" class="flex flex-col items-center color-black mobile-menu-trigger">
           <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 5.83301H17.5" stroke="#1F4A8A" stroke-width="1.2" stroke-linecap="round"/>
                <path d="M2.5 10H17.5" stroke="#1F4A8A" stroke-width="1.2" stroke-linecap="round"/>
                <path d="M2.5 14.167H17.5" stroke="#1F4A8A" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
            <span class=" yekan-11">دسته بندی</span>
        </a>

        <!-- Account -->
        <a href="<?php echo $contact_mode ? '#' : get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="flex flex-col items-center color-black">
            <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10.1643 9.05866C10.0808 9.05033 9.98048 9.05033 9.88856 9.05866C7.8997 8.99199 6.32031 7.36699 6.32031 5.36699C6.32031 3.32533 7.97491 1.66699 10.0306 1.66699C12.078 1.66699 13.7409 3.32533 13.7409 5.36699C13.7326 7.36699 12.1532 8.99199 10.1643 9.05866Z" stroke="#292D32" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.98546 12.133C3.96318 13.483 3.96318 15.683 5.98546 17.0247C8.28351 18.558 12.0523 18.558 14.3504 17.0247C16.3726 15.6747 16.3726 13.4747 14.3504 12.133C12.0607 10.608 8.29187 10.608 5.98546 12.133Z" stroke="#292D32" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class=" yekan-11">حساب کاربری</span>
        </a>
    </div>
</div>

<!-- Mobile Category Drawer -->
<div id="mobile-category-drawer" class="mobile-drawer fixed inset-0 bg-white z-[1000] hidden flex flex-col w-full h-full">
    <!-- Header -->
    <div class="drawer-header flex items-center gap-15">
        <div id="close-mobile-drawer" class="p-5">
           <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
             <path d="M0.75 12.7514L6.73371 6.7507L0.75 0.75" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="yekan-14">دسته بندی</span>
        
    </div>
    
    <!-- Content -->
    <div class="drawer-content flex flex-1 overflow-hidden h-full relative">
        <?php 
        wp_nav_menu([
            'theme_location' => 'product_categories_menu', 
            'menu_class' => 'mobile-drawer-menu relative flex flex-col w-full h-full m-0 p-0',
            'container' => false,
            'walker' => new \TanilChoob\Theme\Walker_Nav_Menu_Custom()
        ]); 
        ?>
    </div>
</div>
