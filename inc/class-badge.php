<?php

/**
 * @author  wpxplore
 * @since   1.0
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Custom_Badge
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
        $badges_data = Directorist_Custom_Badges_Helper::get_badges_from_options();

        if (empty($badges_data)) {
            self::$badges_initialized = true;
            return;
        }

        // Initialize each active badge
        foreach ($badges_data as $atts) {
            new Directorist_Custom_Badge($atts);
        }

        self::$badges_initialized = true;
    }

    public function render()
    {
        add_filter( 'atbdp_listing_type_settings_field_list', [ $this, 'atbdp_listing_type_settings_field_list' ] );
        add_action( 'atbdp_all_listings_badge_template', [ $this, 'atbdp_all_listings_badge_template' ] );
        add_filter( 'directorist_listing_header_layout', [ $this, 'directorist_listing_header_layout' ] );
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

    public function directorist_listing_header_layout( $layout )
    {
        $layout['widgets']['badges']['options']['fields'][$this->atts['id']] = [
            'type' => "toggle",
            'label' => 'Display ' . $this->atts['label'] . ' Badge',
            'value' => false,
        ];

        return $layout;
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
        if (Directorist_Custom_Badges_Conditions::check_conditions($badge_data, get_the_ID())) {
            $this->render_badge();
        }
    }

    private function render_badge()
    {
        $badge_type = isset($this->atts['badge_type']) ? $this->atts['badge_type'] : 'custom';

        if (isset($this->atts['badge_data']['badge_type'])) {
            $badge_type = $this->atts['badge_data']['badge_type'];
        }

        if ('tags' === $badge_type) {
            $this->render_tags();
        } else {
            $this->render_custom_badge();
        }
    }


    /**
     * Render badge HTML
     */
    private function render_custom_badge()
    {
        $badge_id = esc_attr($this->atts['id']);
        $badge_class = esc_attr($this->atts['class']);
        $badge_title = esc_html($this->atts['title']);
        $badge_icon = !empty($this->atts['icon']) ? esc_attr($this->atts['icon']) : '';
        $badge_color = !empty($this->atts['color']) ? sanitize_hex_color($this->atts['color']) : '';
        $has_font_color = !empty($this->atts['font_color']);
        $font_color = $has_font_color ? sanitize_hex_color($this->atts['font_color']) : '#ffffff';
        $font_color = $font_color ?: '#ffffff';
        $badge_padding = !empty($this->atts['badge_padding']) ? esc_attr($this->atts['badge_padding']) : '0 10px';

        // Build inline style for color
        $style = '';
        if (!empty($badge_color) || $has_font_color) {
            $style = ' style="';
            $style .= !empty($badge_color) ? 'background-color: ' . esc_attr($badge_color) . ' !important;' : '';
            $style .= ' color: ' . esc_attr($font_color) . ' !important;';
            $style .= ' padding: ' . esc_attr($badge_padding) . ' !important;"';
        }
        ?>
        <span id="<?php echo esc_attr($badge_id); ?>" class="directorist-badge directorist-info-item directorist-badge--only-text directorist-custom-badge <?php echo esc_attr($badge_class); ?>"<?php echo $style; ?>>
            <?php if ($badge_icon): ?>
                <?php echo directorist_icon($badge_icon); ?>
            <?php endif; ?>
            <?php echo esc_html($badge_title); ?>
        </span>
        <?php
    }

    private function render_tags()
    {
        $tags = $this->get_tags();
        if (empty($tags)) {
            return;
        }

        $maximum_tags = isset($this->atts['maximum_tags']) ? absint($this->atts['maximum_tags']) : 0;
        if ($maximum_tags > 0) {
            $tags = array_slice($tags, 0, $maximum_tags);
        }

        $badge_id = esc_attr($this->atts['id']);
        $badge_class = esc_attr($this->atts['class']);
        $badge_icon = !empty($this->atts['icon']) ? esc_attr($this->atts['icon']) : '';
        $badge_color = !empty($this->atts['color']) ? sanitize_hex_color($this->atts['color']) : '#ffffff';
        $font_color = !empty($this->atts['font_color']) ? sanitize_hex_color($this->atts['font_color']) : '#000000';
        $badge_color = $badge_color ?: '#ffffff';
        $font_color = $font_color ?: '#000000';
        $badge_padding = !empty($this->atts['badge_padding']) ? esc_attr($this->atts['badge_padding']) : '0 5px';

        // Build inline style for color
        $style = '';
        if (!empty($badge_color)) {
            $style = ' style="background-color: ' . esc_attr($badge_color) . ' !important;';
            $style .= ' color: ' . esc_attr($font_color) . ' !important;';
            $style .= ' padding: ' . esc_attr($badge_padding) . ' !important;"';
        }
        ?>
        <span id="<?php echo esc_attr($badge_id); ?>" class="directorist-tags-badge directorist-info-item <?php echo esc_attr($badge_class); ?>">
            <?php foreach ($tags as $tag) : ?>
                <span class="directorist-tag-badge-item" <?php echo $style; ?>>
                    <?php if ($badge_icon): ?>
                        <?php echo directorist_icon($badge_icon); ?>
                    <?php endif; ?>
                    <?php echo esc_html($tag->name); ?>
                </span>
            <?php endforeach; ?>
        </span>
        <?php
    }

    private function get_tags()
    {
        if (!defined('ATBDP_TAGS')) {
            return [];
        }

        $tags = get_the_terms(get_the_ID(), ATBDP_TAGS);
        if (!is_wp_error($tags) && !empty($tags) && is_array($tags)) {
            return $tags;
        }
        return [];
    }

}
