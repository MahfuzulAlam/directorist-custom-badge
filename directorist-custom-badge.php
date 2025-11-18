<?php

/** 
 * @package  Directorist - Custom Badges
 */

/**
 * Plugin Name:       Directorist - Custom Badges
 * Plugin URI:        https://wpxplore.com/tools/directorist-custom-badges/
 * Description:       Best way to creat a custom badge for directorist
 * Version:           3.0.0
 * Requires at least: 5.2
 * Author:            wpWax
 * Author URI:        https://wpxplore.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       directorist-custom-badges
 * Domain Path:       /languages
 */

/* This is an extension for Directorist plugin. It helps using custom code and template overriding of Directorist plugin.*/

/**
 * If this file is called directly, abrot!!!
 */
if (!defined('ABSPATH')) {
    exit;                      // Exit if accessed
}


if (!class_exists('Directorist_Custom_Badges')) {

    final class Directorist_Custom_Badges
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
            if (!isset(self::$instance) && !(self::$instance instanceof Directorist_Custom_Badges)) {
                self::$instance = new Directorist_Custom_Badges;
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
            if ( !defined( 'DIRECTORIST_CUSTOM_BADGES_URI' ) ) {
                define( 'DIRECTORIST_CUSTOM_BADGE_URI', plugin_dir_url( __FILE__ ) );
            }

            if ( !defined( 'DIRECTORIST_CUSTOM_BADGE_DIR' ) ) {
                define( 'DIRECTORIST_CUSTOM_BADGE_DIR', plugin_dir_path( __FILE__ ) );
            }
        }

        /**
         * Included Files
         */
        public function includes()
        {
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/class-helper.php' );
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/class-conditions.php' );
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/class-badge.php' );
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/class-single.php' );
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/class-admin.php' );
            include_once( DIRECTORIST_CUSTOM_BADGE_DIR . '/inc/functions.php' );
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
         *  Enqueue JS file
         */
        public function enqueue_scripts()
        {
            // Replace 'your-plugin-name' with the actual name of your plugin's folder.
            wp_enqueue_script('directorist-custom-script', DIRECTORIST_CUSTOM_BADGE_URI . 'assets/js/main.js', array('jquery'), '1.0', true);
        }

        /**
         *  Enqueue CSS file
         */
        public function enqueue_styles()
        {
            // Replace 'your-plugin-name' with the actual name of your plugin's folder.
            wp_enqueue_style('directorist-custom-style', DIRECTORIST_CUSTOM_BADGE_URI . 'assets/css/main.css', array(), '1.0');
        }

        /**
         * Hooks
         */
        public function hooks()
        {
            // Initialize admin class
            if (is_admin()) {
                new Directorist_Custom_Badges_Admin();
            }
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

    function Directorist_Custom_Badges()
    {
        return Directorist_Custom_Badges::instance();
    }

    if (directorist_is_plugin_active('directorist/directorist-base.php')) {
        Directorist_Custom_Badges(); // get the plugin running
    }
}


?>