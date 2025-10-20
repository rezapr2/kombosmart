<?php
$page_id = get_the_ID();
// Get ACF fields if they exist
$best_toshaks = get_field('best_toshaks', $page_id);
$title = $best_toshaks['title'];
$sub_title = $best_toshaks['sub_title'];
$description = $best_toshaks['description'];
$brands = $best_toshaks['brands'];
?>
<section class="home-best-toshaks flex flex-col container mb-40">
    <div class="title yekan-22 color-black thin text-center color-black-80">
       <?php echo $title; ?>
    </div>
    <div class="sub_title yekan-13 color-black-60 text-center">
        <?php echo $sub_title; ?>
    </div>
    <div class="flex justify-between items-center bottom-row mt-30">
        <div class="description yekan-20 color-black-50">
            <?php echo $description; ?>
        </div>
        <div class="brands flex items-center justify-between">
            <?php foreach ($brands as $brand) : ?>
                <a href="<?php echo esc_url($brand['link']['url']); ?>">
                    <img src="<?php echo esc_url($brand['image']['url']); ?>" alt="<?php echo $brand['link']['title']; ?>">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>