<div class="stories-wrapper container">
    <?php
    $the_args = [
        'post_type' => 'story',
        'posts_per_page' => -1,
        'status' => 'publish',
        'orderby' => 'date',
        'order' => 'asc',
    ];

    $the_query = new WP_Query($the_args);

    if ($the_query->have_posts()) {
    ?>
        <div class="flex stories">
            <?php
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $thumbnail_url = get_the_post_thumbnail_url();
                $subtitle = get_field('subtitle');
                $content_type = get_field('content_type');
                $image = get_field('image');
                $video_link = get_field('video_link');
                $story_content = array(
                    'title' => get_the_title(),
                    'thumbnail_url' => $thumbnail_url,
                    'subtitle' => $subtitle,
                    'content_type' => $content_type,
                    'image' => $image,
                    'video_link' => $video_link,
                );
            ?>
            <div class="story-item relative flex flex-col items-center" data-story-content='<?php echo esc_attr( wp_json_encode( $story_content ) ); ?>'>
                <div class="story-image-wrapper circle-radius relative">
                    <img class="circle-radius" src="<?php echo $thumbnail_url; ?>" alt="<?php the_title(); ?>" />
                </div>
                <div class="title yekan-14 md:yekan-18 color-black-50"><?php the_title(); ?></div>
            </div>
            <?php
            }
            wp_reset_postdata(); ?>
        </div>
    <?php } ?>
</div>