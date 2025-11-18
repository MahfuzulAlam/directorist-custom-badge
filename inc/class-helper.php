<?php

/**
 * Helper class for Directorist Custom Badges
 * 
 * @author  wpwax
 * @since   3.0.0
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Custom_Badges_Helper
{
    /**
     * Convert value to boolean
     * Handles WordPress common boolean representations
     * 
     * @param mixed $value The value to convert
     * @return bool
     */
    public static function convert_to_boolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (bool) intval($value);
        }
        
        $value = strtolower(trim(strval($value)));
        
        // Truthy values
        $truthy = array('1', 'yes', 'true', 'on', 'enabled');
        if (in_array($value, $truthy, true)) {
            return true;
        }
        
        // Falsy values
        $falsy = array('0', 'no', 'false', 'off', 'disabled', '');
        if (in_array($value, $falsy, true)) {
            return false;
        }
        
        // Default: convert to boolean
        return (bool) $value;
    }

    /**
     * Sanitize conditions
     * 
     * @param array $conditions Conditions array to sanitize
     * @return array Sanitized conditions
     */
    public static function sanitize_conditions($conditions)
    {
        if (!is_array($conditions)) {
            return array();
        }

        $sanitized = array();
        $allowed_types = array('meta', 'pricing_plan');
        $allowed_compares = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'EXISTS', 'NOT EXISTS');
        $allowed_meta_types = array('CHAR', 'NUMERIC', 'DECIMAL', 'DATE', 'DATETIME', 'BOOLEAN');

        foreach ($conditions as $condition) {
            if (!isset($condition['type']) || !in_array($condition['type'], $allowed_types)) {
                continue;
            }

            $sanitized_condition = array(
                'type' => sanitize_text_field($condition['type']),
            );

            if ($condition['type'] === 'meta') {
                $sanitized_condition['meta_key'] = sanitize_text_field($condition['meta_key'] ?? '');
                $sanitized_condition['meta_value'] = sanitize_text_field($condition['meta_value'] ?? '');
                $sanitized_condition['compare'] = in_array($condition['compare'] ?? '=', $allowed_compares) ? $condition['compare'] : '=';
                $sanitized_condition['type_cast'] = in_array($condition['type_cast'] ?? 'CHAR', $allowed_meta_types) ? $condition['type_cast'] : 'CHAR';
            } elseif ($condition['type'] === 'pricing_plan') {
                // Handle plan_status_condition (new feature) - can be used with or without plan_id
                $allowed_plan_status_conditions = array('user_active_plan', 'listing_has_plan');
                if (!empty($condition['plan_status_condition']) && in_array($condition['plan_status_condition'], $allowed_plan_status_conditions)) {
                    $sanitized_condition['plan_status_condition'] = sanitize_text_field($condition['plan_status_condition']);
                }
                
                // Always save plan_id and compare (even if plan_status_condition is set)
                $sanitized_condition['plan_id'] = intval($condition['plan_id'] ?? 0);
                $allowed_plan_compares = array('=', '!=', 'IN', 'NOT IN');
                $sanitized_condition['compare'] = in_array($condition['compare'] ?? '=', $allowed_plan_compares) ? $condition['compare'] : '=';
            }

            $sanitized[] = $sanitized_condition;
        }

        return $sanitized;
    }

    /**
     * Generate unique ID
     * 
     * @return string Unique ID
     */
    public static function generate_unique_id()
    {
        return time() . '-' . wp_rand(1000, 9999);
    }

    /**
     * Check if template file exists
     * 
     * @param string $template_file Template file name (without .php extension)
     * @return bool
     */
    public static function template_exists($template_file)
    {
        $file = DIRECTORIST_CUSTOM_BADGE_DIR . '/templates/' . $template_file . '.php';

        if (file_exists($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get badges from options and prepare them for use
     * 
     * @return array Array of badge data
     */
    public static function get_badges_from_options()
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
}

