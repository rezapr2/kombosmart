<?php

$post_id = get_the_ID();
$testimonials_customers = get_field('testimonials_customers', $post_id);
$toshaks_section_subtitle = get_field('toshaks_section_subtitle', $post_id);
if(!$testimonials_customers) {
  return;
}
?>
<div class="testimonials mb-40">
  <div class="container">
    <div class="title yekan-16 md:yekan-24 color-black thin text-center color-black-80">
      <strong class="color-primary">تجربه خرید </strong> مشتریان
    </div>
    <?php if(isset($toshaks_section_subtitle) && $toshaks_section_subtitle): ?>
      <div class="description yekan-12 md:yekan-16 color-black-60 text-center">
        <?php echo $toshaks_section_subtitle; ?>
      </div>
    <?php endif; ?>
    <div class="mt-25 flex flex-col md:flex-row gap-10">
      <div class="testimonial-slider swiper relative flex flex-1 flex-col gap-15">
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
                    <div class="yekan-14 md:yekan-18 color-black"><?php echo $testimonial_customer['customer_name']; ?></div>
                    <div class="color-black-30 yekan-12 md:yekan-14"><?php echo $testimonial_customer['date']; ?></div>
                  </div>
                </div>

                <p class="yekan-14 md:yekan-18 color-black-70">
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
        $customer_videos_gallery = get_field('customer_videos_gallery', $post_id);
        $customer_images = get_field('customer_images', $post_id);
        if($customer_images):
      ?>
      <div class="customer_images grid gap-07">
        <?php 
        $images_count = count($customer_images);
        if($customer_videos_gallery){
          $images_count += count($customer_videos_gallery);
        }
        $limit = $images_count > 3 ? 3 : $images_count;
        $more_count = $images_count > 3 ? ($images_count - 3) : 0;

        // Build lightbox items for all customer images
        $lightbox_items = array();
        foreach ($customer_images as $img) {
          if (!empty($img['url'])) {
            $lightbox_items[] = array(
              'type' => 'image',
              'full' => esc_url($img['url']),
              'thumb' => esc_url($img['url']),
            );
          }
        }
        if($customer_videos_gallery){
          foreach ($customer_videos_gallery as $video) {
            if (!empty($video['video_url'])) {
              $lightbox_items[] = array(
                'type' => 'video',
                'video_url' => esc_url($video['video_url']),
                'thumb' => esc_url($video['video_cover_image']['url']),
              );
            }
          }
        }
        $lightbox_json = wp_json_encode($lightbox_items);

        for ($i = 0; $i < $limit; $i++) {
          if (isset($customer_images[$i])) {
            $is_more_tile = ($i === $limit - 1 && $more_count > 0);
            if ($is_more_tile) {
              echo '<div class="image img'.($i+1).' relative">';
              echo '<img src="'.esc_url($customer_images[$i]['url']).'" alt="'.esc_attr($customer_images[$i]['alt']).'">';
              // Make overlay clickable to open lightbox, reusing product gallery handler
              echo '<div class="more-label open-gallery-lightbox w-100 h-100 flex item-center pointer color-white center absolute flex flex-col items-center gap-2 z-index-5" data-gallery="'.esc_attr($lightbox_json).'" aria-label="مشاهده همه تصاویر"><span class="yekan-20">' . esc_html($more_count) . '+</span><span class="yekan-12">مشاهده همه</span></div>';
              echo '</div>';
            } else {
              echo '<div class="image img'.($i+1).'">';
              echo '<img src="'.esc_url($customer_images[$i]['url']).'" alt="'.esc_attr($customer_images[$i]['alt']).'">';
              echo '</div>';
            }
          }
        }
        ?>
      </div>
      <?php endif; ?>

      <?php
        $customer_video = get_field('customer_video', $post_id);
        if(isset($customer_video['url'])){
          $video_url = $customer_video['url'];
          $video_poster = $customer_video['cover'];
          echo '<div class="customer_video flex">';
          echo '<a class="customer_video-wrapper relative video-lightbox" data-video-url="'.esc_url($video_url).'">';
          echo '<img class="customer_video-poster h-100" src="'.esc_url($video_poster['url']).'" alt="'.esc_attr($title).'">';
          // Dark overlay on top of poster image
          echo '<div class="customer_video-overlay dark-overlay"></div>';
          echo '<div class="absolute center">';
          echo '<svg width="59" height="59" viewBox="0 0 59 59" fill="none" xmlns="http://www.w3.org/2000/svg">
          <foreignObject x="-13.6688" y="-13.6688" width="86.3376" height="86.3376"><div xmlns="http://www.w3.org/1999/xhtml" style="backdrop-filter:blur(6.83px);clip-path:url(#bgblur_0_971_11134_clip_path);height:100%;width:100%"></div></foreignObject><circle data-figma-bg-blur-radius="13.6688" cx="29.5" cy="29.5" r="29.5" fill="white" fill-opacity="0.62"/>
          <path d="M42.7813 27.5512C44.1146 28.321 44.1146 30.2455 42.7812 31.0153L24.0312 41.8406C22.6979 42.6104 21.0312 41.6481 21.0312 40.1085L21.0312 18.4579C21.0312 16.9183 22.6979 15.956 24.0312 16.7258L42.7813 27.5512Z" fill="white"/>
          <defs>
          <clipPath id="bgblur_0_971_11134_clip_path" transform="translate(13.6688 13.6688)"><circle cx="29.5" cy="29.5" r="29.5"/>
          </clipPath></defs>
          </svg>';
          echo '</div></a></div>';
        }
      ?>

    </div>
  </div>

</div>