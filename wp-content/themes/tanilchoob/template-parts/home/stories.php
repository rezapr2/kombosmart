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
            ?>
            <div class="story-item flex flex-col items-center">
                <div class="story-image-wrapper">
                    <img src="<?php echo $thumbnail_url; ?>" alt="<?php the_title(); ?>" />
                </div>
                <div class="title yekan-18 color-black-50"><?php the_title(); ?></div>
            </div>
            <?php
            }
            wp_reset_postdata(); ?>
        </div>
    <?php } ?>
</div>