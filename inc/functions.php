<?php

/**
 * Add your custom php code here
 */


/**
 * Enable the Custom Field Meta Key
 */
add_filter('directorist_custom_field_meta_key_field_args', function ($args) {
	$args['type'] = 'text';
	return $args;
});

/**
 * Initialize badges from options table
 */
add_action( 'init', function(){
    // Initialize badges from wp_options table
    if (class_exists('Directorist_Custom_Badges_Admin') && class_exists('Directorist_Badge')) {
        Directorist_Badge::init_badges_from_options();
    }
} );