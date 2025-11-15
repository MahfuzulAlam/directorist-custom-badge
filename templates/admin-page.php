<?php
/**
 * Admin page template for Custom Badges
 * 
 * @package Directorist - Custom Badges
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$badges = Directorist_Custom_Badges_Admin::get_badges();
?>

<div class="wrap dcb-admin-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Custom Badges', 'directorist-custom-badges'); ?></h1>
    <button type="button" class="page-title-action dcb-add-badge-btn"><?php echo esc_html__('Add New Badge', 'directorist-custom-badges'); ?></button>
    <button type="button" class="page-title-action dcb-export-badges"><?php echo esc_html__('Export', 'directorist-custom-badges'); ?></button>
    <label for="dcb-import-file" class="page-title-action" style="cursor: pointer; margin-left: 5px;">
        <?php echo esc_html__('Import', 'directorist-custom-badges'); ?>
        <input type="file" id="dcb-import-file" accept=".json" style="display: none;">
    </label>
    
    <div class="dcb-notices"></div>

    <div class="dcb-admin-content">
        <!-- Badges Table View -->
        <div class="dcb-badges-table-wrapper" id="dcb-badges-table" style="<?php echo empty($badges) ? 'display: none;' : ''; ?>">
            <table class="wp-list-table widefat fixed striped dcb-badges-table">
                <thead>
                    <tr>
                        <th class="column-order" style="width: 50px;"><?php echo esc_html__('Order', 'directorist-custom-badges'); ?></th>
                        <th class="column-title"><?php echo esc_html__('Badge Title', 'directorist-custom-badges'); ?></th>
                        <th class="column-label"><?php echo esc_html__('Badge Label', 'directorist-custom-badges'); ?></th>
                        <th class="column-id"><?php echo esc_html__('Badge ID', 'directorist-custom-badges'); ?></th>
                        <th class="column-conditions"><?php echo esc_html__('Conditions', 'directorist-custom-badges'); ?></th>
                        <th class="column-status"><?php echo esc_html__('Status', 'directorist-custom-badges'); ?></th>
                        <th class="column-actions"><?php echo esc_html__('Actions', 'directorist-custom-badges'); ?></th>
                    </tr>
                </thead>
                <tbody class="dcb-badges-list sortable">
                    <?php foreach ($badges as $badge): ?>
                        <tr class="dcb-badge-row" data-badge-id="<?php echo esc_attr($badge['id']); ?>">
                            <td class="column-order">
                                <span class="dcb-drag-handle dashicons dashicons-menu"></span>
                            </td>
                            <td class="column-title">
                                <strong><?php echo esc_html($badge['badge_title']); ?></strong>
                            </td>
                            <td class="column-label"><?php echo esc_html($badge['badge_label']); ?></td>
                            <td class="column-id"><code><?php echo esc_html($badge['badge_id']); ?></code></td>
                            <td class="column-conditions">
                                <?php 
                                $condition_count = isset($badge['conditions']) ? count($badge['conditions']) : 0;
                                echo esc_html($condition_count . ' ' . __('condition(s)', 'directorist-custom-badges'));
                                ?>
                            </td>
                            <td class="column-status">
                                <label class="dcb-toggle-switch">
                                    <input type="checkbox" class="dcb-toggle-active" <?php checked($badge['is_active'], true); ?> data-badge-id="<?php echo esc_attr($badge['id']); ?>">
                                    <span class="dcb-toggle-slider"></span>
                                </label>
                            </td>
                            <td class="column-actions">
                                <button type="button" class="button button-small dcb-edit-badge" data-badge-id="<?php echo esc_attr($badge['id']); ?>"><?php echo esc_html__('Edit', 'directorist-custom-badges'); ?></button>
                                <button type="button" class="button button-small dcb-duplicate-badge" data-badge-id="<?php echo esc_attr($badge['id']); ?>"><?php echo esc_html__('Duplicate', 'directorist-custom-badges'); ?></button>
                                <button type="button" class="button button-small button-link-delete dcb-delete-badge" data-badge-id="<?php echo esc_attr($badge['id']); ?>"><?php echo esc_html__('Delete', 'directorist-custom-badges'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div class="dcb-empty-state" id="dcb-empty-state" style="<?php echo !empty($badges) ? 'display: none;' : ''; ?>">
            <p><?php echo esc_html__('No badges configured yet.', 'directorist-custom-badges'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=directorist-custom-badges-form')); ?>" class="button button-primary"><?php echo esc_html__('Add Your First Badge', 'directorist-custom-badges'); ?></a>
        </div>
    </div>
</div>
