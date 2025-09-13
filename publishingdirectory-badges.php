<?php

/** 
 * @package  Directorist - Publishingdirectory Badges
 */

/**
 * Plugin Name:       Directorist - Publishingdirectory Badges
 * Plugin URI:        https://wpwax.com
 * Description:       Custom Badges for Publishingdirectory, Listing export funcition for users and more.
 * Version:           2.0.0
 * Requires at least: 5.2
 * Author:            wpWax
 * Author URI:        https://wpwax.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       publishingdirectory-badges
 * Domain Path:       /languages
 */

/* This is an extension for Directorist plugin. It helps using custom code and template overriding of Directorist plugin.*/

/**
 * If this file is called directly, abrot!!!
 */
if (!defined('ABSPATH')) {
    exit;                      // Exit if accessed
}


if (!class_exists('Publishingdirectory_Badges')) {

    final class Publishingdirectory_Badges
    {
        /**
         * Instance
         */
        private static $instance;

        /**
         * Instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Publishingdirectory_Badges)) {
                self::$instance = new Publishingdirectory_Badges;
                self::$instance->init();
            }
            return self::$instance;
        }

        /**
         * Init
         */
        public function init()
        {
            $this->define_constant();
            $this->includes();
            $this->enqueues();
            $this->hooks();
        }

        /**
         * Define
         */
        public function define_constant()
        {
            if ( !defined( 'PUBLISHINGDIRECTORY_BADGES_URI' ) ) {
                define( 'PUBLISHINGDIRECTORY_BADGES_URI', plugin_dir_url( __FILE__ ) );
            }

            if ( !defined( 'PUBLISHINGDIRECTORY_BADGES_DIR' ) ) {
                define( 'PUBLISHINGDIRECTORY_BADGES_DIR', plugin_dir_path( __FILE__ ) );
            }
        }

        /**
         * Included Files
         */
        public function includes()
        {
            include_once( PUBLISHINGDIRECTORY_BADGES_DIR . '/inc/class-badge.php' );
            include_once( PUBLISHINGDIRECTORY_BADGES_DIR . '/inc/functions.php' );
        }

        /**
         * Enqueues
         */
        public function enqueues()
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        /**
         * Hooks
         */
        public function hooks()
        {
            add_filter('directorist_template', array($this, 'directorist_template'), 10, 2);
        }

        /**
         *  Enqueue JS file
         */
        public function enqueue_scripts()
        {
            // Replace 'your-plugin-name' with the actual name of your plugin's folder.
            wp_enqueue_script('publishingdirectory-script', PUBLISHINGDIRECTORY_BADGES_URI . 'assets/js/main.js', array('jquery'), '1.0', true);
        }

        /**
         *  Enqueue CSS file
         */
        public function enqueue_styles()
        {
            // Replace 'your-plugin-name' with the actual name of your plugin's folder.
            wp_enqueue_style('publishingdirectory-style', PUBLISHINGDIRECTORY_BADGES_URI . 'assets/css/main.css', array(), '1.0');
        }

        /**
         * Template Exists
         */
        public function template_exists($template_file)
        {
            $file = PUBLISHINGDIRECTORY_BADGES_DIR . '/templates/' . $template_file . '.php';

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
            //$data = $args;

            if (isset($args['form'])) $listing_form = $args['form'];

            $file = PUBLISHINGDIRECTORY_BADGES_DIR . '/templates/' . $template_file . '.php';

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

    if (!function_exists('directorist_is_plugin_active')) {
        function directorist_is_plugin_active($plugin)
        {
            return in_array($plugin, (array) get_option('active_plugins', array()), true) || directorist_is_plugin_active_for_network($plugin);
        }
    }

    if (!function_exists('directorist_is_plugin_active_for_network')) {
        function directorist_is_plugin_active_for_network($plugin)
        {
            if (!is_multisite()) {
                return false;
            }

            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins[$plugin])) {
                return true;
            }

            return false;
        }
    }

    function Publishingdirectory_Badges()
    {
        return Publishingdirectory_Badges::instance();
    }

    if (directorist_is_plugin_active('directorist/directorist-base.php')) {
        Publishingdirectory_Badges(); // get the plugin running
    }
}


?>