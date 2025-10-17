<?php

/**
 * Footer
 *
 * The footer template.
 *
 * @since   1.0.0
 * @package WP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use TanilChoob\Theme\Helper;

?>
<footer id="main_footer" class="footer">
    <div class="container">
        <div class="flex justify-between">
            <div class="flex flex-col gap-07">
                <div class="flex">
                    <?php $img = get_field('footer_logo', 'options');
                    if ($img):
                    ?>
                        <img class="footer_logo h-auto" src="<?php echo $img['url']; ?>" alt="<?php echo $img['alt']; ?>">
                    <?php endif; ?>
                    <div class="footer_socials flex">
                        <?php $socials = get_field('social_networks', 'options');
                        if ($socials):
                            foreach ($socials as $key => $social):
                        ?>
                                <a class="footer_social transition border flex item-center" href="<?php echo $social; ?>" target="_blank">
                                    <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/' . $key . '.svg')); ?>
                                </a>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                <div class="description border yekan-16 color-white-70">
                    <?php echo get_field('about_tanil', 'options'); ?>
                </div>
            </div>
            <div class="flex flex-col list list_1 border">
                <?php $list_1 = get_field('list_1', 'options'); ?>
                <div class="title color-white-80 yekan-22 text-center">
                    <?php echo ($list_1['title'] ?: ''); ?>
                </div>
                <ul class="items flex flex-col gap-10">
                    <?php
                    $list = $list_1['list'] ?: [];
                    foreach ($list as $item) {
                        $link = $item['link'];
                        echo '<li><a class="color-white-50 yekan-20" href="' . $link['url'] . '">' . $link['title'] . '</a></li>';
                    }
                    ?>

                </ul>
            </div>
            <div class="flex flex-col list list_2 border">
                <?php $list_2 = get_field('list_2', 'options'); ?>
                <div class="title color-white-80 yekan-22 text-center">
                    <?php echo ($list_2['title'] ?: ''); ?>
                </div>
                <ul class="items flex flex-col gap-10">
                    <?php
                    $list = $list_2['list'] ?: [];
                    foreach ($list as $item) {
                        $link = $item['link'];
                        echo '<li><a class="color-white-50 yekan-20" href="' . $link['url'] . '">' . $link['title'] . '</a></li>';
                    }
                    ?>

                </ul>
            </div>
            <div class="flex flex-col tanil-address flex-shrink-0 gap-07">
                <div class="item flex phone_numbers gap-05">
                    <div class="icon border flex item-center flex-shrink-0 h-100">
                        <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/call.svg')); ?>
                    </div>
                    <div class="text border flex item-center yekan-16 color-white-70 w-full">
                        <?php echo get_field('phone_numbers', 'options'); ?>
                    </div>
                </div>
                <div class="item flex location gap-05" >
                    <div class="icon border flex item-center flex-shrink-0 h-100">
                        <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/location.svg')); ?>
                    </div>
                    <a class="text border flex item-center yekan-16 color-white-70 w-full" href="<?php echo get_field('location', 'options'); ?>">
                        لوكيشن شعب وكارخانه
                    </a>
                </div>
                <div class="item flex address gap-05">
                    <div class="icon border flex item-center flex-shrink-0 h-100">
                        <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/map.svg')); ?>
                    </div>
                    <div class="text border yekan-13 color-white-70 w-full">
                        <?php echo get_field('footer_address', 'options'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright flex justify-center yekan-13 color-white-30">
            <?php echo get_field('footer_copyright_text', 'options'); ?>
        </div>
    </div>
</footer>

<?php
wp_footer();
?>


</body>

</html>