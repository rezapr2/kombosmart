<?php
$page_id = get_the_ID();
$hero_slider = get_field('hero_slider', $page_id);

if ($hero_slider) {
?>
    <div class="hero-slider-wrapper ">
        <div class="hero-slider swiper">
            <div class="swiper-wrapper">
                <?php foreach ($hero_slider as $item) { ?>
                    <div class="swiper-slide ">
                        <div class="container">
                            <div class="slide-content flex justify-between items-center">
                                <div class="hero-slider-content flex flex-col">
                                    <div class="title color-white">
                                        <?php echo $item['title']; ?>
                                    </div>
                                    <div class="description color-white opacity-5 thin">
                                        <?php echo $item['description']; ?>
                                    </div>
                                    <?php if ($item['link']) { ?>
                                        <a href="<?php echo $item['link']['url']; ?>" class="btn yekan-16 bg-white color-primary w-fit">
                                            <?php echo $item['link']['title']; ?>
                                        </a>
                                    <?php } ?>
                                </div>
                                <div class="slide-img flex w-auto">
                                    <?php if ($item['image']) { ?>
                                        <img src="<?php echo $item['image']['url']; ?>" alt="<?php echo $item['title']; ?>">
                                    <?php } ?>
                                </div>

                            </div>
                        </div>

                    </div>
                <?php } ?>

                <!-- Add navigation buttons -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

        </div>
    </div>
<?php
}
?>