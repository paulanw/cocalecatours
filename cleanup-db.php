<?php

// init
error_reporting(E_ALL);

ini_set('display_errors', '1');

define('SHORTINIT', true);

$wp_root_path = dirname(__FILE__);

$wp_content_path = dirname(__FILE__) . '/wp-content';

require($wp_root_path . '/wp-load.php');

// delete wp_postmeta of revision posts
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        'delete from wp_postmeta where post_id in (select ID from wp_posts where post_type = %s)', 'revision'
    )
);

// delete attachments of revision posts
$result = $wpdb->get_results(
    $wpdb->prepare(
        'select * from wp_posts where post_type = %s and 
            (
                post_parent = 0 OR ID in
                    (select ID from wp_posts where post_type = %s)
            )',
        'attachment', 'revision'
    )
);
foreach ($result as $row){
    $wpdb->query(
            $wpdb->prepare('delete from wp_posts where ID = %d', $row->ID
        )
    );
}

// delete revision posts
$wpdb->query(
    $wpdb->prepare(
        'delete from wp_posts where post_type = %s', 'revision'
    )
);
