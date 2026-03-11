<?php
/**
 * Admin page template – Custom Badges list view.
 *
 * @package Directorist_Custom_Badge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$badges = Directorist_Custom_Badges_Admin::get_badges();
?>

<div class="wrap dcb-admin-wrap">

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Custom Badges', 'directorist-custom-badges' ); ?></h1>

	<span class="dcb-toolbar">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-custom-badges-form' ) ); ?>" class="page-title-action">
			<?php esc_html_e( '+ Add New Badge', 'directorist-custom-badges' ); ?>
		</a>
		<button type="button" class="page-title-action dcb-export-badges">
			<?php esc_html_e( 'Export', 'directorist-custom-badges' ); ?>
		</button>
		<label for="dcb-import-file" class="page-title-action" style="cursor:pointer;">
			<?php esc_html_e( 'Import', 'directorist-custom-badges' ); ?>
			<input type="file" id="dcb-import-file" accept=".json" style="display:none;">
		</label>
	</span>

	<div class="dcb-notices"></div>

	<div class="dcb-admin-content">

		<?php if ( empty( $badges ) ) : ?>

			<!-- Empty state ------------------------------------------------- -->
			<div class="dcb-empty-state" id="dcb-empty-state">
				<p><?php esc_html_e( 'No badges configured yet. Create your first badge to get started.', 'directorist-custom-badges' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-custom-badges-form' ) ); ?>" class="button button-primary">
					<?php esc_html_e( '+ Add Your First Badge', 'directorist-custom-badges' ); ?>
				</a>
			</div>

		<?php else : ?>

			<!-- Badges table ------------------------------------------------ -->
			<div class="dcb-badges-table-wrapper" id="dcb-badges-table">
				<table class="wp-list-table widefat fixed striped dcb-badges-table">
					<thead>
						<tr>
							<th class="column-order" scope="col">
								<span class="dashicons dashicons-move" style="color:#bbb;" title="<?php esc_attr_e( 'Drag to reorder', 'directorist-custom-badges' ); ?>"></span>
							</th>
							<th class="column-preview" scope="col"><?php esc_html_e( 'Preview', 'directorist-custom-badges' ); ?></th>
							<th class="column-title" scope="col"><?php esc_html_e( 'Title', 'directorist-custom-badges' ); ?></th>
							<th class="column-id" scope="col"><?php esc_html_e( 'Badge ID', 'directorist-custom-badges' ); ?></th>
							<th class="column-conditions" scope="col"><?php esc_html_e( 'Conditions', 'directorist-custom-badges' ); ?></th>
							<th class="column-status" scope="col"><?php esc_html_e( 'Active', 'directorist-custom-badges' ); ?></th>
							<th class="column-actions" scope="col"><?php esc_html_e( 'Actions', 'directorist-custom-badges' ); ?></th>
						</tr>
					</thead>
					<tbody class="dcb-badges-list sortable">
						<?php foreach ( $badges as $badge ) : ?>
							<?php
							$badge_color      = ! empty( $badge['badge_color'] ) ? esc_attr( $badge['badge_color'] ) : '';
							$badge_icon       = ! empty( $badge['badge_icon'] )  ? esc_attr( $badge['badge_icon'] )  : '';
							$condition_count  = isset( $badge['conditions'] ) ? count( $badge['conditions'] ) : 0;
							$is_active        = ! empty( $badge['is_active'] );

							// Build accessible preview swatch background/color.
							$swatch_style = $badge_color
								? 'background:' . $badge_color . ';color:' . ( Directorist_Custom_Badges_Helper::is_dark_color( $badge_color ) ? '#fff' : '#333' ) . ';'
								: '';
							?>
							<tr class="dcb-badge-row" data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>">

								<!-- Drag handle -->
								<td class="column-order">
									<span class="dcb-drag-handle dashicons dashicons-move" title="<?php esc_attr_e( 'Drag to reorder', 'directorist-custom-badges' ); ?>"></span>
								</td>

								<!-- Badge preview swatch -->
								<td class="column-preview">
									<span class="dcb-badge-preview" style="<?php echo $swatch_style; ?>">
										<?php if ( $badge_icon ) : ?>
											<i class="dcb-badge-icon-preview <?php echo esc_attr( $badge_icon ); ?>"></i>
										<?php endif; ?>
										<?php echo esc_html( $badge['badge_label'] ); ?>
									</span>
								</td>

								<!-- Title -->
								<td class="column-title">
									<strong>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-custom-badges-form&badge_id=' . urlencode( $badge['id'] ) ) ); ?>">
											<?php echo esc_html( $badge['badge_title'] ); ?>
										</a>
									</strong>
								</td>

								<!-- Badge ID -->
								<td class="column-id">
									<code><?php echo esc_html( $badge['badge_id'] ); ?></code>
								</td>

								<!-- Conditions count -->
								<td class="column-conditions">
									<span class="dcb-condition-count <?php echo 0 === $condition_count ? 'is-zero' : ''; ?>">
										<?php echo esc_html( $condition_count ); ?>
									</span>
								</td>

								<!-- Active toggle -->
								<td class="column-status">
									<label class="dcb-toggle-switch" title="<?php echo $is_active ? esc_attr__( 'Active – click to disable', 'directorist-custom-badges' ) : esc_attr__( 'Inactive – click to enable', 'directorist-custom-badges' ); ?>">
										<input
											type="checkbox"
											class="dcb-toggle-active"
											<?php checked( $is_active ); ?>
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
										>
										<span class="dcb-toggle-slider"></span>
									</label>
								</td>

								<!-- Actions -->
								<td class="column-actions">
									<div class="dcb-row-actions">
										<a
											href="<?php echo esc_url( admin_url( 'admin.php?page=directorist-custom-badges-form&badge_id=' . urlencode( $badge['id'] ) ) ); ?>"
											class="dcb-btn-icon"
											title="<?php esc_attr_e( 'Edit', 'directorist-custom-badges' ); ?>"
										><span class="dashicons dashicons-edit"></span></a>

										<button
											type="button"
											class="dcb-btn-icon dcb-duplicate-badge"
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
											title="<?php esc_attr_e( 'Duplicate', 'directorist-custom-badges' ); ?>"
										><span class="dashicons dashicons-admin-page"></span></button>

										<button
											type="button"
											class="dcb-btn-icon is-delete dcb-delete-badge"
											data-badge-id="<?php echo esc_attr( $badge['id'] ); ?>"
											title="<?php esc_attr_e( 'Delete', 'directorist-custom-badges' ); ?>"
										><span class="dashicons dashicons-trash"></span></button>
									</div>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div><!-- /.dcb-badges-table-wrapper -->

		<?php endif; ?>

	</div><!-- /.dcb-admin-content -->

</div><!-- /.wrap -->
