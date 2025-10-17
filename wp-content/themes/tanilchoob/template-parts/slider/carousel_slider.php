<?php

use TanilChoob\Theme\Helper;

$carousel_slider_card = isset($args['card']) ? $args['card'] : 'article-card';
?>
<div class="carousel_slider-wrapper <?php echo isset($args['wrapper_class']) ? $args['wrapper_class'] : ''; ?>" data-slidesPerView="<?php echo isset($args['slidesPerView']) ? $args['slidesPerView'] : 1; ?>">
  <div class="container">

    <div class="slider_header flex justify-between items-center">
      <?php if (isset($args['title'])): ?>
        <div class="title_btn flex items-center gap-10">
          <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/sort.svg')); ?>
          <h3 class="title m-0"><?php echo $args['title']; ?></h3>
        </div>
      <?php endif; ?>
      <div class="flex gap-10">
        <!-- Navigation buttons -->
        <div class="button-prev flex item-center pointer transition">
          <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
        </div>
        <div class="button-next flex item-center pointer transition">
          <?php echo Helper::file_get_contents(Helper::getAssetPath('dist/images/arrow-right.svg')); ?>
        </div>
        <?php if (isset($args['button_link'])): ?>
          <a href="<?php echo $args['button_link']; ?>" class="archive-btn transition yekan-20 regular color-black-80 flex items-center gap-10">
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