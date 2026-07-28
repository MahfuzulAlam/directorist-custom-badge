<?php
/**
 * Admin form page template for Smart Badges (Add/Edit)
 * 
 * @package Directorist - Smart Badges
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get badge ID from URL if editing
$badge_id = isset($_GET['badge_id']) ? sanitize_text_field(wp_unslash($_GET['badge_id'])) : '';
$badge = null;
$is_edit = false;

if (!empty($badge_id)) {
    $badge = Directorist_Smart_Badges_Admin::get_badge($badge_id);
    $is_edit = !empty($badge);
}

$page_title = $is_edit ? __('Edit Badge', 'directorist-smart-badges') : __('Add New Badge', 'directorist-smart-badges');
$form_url = admin_url('admin.php?page=directorist-smart-badges-form');
$list_url = admin_url('admin.php?page=directorist-smart-badges');
$badge_type = $is_edit && isset($badge['badge_type']) ? $badge['badge_type'] : 'custom';
$display_type = $is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label';

?>

<div class="wrap dsb-admin-wrap dsb-admin-wrap--form">

    <header class="dsb-header">
        <div class="dsb-header-brand">
            <a href="<?php echo esc_url($list_url); ?>" class="dsb-back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                <?php echo esc_html__('Badges', 'directorist-smart-badges'); ?>
            </a>
            <h1><?php echo esc_html($page_title); ?></h1>
        </div>
        <div class="dsb-header-actions">
            <label class="dsb-toggle-switch" title="<?php echo esc_attr__('Enable or disable this badge.', 'directorist-smart-badges'); ?>">
                <input type="checkbox" id="dsb-badge-active" name="badge[is_active]" value="1" form="dsb-badge-form" <?php echo ($is_edit && isset($badge['is_active']) && !$badge['is_active']) ? '' : 'checked'; ?>>
                <span class="dsb-toggle-slider"></span>
                <span><?php echo esc_html__('Active', 'directorist-smart-badges'); ?></span>
            </label>
            <button type="submit" form="dsb-badge-form" class="dsb-btn dsb-btn--primary dsb-save-badge"><?php echo esc_html__('Save Badge', 'directorist-smart-badges'); ?></button>
        </div>
    </header>

    <div class="dsb-admin-content">
        <div class="dsb-badge-form-wrapper">
            <div class="dsb-badge-form-container">
                <form id="dsb-badge-form" class="dsb-badge-form" novalidate>
                    <input type="hidden" name="badge[id]" id="dsb-badge-id" value="<?php echo $is_edit && isset($badge['id']) ? esc_attr($badge['id']) : ''; ?>">
                    <input type="hidden" name="badge[order]" id="dsb-badge-order" value="<?php echo $is_edit && isset($badge['order']) ? esc_attr($badge['order']) : ''; ?>">

                    <input type="radio" name="dsb-tab" id="dsb-tab-general" class="dsb-tab-radio" checked>
                    <input type="radio" name="dsb-tab" id="dsb-tab-appearance" class="dsb-tab-radio">
                    <input type="radio" name="dsb-tab" id="dsb-tab-conditions" class="dsb-tab-radio">

                    <div class="dsb-form-layout">

                        <nav class="dsb-tabs" aria-label="<?php echo esc_attr__('Badge settings sections', 'directorist-smart-badges'); ?>">
                            <label class="dsb-tab-label" for="dsb-tab-general">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                                <?php echo esc_html__('General', 'directorist-smart-badges'); ?>
                            </label>
                            <label class="dsb-tab-label" for="dsb-tab-appearance">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22a1 1 0 0 1 0-20 10 9 0 0 1 10 9 5 5 0 0 1-5 5h-2.25a1.75 1.75 0 0 0-1.4 2.8l.3.4a1.75 1.75 0 0 1-1.4 2.8z"/><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/></svg>
                                <?php echo esc_html__('Appearance', 'directorist-smart-badges'); ?>
                            </label>
                            <label class="dsb-tab-label" for="dsb-tab-conditions">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/><path d="M2 14h4M10 8h4M18 16h4"/></svg>
                                <?php echo esc_html__('Conditions', 'directorist-smart-badges'); ?>
                            </label>
                        </nav>

                        <div class="dsb-panels">

                    <section class="dsb-card dsb-tab-panel dsb-tab-panel--general">
                        <h3><?php echo esc_html__('General', 'directorist-smart-badges'); ?></h3>

                        <div class="dsb-form-row">
                            <div class="dsb-form-field">
                                <label for="dsb-badge-type">
                                    <?php echo esc_html__('Badge Type', 'directorist-smart-badges'); ?>
                                </label>
                                <select id="dsb-badge-type" name="badge[badge_type]" class="dsb-select">
                                    <option value="custom" <?php selected($badge_type, 'custom'); ?>>
                                        <?php echo esc_html__('Custom', 'directorist-smart-badges'); ?>
                                    </option>
                                    <option value="tags" <?php selected($badge_type, 'tags'); ?>>
                                        <?php echo esc_html__('Tags', 'directorist-smart-badges'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php echo esc_html__('Choose whether this badge displays custom label text or listing tags.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>
                        
                        <div class="dsb-form-row">
                            <div class="dsb-form-field">
                                <label for="dsb-badge-title">
                                    <?php echo esc_html__('Badge Title', 'directorist-smart-badges'); ?>
                                    <span class="dsb-required">*</span>
                                </label>
                                <input type="text" id="dsb-badge-title" name="badge[badge_title]" class="dsb-input" placeholder="<?php echo esc_attr__('Features', 'directorist-smart-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_title']) ? esc_attr($badge['badge_title']) : ''; ?>" required>
                                <p class="description"><?php echo esc_html__('Internal name for this badge.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row">
                            <div class="dsb-form-field">
                                <label for="dsb-badge-id-field">
                                    <?php echo esc_html__('Badge ID', 'directorist-smart-badges'); ?>
                                    <span class="dsb-required">*</span>
                                </label>
                                <input type="text" id="dsb-badge-id-field" name="badge[badge_id]" class="dsb-input" placeholder="<?php echo esc_attr__('featured-badge', 'directorist-smart-badges'); ?>" pattern="[a-z0-9-]+" value="<?php echo $is_edit && isset($badge['badge_id']) ? esc_attr($badge['badge_id']) : ''; ?>" required>
                                <p class="description"><?php echo esc_html__('Unique identifier (lowercase with hyphens only).', 'directorist-smart-badges'); ?></p>
                                <span class="dsb-field-error"></span>
                            </div>
                        </div>
                    </section>

                    <section class="dsb-card dsb-tab-panel dsb-tab-panel--appearance">
                        <h3><?php echo esc_html__('Appearance', 'directorist-smart-badges'); ?></h3>

                        <div class="dsb-form-row dsb-display-type-row" <?php echo 'tags' === $badge_type ? 'style="display:none;"' : ''; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-display-type">
                                    <?php echo esc_html__('Display Type', 'directorist-smart-badges'); ?>
                                </label>
                                <select id="dsb-display-type" name="badge[display_type]" class="dsb-select">
                                    <option value="label" <?php selected($is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label', 'label'); ?>>
                                        <?php echo esc_html__('Label', 'directorist-smart-badges'); ?>
                                    </option>
                                    <option value="image" <?php selected($is_edit && isset($badge['display_type']) ? $badge['display_type'] : 'label', 'image'); ?>>
                                        <?php echo esc_html__('Image', 'directorist-smart-badges'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php echo esc_html__('Choose whether the badge displays its text label or an uploaded image on the frontend.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <?php
                        $badge_image_id = $is_edit && isset($badge['badge_image_id']) ? absint($badge['badge_image_id']) : 0;
                        $badge_image_url = $is_edit && !empty($badge['badge_image_url']) ? esc_url($badge['badge_image_url']) : '';
                        $badge_image_width = $is_edit && !empty($badge['badge_image_width']) ? absint($badge['badge_image_width']) : 30;
                        $badge_label_font_size = $is_edit && !empty($badge['badge_label_font_size']) ? absint($badge['badge_label_font_size']) : 14;
                        ?>
                        <div class="dsb-form-row dsb-badge-image-row" <?php echo 'tags' !== $badge_type && 'image' === $display_type ? '' : 'style="display:none;"'; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-image-url">
                                    <?php echo esc_html__('Badge Image', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="hidden" id="dsb-badge-image-id" name="badge[badge_image_id]" value="<?php echo esc_attr($badge_image_id); ?>">
                                <input type="hidden" id="dsb-badge-image-url" name="badge[badge_image_url]" value="<?php echo esc_url($badge_image_url); ?>">
                                <div class="dsb-image-upload-control">
                                    <button type="button" class="dsb-btn dsb-upload-badge-image"><?php echo esc_html__('Select Image', 'directorist-smart-badges'); ?></button>
                                    <button type="button" class="dsb-btn dsb-remove-badge-image" <?php echo $badge_image_url ? '' : 'style="display:none;"'; ?>><?php echo esc_html__('Remove Image', 'directorist-smart-badges'); ?></button>
                                    <span class="dsb-badge-image-preview">
                                        <?php if ($badge_image_url) : ?>
                                            <img src="<?php echo esc_url($badge_image_url); ?>" alt="">
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="description"><?php echo esc_html__('Upload or select the image to display instead of the badge label.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-badge-image-row" <?php echo 'tags' !== $badge_type && 'image' === $display_type ? '' : 'style="display:none;"'; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-image-width">
                                    <?php echo esc_html__('Badge Image Width', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="number" id="dsb-badge-image-width" name="badge[badge_image_width]" class="dsb-input" min="1" value="<?php echo esc_attr($badge_image_width); ?>">
                                <p class="description"><?php echo esc_html__('Width of the uploaded badge image in pixels.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-label-display-row" <?php echo ('tags' === $badge_type || 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-label">
                                    <?php echo esc_html__('Badge Label', 'directorist-smart-badges'); ?>
                                    <span class="dsb-required">*</span>
                                </label>
                                <input type="text" id="dsb-badge-label" name="badge[badge_label]" class="dsb-input" placeholder="<?php echo esc_attr__('Featured', 'directorist-smart-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_label']) ? esc_attr($badge['badge_label']) : ''; ?>" <?php echo ('custom' === $badge_type && 'label' === $display_type) ? 'required' : ''; ?>>
                                <p class="description"><?php echo esc_html__('Display text for the badge.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-badge-label-font-size-row" <?php echo ('tags' === $badge_type || 'label' === $display_type) ? '' : 'style="display:none;"'; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-label-font-size">
                                    <?php echo esc_html__('Badge Label Font Size', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="number" id="dsb-badge-label-font-size" name="badge[badge_label_font_size]" class="dsb-input" min="1" value="<?php echo esc_attr($badge_label_font_size); ?>">
                                <p class="description"><?php echo esc_html__('Font size for badge label or tag text in pixels.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-badge-icon-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-icon">
                                    <?php echo esc_html__('Badge Icon', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="text" id="dsb-badge-icon" name="badge[badge_icon]" class="dsb-input" placeholder="<?php echo esc_attr__('las la-check-circle', 'directorist-smart-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_icon']) ? esc_attr($badge['badge_icon']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('Icon class name (e.g., las la-check-circle).', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row">
                            <div class="dsb-form-field">
                                <label for="dsb-badge-class">
                                    <?php echo esc_html__('Badge Class', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="text" id="dsb-badge-class" name="badge[badge_class]" class="dsb-input" placeholder="<?php echo esc_attr__('features-custom-badge', 'directorist-smart-badges'); ?>" value="<?php echo $is_edit && isset($badge['badge_class']) ? esc_attr($badge['badge_class']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('CSS class for styling.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-maximum-tags-row" <?php echo ($is_edit && isset($badge['badge_type']) && 'tags' === $badge['badge_type']) ? '' : 'style="display:none;"'; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-maximum-tags">
                                    <?php echo esc_html__('Maximum Tags', 'directorist-smart-badges'); ?>
                                </label>
                                <input type="number" id="dsb-maximum-tags" name="badge[maximum_tags]" class="dsb-input" placeholder="<?php echo esc_attr__('3', 'directorist-smart-badges'); ?>" min="0" value="<?php echo $is_edit && isset($badge['maximum_tags']) ? esc_attr($badge['maximum_tags']) : ''; ?>">
                                <p class="description"><?php echo esc_html__('Maximum number of listing tags to show on the frontend. Leave empty or use 0 to show all tags.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-badge-color-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-color">
                                    <?php echo esc_html__('Badge Background Color', 'directorist-smart-badges'); ?>
                                </label>
                                <div class="dsb-color-field">
                                    <input type="color" class="dsb-color-swatch" value="<?php echo $is_edit && !empty($badge['badge_color']) ? esc_attr($badge['badge_color']) : '#ffffff'; ?>" aria-label="<?php echo esc_attr__('Pick a background color', 'directorist-smart-badges'); ?>">
                                    <input type="text" id="dsb-badge-color" name="badge[badge_color]" class="dsb-color-input dsb-input" placeholder="#7a45e5" pattern="#[0-9a-fA-F]{6}" value="<?php echo $is_edit && isset($badge['badge_color']) ? esc_attr($badge['badge_color']) : ''; ?>">
                                    <button type="button" class="dsb-btn dsb-color-clear"><?php echo esc_html__('Clear', 'directorist-smart-badges'); ?></button>
                                </div>
                                <p class="description"><?php echo esc_html__('Choose a background color for the badge.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-form-row dsb-badge-text-color-row" <?php echo ('tags' !== $badge_type && 'image' === $display_type) ? 'style="display:none;"' : ''; ?>>
                            <div class="dsb-form-field">
                                <label for="dsb-badge-text-color">
                                    <?php echo esc_html__('Badge Text Color', 'directorist-smart-badges'); ?>
                                </label>
                                <div class="dsb-color-field">
                                    <input type="color" class="dsb-color-swatch" value="<?php echo $is_edit && !empty($badge['badge_text_color']) ? esc_attr($badge['badge_text_color']) : '#ffffff'; ?>" aria-label="<?php echo esc_attr__('Pick a text color', 'directorist-smart-badges'); ?>">
                                    <input type="text" id="dsb-badge-text-color" name="badge[badge_text_color]" class="dsb-color-input dsb-input" placeholder="#ffffff" pattern="#[0-9a-fA-F]{6}" value="<?php echo $is_edit && isset($badge['badge_text_color']) ? esc_attr($badge['badge_text_color']) : ''; ?>">
                                    <button type="button" class="dsb-btn dsb-color-clear"><?php echo esc_html__('Clear', 'directorist-smart-badges'); ?></button>
                                </div>
                                <p class="description"><?php echo esc_html__('Choose a text color for the badge.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>
                    </section>

                    <section class="dsb-card dsb-tab-panel dsb-tab-panel--conditions">
                        <h3><?php echo esc_html__('Conditions', 'directorist-smart-badges'); ?></h3>
                        
                        <div class="dsb-form-row">
                            <div class="dsb-form-field">
                                <label for="dsb-condition-relation">
                                    <?php echo esc_html__('Condition Relation', 'directorist-smart-badges'); ?>
                                </label>
                                <select id="dsb-condition-relation" name="badge[condition_relation]" class="dsb-select">
                                    <option value="AND" <?php echo ($is_edit && isset($badge['condition_relation']) && $badge['condition_relation'] === 'OR') ? '' : 'selected'; ?>><?php echo esc_html__('AND', 'directorist-smart-badges'); ?></option>
                                    <option value="OR" <?php echo ($is_edit && isset($badge['condition_relation']) && $badge['condition_relation'] === 'OR') ? 'selected' : ''; ?>><?php echo esc_html__('OR', 'directorist-smart-badges'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How conditions should be evaluated.', 'directorist-smart-badges'); ?></p>
                            </div>
                        </div>

                        <div class="dsb-conditions-repeater">
                            <div class="dsb-conditions-list" id="dsb-conditions-list">
                                <!-- Conditions will be added here dynamically -->
                                <?php if ($is_edit && !empty($badge['conditions']) && is_array($badge['conditions'])): ?>
                                    <?php foreach ($badge['conditions'] as $idx => $condition): ?>
                                        <?php
                                        // Include condition item template
                                        $index = $idx;
                                        include DIRECTORIST_SMART_BADGE_DIR . 'templates/condition-item.php';
                                        ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="dsb-btn dsb-add-condition">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                <?php echo esc_html__('Add Condition', 'directorist-smart-badges'); ?>
                            </button>
                        </div>
                    </section>

                        </div><!-- /.dsb-panels -->
                    </div><!-- /.dsb-form-layout -->
                </form>

                <datalist id="dsb-meta-keys">
                    <?php foreach ( Directorist_Smart_Badges_Admin::get_listing_meta_keys() as $dsb_meta_key ) : ?>
                        <option value="<?php echo esc_attr( $dsb_meta_key ); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>
    </div>
</div>

<!-- Condition Template (Hidden) -->
<script type="text/template" id="dsb-condition-template">
<?php
// Generate template for JavaScript using placeholders
$index = '{{index}}';
$condition = array(); // Empty for template
ob_start();
include DIRECTORIST_SMART_BADGE_DIR . 'templates/condition-item.php';
$template_output = ob_get_clean();
echo $template_output;
?>
</script>

