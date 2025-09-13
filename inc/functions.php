<?php
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
    // Get all badge configurations from centralized function
    $badges_info = get_qualified_badges_info();
    
    // Register all badges dynamically
    foreach ( $badges_info as $badge_key => $badge_atts ) {
        new Publishing_Directory_Badge( $badge_atts );
    }

    // Qualified Badge
    new Publishing_Directory_Badge( [
        'id'         => 'qualified-badges',
        'label'      => 'Qualified Badges',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-qualified-badges',
        'title'      => 'Qualified Badges',
        'meta_key'   => '_qualified_badges',
        'meta_value' => 'any',
        'class'      => 'qualified-badges-badge'
    ] );
} );


function get_qualified_badges_info(){
    return [
        'award' => [
            'id'         => 'award-winning',
            'label'      => 'Award Winning',
            'icon'       => 'uil uil-text-fields',
            'hook'       => 'atbdp-award-winning',
            'title'      => '🏆 Award Winning',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'award',
            'class'      => 'award-winning-badge'
        ],
        'bestselling' => [
            'id'         => 'bestselling-author',
            'label'      => 'Bestselling Author',
            'icon'       => 'uil uil-text-fields',
            'hook'       => 'atbdp-bestselling-author',
            'title'      => '📚 Bestselling Author',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'bestselling',
            'class'      => 'bestselling-author-badge'
        ],
        'certified' => [
            'id'         => 'certified-professional',
            'label'      => 'Certified Professional',
            'icon'       => 'uil uil-text-fields',
            'hook'       => 'atbdp-certified-professional',
            'title'      => '🖋 Certified Professional',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'certified',
            'class'      => 'certified-professional-badge'
        ],
        'speaker' => [
            'id'         => 'conference-speaker',
            'label'      => 'Conference Speaker',
            'icon'       => 'uil uil-microphone',
            'hook'       => 'atbdp-conference-speaker',
            'title'      => '🎤 Conference Speaker',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'speaker',
            'class'      => 'conference-speaker-badge'
        ],
        'traditional' => [
            'id'         => 'traditionally-published',
            'label'      => 'Traditionally Published',
            'icon'       => 'uil uil-book-open',
            'hook'       => 'atbdp-traditionally-published',
            'title'      => '📖 Traditionally Published',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'traditional',
            'class'      => 'traditionally-published-badge'
        ],
        'portfolio' => [
            'id'         => 'portfolio-available',
            'label'      => 'Portfolio Available',
            'icon'       => 'uil uil-palette',
            'hook'       => 'atbdp-portfolio-available',
            'title'      => '🎨 Portfolio Available',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'portfolio',
            'class'      => 'portfolio-available-badge'
        ],
        'pro_team' => [
            'id'         => 'team-professionals',
            'label'      => 'Team of 3+ Professionals',
            'icon'       => 'uil uil-users-alt',
            'hook'       => 'atbdp-team-professionals',
            'title'      => '👥 Team of 3+ Professionals',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'pro_team',
            'class'      => 'team-professionals-badge'
        ],
        'coverage' => [
            'id'         => 'media-coverage',
            'label'      => 'Media Coverage',
            'icon'       => 'uil uil-tv-retro',
            'hook'       => 'atbdp-media-coverage',
            'title'      => '📺 Media Coverage',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'coverage',
            'class'      => 'media-coverage-badge'
        ],
        'interview' => [
            'id'         => 'media-interviews',
            'label'      => 'Media Interviews',
            'icon'       => 'uil uil-video',
            'hook'       => 'atbdp-media-interviews',
            'title'      => '🎙️ Media Interviews',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'interview',
            'class'      => 'media-interviews-badge'
        ],
        'ghostwriter' => [
            'id'         => 'ghostwriter',
            'label'      => 'Ghostwriter',
            'icon'       => 'uil uil-edit',
            'hook'       => 'atbdp-ghostwriter',
            'title'      => '✍️ Ghostwriter',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'ghostwriter',
            'class'      => 'ghostwriter-badge'
        ],
        'nonprofit' => [
            'id'         => 'verified-nonprofit',
            'label'      => 'Verified Nonprofit',
            'icon'       => 'uil uil-file-check',
            'hook'       => 'atbdp-verified-nonprofit',
            'title'      => '🧾 Verified Nonprofit',
            'meta_key'   => '_qualified_badges',
            'meta_value' => 'nonprofit',
            'class'      => 'verified-nonprofit-badge'
        ],
    ];
}