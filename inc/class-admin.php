<?php

/**
 * Admin class for Directorist Custom Badges
 * 
 * @author  wpwax
 * @since   3.0.0
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Directorist_Custom_Badges_Admin
{
    /**
     * Option name for storing badges
     */
    const OPTION_NAME = 'directorist_custom_badges';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Hooks
     */
    public function hooks()
    {
        add_action('admin_menu', array($this, 'add_admin_submenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_dcb_get_badge', array($this, 'ajax_get_badge'));
        add_action('wp_ajax_dcb_save_badge', array($this, 'ajax_save_badge'));
        add_action('wp_ajax_dcb_delete_badge', array($this, 'ajax_delete_badge'));
        add_action('wp_ajax_dcb_toggle_badge', array($this, 'ajax_toggle_badge'));
        add_action('wp_ajax_dcb_reorder_badges', array($this, 'ajax_reorder_badges'));
        add_action('wp_ajax_dcb_duplicate_badge', array($this, 'ajax_duplicate_badge'));
        add_action('wp_ajax_dcb_export_badges', array($this, 'ajax_export_badges'));
        add_action('wp_ajax_dcb_import_badges', array($this, 'ajax_import_badges'));
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        // Check if we're on our admin pages using hook or page parameter
        $is_list_page = ($hook === 'at_biz_dir_page_directorist-custom-badges');
        $is_form_page = ($hook === 'at_biz_dir_page_directorist-custom-badges-form');
        
        // Also check page parameter for hidden pages
        if (!$is_list_page && !$is_form_page) {
            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            if ($page === 'directorist-custom-badges' || $page === 'directorist-custom-badges-form') {
                $is_list_page = ($page === 'directorist-custom-badges');
                $is_form_page = ($page === 'directorist-custom-badges-form');
            } else {
                return;
            }
        }

        if (!$is_list_page && !$is_form_page) {
            return;
        }

        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue WordPress color picker (only on form page)
        $select2_handle = null;
        if ( $is_form_page ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );

            // Select2 for Meta Key combobox (tags = type custom value).
            $select2_handle = $this->enqueue_select2_for_form();
        }

        $admin_script_deps = array( 'jquery', 'jquery-ui-sortable' );
        if ( $is_form_page && $select2_handle ) {
            $admin_script_deps[] = $select2_handle;
        }

        wp_enqueue_script(
            'directorist-custom-badges-admin',
            DIRECTORIST_CUSTOM_BADGE_URI . 'assets/js/admin.js',
            $admin_script_deps,
            DIRECTORIST_CUSTOM_BADGE_VERSION,
            true
        );

        wp_enqueue_style(
            'directorist-custom-badges-admin',
            DIRECTORIST_CUSTOM_BADGE_URI . 'assets/css/admin.css',
            array(),
            DIRECTORIST_CUSTOM_BADGE_VERSION
        );

        $localize = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'directorist_custom_badges_nonce' ),
            'strings' => array(
                'confirmDelete'    => __( 'Are you sure you want to delete this badge?', 'directorist-custom-badges' ),
                'saving'           => __( 'Saving…', 'directorist-custom-badges' ),
                'saved'            => __( 'Saved successfully!', 'directorist-custom-badges' ),
                'error'            => __( 'An error occurred. Please try again.', 'directorist-custom-badges' ),
                'requiredField'    => __( 'This field is required.', 'directorist-custom-badges' ),
                'uniqueBadgeId'    => __( 'Badge ID must be unique.', 'directorist-custom-badges' ),
                'invalidBadgeId'   => __( 'Badge ID must be lowercase with hyphens only.', 'directorist-custom-badges' ),
                'condition'        => __( 'Condition', 'directorist-custom-badges' ),
                'minimize'         => __( 'Minimize', 'directorist-custom-badges' ),
                'maximize'         => __( 'Maximize', 'directorist-custom-badges' ),
                'metaKeyPlaceholder' => __( 'Select or type a meta key…', 'directorist-custom-badges' ),
            ),
        );

        if ( $is_form_page ) {
            $localize['metaKeys'] = self::get_listing_meta_keys();
        }

        wp_localize_script( 'directorist-custom-badges-admin', 'dcbAdmin', $localize );
    }

    /**
     * Enqueue Select2 (prefer Directorist's bundled copy; CDN fallback).
     */
    /**
     * @return string|null Script handle enqueued, or null.
     */
    private function enqueue_select2_for_form() {
        if ( wp_script_is( 'directorist-select2-script', 'registered' ) ) {
            wp_enqueue_style( 'directorist-select2-style' );
            wp_enqueue_script( 'directorist-select2-script' );
            return 'directorist-select2-script';
        }

        // CDN fallback when Directorist asset loader has not registered handles.
        if ( ! wp_script_is( 'dcb-select2', 'registered' ) ) {
            wp_register_style(
                'dcb-select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                array(),
                '4.1.0-rc.0'
            );
            wp_register_script(
                'dcb-select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                array( 'jquery' ),
                '4.1.0-rc.0',
                true
            );
        }
        wp_enqueue_style( 'dcb-select2' );
        wp_enqueue_script( 'dcb-select2' );
        return 'dcb-select2';
    }

    /**
     * Collect distinct post meta keys used on listings (ATBDP_POST_TYPE).
     *
     * Merges DB-discovered keys with common Directorist field keys so the
     * dropdown is useful even before many listings exist.
     *
     * @return string[] Sorted unique meta keys.
     */
    public static function get_listing_meta_keys() {
        static $db_keys_cached = null;

        global $wpdb;

        $post_type = defined( 'ATBDP_POST_TYPE' ) ? ATBDP_POST_TYPE : 'at_biz_dir';

        if ( null === $db_keys_cached ) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- post_type validated above.
            $db_keys_cached = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT pm.meta_key
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE p.post_type = %s
					AND pm.meta_key != ''
					ORDER BY pm.meta_key ASC
					LIMIT 500",
                    $post_type
                )
            );
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            if ( ! is_array( $db_keys_cached ) ) {
                $db_keys_cached = array();
            }
        }

        $keys = $db_keys_cached;

        // Common Directorist / extension meta keys (always offer as suggestions).
        $common = array(
            '_featured',
            '_price',
            '_atbdp_listing_pricing_plans',
            '_listing_img',
            '_videourl',
            '_manual_lat',
            '_manual_lng',
            '_hide_map',
            '_directory_type',
            '_default_preview_image',
            'address',
            'zip',
            'phone',
            'phone2',
            'fax',
            'email',
            'website',
            'tagline',
        );

        $merged = array_unique( array_merge( $common, $keys ) );
        sort( $merged, SORT_STRING );

        return array_values( $merged );
    }

    /**
     * Add admin submenu
     */
    public function add_admin_submenu()
    {
        $parent_slug = 'edit.php?post_type=at_biz_dir';
        
        // Main badges list page
        add_submenu_page(
            $parent_slug,
            __('Custom Badges', 'directorist-custom-badges'),
            __('Custom Badges', 'directorist-custom-badges'),
            'manage_options',
            'directorist-custom-badges',
            array($this, 'render_admin_page')
        );

        // Add/Edit badge form page (will be hidden from menu using CSS)
        add_submenu_page(
            $parent_slug,
            __('Badge Configuration', 'directorist-custom-badges'),
            __('Badge Configuration', 'directorist-custom-badges'),
            'manage_options',
            'directorist-custom-badges-form',
            array($this, 'render_form_page')
        );

        // Hide the form page from the menu using CSS
        add_action('admin_head', array($this, 'hide_form_page_from_menu_css'));
    }

    /**
     * Hide form page from admin menu using CSS
     */
    public function hide_form_page_from_menu_css()
    {
        ?>
        <style type="text/css">
            /* Hide the form page from submenu - multiple selectors for compatibility */
            #toplevel_page_edit-post_type-at_biz_dir ul.wp-submenu li a[href*="directorist-custom-badges-form"],
            #toplevel_page_edit-post_type-at_biz_dir ul.wp-submenu li:has(a[href*="directorist-custom-badges-form"]),
            #toplevel_page_edit-post_type-at_biz_dir .wp-submenu li a[href*="directorist-custom-badges-form"],
            .wp-submenu li a[href*="page=directorist-custom-badges-form"] {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Render admin page (list view)
     */
    public function render_admin_page()
    {
        $template_path = DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/admin-page.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('Custom Badges', 'directorist-custom-badges') . '</h1><p>' . esc_html__('Template file not found.', 'directorist-custom-badges') . '</p></div>';
        }
    }

    /**
     * Render form page (add/edit)
     */
    public function render_form_page()
    {
        $template_path = DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/admin-form-page.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('Badge Configuration', 'directorist-custom-badges') . '</h1><p>' . esc_html__('Template file not found.', 'directorist-custom-badges') . '</p></div>';
        }
    }

    /**
     * Get all badges
     */
    public static function get_badges()
    {
        $badges = get_option(self::OPTION_NAME, array());
        
        if (!is_array($badges)) {
            return array();
        }

        // Sort by order
        usort($badges, function($a, $b) {
            $order_a = isset($a['order']) ? intval($a['order']) : 0;
            $order_b = isset($b['order']) ? intval($b['order']) : 0;
            return $order_a - $order_b;
        });

        return $badges;
    }

    /**
     * Get single badge by ID
     */
    public static function get_badge($id)
    {
        $badges = self::get_badges();
        
        foreach ($badges as $badge) {
            if (isset($badge['id']) && $badge['id'] === $id) {
                return $badge;
            }
        }

        return false;
    }

    /**
     * Save badge
     */
    public static function save_badge($badge_data)
    {
        $badges = self::get_badges();

        // Validate required fields
        if (empty($badge_data['badge_title']) || empty($badge_data['badge_id']) || empty($badge_data['badge_label'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'directorist-custom-badges'));
        }

        // Sanitize badge_id
        $badge_id = sanitize_key($badge_data['badge_id']);
        
        // Validate badge_id format (lowercase with hyphens only)
        if (!preg_match('/^[a-z0-9-]+$/', $badge_id)) {
            return new WP_Error('invalid_badge_id', __('Badge ID must be lowercase with hyphens only.', 'directorist-custom-badges'));
        }

        // Check if badge_id is unique (if updating, exclude current badge)
        $existing_badge = false;
        if (!empty($badge_data['id'])) {
            $existing_badge = self::get_badge($badge_data['id']);
        }

        foreach ($badges as $badge) {
            if (isset($badge['badge_id']) && $badge['badge_id'] === $badge_id) {
                // If updating same badge, allow same badge_id
                if ($existing_badge && isset($badge['id']) && $badge['id'] === $badge_data['id']) {
                    continue;
                }
                return new WP_Error('duplicate_badge_id', __('Badge ID must be unique.', 'directorist-custom-badges'));
            }
        }

        // Prepare badge data
        $badge = array(
            'id' => !empty($badge_data['id']) ? sanitize_text_field($badge_data['id']) : Directorist_Custom_Badges_Helper::generate_unique_id(),
            'badge_title' => sanitize_text_field($badge_data['badge_title']),
            'badge_icon' => sanitize_text_field($badge_data['badge_icon'] ?? ''),
            'badge_id' => $badge_id,
            'badge_label' => sanitize_text_field($badge_data['badge_label']),
            'badge_class' => sanitize_text_field($badge_data['badge_class'] ?? ''),
            'badge_color' => sanitize_hex_color($badge_data['badge_color'] ?? '') ?: '',
            'conditions' => Directorist_Custom_Badges_Helper::sanitize_conditions($badge_data['conditions'] ?? array()),
            'condition_relation' => in_array($badge_data['condition_relation'] ?? 'AND', array('AND', 'OR')) ? $badge_data['condition_relation'] : 'AND',
            'is_active' => isset($badge_data['is_active']) ? (bool) $badge_data['is_active'] : true,
            'order' => isset($badge_data['order']) ? intval($badge_data['order']) : count($badges),
        );

        // Update or add badge
        if ($existing_badge) {
            $index = array_search($existing_badge, $badges);
            if ($index !== false) {
                $badges[$index] = $badge;
            }
        } else {
            $badges[] = $badge;
        }

        // Save to options
        update_option(self::OPTION_NAME, $badges, 'no');

        return $badge;
    }

    /**
     * Delete badge
     */
    public static function delete_badge($id)
    {
        $badges = self::get_badges();
        
        foreach ($badges as $key => $badge) {
            if (isset($badge['id']) && $badge['id'] === $id) {
                unset($badges[$key]);
                $badges = array_values($badges); // Reindex
                update_option(self::OPTION_NAME, $badges, 'no');
                return true;
            }
        }

        return false;
    }

    /**
     * Toggle badge active status
     */
    public static function toggle_badge($id)
    {
        $badge = self::get_badge($id);
        
        if (!$badge) {
            return false;
        }

        $badge['is_active'] = !$badge['is_active'];
        return self::save_badge($badge);
    }

    /**
     * Reorder badges
     */
    public static function reorder_badges($order_array)
    {
        $badges = self::get_badges();
        $new_order = array();

        foreach ($order_array as $index => $badge_id) {
            foreach ($badges as $badge) {
                if (isset($badge['id']) && $badge['id'] === $badge_id) {
                    $badge['order'] = $index;
                    $new_order[] = $badge;
                    break;
                }
            }
        }

        update_option(self::OPTION_NAME, $new_order, 'no');
        return true;
    }

    /**
     * Duplicate badge
     */
    public static function duplicate_badge($id)
    {
        $badge = self::get_badge($id);
        
        if (!$badge) {
            return false;
        }

        // Create new badge with unique ID
        $new_badge = $badge;
        unset($new_badge['id']);
        $new_badge['badge_id'] = $badge['badge_id'] . '-copy-' . time();
        $new_badge['badge_title'] = $badge['badge_title'] . ' (Copy)';
        $new_badge['order'] = count(self::get_badges());

        return self::save_badge($new_badge);
    }


    /**
     * AJAX: Get badge
     */
    public function ajax_get_badge()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        if (empty($id)) {
            wp_send_json_error(array('message' => __('Badge ID is required.', 'directorist-custom-badges')));
        }

        $badge = self::get_badge($id);

        if ($badge) {
            wp_send_json_success(array('badge' => $badge));
        } else {
            wp_send_json_error(array('message' => __('Badge not found.', 'directorist-custom-badges')));
        }
    }

    /**
     * AJAX: Save badge
     */
    public function ajax_save_badge()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        // Get badge data from POST and sanitize
        $badge_data = isset($_POST['badge']) ? $_POST['badge'] : array();
        
        if (!is_array($badge_data)) {
            wp_send_json_error(array('message' => __('Invalid badge data.', 'directorist-custom-badges')));
        }
        
        // Sanitize badge data
        $badge_data = self::sanitize_badge_post_data($badge_data);
        
        // Ensure conditions is an array
        if (isset($badge_data['conditions']) && !is_array($badge_data['conditions'])) {
            $badge_data['conditions'] = array();
        }
        
        // Re-index conditions array to ensure proper structure
        if (isset($badge_data['conditions']) && is_array($badge_data['conditions'])) {
            $badge_data['conditions'] = array_values($badge_data['conditions']);
        }
        
        $result = self::save_badge($badge_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('badge' => $result, 'message' => __('Badge saved successfully.', 'directorist-custom-badges')));
    }

    /**
     * AJAX: Delete badge
     */
    public function ajax_delete_badge()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        if (empty($id)) {
            wp_send_json_error(array('message' => __('Badge ID is required.', 'directorist-custom-badges')));
        }

        $result = self::delete_badge($id);

        if ($result) {
            wp_send_json_success(array('message' => __('Badge deleted successfully.', 'directorist-custom-badges')));
        } else {
            wp_send_json_error(array('message' => __('Badge not found.', 'directorist-custom-badges')));
        }
    }

    /**
     * AJAX: Toggle badge
     */
    public function ajax_toggle_badge()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        if (empty($id)) {
            wp_send_json_error(array('message' => __('Badge ID is required.', 'directorist-custom-badges')));
        }

        $result = self::toggle_badge($id);

        if ($result) {
            wp_send_json_success(array('badge' => $result, 'message' => __('Badge status updated.', 'directorist-custom-badges')));
        } else {
            wp_send_json_error(array('message' => __('Badge not found.', 'directorist-custom-badges')));
        }
    }

    /**
     * AJAX: Reorder badges
     */
    public function ajax_reorder_badges()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $order = isset($_POST['order']) ? $_POST['order'] : array();

        if (empty($order) || !is_array($order)) {
            wp_send_json_error(array('message' => __('Invalid order data.', 'directorist-custom-badges')));
        }

        // Sanitize order array
        $order = array_map('sanitize_text_field', $order);

        $result = self::reorder_badges($order);

        if ($result) {
            wp_send_json_success(array('message' => __('Badges reordered successfully.', 'directorist-custom-badges')));
        } else {
            wp_send_json_error(array('message' => __('Failed to reorder badges.', 'directorist-custom-badges')));
        }
    }

    /**
     * AJAX: Duplicate badge
     */
    public function ajax_duplicate_badge()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

        if (empty($id)) {
            wp_send_json_error(array('message' => __('Badge ID is required.', 'directorist-custom-badges')));
        }

        $result = self::duplicate_badge($id);

        if ($result) {
            wp_send_json_success(array('badge' => $result, 'message' => __('Badge duplicated successfully.', 'directorist-custom-badges')));
        } else {
            wp_send_json_error(array('message' => __('Badge not found.', 'directorist-custom-badges')));
        }
    }

    /**
     * AJAX: Export badges
     */
    public function ajax_export_badges()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $badges = self::get_badges();
        wp_send_json_success(array('badges' => $badges));
    }

    /**
     * AJAX: Import badges
     */
    public function ajax_import_badges()
    {
        check_ajax_referer('directorist_custom_badges_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'directorist-custom-badges')));
        }

        $badges_data = isset($_POST['badges']) ? $_POST['badges'] : array();

        if (empty($badges_data) || !is_array($badges_data)) {
            wp_send_json_error(array('message' => __('Invalid badges data.', 'directorist-custom-badges')));
        }

        // Sanitize badges data
        foreach ($badges_data as $key => $badge_data) {
            if (is_array($badge_data)) {
                $badges_data[$key] = self::sanitize_badge_post_data($badge_data);
            }
        }

        $imported = 0;
        $errors = array();

        foreach ($badges_data as $badge_data) {
            // Generate new IDs for imported badges
            unset($badge_data['id']);
            $badge_data['badge_id'] = $badge_data['badge_id'] . '-imported-' . time() . '-' . wp_rand(100, 999);
            
            $result = self::save_badge($badge_data);
            
            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } else {
                $imported++;
            }
        }

        if ($imported > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d badge(s) imported successfully.', 'directorist-custom-badges'), $imported),
                'imported' => $imported,
                'errors' => $errors
            ));
        } else {
            wp_send_json_error(array('message' => __('No badges were imported.', 'directorist-custom-badges'), 'errors' => $errors));
        }
    }

    /**
     * Sanitize badge POST data
     *
     * @param array $data Badge data from POST
     * @return array Sanitized badge data
     */
    private static function sanitize_badge_post_data($data)
    {
        if (!is_array($data)) {
            return array();
        }

        $sanitized = array();

        foreach ($data as $key => $value) {
            $key = sanitize_key($key);

            if (is_array($value)) {
                // Recursively sanitize nested arrays
                if ($key === 'conditions') {
                    $sanitized[$key] = Directorist_Custom_Badges_Helper::sanitize_conditions($value);
                } else {
                    $sanitized[$key] = self::sanitize_badge_post_data($value);
                }
            } else {
                // Sanitize based on field type
                switch ($key) {
                    case 'badge_id':
                    case 'id':
                        $sanitized[$key] = sanitize_key($value);
                        break;
                    case 'badge_color':
                        $sanitized[$key] = sanitize_hex_color($value) ?: '';
                        break;
                    case 'is_active':
                        $sanitized[$key] = (bool) $value;
                        break;
                    case 'order':
                        $sanitized[$key] = absint($value);
                        break;
                    case 'condition_relation':
                        $sanitized[$key] = in_array($value, array('AND', 'OR'), true) ? $value : 'AND';
                        break;
                    default:
                        $sanitized[$key] = sanitize_text_field($value);
                        break;
                }
            }
        }

        return $sanitized;
    }
}

