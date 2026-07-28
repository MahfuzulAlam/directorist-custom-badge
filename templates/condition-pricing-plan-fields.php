<?php
/**
 * Pricing Plan Condition Fields Template
 * 
 * @package Directorist - Smart Badges
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
?>

<!-- Pricing Plan Condition Fields -->
<div class="dsb-condition-fields dsb-pricing-plan-fields"<?php echo (!$is_template && isset($condition['type']) && $condition['type'] === 'pricing_plan') ? '' : ' style="display: none;"'; ?>>
    <div class="dsb-form-row">
        <div class="dsb-form-field">
            <label<?php echo (!$is_template) ? ' for="dsb-plan-status-condition-' . esc_attr($index) . '"' : ''; ?>>
                <?php echo esc_html__('Plan Status Condition', 'directorist-smart-badges'); ?>
            </label>
            <select<?php echo (!$is_template) ? ' id="dsb-plan-status-condition-' . esc_attr($index) . '"' : ''; ?> name="badge[conditions][<?php echo esc_attr($index); ?>][plan_status_condition]" class="dsb-select dsb-plan-status-condition">
                <option value="user_active_plan" <?php echo (!$is_template && isset($condition['plan_status_condition']) && $condition['plan_status_condition'] === 'user_active_plan') ? 'selected' : ''; ?>><?php echo esc_html__('User has an active subscription', 'directorist-smart-badges'); ?></option>
                <option value="listing_has_plan" <?php echo (!$is_template && isset($condition['plan_status_condition']) && $condition['plan_status_condition'] === 'listing_has_plan') ? 'selected' : ''; ?>><?php echo esc_html__('Listing is assigned to a plan', 'directorist-smart-badges'); ?></option>
            </select>
            <p class="description">
                <?php echo esc_html__('Choose how plan status should be checked for this badge. "User has an active subscription" applies the badge only if the listing owner currently has an active pricing plan. "Listing is assigned to a plan" applies the badge only if this specific listing is linked to a pricing plan.', 'directorist-smart-badges'); ?>
            </p>
        </div>
    </div>
    <div class="dsb-form-row dsb-plan-id-row">
        <div class="dsb-form-field">
            <label><?php echo esc_html__('Plan ID', 'directorist-smart-badges'); ?></label>
            <input type="number" name="badge[conditions][<?php echo esc_attr($index); ?>][plan_id]" class="dsb-input" placeholder="<?php echo esc_attr__('897', 'directorist-smart-badges'); ?>" min="0" value="<?php echo (!$is_template && isset($condition['plan_id'])) ? esc_attr($condition['plan_id']) : ''; ?>">
            <p class="description"><?php echo esc_html__('Leave empty when using Plan Status Condition.', 'directorist-smart-badges'); ?></p>
        </div>
    </div>
    <div class="dsb-form-row dsb-plan-compare-row">
        <div class="dsb-form-field">
            <label><?php echo esc_html__('Compare', 'directorist-smart-badges'); ?></label>
            <select name="badge[conditions][<?php echo esc_attr($index); ?>][compare]" class="dsb-select">
                <option value="=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '=') ? 'selected' : ''; ?>>=</option>
                <option value="!=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '!=') ? 'selected' : ''; ?>>!=</option>
                <option value="IN" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'IN') ? 'selected' : ''; ?>>IN</option>
                <option value="NOT IN" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'NOT IN') ? 'selected' : ''; ?>>NOT IN</option>
            </select>
        </div>
    </div>
</div>

