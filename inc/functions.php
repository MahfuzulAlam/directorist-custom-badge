<?php

/**
 * Add your custom php code here
 */


/**
 * Enable the Custom Field Meta Key
 */
add_filter('directorist_custom_field_meta_key_field_args', function ($args) {
	if(get_directorist_option('enable_field_key', false)) $args['type'] = 'text';
	return $args;
});

/**
 * Initialize badges from options table
 */
add_action( 'init', function(){
    // Initialize badges from wp_options table
    if (class_exists('Directorist_Custom_Badges_Admin') && class_exists('Directorist_Badge')) {
        Directorist_Custom_Badge::init_badges_from_options();
    }
} );

add_filter( 'atbdp_listing_type_settings_field_list', function ( $fields ) {
    $fields['enable_field_key'] = array(
        'label' => 'Field Key',
        'type' => 'toggle',
        'default' => false,
        'description' => 'Enable field meta key in the directory builder.',
    );
    return $fields;
} );

add_filter( 'atbdp_caching_controls', function ( $controls ) {
    $controls['debugging']['fields'] = ['script_debugging','enable_field_key'];
    return $controls;
} );