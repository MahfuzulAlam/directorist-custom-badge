<?php

/**
 * @author  wpwax
 * @since   1.0
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Badge
{
    public $atts;
    private static $badges_initialized = false;

    public function __construct( $atts = [] )
    {
        $this->atts = $atts;
        $this->render();
    }

    /**
     * Initialize badges from options
     */
    public static function init_badges_from_options()
    {
        if (self::$badges_initialized) {
            return;
        }

        // Get badges from options
        $badges = Directorist_Custom_Badges_Admin::get_badges();
        
        if (empty($badges)) {
            self::$badges_initialized = true;
            return;
        }

        // Initialize each active badge
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

            new Directorist_Badge($atts);
        }

        self::$badges_initialized = true;
    }

    public function render()
    {
        add_filter( 'atbdp_listing_type_settings_field_list', [ $this, 'atbdp_listing_type_settings_field_list' ] );
        add_action( 'atbdp_all_listings_badge_template', [ $this, 'atbdp_all_listings_badge_template' ] );
    }

    public function atbdp_listing_type_settings_field_list( $fields )
    {
        foreach ( $fields as $key => $value ) {
            // setup widgets
            $widget = [
                'type'    => "badge",
                'id'      => $this->atts[ 'id' ],
                'label'   => $this->atts[ 'label' ],
                'icon'    => $this->atts[ 'icon' ] ? $this->atts[ 'icon' ]: "",
                'hook'    => $this->atts[ 'hook' ],
                'options' => [],
            ];
    
            if ( 'listings_card_grid_view' === $key ) {
                // register widget
                $fields[$key]['card_templates']['grid_view_with_thumbnail']['widgets'][$this->atts[ 'id' ]] = $widget;
                $fields[$key]['card_templates']['grid_view_without_thumbnail']['widgets'][$this->atts[ 'id' ]] = $widget;
    
                // grid with preview image
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['top_right']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['top_left']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['bottom_right']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['bottom_left']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['body']['top']['acceptedWidgets'], $this->atts[ 'id' ] );
    
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['footer']['right']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['footer']['left']['acceptedWidgets'], $this->atts[ 'id' ] );
    
                // grid without preview image
                array_push( $fields[$key]['card_templates']['grid_view_without_thumbnail']['layout']['body']['quick_info']['acceptedWidgets'], $this->atts[ 'id' ] );
            }
    
            if ( 'listings_card_list_view' === $key ) {
                // register widget
                $fields[$key]['card_templates']['list_view_with_thumbnail']['widgets'][$this->atts[ 'id' ]] = $widget;
                $fields[$key]['card_templates']['list_view_without_thumbnail']['widgets'][$this->atts[ 'id' ]] = $widget;
    
                // grid with preview image
                array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['thumbnail']['top_right']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['body']['top']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['body']['right']['acceptedWidgets'], $this->atts[ 'id' ] );
    
                // grid without preview image
                array_push( $fields[$key]['card_templates']['list_view_without_thumbnail']['layout']['body']['top']['acceptedWidgets'], $this->atts[ 'id' ] );
                array_push( $fields[$key]['card_templates']['list_view_without_thumbnail']['layout']['body']['right']['acceptedWidgets'], $this->atts[ 'id' ] );
            }
    
        }

        return $fields;

    }

    public function atbdp_all_listings_badge_template( $field )
    {
        // Check if this badge matches the widget_key
        if (!isset($field['widget_key']) || $field['widget_key'] !== $this->atts['id']) {
            return;
        }

        // Get badge data
        $badge_data = isset($this->atts['badge_data']) ? $this->atts['badge_data'] : null;
        
        if (!$badge_data) {
            // Fallback to old method if badge_data is not available
            if (isset($this->atts['meta_key']) && isset($this->atts['meta_value'])) {
                $meta_value = get_post_meta(get_the_ID(), $this->atts['meta_key'], true);
                if ($meta_value == $this->atts['meta_value']) {
                    $this->render_badge();
                }
            }
            return;
        }

        // Check conditions
        if ($this->check_conditions($badge_data, get_the_ID())) {
            $this->render_badge();
        }
    }

    /**
     * Check if badge conditions are met
     */
    private function check_conditions($badge_data, $listing_id)
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
                $result = $this->check_meta_condition($condition, $listing_id);
            } elseif ($condition['type'] === 'pricing_plan') {
                $result = $this->check_pricing_plan_condition($condition, $listing_id);
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
    private function check_meta_condition($condition, $listing_id)
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
            $meta_value = $this->convert_to_boolean($meta_value);
            $compare_value = $this->convert_to_boolean($compare_value);
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
     * Convert value to boolean
     * Handles WordPress common boolean representations
     */
    private function convert_to_boolean($value)
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
     * Check pricing plan condition
     */
    private function check_pricing_plan_condition($condition, $listing_id)
    {
        $plan_status_result = null;
        $plan_id_result = null;
        
        // Check plan_status_condition if set (new feature)
        if (!empty($condition['plan_status_condition'])) {
            $plan_status_result = $this->check_plan_status_condition($condition['plan_status_condition'], $listing_id);
        }

        // Check plan_id if set (backward compatibility and can work alongside plan_status_condition)
        if (!empty($condition['plan_id']) && intval($condition['plan_id']) > 0) {
            $listing_plan_id = get_post_meta($listing_id, '_fm_plans', true);
            $listing_plan_id = intval($listing_plan_id);
            $compare = isset($condition['compare']) ? $condition['compare'] : '=';

            switch ($compare) {
                case '=':
                    $plan_id = intval($condition['plan_id']);
                    $plan_id_result = ($listing_plan_id === $plan_id);
                    break;
                case '!=':
                    $plan_id = intval($condition['plan_id']);
                    $plan_id_result = ($listing_plan_id !== $plan_id);
                    break;
                case 'IN':
                    // Handle comma-separated plan IDs or single value
                    $plan_ids = is_array($condition['plan_id']) 
                        ? $condition['plan_id'] 
                        : explode(',', strval($condition['plan_id']));
                    $plan_ids = array_map('intval', array_map('trim', $plan_ids));
                    $plan_id_result = in_array($listing_plan_id, $plan_ids);
                    break;
                case 'NOT IN':
                    // Handle comma-separated plan IDs or single value
                    $plan_ids = is_array($condition['plan_id']) 
                        ? $condition['plan_id'] 
                        : explode(',', strval($condition['plan_id']));
                    $plan_ids = array_map('intval', array_map('trim', $plan_ids));
                    $plan_id_result = !in_array($listing_plan_id, $plan_ids);
                    break;
                default:
                    $plan_id = intval($condition['plan_id']);
                    $plan_id_result = ($listing_plan_id === $plan_id);
                    break;
            }
        }

        // If both conditions are set, both must be true (AND logic)
        if ($plan_status_result !== null && $plan_id_result !== null) {
            return $plan_status_result && $plan_id_result;
        }
        
        // If only plan_status_condition is set, use that result
        if ($plan_status_result !== null) {
            return $plan_status_result;
        }
        
        // If only plan_id is set, use that result
        if ($plan_id_result !== null) {
            return $plan_id_result;
        }

        // If neither is set, return false
        return false;
    }

    /**
     * Check plan status condition
     * 
     * @param string $plan_status_condition The plan status condition type (user_active_plan or listing_has_plan)
     * @param int $listing_id The listing ID
     * @return bool
     */
    private function check_plan_status_condition($plan_status_condition, $listing_id)
    {
        if ($plan_status_condition === 'user_active_plan') {
            // Check if listing owner has at least one active plan
            $listing = get_post($listing_id);
            if (!$listing || !$listing->post_author) {
                return false;
            }

            $user_id = $listing->post_author;

            // Check if user has any active orders (completed payment, not exited)
            $args = array(
                'post_type'      => 'atbdp_orders',
                'posts_per_page' => 1,
                'post_status'    => 'publish',
                'author'         => $user_id,
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
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

            $active_orders = new WP_Query($args);
            return $active_orders->have_posts();

        } elseif ($plan_status_condition === 'listing_has_plan') {
            // Check if listing is assigned to a plan
            $listing_plan_id = get_post_meta($listing_id, '_fm_plans', true);
            return !empty($listing_plan_id) && intval($listing_plan_id) > 0;
        }

        return false;
    }

    /**
     * Render badge HTML
     */
    private function render_badge()
    {
        $badge_id = esc_attr($this->atts['id']);
        $badge_class = esc_attr($this->atts['class']);
        $badge_title = esc_html($this->atts['title']);
        $badge_icon = !empty($this->atts['icon']) ? esc_attr($this->atts['icon']) : '';
        $badge_color = !empty($this->atts['color']) ? esc_attr($this->atts['color']) : '';
        
        // Build inline style for color
        $style = '';
        if (!empty($badge_color)) {
            $style = ' style="background-color: ' . esc_attr($badge_color) . ';"';
        }
        ?>
        <span id="<?php echo $badge_id; ?>" class="directorist-badge directorist-info-item directorist-badge--only-text directorist-custom-badge <?php echo $badge_class; ?>"<?php echo $style; ?>>
            <?php if ($badge_icon): ?>
                <?php echo directorist_icon($badge_icon); ?>
            <?php endif; ?>
            <?php echo $badge_title; ?>
        </span>
        <?php
    }

}