<?php

use TanilChoob\Theme\Helper;

$type = isset($args['type']) ? $args['type'] : 'default';
$color = isset($args['color']) ? $args['color'] : 'black';
$carousel_slider_card = isset($args['card']) ? $args['card'] : 'article-card';
?>
<div class="carousel_slider-wrapper relative <?php echo $type; ?> <?php echo isset($args['wrapper_class']) ? $args['wrapper_class'] : ''; ?>" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
  <div class="container">

    <div class="slider_header flex justify-between items-center">
      <?php if (isset($args['title'])): ?>
        <div class="title_btn <?php echo $color; ?> flex items-center gap-10">
          <?php if ($type == 'special_offers'): ?>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8.57031 15.2704L15.1103 8.73047" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M8.98001 10.3701C9.65932 10.3701 10.21 9.81948 10.21 9.14017C10.21 8.46086 9.65932 7.91016 8.98001 7.91016C8.3007 7.91016 7.75 8.46086 7.75 9.14017C7.75 9.81948 8.3007 10.3701 8.98001 10.3701Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M15.519 16.0899C16.1984 16.0899 16.7491 15.5392 16.7491 14.8599C16.7491 14.1806 16.1984 13.6299 15.519 13.6299C14.8397 13.6299 14.2891 14.1806 14.2891 14.8599C14.2891 15.5392 14.8397 16.0899 15.519 16.0899Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          <?php else: ?>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3 7H21" stroke="black" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" />
              <path d="M6 12H18" stroke="black" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" />
              <path d="M10 17H14" stroke="black" stroke-opacity="0.8" stroke-width="1.5" stroke-linecap="round" />
            </svg>
          <?php endif; ?>
          <h3 class="title m-0"><?php echo $args['title']; ?></h3>
        </div>
      <?php endif; ?>
      <?php if (isset($args['end_off_sale'])): ?>
        <div class="countdown-timer regular color-primary" data-end-date="<?php echo esc_attr($args['end_off_sale']); ?>">
          <div class="countdown-timer__time flex items-center gap-10">
            <span class="countdown-timer__seconds yekan-26">00</span>:
            <span class="countdown-timer__days yekan-26 ">00</span>:
            <span class="countdown-timer__hours yekan-26 ">00</span>:
            <span class="countdown-timer__minutes yekan-26 ">00</span>
          </div>
        </div>
      <?php endif; ?>
      <div class="flex gap-10">
        <!-- Navigation buttons -->
        <div class="button-prev <?php echo $color; ?> flex item-center pointer transition">
          <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
        </div>
        <div class="button-next <?php echo $color; ?> flex item-center pointer transition">
          <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
        </div>
        <?php if (isset($args['button_link'])): ?>
          <a href="<?php echo $args['button_link']; ?>" class="<?php echo ($color === 'white' ? 'color-white ' : 'color-black-80 '); echo $color; ?> archive-btn transition yekan-20 regular  flex items-center gap-10">
            <?php echo isset($args['button_text']) ? $args['button_text'] : 'مشاهده همه'; ?>
            <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-left-circle.svg')); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <!-- Swiper -->
  <div class="container-right">
    <div class="swiper carousel_slider">
      <div class="swiper-wrapper">
        <?php
        $query = new WP_Query($args['query']);

        if ($query->have_posts()) :
          while ($query->have_posts()) : $query->the_post();
            get_template_part('template-parts/cards/' . $carousel_slider_card, null, ['post_id' => get_the_ID()]);
          endwhile;
          wp_reset_postdata();
        endif;
        ?>
      </div>
    </div>
  </div>
</div>