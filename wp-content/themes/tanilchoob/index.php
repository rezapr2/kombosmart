<?php
/**
 * Index
 *
 * Theme index.
 *
 * @since   1.0.0
 * @package WP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TanilChoob\Theme\Helper;

get_header();
?>
<div class="archive">
    <div class="container">
        <div class="row">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <article class="box">
                        <figure>
                            <a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'blog' ); ?>
                            </a>
                        </figure>
                        <header class="title">
                            <h2>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                        </header>
                        <?php the_content(); ?>
                    </article>
                </div>
			<?php endwhile; ?>
				<div class="col-12">
                    <?php
                    the_posts_pagination( [
				        'mid_size' => 2,
                        'prev_text' => '<i class="feather icon-chevron-right"></i>',
                        'next_text' => '<i class="feather icon-chevron-left"></i>',
                    ] );
                    ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
