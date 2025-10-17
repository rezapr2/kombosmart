<?php
$page_id = get_the_ID();
$features = get_field('features', $page_id);
?>
<div class="home-features container">
    <div class="flex features justify-center">
        <?php if ($features) {
            foreach ($features as $item) { ?>
                <div class="feature-item flex flex-col items-center">
                    <div class="icon">
                        <img src="<?php echo ($item['image']['url'] ?: '') ?>" alt="<?php echo ($item['title'] ?: $item['image']['alt']); ?>">
                    </div>
                    <div class="title yekan-22 color-black text-center">
                        <?php echo ($item['title'] ?: ''); ?>
                    </div>
                    <div class="description yekan-16 opacity-5 text-center">
                        <?php echo ($item['description'] ?: ''); ?>
                    </div>
                </div>
        <?php }
        } ?>
    </div>
</div>