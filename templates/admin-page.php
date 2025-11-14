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
            <button type="button" class="button button-primary dcb-add-badge-btn"><?php echo esc_html__('Add Your First Badge', 'directorist-custom-badges'); ?></button>
        </div>

        <!-- Badge Form (Repeater) -->
        <div class="dcb-badge-form-wrapper" id="dcb-badge-form-wrapper" style="display: none;">
            <div class="dcb-badge-form-container">
                <div class="dcb-badge-form-header">
                    <h2 class="dcb-form-title"><?php echo esc_html__('Badge Configuration', 'directorist-custom-badges'); ?></h2>
                    <button type="button" class="button dcb-close-form"><?php echo esc_html__('Close', 'directorist-custom-badges'); ?></button>
                </div>

                <form id="dcb-badge-form" class="dcb-badge-form">
                    <input type="hidden" name="badge[id]" id="dcb-badge-id" value="">
                    <input type="hidden" name="badge[order]" id="dcb-badge-order" value="">

                    <div class="dcb-form-section">
                        <h3><?php echo esc_html__('Basic Information', 'directorist-custom-badges'); ?></h3>
                        
                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-title">
                                    <?php echo esc_html__('Badge Title', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-title" name="badge[badge_title]" class="dcb-input" placeholder="<?php echo esc_attr__('Features', 'directorist-custom-badges'); ?>" required>
                                <p class="description"><?php echo esc_html__('Internal name for this badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-icon">
                                    <?php echo esc_html__('Badge Icon', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-icon" name="badge[badge_icon]" class="dcb-input" placeholder="<?php echo esc_attr__('uil uil-text-fields', 'directorist-custom-badges'); ?>">
                                <p class="description"><?php echo esc_html__('Icon class name (e.g., uil uil-text-fields).', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-id-field">
                                    <?php echo esc_html__('Badge ID', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-id-field" name="badge[badge_id]" class="dcb-input" placeholder="<?php echo esc_attr__('featured-badge', 'directorist-custom-badges'); ?>" pattern="[a-z0-9-]+" required>
                                <p class="description"><?php echo esc_html__('Unique identifier (lowercase with hyphens only).', 'directorist-custom-badges'); ?></p>
                                <span class="dcb-field-error"></span>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-label">
                                    <?php echo esc_html__('Badge Label', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-label" name="badge[badge_label]" class="dcb-input" placeholder="<?php echo esc_attr__('Featured', 'directorist-custom-badges'); ?>" required>
                                <p class="description"><?php echo esc_html__('Display text for the badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-class">
                                    <?php echo esc_html__('Badge Class', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-class" name="badge[badge_class]" class="dcb-input" placeholder="<?php echo esc_attr__('features-custom-badge', 'directorist-custom-badges'); ?>">
                                <p class="description"><?php echo esc_html__('CSS class for styling.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="dcb-form-section">
                        <h3><?php echo esc_html__('Conditions', 'directorist-custom-badges'); ?></h3>
                        
                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-condition-relation">
                                    <?php echo esc_html__('Condition Relation', 'directorist-custom-badges'); ?>
                                </label>
                                <select id="dcb-condition-relation" name="badge[condition_relation]" class="dcb-select">
                                    <option value="AND"><?php echo esc_html__('AND', 'directorist-custom-badges'); ?></option>
                                    <option value="OR"><?php echo esc_html__('OR', 'directorist-custom-badges'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How conditions should be evaluated.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-conditions-repeater">
                            <div class="dcb-conditions-list" id="dcb-conditions-list">
                                <!-- Conditions will be added here dynamically -->
                            </div>
                            <button type="button" class="button dcb-add-condition"><?php echo esc_html__('Add Condition', 'directorist-custom-badges'); ?></button>
                        </div>
                    </div>

                    <div class="dcb-form-section">
                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label class="dcb-checkbox-label">
                                    <input type="checkbox" id="dcb-badge-active" name="badge[is_active]" value="1" checked>
                                    <?php echo esc_html__('Active', 'directorist-custom-badges'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('Enable or disable this badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="dcb-form-actions">
                        <button type="submit" class="button button-primary dcb-save-badge"><?php echo esc_html__('Save Badge', 'directorist-custom-badges'); ?></button>
                        <button type="button" class="button dcb-cancel-form"><?php echo esc_html__('Cancel', 'directorist-custom-badges'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Condition Template (Hidden) -->
<script type="text/template" id="dcb-condition-template">
    <div class="dcb-condition-item" data-condition-index="{{index}}">
        <div class="dcb-condition-header">
            <span class="dcb-condition-title"><?php echo esc_html__('Condition', 'directorist-custom-badges'); ?> #{{index}}</span>
            <button type="button" class="button-link dcb-remove-condition"><?php echo esc_html__('Remove', 'directorist-custom-badges'); ?></button>
        </div>
        <div class="dcb-condition-body">
            <div class="dcb-form-row">
                <div class="dcb-form-field">
                    <label><?php echo esc_html__('Condition Type', 'directorist-custom-badges'); ?></label>
                    <select name="badge[conditions][{{index}}][type]" class="dcb-condition-type dcb-select">
                        <option value="meta"><?php echo esc_html__('Meta', 'directorist-custom-badges'); ?></option>
                        <option value="pricing_plan"><?php echo esc_html__('Pricing Plan', 'directorist-custom-badges'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Meta Condition Fields -->
            <div class="dcb-condition-fields dcb-meta-fields">
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Meta Key', 'directorist-custom-badges'); ?></label>
                        <input type="text" name="badge[conditions][{{index}}][meta_key]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_key', 'directorist-custom-badges'); ?>">
                    </div>
                </div>
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Meta Value', 'directorist-custom-badges'); ?></label>
                        <input type="text" name="badge[conditions][{{index}}][meta_value]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_value', 'directorist-custom-badges'); ?>">
                    </div>
                </div>
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Compare', 'directorist-custom-badges'); ?></label>
                        <select name="badge[conditions][{{index}}][compare]" class="dcb-select">
                            <option value="=">=</option>
                            <option value="!=">!=</option>
                            <option value=">">&gt;</option>
                            <option value=">=">&gt;=</option>
                            <option value="<">&lt;</option>
                            <option value="<=">&lt;=</option>
                            <option value="LIKE">LIKE</option>
                            <option value="NOT LIKE">NOT LIKE</option>
                            <option value="IN">IN</option>
                            <option value="NOT IN">NOT IN</option>
                            <option value="EXISTS">EXISTS</option>
                            <option value="NOT EXISTS">NOT EXISTS</option>
                        </select>
                    </div>
                </div>
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Type', 'directorist-custom-badges'); ?></label>
                        <select name="badge[conditions][{{index}}][type_cast]" class="dcb-select">
                            <option value="CHAR">CHAR</option>
                            <option value="NUMERIC">NUMERIC</option>
                            <option value="DECIMAL">DECIMAL</option>
                            <option value="DATE">DATE</option>
                            <option value="DATETIME">DATETIME</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pricing Plan Condition Fields -->
            <div class="dcb-condition-fields dcb-pricing-plan-fields" style="display: none;">
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Plan ID', 'directorist-custom-badges'); ?></label>
                        <input type="number" name="badge[conditions][{{index}}][plan_id]" class="dcb-input" placeholder="<?php echo esc_attr__('897', 'directorist-custom-badges'); ?>" min="0">
                    </div>
                </div>
                <div class="dcb-form-row">
                    <div class="dcb-form-field">
                        <label><?php echo esc_html__('Compare', 'directorist-custom-badges'); ?></label>
                        <select name="badge[conditions][{{index}}][compare]" class="dcb-select">
                            <option value="=">=</option>
                            <option value="!=">!=</option>
                            <option value="IN">IN</option>
                            <option value="NOT IN">NOT IN</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
