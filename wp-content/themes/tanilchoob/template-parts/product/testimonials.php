<?php

use TanilChoob\Theme\Helper;

$post_id = get_the_ID();
$testimonials_customers = get_field('testimonials_customers', $post_id);
?>
<div class="testimonials mb-40">
  <div class="container">
    <div class="title yekan-24 color-black thin text-center color-black-80">
      <strong class="color-primary">تجربه خرید </strong> مشتریان
    </div>
    <div class="description yekan-16 color-black-60 text-center">
      توضیحات درمورد بخش بهترین تشک ها
    </div>

    <div class="mt-25 flex gap-10">
      <div class="testimonial-slider swiper relative flex flex-col gap-15">
        <div class="swiper-wrapper">
          <?php if ($testimonials_customers):
            foreach ($testimonials_customers as $testimonial_customer):
          ?>
              <div class="swiper-slide">
                <div class="flex items-center gap-10">
                  <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#CBD5E1,#94A3B8);">
                    <img src="<?php echo $testimonial_customer['image']['url']; ?>" alt="<?php echo $testimonial_customer['customer_name']; ?>">
                  </div>
                  <div class="flex flex-col gap-04">
                    <div class="yekan-18 color-black"><?php echo $testimonial_customer['customer_name']; ?></div>
                    <div class="color-black-30 yekan-14"><?php echo $testimonial_customer['date']; ?></div>
                  </div>
                </div>

                <p class="yekan-18 color-black-70">
                  <?php echo $testimonial_customer['description']; ?>
                </p>
              </div>
          <?php
            endforeach;
          endif; ?>
        </div>
        <div class="swiper-navigation flex bg-black absolute bottom-0 left-0">
          <div class="swiper-button-prev button-prev pointer flex item-center">
            <svg width="25" height="18" viewBox="0 0 25 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.4297 17.0439L23.523 8.95061L15.4297 0.85728" stroke="white" stroke-width="1.71429" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M0.859375 8.9502L23.2994 8.9502" stroke="white" stroke-width="1.71429" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div class="swiper-button-next button-next pointer flex item-center">
            <svg width="25" height="18" viewBox="0 0 25 18" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8.95312 17.0439L0.859793 8.95061L8.95313 0.85728" stroke="white" stroke-width="1.71429" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M23.5234 8.9502L1.08344 8.95019" stroke="white" stroke-width="1.71429" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
        </div>


      </div>
        <?php 
        $customer_images = get_field('customer_images', $post_id);
        if($customer_images):
      ?>
      <div class="customer_images grid gap-07">
        <?php 
        $images_count = count($customer_images);
        for ($i=0; $i < ($images_count > 3 ? 3 : $images_count); $i++) { 
          if(isset($customer_images[$i])){
            echo '<div class="image img'.($i+1).'">';
            echo '<img src="'.$customer_images[$i]['url'].'" alt="'.$customer_images[$i]['alt'].'">';
            echo '</div>';
          }
        }?>
      </div>
      <?php endif; ?>

      <?php
        $customer_video = get_field('customer_video', $post_id);
        if(isset($customer_video['url'])){
          $video_url = $customer_video['url'];
          $video_poster = $customer_video['cover'];
          $title = $customer_video['title'];
          echo '<div class="about-tanil__video">';
          echo '<a class="about-tanil__video-wrapper relative video-lightbox" data-video-url="'.esc_url($video_url).'">';
          echo '<img class="about-tanil__video-poster flex" src="'.esc_url($video_poster['url']).'" alt="'.esc_attr($title).'">';
          echo '<div class="absolute center">';
          echo '<img class="about-tanil__video-play-icon" src="'.esc_url(Helper::getAssetUri('images/play_icon.svg')).'" alt="Play Icon">';
          echo '</div></a></div>';
        }
      ?>

    </div>
  </div>

</div>