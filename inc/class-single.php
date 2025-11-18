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