<?php

/**
 * @author  wpxplore
 * @since   1.0
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Custom_Single_Listing_Badge
{

    public function __construct()
    {
        add_filter( 'directorist_template', [ $this, 'directorist_template' ], 10, 2 );
    }

    public function get_badges_from_options()
    {
        return Directorist_Custom_Badges_Helper::get_badges_from_options();
    }

    /**
     * Template Exists
     */
    public function template_exists($template_file)
    {
        return Directorist_Custom_Badges_Helper::template_exists($template_file);
    }

    /**
     * Get Template
     */
    public function get_template($template_file, $args = array())
    {
        if (is_array($args)) {
            extract($args);
        }
        
        if($template_file !== 'single/fields/badges') {
            return;
        }

        if (isset($args['listing'])) $listing = $args['listing'];

        $custom_badges = $this->get_listing_badges($data);

        $file = DIRECTORIST_CUSTOM_BADGE_DIR . '/templates/' . $template_file . '.php';

        if ($this->template_exists($template_file)) {
            include $file;
        }
    }

    /**
     * Directorist Template
     */
    public function directorist_template($template, $field_data)
    {
        if ($this->template_exists($template)) $template = $this->get_template($template, $field_data);
        return $template;
    }

    /**
     * Get Listing Badges
     */
    public function get_listing_badges($data)
    {
        $listing_badges = [];

        $default_badges = [ 'featured_badge', 'popular_badge', 'new_badge'];

        $badge_options = $this->get_badges_from_options();

        foreach ($data['options']['fields'] as $key => $badge) {
            if($badge['value'] && ! in_array($key, $default_badges)) {
                if( ! empty($badge_options) ){
                    foreach($badge_options as $badge_option) {
                        if($badge_option['id'] == $key && $badge_option['badge_data']['is_active']) {
                            $badge['data'] = $badge_option;
                        }
                    }
                }
                $listing_badges[$key] = $badge;
            }
        }

        return $listing_badges;
    }
}

new Directorist_Custom_Single_Listing_Badge();