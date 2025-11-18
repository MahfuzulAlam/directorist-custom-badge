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
        // Get badges from options
        $badges = Directorist_Custom_Badges_Admin::get_badges();
        
        if (empty($badges)) {
            return [];
        }

        // Initialize each active badge
        $badges_data = [];

        foreach ($badges as $badge) {
            if (!isset($badge['is_active']) || !$badge['is_active']) {
                continue;
            }

            // Prepare attributes for Directorist_Badge
            $atts = array(
                'id' => $badge['badge_id'],
                'label' => $badge['badge_label'],
                'icon' => !empty($badge['badge_icon']) ? $badge['badge_icon'] : '',
                'hook' => 'atbdp-' . $badge['badge_id'],
                'title' => $badge['badge_label'],
                'class' => !empty($badge['badge_class']) ? $badge['badge_class'] : '',
                'color' => !empty($badge['badge_color']) ? $badge['badge_color'] : '',
                'badge_data' => $badge, // Store full badge data for condition checking
            );

            $badges_data[] = $atts;
        }

        return $badges_data;
    }

    /**
     * Template Exists
     */
    public function template_exists($template_file)
    {
        $file = DIRECTORIST_CUSTOM_BADGE_DIR . '/templates/' . $template_file . '.php';

        if (file_exists($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Template
     */
    public function get_template($template_file, $args = array())
    {
        if (is_array($args)) {
            extract($args);
        }

        if (isset($args['listing'])) $listing = $args['listing'];

        $badges = $this->get_badges_from_options();

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
}

new Directorist_Custom_Single_Listing_Badge();