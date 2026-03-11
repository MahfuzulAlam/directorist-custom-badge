<?php
/**
 * Condition Item Template.
 *
 * Renders a single repeatable condition row. Supports:
 *  - Drag-to-reorder within the list via `.dcb-condition-drag` handle.
 *  - Minimize / maximize the condition body via `.dcb-toggle-condition`.
 *  - A one-line summary shown in the header when the body is collapsed.
 *
 * @package Directorist_Custom_Badge
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

<div class="dcb-condition-item" data-condition-index="<?php echo esc_attr( $index ); ?>">

	<div class="dcb-condition-header">

		<!-- Drag handle -->
		<span class="dcb-condition-drag dashicons dashicons-move" title="<?php esc_attr_e( 'Drag to reorder', 'directorist-custom-badges' ); ?>"></span>

		<!-- Title + collapsed summary -->
		<span class="dcb-condition-title">
			<?php esc_html_e( 'Condition', 'directorist-custom-badges' ); ?>
			#<?php echo $is_template ? '{{index}}' : esc_html( $condition_number ); ?>
		</span>
		<span class="dcb-condition-summary"><?php echo $summary; // already escaped above ?></span>

		<!-- Header action buttons -->
		<div class="dcb-condition-header-actions">
			<button
				type="button"
				class="dcb-toggle-condition"
				title="<?php esc_attr_e( 'Minimize', 'directorist-custom-badges' ); ?>"
				aria-expanded="true"
			><span class="dashicons dashicons-arrow-up-alt2"></span></button>

			<button
				type="button"
				class="dcb-remove-condition"
				title="<?php esc_attr_e( 'Remove condition', 'directorist-custom-badges' ); ?>"
				aria-label="<?php esc_attr_e( 'Remove condition', 'directorist-custom-badges' ); ?>"
			><span class="dashicons dashicons-trash"></span></button>
		</div>

	</div><!-- /.dcb-condition-header -->

	<div class="dcb-condition-body">

		<!-- Condition type selector -->
		<div class="dcb-form-row">
			<div class="dcb-form-field">
				<label><?php esc_html_e( 'Condition Type', 'directorist-custom-badges' ); ?></label>
				<select name="badge[conditions][<?php echo esc_attr( $index ); ?>][type]" class="dcb-condition-type dcb-select">
					<option value="meta" <?php echo ( ! $is_template && 'meta' === $condition_type ) ? 'selected' : ''; ?>>
						<?php esc_html_e( 'Meta Field', 'directorist-custom-badges' ); ?>
					</option>
					<option value="pricing_plan" <?php echo ( ! $is_template && 'pricing_plan' === $condition_type ) ? 'selected' : ''; ?>>
						<?php esc_html_e( 'Pricing Plan', 'directorist-custom-badges' ); ?>
					</option>
				</select>
			</div>
		</div>

		<?php
		include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-meta-fields.php';
		include DIRECTORIST_CUSTOM_BADGE_DIR . 'templates/condition-pricing-plan-fields.php';
		?>

	</div><!-- /.dcb-condition-body -->

</div><!-- /.dcb-condition-item -->
