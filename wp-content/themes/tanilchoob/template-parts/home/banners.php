<?php
$banners = $args['banners'] ?: [];
if (empty($banners)) {
    return;
}
?>
<div class="banners flex flex-col md:flex-row gap-06 md:gap-10 md:gap-20 py-20 md:py-40 container">
    <?php foreach ($banners as $banner) : ?>
        <div class="banner-item flex-1">
            <?php
            $img = $banner['image'] ?: null;
            $mobile_image = $banner['mobile_image'] ?: null;
            $link = $banner['link'] ?: null;
            if (wp_is_mobile() && $mobile_image) {
                $img = $mobile_image;
            }
            if ($img) {
                echo '<a class="flex" href="' . $link['url'] . '" target="_blank" rel="noopener noreferrer">';
                echo '<img class="w-full" src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'banner') . '" />';
                echo '</a>';
            }
            ?>
        </div>
    <?php endforeach; ?>
</div>