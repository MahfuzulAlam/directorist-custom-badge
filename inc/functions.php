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
 * Add a custom badge
 */

add_action( 'init', function(){
    $my_badge_atts = [
        'id'         => 'my-badge',
        'label'      => 'Badge',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-my-badge',
        'title'      => 'My Badge',
        'meta_key'   => '_custom-select',
        'meta_value' => 'Free',
        'class'      => 'my-custom-badge'
    ];
    new Directorist_Badge( $my_badge_atts );
} );