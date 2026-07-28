<?php

/**
 * @author  wpxplore
 * @since   1.0
 * @version 3.4.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Smart_Single_Listing_Badge
{

    public function __construct()
    {
        add_filter( 'directorist_template', [ $this, 'directorist_template' ], 10, 2 );
    }

    /**
     * Get badges from options
     *
     * @return array Badges data
     */
    public function get_badges_from_options()
    {
        return Directorist_Smart_Badges_Helper::get_badges_from_options();
    }

    /**
     * Check if template exists
     *
     * @param string $template_file Template file name
     * @return bool
     */
    public function template_exists($template_file)
    {
        return Directorist_Smart_Badges_Helper::template_exists($template_file);
    }

    /**
     * Get Template
     *
     * Exposes $listing, $data, and $custom_badges to the included template.
     */
    public function get_template($template_file, $args = array())
    {
        $listing = isset($args['listing']) ? $args['listing'] : null;
        $data = isset($args['data']) ? $args['data'] : $args;

        $custom_badges = $this->get_listing_badges($data);

        if ($this->template_exists($template_file)) {
            include DIRECTORIST_SMART_BADGE_DIR . '/templates/' . $template_file . '.php';
        }
    }

    /**
     * Directorist template filter
     *
     * @param mixed $template   Template content
     * @param array $field_data Field data
     * @return mixed
     */
    public function directorist_template($template, $field_data)
    {
        if ( $template == 'single/fields/badges' && $this->template_exists($template)) {
            return $this->get_template($template, $field_data);
        }
        return $template;
    }

    /**
     * Get listing badges
     *
     * @param array $data Template data
     * @return array Listing badges
     */
    public function get_listing_badges($data)
    {
        $listing_badges = array();

        if (!isset($data['options']['fields']) || !is_array($data['options']['fields'])) {
            return $listing_badges;
        }

        $default_badges = array('featured_badge', 'popular_badge', 'new_badge');
        $badge_options = $this->get_badges_from_options();

        foreach ($data['options']['fields'] as $key => $badge) {
            if (!isset($badge['value']) || !$badge['value']) {
                continue;
            }

            $key = sanitize_key($key);

            if (in_array($key, $default_badges, true)) {
                continue;
            }

            if (!empty($badge_options)) {
                foreach ($badge_options as $badge_option) {
                    if (isset($badge_option['id']) && $badge_option['id'] === $key
                        && isset($badge_option['badge_data']['is_active'])
                        && $badge_option['badge_data']['is_active']
                    ) {
                        $badge['data'] = $badge_option;
                        break;
                    }
                }
            }

            $listing_badges[$key] = $badge;
        }

        return $listing_badges;
    }
}

new Directorist_Smart_Single_Listing_Badge();
