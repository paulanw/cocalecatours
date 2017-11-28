<?php
/**
 * Mixfolio functions and definitions
 *
 * @package Mixfolio
 * @since Mixfolio 1.1
 */
//require('inc/themeSpeedup.php');
//$themeSettings = new themeSpeedup();

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Mixfolio 1.1
 */
if (!isset($content_width))
    $content_width = 637; /* pixels */

if (!function_exists('mixfolio_setup')):

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which runs
     * before the init hook. The init hook is too late for some features, such as indicating
     * support post thumbnails.
     *
     * @since Mixfolio 1.1
     */
    function mixfolio_setup() {

        /**
         * Custom menu functionality for this theme.
         */
        require( get_template_directory() . '/inc/menus.php' );

        /**
         * Custom template tags for this theme.
         */
        require( get_template_directory() . '/inc/template-tags.php' );

        /**
         * Custom functions that act independently of the theme templates
         */
        require( get_template_directory() . '/inc/tweaks.php' );

        /**
         * Custom widgets
         */
        require( get_template_directory() . '/inc/custom-widgets.class.php' );

        /**
         * Custom Theme Options
         */
        require( get_template_directory() . '/inc/theme-options/theme-options.php' );

        /**
         * Make theme available for translation
         * Translations can be filed in the /languages/ directory
         * If you're building a theme based on Mixfolio, use a find and replace
         * to change 'mixfolio' to the name of your theme in all the template files
         */
        load_theme_textdomain('mixfolio', get_template_directory() . '/languages');

        /**
         * Add default posts and comments RSS feed links to head
         */
        add_theme_support('automatic-feed-links');

        /**
         * Enable support for Post Thumbnails
         */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(300, 200, true); // 300 pixels wide by 200 pixels high, hard crop mode
        add_image_size('mixfolio-featured-thumbnail', 300, 200, true); // 300 pixels wide by 200 pixels high, hard crop mode

        /**
         * Enable support for Post Formats
         */
        add_theme_support('post-formats', array('image', 'gallery', 'link', 'quote', 'video'));

        /**
         * This theme uses wp_nav_menu() in two locations.
         */
        register_nav_menus(array(
           'primary' => __('Primary Menu', 'mixfolio'),
           'secondary' => __('Secondary Menu', 'mixfolio'),
        ));

        /**
         * Custom Background
         */
        add_theme_support('custom-background');
    }

endif; // mixfolio_setup
add_action('after_setup_theme', 'mixfolio_setup');

function register_my_menu() {
    register_nav_menu('activities', __('Activities'));
}

add_action('init', 'register_my_menu');




/**
 * Load Mixfolio options
 */
global $mixfolio_options;
$mixfolio_options = get_option('mixfolio_theme_options');

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since Mixfolio 1.1
 */
function mixfolio_widgets_init() {
    register_sidebar(array(
       'name' => __('Sidebar 1', 'mixfolio'),
       'id' => 'sidebar-1',
       'before_widget' => '<aside id="%1$s" class="widget %2$s">',
       'after_widget' => "</aside>",
       'before_title' => '<h1 class="widget-title">',
       'after_title' => '</h1>',
    ));
    register_sidebar(array(
       'name' => __('categorieen', 'mixfolio'),
       'id' => 'sidebar-2',
       'before_widget' => '<aside id="%1$s" class="widget %2$s">',
       'after_widget' => "</aside>",
       'before_title' => '<h1 class="widget-title">',
       'after_title' => '</h1>',
    ));
    register_sidebar(array(
       'name' => __('location', 'mixfolio'),
       'id' => 'sidebar-3',
       'before_widget' => '<div id="secondary" class="widget-area" role="complementary">',
       'after_widget' => "</div>",
       'before_title' => '<h1 class="widget-title">',
       'after_title' => '</h1>',
    ));
}

add_action('widgets_init', 'mixfolio_widgets_init');

/**
 * Enqueue scripts and styles
 *
 * @since Mixfolio 1.1
 */
function mixfolio_scripts() {
    global $mixfolio_options;

    // Theme stylesheet
    wp_enqueue_style('style', get_stylesheet_uri());
    wp_enqueue_style('mobile', get_template_directory_uri() . '/css/mobile.css');
    wp_enqueue_style('hover', get_template_directory_uri() . '/css/hover-min.css');

    // Threaded comments
    if (is_singular() && comments_open() && get_option('thread_comments'))
        wp_enqueue_script('comment-reply');

    // Selectivizr - CSS3 pseudo-class and attribute selectors for IE 6-8
    wp_register_script('selectivizr', get_template_directory_uri() . '/js/selectivizr-min.js', array('jquery'), '1.0.2');
    wp_enqueue_script('selectivizr');

    // Reveal: jQuery Modals Made Easy
    if (isset($mixfolio_options['mixfolio_display_contact_information']) && 'on' == $mixfolio_options['mixfolio_display_contact_information']) {
        wp_register_script('reveal', get_template_directory_uri() . '/js/jquery.reveal.js', array('jquery'), '1.0');
        wp_enqueue_script('reveal');
    }

    // Enqueue toggle menu for small screens
    wp_enqueue_script('small-menu', get_template_directory_uri() . '/js/small-menu.js', array('jquery'), '20120206', true);

    // Tweetable: jQuery twitter feed plugin, https://github.com/philipbeel/Tweetable
    if (
          is_home() &&
          isset($mixfolio_options['mixfolio_twitter_id']) && '' != $mixfolio_options['mixfolio_twitter_id'] &&
          isset($mixfolio_options['mixfolio_display_welcome_area']) && 'on' == $mixfolio_options['mixfolio_display_welcome_area']
    ) {
        wp_register_script('tweetable', get_template_directory_uri() . '/js/tweetable.jquery.js', array('jquery'), '2.0');
        wp_enqueue_script('tweetable');
    }

    // FitVids.js: A lightweight, easy-to-use jQuery plugin for fluid width video embeds.
    wp_register_script('fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array('jquery'), '1.0');
    wp_enqueue_script('fitvids');

    // Mixfolio custom JS
    wp_register_script('core', get_template_directory_uri() . '/js/jquery.core.js');
    wp_enqueue_script('core');
}

add_action('wp_enqueue_scripts', 'mixfolio_scripts');

/**
 * Implement the Custom Header feature
 *
 * @since Mixfolio 1.1
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Show tweets in Welcome Area if active
 */
function mixfolio_welcome_area_tweets() {
    if (!is_home())
        return;

    global $mixfolio_options;
    if (
          isset($mixfolio_options['mixfolio_twitter_id']) && '' != $mixfolio_options['mixfolio_twitter_id'] &&
          isset($mixfolio_options['mixfolio_display_welcome_area']) && 'on' == $mixfolio_options['mixfolio_display_welcome_area']
    ) :
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#tweets').tweetable({
                    limit: 1,
                    username: '<?php echo esc_attr($mixfolio_options['mixfolio_twitter_id']); ?>',
                    replies: true
                });
            });
        </script><?php
    endif;
}

add_action('wp_head', 'mixfolio_welcome_area_tweets');

if (!function_exists('mixfolio_custom_background_check')) :
    /*
     * Disable text shadows if the user manually sets a custom background color
     */

    function mixfolio_custom_background_check() {

        if ('' != get_background_color()) :
            ?>
            <style type="text/css">
                .commentlist,
                #comments,
                #respond {
                    text-shadow: none;
                }
            </style>
        <?php
        endif;
    }

endif;

add_action('wp_head', 'mixfolio_custom_background_check');

/**
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 */
function mixfolio_content_width() {
    if (is_page_template('full-width-page.php') || is_attachment() || !is_active_sidebar('sidebar-1') || has_post_format('gallery') || has_post_format('image') || has_post_format('video')) {
        global $content_width;
        $content_width = 980;
    }
}

add_action('template_redirect', 'mixfolio_content_width');

/**
 * Register activities post type.
 *
 */
function register_pt_activities() {
    $labels = array(
       'name' => __('activities'),
       'singular_name' => __('Activity'),
       'menu_name' => __('Activities'),
       'name_admin_bar' => __('Activities'),
       'add_new' => __('Add New'),
       'add_new_item' => __('Add New Activities'),
       'new_item' => __('New Activities'),
       'edit_item' => __('Edit Activities'),
       'view_item' => __('View Activities'),
       'all_items' => __('All Activities'),
       'search_items' => __('Search Activities'),
       'parent_item_colon' => __('Parent Activities:'),
       'not_found' => __('No Activities found.'),
       'not_found_in_trash' => __('No Activities found in Trash.')
    );

    $args = array(
       'labels' => $labels,
       'public' => true,
       'publicly_queryable' => true,
       'show_ui' => true,
       'show_in_menu' => true,
       'query_var' => true,
       'rewrite' => array('slug' => 'activities'),
       'capability_type' => 'post',
       'has_archive' => true,
       'hierarchical' => false,
       'menu_position' => null,
       'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
       'taxonomies' => array('category')
    );

    register_post_type('activities', $args);
}

add_action('init', 'register_pt_activities');

/**
 * Register blog post type.
 *
 */
function register_pt_blog() {
    $labels = array(
       'name' => __('blog'),
       'singular_name' => __('Blog'),
       'menu_name' => __('Blog'),
       'name_admin_bar' => __('Blog'),
       'add_new' => __('Add New'),
       'add_new_item' => __('Add New Blog'),
       'new_item' => __('New Blog'),
       'edit_item' => __('Edit Blog'),
       'view_item' => __('View Blog'),
       'all_items' => __('All Blogs'),
       'search_items' => __('Search Blog'),
       'parent_item_colon' => __('Parent Blog:'),
       'not_found' => __('No blogs found.'),
       'not_found_in_trash' => __('No blogs found in trash.')
    );

    $args = array(
       'labels' => $labels,
       'public' => true,
       'publicly_queryable' => true,
       'show_ui' => true,
       'show_in_menu' => true,
       'query_var' => true,
       'rewrite' => array('slug' => 'blog'),
       'capability_type' => 'post',
       'has_archive' => true,
       'hierarchical' => false,
       'menu_position' => null,
       'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
       'taxonomies' => array('category')
    );

    register_post_type('blog', $args);
}

add_action('init', 'register_pt_blog');

/**
 * Register blog post type.
 *
 */
function register_pt_environment() {
    $labels = array(
       'name' => __('environment'),
       'singular_name' => __('Environment'),
       'menu_name' => __('Environment'),
       'name_admin_bar' => __('Environment'),
       'add_new' => __('Add New'),
       'add_new_item' => __('Add New environment'),
       'new_item' => __('New environment'),
       'edit_item' => __('Edit environment'),
       'view_item' => __('View environment'),
       'all_items' => __('All environment'),
       'search_items' => __('Search environment'),
       'parent_item_colon' => __('Parent environment:'),
       'not_found' => __('No environment found.'),
       'not_found_in_trash' => __('No environment found in trash.')
    );

    $args = array(
       'labels' => $labels,
       'public' => true,
       'publicly_queryable' => true,
       'show_ui' => true,
       'show_in_menu' => true,
       'query_var' => true,
       'rewrite' => array('slug' => 'environment'),
       'capability_type' => 'post',
       'has_archive' => true,
       'hierarchical' => false,
       'menu_position' => null,
       'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
       'taxonomies' => array('category')
    );

    register_post_type('environment', $args);
}

add_action('init', 'register_pt_environment');

add_filter('wpseo_breadcrumb_output', 'change_blog_to_cocalecatalk', 10, 1);

function change_blog_to_cocalecatalk($linktext) {
    return str_replace('>blog<', '>cocalecatalk<', $linktext);
}

add_action('nav_menu_css_class', 'add_current_nav_class', 10, 2);

function add_current_nav_class($classes, $item) {

    // Getting the current post details
    global $post;

    // Getting the post type of the current post
    if (!empty($post->ID)) {
        $current_post_type = get_post_type_object(get_post_type($post->ID));
        $current_post_type_slug = $current_post_type->rewrite['slug'];

        // Getting the URL of the menu item
        $menu_slug = strtolower(trim($item->url));

        // If the menu item URL contains the current post types slug add the current-menu-item class
        if (strpos($menu_slug, $current_post_type_slug) !== false) {

            $classes[] = 'current-menu-item';
        }
    }

    // Return the corrected set of classes to be added to the menu item
    return $classes;
}

add_filter('widget_title_custom', 'categories_delete_title', 10, 3);

function categories_delete_title($title, $instance, $id_base) {
    if ($id_base == 'categories') {
        return '';
    }
    return $title;
}

add_filter('widget_categories_dropdown_args', 'categories_which', 10, 1);

function categories_which($args) {
    echo "<pre>";
    print_r($args);
    exit;
    return $title;
}

add_action('pre_get_posts', 'environment_categories');

function environment_categories($query) {

    if ($query->is_main_query() && !empty($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'environment') {

        parse_str($_SERVER['QUERY_STRING'], $arrqs);
        foreach ($arrqs as $key => $value) {

            if ($key == 'category') {
                // select all environment blogs of this category
                $query->set('category_name', esc_sql($value));
            }
        }
    }
    return $query;
}
