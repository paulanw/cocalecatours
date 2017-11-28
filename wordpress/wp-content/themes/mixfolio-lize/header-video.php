<?php
global $mixfolio_options;
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package Mixfolio
 */
?><!DOCTYPE html>
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="initial-scale=1.0, width=device-width" />
        <title><?php wp_title('|', true, 'right'); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.png" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

        <!--[if lt IE 9]>
        <script src="<?php echo get_stylesheet_directory(); ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
        <?php wp_head(); ?>
    </head>
    <body lang="en" <?php body_class(); ?>>
        <div id="page" class="hfeed">
            <header id="branding" role="banner" data-dropdown="dropdown"> 
                <div class="video-container">
   
<video id="backgroundvid" autoplay loop>
<source src="http://www.cocalecatours.com/wordpress/wp-content/uploads/2017/11/mangrove.mp4" type="video/mp4" />
    <--<source src="__VIDEO__.OGV" type="video/ogg" />-->
</video>
                    
             
<video poster="placeholder.jpg" id="backgroundvid" autoplay loop>
<source src="http://www.cocalecatours.com/wordpress/wp-content/uploads/2017/11/mangrove.mp4" type='video/mp4; codecs="avc1.4D401E, mp4a.40.2"' autoplay>
<!--<source src="http://localhost/cocalecatours/wordpress/wp-content/uploads/2017/11/Great-experience-and-low-impact-on-nature.mp4" type='video/mp4; codecs="avc1.4D401E, mp4a.40.2"'>
<source src="http://localhost/cocalecatours/wordpress/wp-content/uploads/2017/11/horseback%20ride%20Playa%20Las%20Lajas%20Panama.mp4" type='video/mp4; codecs="avc1.4D401E, mp4a.40.2"'>-->
<p>Fallback content to cover incompatibility issues</p>
</video></div>
<style>
html{
    margin-top: 0 !important;
}
video#backgroundvid {
    width: 100%;
    margin-top: -5%;
    position: relative;
    z-index: 0;
}
.video-container{
    width: 100%;
    overflow:hidden;
    display:block;
    height: 500px;
    position: relative;
}
.home #branding-inner{
    height: 0;
}
#branding-inner{
    height: 0;
}
.overlay{
    padding-bottom: 40px;
    position: absolute;
    width: 100%;
    bottom: 15%;
    z-index:1;
}
</style>


                <!--<div id="branding-inner" class="background-photo" style="overflow:hidden;background-image: url(<?php echo get_bloginfo('template_directory'); ?>/images/header-1.jpg)">-->
<div id="branding">                    
<div class="container">
    <div class='overlay'>
                        <hgroup>
                            <h1 id="site-title">
                                <a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
                                    <?php if (isset($mixfolio_options['mixfolio_logo']) && '' != $mixfolio_options['mixfolio_logo']) { ?>
                                        <img class="sitetitle" id="logo-image-home" src="<?php echo $mixfolio_options['mixfolio_logo']; ?>" alt="<?php bloginfo('name'); ?>" />
                                        <?php
                                    } else {
                                        bloginfo('name');
                                    }
                                    ?>
                                </a>
                            </h1><!-- #site-title -->
                            <?php if ('' != get_bloginfo('description')) : ?>
                                <h2 id="site-description" style="position: absolute;color: yellow">
                                    <?php bloginfo('description'); ?>
                                </h2><!-- #site-description -->
                            <?php endif; ?>
                        </hgroup>
                    </div><!-- .container -->
                </div><!-- #branding-inner -->
            </div><!-- .overlay -->

                <div id="branding-inner-mobility">
                    <div class="container">
                        <hgroup>
                            <h1 id="site-title">
                                <a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
                                    <?php if (isset($mixfolio_options['mixfolio_logo']) && '' != $mixfolio_options['mixfolio_logo']) { ?>
                                        <img class="sitetitle" id="logo-image-home" src="<?php echo $mixfolio_options['mixfolio_logo']; ?>" alt="<?php bloginfo('name'); ?>" />
                                        <?php
                                    } else {
                                        bloginfo('name');
                                    }
                                    ?>
                                </a>
                            </h1><!-- #site-title -->
                            <?php if ('' != get_bloginfo('description')) : ?>
                                <h2 id="site-description">
                                    <?php bloginfo('description'); ?>
                                </h2><!-- #site-description -->
                            <?php endif; ?>
                        </hgroup>
                    </div><!-- .container -->
                </div>

                <div id="branding-inner1">
                    <div class="container">
                        <hgroup>
                            <nav role="navigation" class="nav site-navigation main-navigation">
                                <h1 class="assistive-text"><?php _e('Menu', 'mixfolio'); ?></h1>
                                <div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e('Skip to content', '_s'); ?>"><?php _e('Skip to content', 'mixfolio'); ?></a></div>
                                <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
                            </nav><!-- .nav .site-navigation .main-navigation -->
                    </div><!-- .container -->
                </div><!-- #branding-inner -->
            </header><!-- #branding -->

            <div class="main-outer">
                <div id="main" class="row">
                    <div class="twelve columns">
                        <?php
                        $header_image = get_header_image();
                        if (!empty($header_image)) :
                            ?>
                            <div class="header-image">
                                <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
                                    <img src="<?php header_image(); ?>" width="<?php echo get_custom_header()->width; ?>" alt="" />
                                </a>
                            </div><!-- .header-image -->
                            <?php
                        endif; // if ( ! empty( $header_image ) ); ?>