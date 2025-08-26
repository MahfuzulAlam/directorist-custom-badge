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
    $verification_atts = [
        'id'         => 'verification-badge',
        'label'      => 'Verification badge',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-my-badge',
        'title'      => 'Verified by Admin',
        'meta_key'   => '_verification',
        'meta_value' => 'yes',
        'class'      => 'verification-badge'
    ];
    new Directorist_Badge( $verification_atts );
} );