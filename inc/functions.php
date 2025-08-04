<?php

/**
 * Add your custom php code here
 */


/**
 * Enable the Custom Field Meta Key
 */
// add_filter('directorist_custom_field_meta_key_field_args', function ($args) {
//     $args['type'] = 'text';
//     return $args;
// });

/**
 * Add a custom badge
 */

add_action('init', function () {
    $my_badge_atts = [
        'id'         => 'partenaire',
        'label'      => 'Partenaire Officiel',
        'icon'       => 'uil uil-text-fields',
        'hook'       => 'atbdp-badge-partenaire',
        'title'      => 'Partenaire Officiel',
        'meta_key'   => '_official_partner',
        'meta_value' => 'yes',
        'class'      => 'my-badge-partenaire'
    ];
    new Directorist_Badge($my_badge_atts);
});


/**
 * Create a preset field
 */

add_filter('atbdp_form_preset_widgets', 'directorist_official_partner_badge');

if (! function_exists('directorist_official_partner_badge')) {
    function directorist_official_partner_badge($widgets)
    {
        $widgets['official_partner'] = [
            'label' => 'Partenaire Officiel',
            'icon' => 'la la-link',
            'show' => true,
            'options' => [
                'field_key' => [
                    'type'   => 'meta-key',
                    'hidden' => true,
                    'value'  => 'official_partner',
                ],
                'label' => [
                    'type'  => 'text',
                    'label' => 'Label',
                    'value' => 'Partenaire Officiel',
                ],
                'only_for_admin' => [
                    'type'  => 'toggle',
                    'label' => __( 'Admin Only', 'directorist' ),
                    'value' => false,
                ],
            ],
        ];
        return $widgets;
    }
}

/**
 * Directorist Field Template
 */

add_filter('directorist_field_template', 'directorist_add_listing_official_partner_field', 10, 2);

if (! function_exists('directorist_add_listing_official_partner_field')) {
    function directorist_add_listing_official_partner_field($template, $field_data)
    {
        if ($field_data['widget_name'] == 'official_partner') {
            if( is_admin() || ( ! isset($field_data['only_for_admin']) || ! $field_data['only_for_admin'] )) {
                $template .= directorist_custom_badge_load_template('add-listing', $field_data);
            }
        }
        return $template;
    }
}


function directorist_custom_badge_load_template($template_file, $args = array())
{
    $data = $args;

    $theme_template  = '/directorist-custom-badge/' . $template_file . '.php';
    $plugin_template = DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/' . $template_file . '.php';

    if (file_exists(get_stylesheet_directory() . $theme_template)) {
        $file = get_stylesheet_directory() . $theme_template;
    } elseif (file_exists(get_template_directory() . $theme_template)) {
        $file = get_template_directory() . $theme_template;
    } else {
        $file = $plugin_template;
    }

    //e_var_dump($file);

    if (file_exists($file)) {
        include $file;
    }
}
