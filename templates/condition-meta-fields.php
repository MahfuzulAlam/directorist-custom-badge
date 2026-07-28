<?php
/**
 * Meta Condition Fields Template.
 *
 * Rendered both server-side (for existing conditions) and as a JS template
 * (placeholder index = '{{index}}'). The grid layout places Compare beside
 * Meta Key so the user sees the operator before filling in the value.
 * Meta Value and Type Cast are hidden automatically when the operator is
 * EXISTS or NOT EXISTS (handled in admin.js via handleCompareChange).
 *
 * @package Directorist_Smart_Badge
 *
 * @var int|string $index     Condition index or '{{index}}' placeholder.
 * @var array      $condition Saved condition data (empty array for new / template).
 * @var bool       $is_template True when rendering the JS template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_template   = ( false !== strpos( (string) $index, '{{' ) );
$condition     = isset( $condition ) ? $condition : array();
$saved_compare = ! $is_template && isset( $condition['compare'] ) ? $condition['compare'] : '=';
$hide_value    = ! $is_template && in_array( $saved_compare, array( 'EXISTS', 'NOT EXISTS' ), true );

$saved_meta_key = ( ! $is_template && isset( $condition['meta_key'] ) ) ? (string) $condition['meta_key'] : '';
?>


<!-- Meta Condition Fields -->
<div class="dsb-condition-fields dsb-meta-fields"<?php
	if ( ! $is_template && isset( $condition['type'] ) && 'pricing_plan' === $condition['type'] ) {
		echo ' style="display:none;"';
	}
?>>

	<!-- Row 1: Meta Key + Compare (always visible) -->
	<div class="dsb-form-row dsb-form-row--grid">

		<div class="dsb-form-field dsb-form-field--meta-key">
			<label for="dsb-meta-key-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Meta Key', 'directorist-smart-badges' ); ?></label>
			<input
				type="text"
				id="dsb-meta-key-<?php echo esc_attr( $index ); ?>"
				name="badge[conditions][<?php echo esc_attr( $index ); ?>][meta_key]"
				class="dsb-input dsb-meta-key-input"
				list="dsb-meta-keys"
				placeholder="<?php esc_attr_e( 'Select or type a meta key…', 'directorist-smart-badges' ); ?>"
				value="<?php echo esc_attr( $saved_meta_key ); ?>"
			>
			<p class="description"><?php esc_html_e( 'Choose from keys already used on listings, or type a custom key.', 'directorist-smart-badges' ); ?></p>
		</div>

		<div class="dsb-form-field">
			<label><?php esc_html_e( 'Compare', 'directorist-smart-badges' ); ?></label>
			<select
				name="badge[conditions][<?php echo esc_attr( $index ); ?>][compare]"
				class="dsb-select dsb-compare-select"
			>
				<?php
				$operators = array(
					'='          => '= &nbsp;(equals)',
					'!='         => '!= &nbsp;(not equals)',
					'>'          => '&gt; &nbsp;(greater than)',
					'>='         => '&gt;= &nbsp;(greater or equal)',
					'<'          => '&lt; &nbsp;(less than)',
					'<='         => '&lt;= &nbsp;(less or equal)',
					'LIKE'       => 'LIKE &nbsp;(contains)',
					'NOT LIKE'   => 'NOT LIKE &nbsp;(not contains)',
					'IN'         => 'IN &nbsp;(value in array)',
					'NOT IN'     => 'NOT IN &nbsp;(value not in array)',
					'EXISTS'     => 'EXISTS &nbsp;(key exists)',
					'NOT EXISTS' => 'NOT EXISTS &nbsp;(key absent)',
				);
				foreach ( $operators as $val => $label ) {
					$selected = ( ! $is_template && $saved_compare === $val ) ? ' selected' : '';
					printf(
						'<option value="%s"%s>%s</option>',
						esc_attr( $val ),
						$selected,
						$label // already escaped / contains HTML entities only
					);
				}
				?>
			</select>
		</div>

	</div><!-- /.dsb-form-row--grid (row 1) -->

	<!-- Row 2: Meta Value + Type Cast (hidden for EXISTS / NOT EXISTS) -->
	<div class="dsb-form-row dsb-form-row--grid dsb-meta-value-row"<?php echo $hide_value ? ' style="display:none;"' : ''; ?>>

		<div class="dsb-form-field">
			<label><?php esc_html_e( 'Meta Value', 'directorist-smart-badges' ); ?></label>
			<input
				type="text"
				name="badge[conditions][<?php echo esc_attr( $index ); ?>][meta_value]"
				class="dsb-input"
				placeholder="<?php esc_attr_e( 'expected value', 'directorist-smart-badges' ); ?>"
				value="<?php echo ( ! $is_template && isset( $condition['meta_value'] ) ) ? esc_attr( $condition['meta_value'] ) : ''; ?>"
			>
		</div>

		<div class="dsb-form-field">
			<label><?php esc_html_e( 'Type Cast', 'directorist-smart-badges' ); ?></label>
			<select
				name="badge[conditions][<?php echo esc_attr( $index ); ?>][type_cast]"
				class="dsb-select"
			>
				<?php
				$types = array(
					'CHAR'     => 'CHAR &nbsp;(string)',
					'NUMERIC'  => 'NUMERIC &nbsp;(integer / float)',
					'DECIMAL'  => 'DECIMAL &nbsp;(float)',
					'DATE'     => 'DATE',
					'DATETIME' => 'DATETIME',
					'BOOLEAN'  => 'BOOLEAN',
				);
				foreach ( $types as $val => $label ) {
					$saved_type = ! $is_template && isset( $condition['type_cast'] ) ? $condition['type_cast'] : 'CHAR';
					$selected   = ( ! $is_template && $saved_type === $val ) ? ' selected' : '';
					printf(
						'<option value="%s"%s>%s</option>',
						esc_attr( $val ),
						$selected,
						$label
					);
				}
				?>
			</select>
		</div>

	</div><!-- /.dsb-form-row--grid (row 2) -->

</div><!-- /.dsb-meta-fields -->
