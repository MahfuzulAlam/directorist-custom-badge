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

    /**
     * Check if listing has plan
     *
     * @param int    $listing_id Listing ID
     * @param string $plan_id    Plan ID(s) - comma separated or single value
     * @param string $compare     Comparison operator
     * @return bool
     */
    public static function listing_has_plan($listing_id = 0, $plan_id = 0, $compare = '')
    {
        $listing_id = absint($listing_id);
        if (empty($listing_id) || empty($plan_id) || empty($compare)) {
            return false;
        }

        if (!is_array($plan_id)) {
            $plan_id = explode(',', $plan_id);
        }

        $listing_plan_id = get_post_meta($listing_id, '_fm_plans', true);
        $listing_plan_id = intval($listing_plan_id);

        $plan_id_result = false;

        switch ($compare) {
            case '=':
                // Passes if the listing_plan_id matches any of the given plan_ids
                $plan_ids = array_map('intval', array_map('trim', $plan_id));
                $plan_id_result = in_array($listing_plan_id, $plan_ids, true);
                break;
            case '!=':
                // Passes if the listing_plan_id does not match any of the given plan_ids
                $plan_ids = array_map('intval', array_map('trim', $plan_id));
                $plan_id_result = !in_array($listing_plan_id, $plan_ids, true);
                break;
            case 'IN':
                // Synonym for '=' when plan_id is an array
                $plan_ids = array_map('intval', array_map('trim', $plan_id));
                $plan_id_result = in_array($listing_plan_id, $plan_ids, true);
                break;
            case 'NOT IN':
                // Synonym for '!=' when plan_id is an array
                $plan_ids = array_map('intval', array_map('trim', $plan_id));
                $plan_id_result = !in_array($listing_plan_id, $plan_ids, true);
                break;
            default:
                $plan_ids = array_map('intval', array_map('trim', $plan_id));
                $plan_id_result = in_array($listing_plan_id, $plan_ids, true);
                break;
        }

        return $plan_id_result;
    }

    /**
     * Check if user has active plans
     *
     * @param int    $user_id User ID
     * @param string $plans   Plan ID(s) - comma separated
     * @return bool
     */
    public static function user_has_active_plans($user_id = 0, $plans = '')
    {
        if (empty($plans)) {
            return false;
        }

        $plans = explode(',', $plans);
        $plans = array_map('absint', array_map('trim', $plans));
        $plans = array_filter($plans);

        if (empty($plans)) {
            return false;
        }

        $user_id = absint($user_id);
        if (empty($user_id)) {
            return false;
        }

        $orders = self::get_orders_by_user($user_id, false);

        if ($orders->have_posts()) {
            foreach ($orders->posts as $order) {
                // Get pricing plan ID from the order meta _fm_plan_ordered
                $pricing_plan_id = absint(get_post_meta($order, '_fm_plan_ordered', true));

                if (empty($pricing_plan_id) || !in_array($pricing_plan_id, $plans, true)) {
                    continue;
                }

                // Check the period and find the expired date
                $period = get_post_meta($pricing_plan_id, '_recurrence_period_term', true);
                $length = absint(get_post_meta($pricing_plan_id, 'fm_length', true));
                $days = self::convert_period_to_days($period, $length);

                if (empty($days)) {
                    continue;
                }

                // Get order submit date (publish date)
                $order_date = get_the_date('Y-m-d', $order);
                if (empty($order_date)) {
                    continue;
                }

                $expired_date = date('Y-m-d', strtotime($order_date . ' + ' . $days . ' days'));
                $current_date = date('Y-m-d');

                // Check if the expired date is greater than the current date
                if ($expired_date > $current_date) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get orders by user
     *
     * @param int  $user_id User ID
     * @param bool $is_paid Whether to get only paid plans
     * @return WP_Query
     */
    public static function get_orders_by_user($user_id = 0, $is_paid = false)
    {
        $user_id = absint($user_id);
        if (empty($user_id)) {
            return new WP_Query(array('post__in' => array(0)));
        }

        if ($is_paid) {
            $pricing_plans = self::get_paid_pricing_plans();
        } else {
            $pricing_plans = self::get_all_pricing_plans();
        }

        if (empty($pricing_plans)) {
            return new WP_Query(array('post__in' => array(0)));
        }

        $args = array(
            'post_type'      => 'atbdp_orders',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'author'         => $user_id,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_fm_plan_ordered',
                    'value'   => $pricing_plans,
                    'compare' => 'IN',
                ),
                array(
                    'key'     => '_payment_status',
                    'value'   => 'completed',
                    'compare' => '=',
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_order_status',
                        'value'   => 'exit',
                        'compare' => '!=',
                    ),
                    array(
                        'key'     => '_order_status',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ),
        );

        return new WP_Query($args);
    }

    /**
     * Get all pricing plans
     *
     * @return array Array of plan IDs
     */
    public static function get_all_pricing_plans()
    {
        $args = array(
            'post_type'      => 'atbdp_pricing_plans',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts;
        }

        return array();
    }

    /**
     * Get paid pricing plans
     *
     * @return array Array of plan IDs
     */
    public static function get_paid_pricing_plans()
    {
        $args = array(
            'post_type'      => 'atbdp_pricing_plans',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => 'fm_price',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
            ),
            'fields'         => 'ids',
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts;
        }

        return array();
    }

    /**
     * Convert period to days
     *
     * @param string $period Period type (month, year, day)
     * @param int    $length Length value
     * @return int Days
     */
    public static function convert_period_to_days($period, $length)
    {
        $length = absint($length);
        if (empty($length)) {
            return 0;
        }

        switch (strtolower($period)) {
            case 'month':
            case 'months':
                return $length * 30;
            case 'year':
            case 'years':
                return $length * 365;
            case 'day':
            case 'days':
            default:
                return $length;
        }
    }
}

