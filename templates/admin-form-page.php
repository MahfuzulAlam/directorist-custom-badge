<?php
/**
 * Admin form page template for Custom Badges (Add/Edit)
 * 
 * @package Directorist - Custom Badges
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get badge ID from URL if editing
$badge_id = isset($_GET['badge_id']) ? sanitize_text_field($_GET['badge_id']) : '';
$badge = null;
$is_edit = false;

if (!empty($badge_id)) {
    $badge = Directorist_Custom_Badges_Admin::get_badge($badge_id);
    $is_edit = !empty($badge);
}

$page_title = $is_edit ? __('Edit Badge', 'directorist-custom-badges') : __('Add New Badge', 'directorist-custom-badges');
$form_url = admin_url('admin.php?page=directorist-custom-badges-form');
$list_url = admin_url('admin.php?page=directorist-custom-badges');

?>

<div class="wrap dcb-admin-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    <a href="<?php echo esc_url($list_url); ?>" class="page-title-action"><?php echo esc_html__('← Back to Badges', 'directorist-custom-badges'); ?></a>
    
    <div class="dcb-notices"></div>

    <div class="dcb-admin-content">
        <div class="dcb-badge-form-wrapper">
            <div class="dcb-badge-form-container">
                <form id="dcb-badge-form" class="dcb-badge-form">
                    <input type="hidden" name="badge[id]" id="dcb-badge-id" value="<?php echo $is_edit && isset($badge['id']) ? esc_attr($badge['id']) : ''; ?>">
                    <input type="hidden" name="badge[order]" id="dcb-badge-order" value="<?php echo $is_edit && isset($badge['order']) ? esc_attr($badge['order']) : ''; ?>">

                    <div class="dcb-form-section">
                        <h3><?php echo esc_html__('Basic Information', 'directorist-custom-badges'); ?></h3>
                        
                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-title">
                                    <?php echo esc_html__('Badge Title', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-title" name="badge[badge_title]" class="dcb-input" placeholder="<?php echo esc_attr__('Features', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_title']) ? esc_attr($badge['badge_title']) : ''; ?>" required>
                                <p class="description"><?php echo esc_html__('Internal name for this badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-icon">
                                    <?php echo esc_html__('Badge Icon', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-icon" name="badge[badge_icon]" class="dcb-input" placeholder="<?php echo esc_attr__('uil uil-text-fields', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_icon']) ? esc_attr($badge['badge_icon']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('Icon class name (e.g., uil uil-text-fields).', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-id-field">
                                    <?php echo esc_html__('Badge ID', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-id-field" name="badge[badge_id]" class="dcb-input" placeholder="<?php echo esc_attr__('featured-badge', 'directorist-custom-badges'); ?>" pattern="[a-z0-9-]+" value="<?php echo $is_edit && isset($badge['badge_id']) ? esc_attr($badge['badge_id']) : ''; ?>" required>
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
                                <input type="text" id="dcb-badge-label" name="badge[badge_label]" class="dcb-input" placeholder="<?php echo esc_attr__('Featured', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_label']) ? esc_attr($badge['badge_label']) : ''; ?>" required>
                                <p class="description"><?php echo esc_html__('Display text for the badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-class">
                                    <?php echo esc_html__('Badge Class', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-class" name="badge[badge_class]" class="dcb-input" placeholder="<?php echo esc_attr__('features-custom-badge', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_class']) ? esc_attr($badge['badge_class']) : ''; ?>">
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
                                    <option value="AND" <?php echo ($is_edit && isset($badge['condition_relation']) && $badge['condition_relation'] === 'OR') ? '' : 'selected'; ?>><?php echo esc_html__('AND', 'directorist-custom-badges'); ?></option>
                                    <option value="OR" <?php echo ($is_edit && isset($badge['condition_relation']) && $badge['condition_relation'] === 'OR') ? 'selected' : ''; ?>><?php echo esc_html__('OR', 'directorist-custom-badges'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How conditions should be evaluated.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-conditions-repeater">
                            <div class="dcb-conditions-list" id="dcb-conditions-list">
                                <!-- Conditions will be added here dynamically -->
                                <?php if ($is_edit && !empty($badge['conditions']) && is_array($badge['conditions'])): ?>
                                    <?php foreach ($badge['conditions'] as $idx => $condition): ?>
                                        <div class="dcb-condition-item" data-condition-index="<?php echo esc_attr($idx); ?>">
                                            <div class="dcb-condition-header">
                                                <span class="dcb-condition-title"><?php echo esc_html__('Condition', 'directorist-custom-badges'); ?> #<?php echo esc_html($idx + 1); ?></span>
                                                <button type="button" class="button-link dcb-remove-condition"><?php echo esc_html__('Remove', 'directorist-custom-badges'); ?></button>
                                            </div>
                                            <div class="dcb-condition-body">
                                                <div class="dcb-form-row">
                                                    <div class="dcb-form-field">
                                                        <label><?php echo esc_html__('Condition Type', 'directorist-custom-badges'); ?></label>
                                                        <select name="badge[conditions][<?php echo esc_attr($idx); ?>][type]" class="dcb-condition-type dcb-select">
                                                            <option value="meta" <?php selected($condition['type'] ?? 'meta', 'meta'); ?>><?php echo esc_html__('Meta', 'directorist-custom-badges'); ?></option>
                                                            <option value="pricing_plan" <?php selected($condition['type'] ?? '', 'pricing_plan'); ?>><?php echo esc_html__('Pricing Plan', 'directorist-custom-badges'); ?></option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Meta Condition Fields -->
                                                <div class="dcb-condition-fields dcb-meta-fields" style="<?php echo (isset($condition['type']) && $condition['type'] === 'pricing_plan') ? 'display: none;' : ''; ?>">
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Meta Key', 'directorist-custom-badges'); ?></label>
                                                            <input type="text" name="badge[conditions][<?php echo esc_attr($idx); ?>][meta_key]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_key', 'directorist-custom-badges'); ?>" value="<?php echo isset($condition['meta_key']) ? esc_attr($condition['meta_key']) : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Meta Value', 'directorist-custom-badges'); ?></label>
                                                            <input type="text" name="badge[conditions][<?php echo esc_attr($idx); ?>][meta_value]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_value', 'directorist-custom-badges'); ?>" value="<?php echo isset($condition['meta_value']) ? esc_attr($condition['meta_value']) : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Compare', 'directorist-custom-badges'); ?></label>
                                                            <select name="badge[conditions][<?php echo esc_attr($idx); ?>][compare]" class="dcb-select">
                                                                <option value="=" <?php selected($condition['compare'] ?? '=', '='); ?>>=</option>
                                                                <option value="!=" <?php selected($condition['compare'] ?? '', '!='); ?>>!=</option>
                                                                <option value=">" <?php selected($condition['compare'] ?? '', '>'); ?>>&gt;</option>
                                                                <option value=">=" <?php selected($condition['compare'] ?? '', '>='); ?>>&gt;=</option>
                                                                <option value="<" <?php selected($condition['compare'] ?? '', '<'); ?>>&lt;</option>
                                                                <option value="<=" <?php selected($condition['compare'] ?? '', '<='); ?>>&lt;=</option>
                                                                <option value="LIKE" <?php selected($condition['compare'] ?? '', 'LIKE'); ?>>LIKE</option>
                                                                <option value="NOT LIKE" <?php selected($condition['compare'] ?? '', 'NOT LIKE'); ?>>NOT LIKE</option>
                                                                <option value="IN" <?php selected($condition['compare'] ?? '', 'IN'); ?>>IN</option>
                                                                <option value="NOT IN" <?php selected($condition['compare'] ?? '', 'NOT IN'); ?>>NOT IN</option>
                                                                <option value="EXISTS" <?php selected($condition['compare'] ?? '', 'EXISTS'); ?>>EXISTS</option>
                                                                <option value="NOT EXISTS" <?php selected($condition['compare'] ?? '', 'NOT EXISTS'); ?>>NOT EXISTS</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Type', 'directorist-custom-badges'); ?></label>
                                                            <select name="badge[conditions][<?php echo esc_attr($idx); ?>][type_cast]" class="dcb-select">
                                                                <option value="CHAR" <?php selected($condition['type_cast'] ?? 'CHAR', 'CHAR'); ?>>CHAR</option>
                                                                <option value="NUMERIC" <?php selected($condition['type_cast'] ?? '', 'NUMERIC'); ?>>NUMERIC</option>
                                                                <option value="DECIMAL" <?php selected($condition['type_cast'] ?? '', 'DECIMAL'); ?>>DECIMAL</option>
                                                                <option value="DATE" <?php selected($condition['type_cast'] ?? '', 'DATE'); ?>>DATE</option>
                                                                <option value="DATETIME" <?php selected($condition['type_cast'] ?? '', 'DATETIME'); ?>>DATETIME</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pricing Plan Condition Fields -->
                                                <div class="dcb-condition-fields dcb-pricing-plan-fields" style="<?php echo (isset($condition['type']) && $condition['type'] === 'pricing_plan') ? '' : 'display: none;'; ?>">
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Plan ID', 'directorist-custom-badges'); ?></label>
                                                            <input type="number" name="badge[conditions][<?php echo esc_attr($idx); ?>][plan_id]" class="dcb-input" placeholder="<?php echo esc_attr__('897', 'directorist-custom-badges'); ?>" min="0" value="<?php echo isset($condition['plan_id']) ? esc_attr($condition['plan_id']) : ''; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="dcb-form-row">
                                                        <div class="dcb-form-field">
                                                            <label><?php echo esc_html__('Compare', 'directorist-custom-badges'); ?></label>
                                                            <select name="badge[conditions][<?php echo esc_attr($idx); ?>][compare]" class="dcb-select">
                                                                <option value="=" <?php selected($condition['compare'] ?? '=', '='); ?>>=</option>
                                                                <option value="!=" <?php selected($condition['compare'] ?? '', '!='); ?>>!=</option>
                                                                <option value="IN" <?php selected($condition['compare'] ?? '', 'IN'); ?>>IN</option>
                                                                <option value="NOT IN" <?php selected($condition['compare'] ?? '', 'NOT IN'); ?>>NOT IN</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button dcb-add-condition"><?php echo esc_html__('Add Condition', 'directorist-custom-badges'); ?></button>
                        </div>
                    </div>

                    <div class="dcb-form-section">
                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label class="dcb-checkbox-label">
                                    <input type="checkbox" id="dcb-badge-active" name="badge[is_active]" value="1" <?php echo ($is_edit && isset($badge['is_active']) && !$badge['is_active']) ? '' : 'checked'; ?>>
                                    <?php echo esc_html__('Active', 'directorist-custom-badges'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('Enable or disable this badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="dcb-form-actions">
                        <button type="submit" class="button button-primary dcb-save-badge"><?php echo esc_html__('Save Badge', 'directorist-custom-badges'); ?></button>
                        <a href="<?php echo esc_url($list_url); ?>" class="button"><?php echo esc_html__('Cancel', 'directorist-custom-badges'); ?></a>
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

<script type="text/javascript">
// Initialize form with badge data if editing and set condition index
jQuery(document).ready(function($) {
    <?php if ($is_edit && $badge): ?>
    // Set condition index to continue from existing conditions
    if (typeof DCBAdmin !== 'undefined') {
        var conditionCount = <?php echo !empty($badge['conditions']) ? count($badge['conditions']) : 0; ?>;
        DCBAdmin.conditionIndex = conditionCount;
    }
    <?php endif; ?>
});
</script>

