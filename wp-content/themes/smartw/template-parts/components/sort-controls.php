<?php
/**
 * Sort controls for product archives (chips on desktop, select on mobile) + notices.
 */
?>
<div class="container mt-20">
    <div class="category-controls py-25">
        <?php
        // Show notices and result count (without default dropdown ordering)
        if (function_exists('woocommerce_output_all_notices')) {
            woocommerce_output_all_notices();
        }

        // Current orderby from query (fallback to date)
        $current_orderby = isset($_GET['orderby']) ? wc_clean(wp_unslash($_GET['orderby'])) : 'date';

        // Define our custom sort options to match the desired UI
        $sort_options = array(
            'date'       => 'جدیدترین',      // Newest
            'price-desc' => 'گران‌ترین',      // Most expensive
            'price'      => 'ارزان‌ترین',     // Cheapest
            'popularity' => 'پربازدیدترین',   // Most viewed/popular
            // You can add 'rating' => 'بالاترین امتیاز' if needed
        );
        ?>
        <div class="flex flex-col md:flex-row gap-20 items-center justify-between">
            <div class="w-full md:w-auto justify-between flex items-center gap-15">
                <div class="flex items-center color-black-30">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 7H21" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M6 12H18" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M10 17H14" stroke="black" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="yekan-14 color-black-60">مرتب سازی بر اساس:</span>
                <div class="hidden md:flex items-center gap-20">
                    <?php foreach ($sort_options as $orderby => $label):
                        // Build link preserving existing query args while setting orderby
                        $url = add_query_arg(array('orderby' => $orderby));
                        $is_active = ($current_orderby === $orderby);
                    ?>
                        <a href="<?php echo esc_url($url); ?>" class="sort-chip<?php echo $is_active ? ' is-active' : ''; ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="md:hidden">
                    <div class="sort-select-wrapper">
                        <select aria-label="مرتب سازی" onchange="if(this.value){window.location.href=this.value;}" class="sort-select yekan-14 color-black-80">
                            <?php foreach ($sort_options as $orderby => $label):
                                $url = add_query_arg(array('orderby' => $orderby));
                                $is_active = ($current_orderby === $orderby);
                            ?>
                                <option value="<?php echo esc_url($url); ?>" <?php echo $is_active ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>                
            </div>
            <?php get_template_part('template-parts/components/products-video-button'); ?>
        </div>
    </div>
</div>
