<?php
$page_id = get_the_ID();
$hero_slider = get_field('hero_slider', $page_id);

if ($hero_slider) {
?>
    <div class="hero-slider-wrapper ">
        <div class="hero-slider swiper">
            <div class="swiper-wrapper">
                <?php foreach ($hero_slider as $item) { ?>
                    <div class="swiper-slide <?php echo isset($item['background_color']) ? $item['background_color'] : 'purple'; ?>">
                        <div class="container">
                            <div class="slide-content flex justify-between items-center">
                                <div class="hero-slider-content flex flex-col">
                                    <div class="title color-white">
                                        <?php echo $item['title']; ?>
                                    </div>
                                    <div class="description color-white opacity-8 thin">
                                        <?php echo $item['description']; ?>
                                    </div>
                                    <?php if ($item['link']) { ?>
                                        <a href="<?php echo $item['link']['url']; ?>" class="btn color-primary yekan-7 md:yekan-18 bg-white-30 w-fit transition">
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

                <div class="container swiper-navigation absolute hidden md:flex items-center gap-10">
                    <!-- Add navigation buttons -->
                    <div class="swiper-button-prev circle-radius transition flex item-center">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 8.22852H15M15 8.22852L8 1.22852M15 8.22852L8 15.2285" stroke="white" stroke-opacity="0.52" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="swiper-button-next circle-radius transition flex item-center">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 8.22852H1M1 8.22852L8 1.22852M1 8.22852L8 15.2285" stroke="#6C3BFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>

            </div>

        </div>
    </div>
<?php
}
?>