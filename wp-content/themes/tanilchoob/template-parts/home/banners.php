<?php
$banners = $args['banners'] ?: [];
$banners_mobile = $args['banners_mobile'] ?: [];
if (empty($banners)) {
    return;
}
?>
<div class="banners flex flex-col md:flex-row gap-07 md:gap-20 py-20 md:py-40 container">
    <?php foreach ($banners as $banner) : ?>
        <div class="banner-item flex-1 hidden md:flex">
            <?php
            $img = $banner['image'] ?: null;
            $link = $banner['link'] ?: null;
            if ($img) {
                echo '<a class="flex" href="' . $link['url'] . '" target="_blank" rel="noopener noreferrer">';
                echo '<img class="w-full" src="' . $img['url'] . '" width="' . $img['width'] . '" height="' . $img['height'] . '" alt="' . ($link['title'] ?: 'banner') . '" />';
                echo '</a>';
            }
            ?>
        </div>
    <?php endforeach; ?>
    <?php foreach ($banners_mobile as $banner) : ?>
        <div class="banner-item flex-1 flex md:hidden">
            <?php
            $img = $banner['image'] ?: null;
            $link = $banner['link'] ?: null;
            if ($img) {
                echo '<a class="flex" href="' . $link['url'] . '" target="_blank" rel="noopener noreferrer">';
                echo '<img class="w-full" src="' . $img['url'] . '" width="' . $img['width'] . '" height="' . $img['height'] . '" alt="' . ($link['title'] ?: 'banner') . '" />';
                echo '</a>';
            }
            ?>
        </div>
    <?php endforeach; ?>
</div>
