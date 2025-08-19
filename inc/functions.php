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
    /**
     * Verified Badge
     */
    $verified_badge_atts = [
        'id'         => 'verified-badge',
        'label'      => 'Verified',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-verified-badge',
        'title'      => 'Verified',
        'meta_key'   => '_fm_plans',
        'meta_value' => 252,
        'class'      => 'verified-badge'
    ];
    new Directorist_Badge( $verified_badge_atts );

    /**
     * Featured Provider Badge
     */
    $featured_provider_atts = [
        'id'         => 'featured-provider-badge',
        'label'      => 'Featured Provider',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-featured-provider-badge',
        'title'      => 'Featured Provider',
        'meta_key'   => '_fm_plans',
        'meta_value' => 252,
        'class'      => 'featured-provider-badge'
    ];
    new Directorist_Badge( $featured_provider_atts );

    /**
     * Best Overall Badge
     */
    $best_overall_atts = [
        'id'         => 'best-overall-badge',
        'label'      => 'Best Overall',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-best-overall-badge',
        'title'      => 'Best Overall',
        'meta_key'   => '_best_overall',
        'meta_value' => 'yes',
        'class'      => 'best-overall-badge'
    ];
    new Directorist_Badge( $best_overall_atts );

    /**
     * Best in Catgeory Badge
     */
    $best_in_category_atts = [
        'id'         => 'best-in-category-badge',
        'label'      => 'Best in Catgeory',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-best-in-category-badge',
        'title'      => 'Best in Category',
        'meta_key'   => '_best_in_category',
        'meta_value' => 'yes',
        'class'      => 'best-in-category-badge'
    ];
    new Directorist_Badge( $best_in_category_atts );
} );

