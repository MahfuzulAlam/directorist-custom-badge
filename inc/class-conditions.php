<?php

/**
 * Conditions class for Directorist Custom Badges
 * 
 * @author  wpwax
 * @since   3.0.0
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Custom_Badges_Conditions
{
    /**
     * Check if badge conditions are met
     */
    public static function check_conditions($badge_data, $listing_id)
    {
        if (empty($badge_data['conditions']) || !is_array($badge_data['conditions'])) {
            return false;
        }

        $conditions = $badge_data['conditions'];
        $relation = isset($badge_data['condition_relation']) ? $badge_data['condition_relation'] : 'AND';
        
        if (empty($conditions)) {
            return false;
        }

        $results = array();

        foreach ($conditions as $condition) {
            if (!isset($condition['type'])) {
                continue;
            }

            $result = false;

            if ($condition['type'] === 'meta') {
                $result = self::check_meta_condition($condition, $listing_id);
            } elseif ($condition['type'] === 'pricing_plan') {
                $result = self::check_pricing_plan_condition($condition, $listing_id);
            }

            $results[] = $result;
        }

        // Evaluate results based on relation
        if ($relation === 'OR') {
            return in_array(true, $results, true);
        } else {
            // AND relation - all must be true
            return !in_array(false, $results, true) && !empty($results);
        }
    }

    /**
     * Check meta condition
     */
    public static function check_meta_condition($condition, $listing_id)
    {
        if (empty($condition['meta_key'])) {
            return false;
        }

        $meta_value = get_post_meta($listing_id, $condition['meta_key'], true);
        $compare_value = isset($condition['meta_value']) ? $condition['meta_value'] : '';
        $compare = isset($condition['compare']) ? $condition['compare'] : '=';
        $type_cast = isset($condition['type_cast']) ? $condition['type_cast'] : 'CHAR';

        // Ensure values are not null
        $meta_value = is_null($meta_value) ? '' : $meta_value;
        $compare_value = is_null($compare_value) ? '' : $compare_value;

        // Handle EXISTS and NOT EXISTS first (don't need compare_value)
        if ($compare === 'EXISTS') {
            return !empty($meta_value) || $meta_value === '0' || $meta_value === 0;
        }
        if ($compare === 'NOT EXISTS') {
            return empty($meta_value) && $meta_value !== '0' && $meta_value !== 0;
        }

        // Cast meta value based on type for comparison operations
        if ($type_cast === 'NUMERIC' || $type_cast === 'DECIMAL') {
            $meta_value = is_numeric($meta_value) ? floatval($meta_value) : 0;
            $compare_value = is_numeric($compare_value) ? floatval($compare_value) : 0;
        } elseif ($type_cast === 'DATE' || $type_cast === 'DATETIME') {
            $meta_value = !empty($meta_value) ? strtotime($meta_value) : 0;
            $compare_value = !empty($compare_value) ? strtotime($compare_value) : 0;
        } elseif ($type_cast === 'BOOLEAN') {
            // Convert to boolean - WordPress common truthy values: '1', 'yes', 'true', 'on', 1, true
            $meta_value = Directorist_Custom_Badges_Helper::convert_to_boolean($meta_value);
            $compare_value = Directorist_Custom_Badges_Helper::convert_to_boolean($compare_value);
        } else {
            // CHAR - ensure strings for string operations
            $meta_value = strval($meta_value);
            $compare_value = strval($compare_value);
        }

        switch ($compare) {
            case '=':
                return $meta_value == $compare_value;
            case '!=':
                return $meta_value != $compare_value;
            case '>':
                return $meta_value > $compare_value;
            case '>=':
                return $meta_value >= $compare_value;
            case '<':
                return $meta_value < $compare_value;
            case '<=':
                return $meta_value <= $compare_value;
            case 'LIKE':
                // Ensure strings for LIKE operations
                $meta_value = is_null($meta_value) ? '' : strval($meta_value);
                $compare_value = is_null($compare_value) ? '' : strval($compare_value);
                if (empty($meta_value) || empty($compare_value)) {
                    return false;
                }
                return strpos($meta_value, $compare_value) !== false;
            case 'NOT LIKE':
                // Ensure strings for NOT LIKE operations
                $meta_value = is_null($meta_value) ? '' : strval($meta_value);
                $compare_value = is_null($compare_value) ? '' : strval($compare_value);
                if (empty($meta_value) || empty($compare_value)) {
                    return true; // If meta_value is empty, it doesn't contain compare_value
                }
                return strpos($meta_value, $compare_value) === false;
            case 'IN':
                $compare_value_str = is_null($compare_value) ? '' : strval($compare_value);
                $meta_value_str = is_null($meta_value) ? '' : strval($meta_value);
                $values = !empty($compare_value_str) ? array_map('trim', explode(',', $compare_value_str)) : array();
                return in_array($meta_value_str, $values, true);
            case 'NOT IN':
                $compare_value_str = is_null($compare_value) ? '' : strval($compare_value);
                $meta_value_str = is_null($meta_value) ? '' : strval($meta_value);
                $values = !empty($compare_value_str) ? array_map('trim', explode(',', $compare_value_str)) : array();
                return !in_array($meta_value_str, $values, true);
            default:
                return $meta_value == $compare_value;
        }
    }

    /**
     * Check pricing plan condition
     */
    public static function check_pricing_plan_condition($condition, $listing_id)
    {
        if ( ! class_exists( 'ATBDP_Pricing_Plans' ) ) {
            return false;
        }

        if( ! isset( $condition['plan_status_condition'] ) ) {
            $condition['plan_status_condition'] = 'listing_has_plan';
        }

        return self::check_plan_status_condition($condition, $listing_id);

    }

    /**
     * Check plan status condition
     * 
     * @param string $plan_status_condition The plan status condition type (user_active_plan or listing_has_plan)
     * @param int $listing_id The listing ID
     * @return bool
     */
    public static function check_plan_status_condition($condition, $listing_id)
    {
        if ($condition['plan_status_condition'] === 'user_active_plan') {
            // Check if listing owner has at least one active plan
            $listing = get_post($listing_id);
            if (!$listing || !$listing->post_author) {
                return false;
            }

            $user_id = $listing->post_author;

            // Check if user has any active orders (completed payment, not exited)
            $user_has_active_plans = Directorist_Custom_Badges_Helper::user_has_active_plans( $user_id, $condition['plan_id'] );
            return $user_has_active_plans;

        } elseif ($condition['plan_status_condition'] === 'listing_has_plan') {
            // Check if listing is assigned to a plan
            $listing_has_plan = Directorist_Custom_Badges_Helper::listing_has_plan( $listing_id, $condition['plan_id'], $condition['compare'] );
            return $listing_has_plan;
        }

        return false;
    }

}

