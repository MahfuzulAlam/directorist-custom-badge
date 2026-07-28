<?php
/**
 * Admin page template – Smart Badges list view.
 *
 * @package Directorist_Smart_Badge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$badges = Directorist_Smart_Badges_Admin::get_badges();
?>

<div class="wrap dsb-admin-wrap dsb-admin-wrap--list">

	<header class="dsb-header">
		<div class="dsb-header-brand">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
			<h1><?php esc_html_e( 'Smart Badges', 'directorist-smart-badges' ); ?></h1>
		</div>
		<div class="dsb-header-actions">
			<button type="button" class="dsb-btn dsb-export-badges">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
				<?php esc_html_e( 'Export', 'directorist-smart-badges' ); ?>
			</button>
			<label for="dsb-import-file" class="dsb-btn">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
				<?php esc_html_e( 'Import', 'directorist-smart-badges' ); ?>
				<input type="file" id="dsb-import-file" accept=".json" style="display:none;">
			</label>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-smart-badges-form' ) ); ?>" class="dsb-btn dsb-btn--primary">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
				<?php esc_html_e( 'Add New Badge', 'directorist-smart-badges' ); ?>
			</a>
		</div>
	</header>

	<div class="dsb-admin-content">

		<?php if ( empty( $badges ) ) : ?>

			<!-- Empty state ------------------------------------------------- -->
			<div class="dsb-card dsb-empty-state" id="dsb-empty-state">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
				<p><?php esc_html_e( 'No badges configured yet. Create your first badge to get started.', 'directorist-smart-badges' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-smart-badges-form' ) ); ?>" class="dsb-btn dsb-btn--primary">
					<?php esc_html_e( 'Add Your First Badge', 'directorist-smart-badges' ); ?>
				</a>
			</div>

		<?php else : ?>

			<!-- Badges table ------------------------------------------------ -->
			<div class="dsb-card dsb-table-scroll" id="dsb-badges-table">
				<table class="dsb-table">
					<thead>
						<tr>
							<th class="column-order" scope="col">
								<span class="screen-reader-text"><?php esc_html_e( 'Reorder', 'directorist-smart-badges' ); ?></span>
							</th>
							<th class="column-preview" scope="col"><?php esc_html_e( 'Preview', 'directorist-smart-badges' ); ?></th>
							<th class="column-title" scope="col"><?php esc_html_e( 'Title', 'directorist-smart-badges' ); ?></th>
							<th class="column-id" scope="col"><?php esc_html_e( 'Badge ID', 'directorist-smart-badges' ); ?></th>
							<th class="column-conditions" scope="col"><?php esc_html_e( 'Conditions', 'directorist-smart-badges' ); ?></th>
							<th class="column-status" scope="col"><?php esc_html_e( 'Active', 'directorist-smart-badges' ); ?></th>
							<th class="column-actions" scope="col"><?php esc_html_e( 'Actions', 'directorist-smart-badges' ); ?></th>
						</tr>
					</thead>
					<tbody class="dsb-badges-list sortable">
						<?php foreach ( $badges as $badge ) : ?>
							<?php
							$badge_color      = ! empty( $badge['badge_color'] ) ? esc_attr( $badge['badge_color'] ) : '';
							$badge_text_color = ! empty( $badge['badge_text_color'] ) ? esc_attr( $badge['badge_text_color'] ) : '';
							$badge_label_font_size = ! empty( $badge['badge_label_font_size'] ) ? absint( $badge['badge_label_font_size'] ) : 14;
							$badge_label_font_size = $badge_label_font_size > 0 ? $badge_label_font_size : 14;
							$badge_icon       = ! empty( $badge['badge_icon'] )  ? esc_attr( $badge['badge_icon'] )  : '';
							$badge_type       = ! empty( $badge['badge_type'] ) ? $badge['badge_type'] : 'custom';
							$badge_type       = in_array( $badge_type, array( 'custom', 'tags' ), true ) ? $badge_type : 'custom';
							$display_type     = ! empty( $badge['display_type'] ) ? $badge['display_type'] : 'label';
							$display_type     = in_array( $display_type, array( 'label', 'image' ), true ) ? $display_type : 'label';
							$badge_image_url  = ! empty( $badge['badge_image_url'] ) ? $badge['badge_image_url'] : '';
							if ( ! $badge_image_url && ! empty( $badge['badge_image_id'] ) ) {
								$badge_image_url = wp_get_attachment_image_url( absint( $badge['badge_image_id'] ), 'full' );
							}
							$condition_count  = isset( $badge['conditions'] ) ? count( $badge['conditions'] ) : 0;
							$is_active        = ! empty( $badge['is_active'] );

							// Build accessible preview swatch background/color.
							$swatch_style = '';
							if ( $badge_color ) {
								$swatch_style .= 'background:' . $badge_color . ';';
							}
							if ( $badge_text_color ) {
								$swatch_style .= 'color:' . $badge_text_color . ';';
							} elseif ( $badge_color ) {
								$swatch_style .= 'color:' . ( Directorist_Smart_Badges_Helper::is_dark_color( $badge_color ) ? '#fff' : '#333' ) . ';';
							}
							$swatch_style .= 'font-size:' . $badge_label_font_size . 'px;';
							?>
							<tr class="dsb-badge-row" data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>">

								<!-- Drag handle -->
								<td class="column-order" data-label="<?php esc_attr_e( 'Reorder', 'directorist-smart-badges' ); ?>">
									<span class="dsb-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'directorist-smart-badges' ); ?>">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01"/></svg>
									</span>
								</td>

								<!-- Badge preview swatch -->
								<td class="column-preview" data-label="<?php esc_attr_e( 'Preview', 'directorist-smart-badges' ); ?>">
									<span class="dsb-badge-preview" style="<?php echo $swatch_style; ?>">
										<?php if ( 'custom' === $badge_type && 'image' === $display_type && $badge_image_url ) : ?>
											<img src="<?php echo esc_url( $badge_image_url ); ?>" alt="<?php echo esc_attr( $badge['badge_label'] ); ?>" class="dsb-badge-preview-image">
										<?php else : ?>
											<?php if ( $badge_icon ) : ?>
												<i class="dsb-badge-icon-preview <?php echo esc_attr( $badge_icon ); ?>"></i>
											<?php endif; ?>
											<?php echo esc_html( $badge['badge_label'] ); ?>
										<?php endif; ?>
									</span>
								</td>

								<!-- Title -->
								<td class="column-title" data-label="<?php esc_attr_e( 'Title', 'directorist-smart-badges' ); ?>">
									<strong>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-smart-badges-form&badge_id=' . urlencode( $badge['id'] ) ) ); ?>">
											<?php echo esc_html( $badge['badge_title'] ); ?>
										</a>
									</strong>
								</td>

								<!-- Badge ID -->
								<td class="column-id" data-label="<?php esc_attr_e( 'Badge ID', 'directorist-smart-badges' ); ?>">
									<code><?php echo esc_html( $badge['badge_id'] ); ?></code>
								</td>

								<!-- Conditions count -->
								<td class="column-conditions" data-label="<?php esc_attr_e( 'Conditions', 'directorist-smart-badges' ); ?>">
									<span class="dsb-condition-count <?php echo 0 === $condition_count ? 'is-zero' : ''; ?>">
										<?php echo esc_html( $condition_count ); ?>
									</span>
								</td>

								<!-- Active toggle -->
								<td class="column-status" data-label="<?php esc_attr_e( 'Active', 'directorist-smart-badges' ); ?>">
									<label class="dsb-toggle-switch" title="<?php echo $is_active ? esc_attr__( 'Active – click to disable', 'directorist-smart-badges' ) : esc_attr__( 'Inactive – click to enable', 'directorist-smart-badges' ); ?>">
										<input
											type="checkbox"
											class="dsb-toggle-active"
											<?php checked( $is_active ); ?>
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
										>
										<span class="dsb-toggle-slider"></span>
									</label>
								</td>

								<!-- Actions -->
								<td class="column-actions" data-label="<?php esc_attr_e( 'Actions', 'directorist-smart-badges' ); ?>">
									<div class="dsb-row-actions">
										<a
											href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-smart-badges-form&badge_id=' . urlencode( $badge['id'] ) ) ); ?>"
											class="dsb-btn-icon"
											title="<?php esc_attr_e( 'Edit', 'directorist-smart-badges' ); ?>"
											aria-label="<?php esc_attr_e( 'Edit', 'directorist-smart-badges' ); ?>"
										><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21.17 6.83a2.85 2.85 0 0 0-4-4L3.84 16.17a2 2 0 0 0-.5.83l-1.32 4.35a.5.5 0 0 0 .62.62l4.35-1.32a2 2 0 0 0 .83-.5Z"/><path d="m15 5 4 4"/></svg></a>

										<button
											type="button"
											class="dsb-btn-icon dsb-duplicate-badge"
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
											title="<?php esc_attr_e( 'Duplicate', 'directorist-smart-badges' ); ?>"
											aria-label="<?php esc_attr_e( 'Duplicate', 'directorist-smart-badges' ); ?>"
										><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg></button>

										<button
											type="button"
											class="dsb-btn-icon is-delete dsb-delete-badge"
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
											title="<?php esc_attr_e( 'Delete', 'directorist-smart-badges' ); ?>"
											aria-label="<?php esc_attr_e( 'Delete', 'directorist-smart-badges' ); ?>"
										><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
									</div>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div><!-- /.dsb-badges-table-wrapper -->

		<?php endif; ?>

	</div><!-- /.dsb-admin-content -->

</div><!-- /.wrap -->
