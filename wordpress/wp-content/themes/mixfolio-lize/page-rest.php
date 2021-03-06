<?php
/**
 * Template Name: Rest
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'page' ); ?>

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
            
            <?php wp_nav_menu( array( 'theme_location' => 'activities' ) ); ?>
            
		</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>