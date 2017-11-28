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

	<section id="primary" class="standard">
		<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

				<?php rewind_posts(); ?>

                <div class="row">
                    <div class="full-width">
                        <article class="environment">
                            <ul class="grid">
                            <?php // get activities

                                $i = 1;
                                while (have_posts()){
									the_post();

                                    ?>
                                    <li class="eleven columns">
                                        <a href="<?php the_permalink();?>" alt="<?php the_title();?>" title="<?php the_title();?>">
                                        <?php the_title();?></a>
                                    	<div><a href="<?php the_permalink();?>" alt="<?php the_title();?>" title="<?php the_title();?>" class="klein"><?php the_excerpt();?></a></div>
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

	<div id="secondary" class="widget-area" role="complementary">
		<?php dynamic_sidebar('categorieen'); ?>
    </div>
    
<?php get_footer(); ?>