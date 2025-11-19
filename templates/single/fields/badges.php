<?php
/**
 * @author  wpWax
 * @since   6.7
 * @version 8.5
 */

use \Directorist\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! $listing->has_badge( $data ) ) {
    return;
}

?>

<?php if ( $listing->display_new_badge( $data ) || $listing->display_featured_badge( $data ) || $listing->display_popular_badge( $data ) || ! empty( $custom_badges ) ) : ?>

    <div class="directorist-info-item directorist-info-item-badges">

        <?php if ( $listing->display_new_badge( $data ) ) : ?>
            <span class="directorist-badge directorist-badge-new"><?php echo esc_html( Helper::new_badge_text() ); ?></span>
        <?php endif; ?>

        <?php if ( $listing->display_featured_badge( $data ) ) : ?>
            <span class="directorist-badge directorist-badge-featured ">
                <?php echo esc_html( Helper::featured_badge_text() ); ?>
            </span>
        <?php endif; ?>

        <?php if ( $listing->display_popular_badge( $data ) ) : ?>
            <span class="directorist-badge directorist-badge-popular"><?php echo esc_html( Helper::popular_badge_text() ); ?></span>
        <?php endif; ?>

        <?php if ( ! empty( $custom_badges ) ) : ?>
            <?php foreach ( $custom_badges as $badge ) : ?>
                <?php if( ! isset( $badge['data'] ) ) continue; ?>
                <?php if (Directorist_Custom_Badges_Conditions::check_conditions( $badge['data']['badge_data'], get_the_ID()) ): ?>
                <?php
                    $badge_id    = isset( $badge['data']['id'] ) ? $badge['data']['id'] : '';
                    $badge_label = isset( $badge['data']['label'] ) ? $badge['data']['label'] : '';
                    $badge_icon  = !empty( $badge['data']['icon'] ) ? $badge['data']['icon'] : '';
                    $badge_color = !empty( $badge['data']['color'] ) ? $badge['data']['color'] : '';
                    $badge_class = !empty( $badge['data']['class'] ) ? $badge['data']['class'] : '';
                    $style_attr  = $badge_color ? ' style="background-color:' . esc_attr( $badge_color ) . ';"' : '';
                ?>
                <span class="directorist-badge directorist-custom-badge-single directorist-badge-single-<?php echo esc_attr( $badge_id ); ?> <?php echo esc_attr( $badge_class ); ?>"<?php echo $style_attr; ?>>
                    <?php if ( $badge_icon ) : ?>
                        <?php echo function_exists( 'directorist_icon' ) ? directorist_icon( $badge_icon ) : '<i class="' . esc_attr( $badge_icon ) . '"></i>'; ?>
                    <?php endif; ?>
                    <?php echo esc_html( $badge_label ); ?>
                </span>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

<?php endif; ?>