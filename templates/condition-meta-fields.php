<?php
/**
 * Meta Condition Fields Template
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
?>

<!-- Meta Condition Fields -->
<div class="dcb-condition-fields dcb-meta-fields"<?php echo (!$is_template && isset($condition['type']) && $condition['type'] === 'pricing_plan') ? ' style="display: none;"' : ''; ?>>
    <div class="dcb-form-row">
        <div class="dcb-form-field">
            <label><?php echo esc_html__('Meta Key', 'directorist-custom-badges'); ?></label>
            <input type="text" name="badge[conditions][<?php echo esc_attr($index); ?>][meta_key]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_key', 'directorist-custom-badges'); ?>" value="<?php echo (!$is_template && isset($condition['meta_key'])) ? esc_attr($condition['meta_key']) : ''; ?>">
        </div>
    </div>
    <div class="dcb-form-row">
        <div class="dcb-form-field">
            <label><?php echo esc_html__('Meta Value', 'directorist-custom-badges'); ?></label>
            <input type="text" name="badge[conditions][<?php echo esc_attr($index); ?>][meta_value]" class="dcb-input" placeholder="<?php echo esc_attr__('meta_value', 'directorist-custom-badges'); ?>" value="<?php echo (!$is_template && isset($condition['meta_value'])) ? esc_attr($condition['meta_value']) : ''; ?>">
        </div>
    </div>
    <div class="dcb-form-row">
        <div class="dcb-form-field">
            <label><?php echo esc_html__('Compare', 'directorist-custom-badges'); ?></label>
            <select name="badge[conditions][<?php echo esc_attr($index); ?>][compare]" class="dcb-select">
                <option value="=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '=') ? 'selected' : ''; ?>>=</option>
                <option value="!=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '!=') ? 'selected' : ''; ?>>!=</option>
                <option value=">" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '>') ? 'selected' : ''; ?>>&gt;</option>
                <option value=">=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '>=') ? 'selected' : ''; ?>>&gt;=</option>
                <option value="<" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '<') ? 'selected' : ''; ?>>&lt;</option>
                <option value="<=" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === '<=') ? 'selected' : ''; ?>>&lt;=</option>
                <option value="LIKE" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'LIKE') ? 'selected' : ''; ?>>LIKE</option>
                <option value="NOT LIKE" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'NOT LIKE') ? 'selected' : ''; ?>>NOT LIKE</option>
                <option value="IN" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'IN') ? 'selected' : ''; ?>>IN</option>
                <option value="NOT IN" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'NOT IN') ? 'selected' : ''; ?>>NOT IN</option>
                <option value="EXISTS" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'EXISTS') ? 'selected' : ''; ?>>EXISTS</option>
                <option value="NOT EXISTS" <?php echo (!$is_template && isset($condition['compare']) && $condition['compare'] === 'NOT EXISTS') ? 'selected' : ''; ?>>NOT EXISTS</option>
            </select>
        </div>
    </div>
    <div class="dcb-form-row">
        <div class="dcb-form-field">
            <label><?php echo esc_html__('Type', 'directorist-custom-badges'); ?></label>
            <select name="badge[conditions][<?php echo esc_attr($index); ?>][type_cast]" class="dcb-select">
                <option value="CHAR" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'CHAR') ? 'selected' : ''; ?>>CHAR</option>
                <option value="NUMERIC" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'NUMERIC') ? 'selected' : ''; ?>>NUMERIC</option>
                <option value="DECIMAL" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'DECIMAL') ? 'selected' : ''; ?>>DECIMAL</option>
                <option value="DATE" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'DATE') ? 'selected' : ''; ?>>DATE</option>
                <option value="DATETIME" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'DATETIME') ? 'selected' : ''; ?>>DATETIME</option>
                <option value="BOOLEAN" <?php echo (!$is_template && isset($condition['type_cast']) && $condition['type_cast'] === 'BOOLEAN') ? 'selected' : ''; ?>>BOOLEAN</option>
            </select>
        </div>
    </div>
</div>

