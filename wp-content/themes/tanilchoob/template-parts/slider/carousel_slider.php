<?php
if(!isset($args['query'])){
    return;
}
?>
  <div class="swiper carousel_slider">
    <div class="swiper-wrapper">
      <?php
      $query = new WP_Query($args['query']);

      if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
          get_template_part('template-parts/cards/' . $args['card'], null, ['post_id' => get_the_ID()]);
        endwhile;
        wp_reset_postdata();
      endif;
      ?>
    </div>
  </div>
