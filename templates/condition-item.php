<?php
/**
 * Condition Item Template.
 *
 * Renders a single repeatable condition row. Supports:
 *  - Drag-to-reorder within the list via `.dsb-condition-drag` handle.
 *  - Minimize / maximize the condition body via `.dsb-toggle-condition`.
 *  - A one-line summary shown in the header when the body is collapsed.
 *
 * @package Directorist_Smart_Badge
 *
 * @var int|string $index     Condition index or '{{index}}' JS placeholder.
 * @var array      $condition Saved condition data (empty for new / template).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_template      = ( false !== strpos( (string) $index, '{{' ) );
$condition        = isset( $condition ) ? $condition : array();
$condition_type   = ( ! $is_template && isset( $condition['type'] ) ) ? $condition['type'] : 'meta';
$condition_number = ( ! $is_template && is_numeric( $index ) ) ? intval( $index ) + 1 : '{{index}}';

// Build a brief header summary for when the item is collapsed.
$summary = '';
if ( ! $is_template ) {
	if ( 'meta' === $condition_type ) {
		$key     = isset( $condition['meta_key'] )   ? $condition['meta_key']   : '';
		$op      = isset( $condition['compare'] )    ? $condition['compare']    : '=';
		$val     = isset( $condition['meta_value'] ) ? $condition['meta_value'] : '';
		$summary = esc_html( $key . ' ' . $op . ( in_array( $op, array( 'EXISTS', 'NOT EXISTS' ), true ) ? '' : ' ' . $val ) );
	} elseif ( 'pricing_plan' === $condition_type ) {
		$status  = isset( $condition['plan_status_condition'] ) ? $condition['plan_status_condition'] : '';
		$summary = esc_html( str_replace( '_', ' ', $status ) );
	}
}
?>

<div class="dsb-condition-item" data-condition-index="<?php echo esc_attr( $index ); ?>">

	<div class="dsb-condition-header">

		<!-- Drag handle -->
		<span class="dsb-condition-drag" title="<?php esc_attr_e( 'Drag to reorder', 'directorist-smart-badges' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01"/></svg>
		</span>

		<!-- Title + collapsed summary -->
		<span class="dsb-condition-title">
			<?php esc_html_e( 'Condition', 'directorist-smart-badges' ); ?>
			#<?php echo $is_template ? '{{index}}' : esc_html( $condition_number ); ?>
		</span>
		<span class="dsb-condition-summary"><?php echo $summary; // already escaped above ?></span>

		<!-- Header action buttons -->
		<div class="dsb-condition-header-actions">
			<button
				type="button"
				class="dsb-btn-icon dsb-toggle-condition"
				title="<?php esc_attr_e( 'Minimize', 'directorist-smart-badges' ); ?>"
				aria-expanded="true"
			><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m18 15-6-6-6 6"/></svg></button>

			<button
				type="button"
				class="dsb-btn-icon is-delete dsb-remove-condition"
				title="<?php esc_attr_e( 'Remove condition', 'directorist-smart-badges' ); ?>"
				aria-label="<?php esc_attr_e( 'Remove condition', 'directorist-smart-badges' ); ?>"
			><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
		</div>

	</div><!-- /.dsb-condition-header -->

	<div class="dsb-condition-body">

		<!-- Condition type selector -->
		<div class="dsb-form-row">
			<div class="dsb-form-field">
				<label><?php esc_html_e( 'Condition Type', 'directorist-smart-badges' ); ?></label>
				<select name="badge[conditions][<?php echo esc_attr( $index ); ?>][type]" class="dsb-condition-type dsb-select">
					<option value="meta" <?php echo ( ! $is_template && 'meta' === $condition_type ) ? 'selected' : ''; ?>>
						<?php esc_html_e( 'Meta Field', 'directorist-smart-badges' ); ?>
					</option>
					<option value="pricing_plan" <?php echo ( ! $is_template && 'pricing_plan' === $condition_type ) ? 'selected' : ''; ?>>
						<?php esc_html_e( 'Pricing Plan', 'directorist-smart-badges' ); ?>
					</option>
				</select>
			</div>
		</div>

		<?php
		include DIRECTORIST_SMART_BADGE_DIR . 'templates/condition-meta-fields.php';
		include DIRECTORIST_SMART_BADGE_DIR . 'templates/condition-pricing-plan-fields.php';
		?>

	</div><!-- /.dsb-condition-body -->

</div><!-- /.dsb-condition-item -->
