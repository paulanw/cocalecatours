<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Mixfolio
 */

global $mixfolio_count;

$mixfolio_count = 1;

get_header(); ?>

	<section id="primary" class="full-width">
		<div id="content" role="main">

			<!--<div class="entry-content">
				<?php// the_content(); ?>
			</div>-->
        
			<?php if ( have_posts() ) : ?>

				<?php rewind_posts(); ?>

                <div class="row">
                    <div class="full-width">
                        <article class="activity">
                            <ul class="grid">
                            <?php // get activities

                                $i = 1;
                                while (have_posts()){
									the_post();
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

                            ?>
                            </ul>
                        </article>
                    </div>
                </div>

				<?php mixfolio_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title">
							<?php _e( 'Nothing Found', 'mixfolio' ); ?>
						</h1><!-- .entry-title -->
					</header><!-- .entry-header -->

					<div class="entry-content">
						<p>
							<?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'mixfolio' ); ?>
						</p>
					</div><!-- .entry-content -->
				</article><!-- #post-0 -->

			<?php endif; ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_footer(); ?>