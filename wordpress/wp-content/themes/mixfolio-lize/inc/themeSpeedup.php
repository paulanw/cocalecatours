<?php

/**
 * Functions and definitions to speed up a theme
 */
class themeSpeedup {

    public function __construct() {

        add_action('init', array($this, 'stopLoadingWpEmbed'));
        add_action('after_setup_theme', array($this, 'removeActions'));

        # defer loading javascript
        add_filter('script_loader_tag', array($this, 'parsingJS'), 10, 3);
    }

    /**
     *  Remove the WP embed script
     *  When necessary to create Youtube links automatically > remove this action
     */
    public function stopLoadingWpEmbed() {
        if (!is_admin()) {
            wp_deregister_script('wp-embed');
        }
    }

    /**
     * Remove algemene Wordpress filters en actions die niet nodig zijn
     */
    public function removeActions() {

        // remove header links
        remove_action('wp_head', 'wp_generator');                           // WP version
        remove_action('wp_head', 'wlwmanifest_link');                       // Windows Live Writer
        remove_action('wp_head', 'rsd_link');                               // EditURI link
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'feed_links_extra', 3);                    // Category Feeds
        remove_action('wp_head', 'feed_links', 2);                          // Post and Comment Feeds
        remove_action('wp_head', 'index_rel_link');                         // index link
        remove_action('wp_head', 'parent_post_rel_link', 10);               // previous link
        remove_action('wp_head', 'start_post_rel_link', 10);                // start link
        add_filter('the_generator', '__return_false');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);    // Links for Adjacent Posts
        remove_action('wp_head', 'print_emoji_detection_script', 7);        // Remove WP emoji script
        remove_action('wp_print_styles', 'print_emoji_styles');             // Remove WP emoji styles

        if (!(is_admin() )) {

            // Move all JS from header to footer
          remove_action('wp_head', 'wp_print_scripts');
          remove_action('wp_head', 'wp_print_head_scripts', 9);
          remove_action('wp_head', 'wp_enqueue_scripts', 1);
          add_action('wp_footer', 'wp_print_scripts', 5);
          add_action('wp_footer', 'wp_print_head_scripts', 5);
          add_action('wp_footer', 'wp_enqueue_scripts', 5);
        }
    }

    /*
     * Add defer attribute to all scripts (including plugins)
     * Add async attribute to script url if you want to load the script asynchronuously instead of defer
     * Only add filter is it's not admin
     */

    public function parsingJS($tag, $handle, $src) {

        if (is_admin())
            return $tag;

        if (false === strrpos($src, 'js')) {
            return $src;
        }

        # Do not add defer or async attribute to these scripts
        $scripts_to_exclude = array('jquery.js');
        foreach ($scripts_to_exclude as $exclude_script) {
            if (true == strpos($tag, $exclude_script)) {
                return $tag;
            }
        }

        # Defer or async all remaining scripts not excluded above
        return str_replace(' src', ' defer="defer" src', $tag);
    }

}
