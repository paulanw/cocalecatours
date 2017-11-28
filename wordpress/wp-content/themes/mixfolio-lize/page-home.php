<?php
/**
 * Template Name: Home
 */

get_header('home'); ?>

<div id="primary">
    <div id="content" role="main">

        <?php while ( have_posts() ) : the_post(); ?>

            <?php get_template_part( 'content', 'page' ); ?>

            <?php comments_template( '', true ); ?>

        <?php endwhile; // end of the loop. ?>

    </div><!-- #content -->

</div><!-- #primary -->

<?php get_sidebar(); ?>

<div style="clear:both"><br /></div>

<div class="row">
    <div class="full-width">
        <article class="activity">
            <ul class="grid">
            <?php // get activities
            $args = array('post_type' => 'activities', 'order by' => 'post_name', 'order' => 'asc');
            $posts = new WP_Query($args);
            if (count($posts) > 0) {
				$i = 1;
                foreach ($posts->posts as $post){
					$class = "wrap four columns";
					if ($i == 3){
						$class = "three wrap four columns";
						$i = 0;
					}
                    ?>
                    <li class="<?php echo $class; ?>"><a href="<?php the_permalink();?>" alt="<?php the_title();?>" title="<?php the_title();?>">
                            <figure><?php the_post_thumbnail( 'mixfolio-featured-thumbnail', array('class' => 'hvr-round-corners') );?>
                                <figcaption><?php the_title();?></figcaption>
                            </figure>
                        </a>
                    </li>
                    <?php
					$i++;
                }
            }
            ?>
            </ul>
    	</article>
	</div>
</div>

<?php get_footer(); ?>