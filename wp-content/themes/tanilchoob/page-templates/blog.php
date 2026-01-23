<?php

/**
 * Template Name: Blog
 * Description: A custom Blog page template with info and form.
 * @package TanilChoob
 */

defined('ABSPATH') || exit;

use TanilChoob\Theme\Helper;

get_header();

// woocommerce_breadcrumb
if (function_exists('woocommerce_breadcrumb')) {
    woocommerce_breadcrumb();
}


?>
<div id="page-warranty" class="page-template-warranty">
    <section class="top_posts container flex flex-col items-center">
        <div class="flex">
            <div class="highted_post post">
            </div>
            <div class="grid grid-cols-2">

            </div>
        </div>
    </section>
    <section class="top_ads">
    </section>
    <section class="flex">
        <aside class="sidebar">
        </aside>
        <main class="main_posts">
        </main>
    </section>
</div>
<?php
get_footer();
/* Omit closing PHP tag to avoid "headers already sent" issues. */