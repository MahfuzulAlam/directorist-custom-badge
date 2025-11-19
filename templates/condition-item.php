<?php
/**
 * Condition Item Template
 * 
 * @package Directorist - Custom Badges
 * 
 * @var int|string $index Condition index (can be numeric or placeholder like {{index}})
 * @var array $condition Condition data (optional, for PHP rendering)
 * @var bool $is_template Whether this is for JavaScript template (uses placeholders)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$is_template = (strpos($index, '{{') !== false);
$condition = isset($condition) ? $condition : array();
$condition_type = (!$is_template && isset($condition['type'])) ? $condition['type'] : 'meta';
$condition_number = (!$is_template && is_numeric($index)) ? intval($index) + 1 : '{{index}}';
?>

<div class="dcb-condition-item" data-condition-index="<?php echo esc_attr($index); ?>">
    <div class="dcb-condition-header">
        <span class="dcb-condition-title"><?php echo esc_html__('Condition', 'directorist-custom-badges'); ?> #<?php echo $is_template ? '{{index}}' : esc_html($condition_number); ?></span>
        <button type="button" class="button-link dcb-remove-condition"><?php echo esc_html__('Remove', 'directorist-custom-badges'); ?></button>
    </div>
    <div class="dcb-condition-body">
        <div class="dcb-form-row">
            <div class="dcb-form-field">
                <label><?php echo esc_html__('Condition Type', 'directorist-custom-badges'); ?></label>
                <select name="badge[conditions][<?php echo esc_attr($index); ?>][type]" class="dcb-condition-type dcb-select">
                    <option value="meta" <?php echo (!$is_template && $condition_type === 'meta') ? 'selected' : ''; ?>><?php echo esc_html__('Meta', 'directorist-custom-badges'); ?></option>
                    <option value="pricing_plan" <?php echo (!$is_template && $condition_type === 'pricing_plan') ? 'selected' : ''; ?>><?php echo esc_html__('Pricing Plan', 'directorist-custom-badges'); ?></option>
                </select>
            </div>
        </div>

        <?php
        // Include meta condition fields
        include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-meta-fields.php';
        
        // Include pricing plan condition fields
        include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-pricing-plan-fields.php';
        ?>
    </div>
</div>

