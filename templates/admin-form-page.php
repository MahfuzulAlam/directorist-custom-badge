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
$badge_type = $is_edit && isset($badge['badge_type']) ? $badge['badge_type'] : 'custom';
$display_type = $is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label';

?>

<div class="wrap dcb-admin-wrap dcb-admin-wrap--form">
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
                                <label for="dcb-badge-type">
                                    <?php echo esc_html__('Badge Type', 'directorist-custom-badges'); ?>
                                </label>
                                <select id="dcb-badge-type" name="badge[badge_type]" class="dcb-select">
                                    <option value="custom" <?php selected($badge_type, 'custom'); ?>>
                                        <?php echo esc_html__('Custom', 'directorist-custom-badges'); ?>
                                    </option>
                                    <option value="tags" <?php selected($badge_type, 'tags'); ?>>
                                        <?php echo esc_html__('Tags', 'directorist-custom-badges'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php echo esc_html__('Choose whether this badge displays custom label text or listing tags.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>
                        
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
                                <label for="dcb-badge-id-field">
                                    <?php echo esc_html__('Badge ID', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-id-field" name="badge[badge_id]" class="dcb-input" placeholder="<?php echo esc_attr__('featured-badge', 'directorist-custom-badges'); ?>" pattern="[a-z0-9-]+" value="<?php echo $is_edit && isset($badge['badge_id']) ? esc_attr($badge['badge_id']) : ''; ?>" required>
                                <p class="description"><?php echo esc_html__('Unique identifier (lowercase with hyphens only).', 'directorist-custom-badges'); ?></p>
                                <span class="dcb-field-error"></span>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-display-type-row" <?php echo 'tags' === $badge_type ? 'style="display:none;"' : ''; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-display-type">
                                    <?php echo esc_html__('Display Type', 'directorist-custom-badges'); ?>
                                </label>
                                <select id="dcb-display-type" name="badge[display_type]" class="dcb-select">
                                    <option value="label" <?php selected($is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label', 'label'); ?>>
                                        <?php echo esc_html__('Label', 'directorist-custom-badges'); ?>
                                    </option>
                                    <option value="image" <?php selected($is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label', 'image'); ?>>
                                        <?php echo esc_html__('Image', 'directorist-custom-badges'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php echo esc_html__('Choose whether the badge displays its text label or an uploaded image on the frontend.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <?php
                        $badge_image_id = $is_edit && isset($badge['badge_image_id']) ? absint($badge['badge_image_id']) : 0;
                        $badge_image_url = $is_edit && !empty($badge['badge_image_url']) ? esc_url($badge['badge_image_url']) : '';
                        $badge_image_width = $is_edit && !empty($badge['badge_image_width']) ? absint($badge['badge_image_width']) : 30;
                        $badge_label_font_size = $is_edit && !empty($badge['badge_label_font_size']) ? absint($badge['badge_label_font_size']) : 14;
                        ?>
                        <div class="dcb-form-row dcb-badge-image-row" <?php echo 'tags' !== $badge_type && 'image' === $display_type ? '' : 'style="display:none;"'; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-image-url">
                                    <?php echo esc_html__('Badge Image', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="hidden" id="dcb-badge-image-id" name="badge[badge_image_id]" value="<?php echo esc_attr($badge_image_id); ?>">
                                <input type="hidden" id="dcb-badge-image-url" name="badge[badge_image_url]" value="<?php echo esc_url($badge_image_url); ?>">
                                <div class="dcb-image-upload-control">
                                    <button type="button" class="button dcb-upload-badge-image"><?php echo esc_html__('Select Image', 'directorist-custom-badges'); ?></button>
                                    <button type="button" class="button dcb-remove-badge-image" <?php echo $badge_image_url ? '' : 'style="display:none;"'; ?>><?php echo esc_html__('Remove Image', 'directorist-custom-badges'); ?></button>
                                    <span class="dcb-badge-image-preview">
                                        <?php if ($badge_image_url) : ?>
                                            <img src="<?php echo esc_url($badge_image_url); ?>" alt="">
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="description"><?php echo esc_html__('Upload or select the image to display instead of the badge label.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-badge-image-row" <?php echo 'tags' !== $badge_type && 'image' === $display_type ? '' : 'style="display:none;"'; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-image-width">
                                    <?php echo esc_html__('Badge Image Width', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="number" id="dcb-badge-image-width" name="badge[badge_image_width]" class="dcb-input" min="1" value="<?php echo esc_attr($badge_image_width); ?>">
                                <p class="description"><?php echo esc_html__('Width of the uploaded badge image in pixels.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-label-display-row" <?php echo ('tags' === $badge_type || 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-label">
                                    <?php echo esc_html__('Badge Label', 'directorist-custom-badges'); ?>
                                    <span class="dcb-required">*</span>
                                </label>
                                <input type="text" id="dcb-badge-label" name="badge[badge_label]" class="dcb-input" placeholder="<?php echo esc_attr__('Featured', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_label']) ? esc_attr($badge['badge_label']) : ''; ?>" <?php echo ('custom' === $badge_type && 'label' === $display_type) ? 'required' : ''; ?>>
                                <p class="description"><?php echo esc_html__('Display text for the badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-badge-label-font-size-row" <?php echo ('tags' === $badge_type || 'label' === $display_type) ? '' : 'style="display:none;"'; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-label-font-size">
                                    <?php echo esc_html__('Badge Label Font Size', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="number" id="dcb-badge-label-font-size" name="badge[badge_label_font_size]" class="dcb-input" min="1" value="<?php echo esc_attr($badge_label_font_size); ?>">
                                <p class="description"><?php echo esc_html__('Font size for badge label or tag text in pixels.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-badge-icon-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
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
                                <label for="dcb-badge-class">
                                    <?php echo esc_html__('Badge Class', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-class" name="badge[badge_class]" class="dcb-input" placeholder="<?php echo esc_attr__('features-custom-badge', 'directorist-custom-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_class']) ? esc_attr($badge['badge_class']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('CSS class for styling.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-maximum-tags-row" <?php echo ($is_edit && isset($badge['badge_type']) && 'tags' === $badge['badge_type']) ? '' : 'style="display:none;"'; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-maximum-tags">
                                    <?php echo esc_html__('Maximum Tags', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="number" id="dcb-maximum-tags" name="badge[maximum_tags]" class="dcb-input" placeholder="<?php echo esc_attr__('3', 'directorist-custom-badges'); ?>" min="0" value="<?php echo $is_edit && isset($badge['maximum_tags']) ? esc_attr($badge['maximum_tags']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('Maximum number of listing tags to show on the frontend. Leave empty or use 0 to show all tags.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-badge-color-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-color">
                                    <?php echo esc_html__('Badge Background Color', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-color" name="badge[badge_color]" class="dcb-color-picker dcb-input" value="<?php echo $is_edit && isset($badge['badge_color']) ? esc_attr($badge['badge_color']) : ''; ?>" data-default-color="">
                                <p class="description"><?php echo esc_html__('Choose a background color for the badge.', 'directorist-custom-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dcb-form-row dcb-badge-text-color-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dcb-form-field">
                                <label for="dcb-badge-text-color">
                                    <?php echo esc_html__('Badge Text Color', 'directorist-custom-badges'); ?>
                                </label>
                                <input type="text" id="dcb-badge-text-color" name="badge[badge_text_color]" class="dcb-color-picker dcb-input" value="<?php echo $is_edit && isset($badge['badge_text_color']) ? esc_attr($badge['badge_text_color']) : ''; ?>" data-default-color="">
                                <p class="description"><?php echo esc_html__('Choose a text color for the badge.', 'directorist-custom-badges'); ?></p>
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
