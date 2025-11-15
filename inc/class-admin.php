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
        wp_enqueue_script(
            'directorist-custom-badges-admin',
            DIRECTORIST_CUSTOM_BADGE_URI . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            '3.0.0',
            true
        );

        wp_enqueue_style(
            'directorist-custom-badges-admin',
            DIRECTORIST_CUSTOM_BADGE_URI . 'assets/css/admin.css',
            array(),
            '3.0.0'
        );

        wp_localize_script('directorist-custom-badges-admin', 'dcbAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('directorist_custom_badges_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this badge?', 'directorist-custom-badges'),
                'confirmBulkDelete' => __('Are you sure you want to delete selected badges?', 'directorist-custom-badges'),
                'saving' => __('Saving...', 'directorist-custom-badges'),
                'saved' => __('Saved successfully!', 'directorist-custom-badges'),
                'error' => __('An error occurred. Please try again.', 'directorist-custom-badges'),
                'requiredField' => __('This field is required.', 'directorist-custom-badges'),
                'uniqueBadgeId' => __('Badge ID must be unique.', 'directorist-custom-badges'),
                'invalidBadgeId' => __('Badge ID must be lowercase with hyphens only.', 'directorist-custom-badges'),
            )
        ));
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
            'id' => !empty($badge_data['id']) ? sanitize_text_field($badge_data['id']) : self::generate_unique_id(),
            'badge_title' => sanitize_text_field($badge_data['badge_title']),
            'badge_icon' => sanitize_text_field($badge_data['badge_icon'] ?? ''),
            'badge_id' => $badge_id,
            'badge_label' => sanitize_text_field($badge_data['badge_label']),
            'badge_class' => sanitize_text_field($badge_data['badge_class'] ?? ''),
            'conditions' => self::sanitize_conditions($badge_data['conditions'] ?? array()),
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
     * Sanitize conditions
     */
    private static function sanitize_conditions($conditions)
    {
        if (!is_array($conditions)) {
            return array();
        }

        $sanitized = array();
        $allowed_types = array('meta', 'pricing_plan');
        $allowed_compares = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'EXISTS', 'NOT EXISTS');
        $allowed_meta_types = array('CHAR', 'NUMERIC', 'DECIMAL', 'DATE', 'DATETIME');

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
     */
    private static function generate_unique_id()
    {
        return time() . '-' . wp_rand(1000, 9999);
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

        $badge_data = isset($_POST['badge']) ? $_POST['badge'] : array();
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
}

