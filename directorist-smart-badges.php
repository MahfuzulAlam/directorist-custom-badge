<?php

/** 
 * @package  Directorist - Smart Badges
 */

/**
 * Plugin Name:       Directorist - Smart Badges
 * Plugin URI:        https://wpxplore.com/tools/directorist-smart-badges/
 * Description:       Best way to create smart badges for Directorist with advanced condition-based display rules
 * Version:           3.4.0
 * Requires at least: 5.2
 * Author:            wpWax
 * Author URI:        https://wpxplore.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       directorist-smart-badges
 * Domain Path:       /languages
 */

/* This is an extension for Directorist plugin. It helps using custom code and template overriding of Directorist plugin.*/

/**
 * If this file is called directly, abort!
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Directorist_Smart_Badges')) {

    final class Directorist_Smart_Badges
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
            if (!isset(self::$instance) && !(self::$instance instanceof Directorist_Smart_Badges)) {
                self::$instance = new Directorist_Smart_Badges;
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
            $this->migrate_legacy_option();
            $this->includes();
            $this->enqueues();
            $this->hooks();
        }

        /**
         * One-time copy of badges saved under the pre-rename
         * "directorist_custom_badges" option so existing badges
         * survive the rename to Smart Badges.
         */
        public function migrate_legacy_option()
        {
            if (false !== get_option('directorist_smart_badges', false)) {
                return;
            }

            $legacy = get_option('directorist_custom_badges', false);
            if (false !== $legacy) {
                update_option('directorist_smart_badges', $legacy, 'no');
            }
        }

        /**
         * Define constants
         */
        public function define_constant()
        {
            /**
             * Plugin version
             */
            if (!defined('DIRECTORIST_SMART_BADGE_VERSION')) {
                define('DIRECTORIST_SMART_BADGE_VERSION', '3.4.0');
            }

            if (!defined('DIRECTORIST_SMART_BADGE_URI')) {
                define('DIRECTORIST_SMART_BADGE_URI', plugin_dir_url(__FILE__));
            }

            if (!defined('DIRECTORIST_SMART_BADGE_DIR')) {
                define('DIRECTORIST_SMART_BADGE_DIR', plugin_dir_path(__FILE__));
            }
        }

        /**
         * Included Files
         */
        public function includes()
        {
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/class-helper.php' );
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/class-conditions.php' );
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/class-badge.php' );
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/class-single.php' );
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/class-admin.php' );
            include_once( DIRECTORIST_SMART_BADGE_DIR . '/inc/functions.php' );
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
         * Enqueue JS file
         */
        public function enqueue_scripts()
        {
            wp_enqueue_script(
                'directorist-smart-badges-script',
                DIRECTORIST_SMART_BADGE_URI . 'assets/js/main.js',
                array('jquery'),
                DIRECTORIST_SMART_BADGE_VERSION,
                true
            );
        }

        /**
         * Enqueue CSS file
         */
        public function enqueue_styles()
        {
            wp_enqueue_style(
                'directorist-smart-badges-style',
                DIRECTORIST_SMART_BADGE_URI . 'assets/css/main.css',
                array(),
                DIRECTORIST_SMART_BADGE_VERSION
            );
        }

        /**
         * Hooks
         */
        public function hooks()
        {
            // Initialize admin class
            if (is_admin()) {
                new Directorist_Smart_Badges_Admin();
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

    /**
     * Main plugin function
     *
     * @return Directorist_Smart_Badges
     */
    function directorist_smart_badges()
    {
        return Directorist_Smart_Badges::instance();
    }

    // Initialize plugin if Directorist is active
    if (directorist_is_plugin_active('directorist/directorist-base.php')) {
        directorist_smart_badges();
    }
}
