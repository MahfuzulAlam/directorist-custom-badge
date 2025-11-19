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
                                <input type="text" id="dcb-badge-icon" name="badge[badge_icon]" class="dcb-input" placeholder="<?php echo esc_attr__('las la-check-circle', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_icon']) ? esc_attr($badge['badge_icon']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('Icon class name (e.g., las la-check-circle).', 'directorist-custom-badges'); ?></p>
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

                        <div class="dcb-form-row">
                            <div class="dcb-form-field">
                                <label for="dcb-badge-color">
                                    <?php echo esc_html__('Badge Color', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-color" name="badge[badge_color]" class="dcb-color-picker dcb-input" value="<?php echo $is_edit && isset($badge['badge_color']) ? esc_attr($badge['badge_color']) : ''; ?>" data-default-color="">
                                <p class="description"><?php echo esc_html__('Choose a color for the badge background or text.', 'directorist-custom-badges'); ?></p>
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
                                        <?php
                                        // Include condition item template
                                        $index = $idx;
                                        include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-item.php';
                                        ?>
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
<?php
// Generate template for JavaScript using placeholders
$index = '{{index}}';
$condition = array(); // Empty for template
ob_start();
include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-item.php';
$template_output = ob_get_clean();
echo $template_output;
?>
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

