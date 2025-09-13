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
    $award_winning_atts = [
        'id'         => 'award-winning',
        'label'      => 'Award Winning',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-award-winning',
        'title'      => '🏆 Award Winning',
        'meta_key'   => '_qualified_badges',
        'meta_value' => 'award',
        'class'      => 'award-winning-badge'
    ];
    $bestselling_author_atts = [
        'id'         => 'bestselling-author',
        'label'      => 'Bestselling Author',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-bestselling-author',
        'title'      => '📚 Bestselling Author',
        'meta_key'   => '_qualified_badges',
        'meta_value' => 'bestselling',
        'class'      => 'bestselling-author-badge'
    ];

    $certified_professional_atts = [
        'id'         => 'certified-professional',
        'label'      => 'Certified Professional',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-certified-professional',
        'title'      => '🖋 Certified Professional',
        'meta_key'   => '_qualified_badges',
        'meta_value' => 'certified',
        'class'      => 'certified-professional-badge'
    ];

    // Register the badges
    new Publishing_Directory_Badge( $award_winning_atts );
    new Publishing_Directory_Badge( $bestselling_author_atts );
    new Publishing_Directory_Badge( $certified_professional_atts );
} );