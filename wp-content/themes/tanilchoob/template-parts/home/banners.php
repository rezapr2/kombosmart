<?php
$banners = $args['banners'] ?: [];
if (empty($banners)) {
    return;
}
?>
<div class="banners flex flex-row gap-20 py-40 container">
    <?php foreach ($banners as $banner) : ?>
        <div class="banner-item flex-1">
            <?php
            $img = $banner['image'] ?: null;
            $link = $banner['link'] ?: null;
            if ($img) {
                echo '<a href="' . $link['url'] . '" target="_blank" rel="noopener noreferrer">';
                echo '<img class="w-full" src="' . $img['url'] . '" alt="' . ($link['title'] ?: 'banner') . '" />';
                echo '</a>';
            }
            ?>
        </div>
    <?php endforeach; ?>
</div>