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
                    $badge_text_color = !empty( $badge['data']['font_color'] ) ? $badge['data']['font_color'] : '';
                    $badge_label_font_size = ! empty( $badge['data']['label_font_size'] ) ? absint( $badge['data']['label_font_size'] ) : 14;
                    $badge_label_font_size = $badge_label_font_size > 0 ? $badge_label_font_size : 14;
                    $badge_class = !empty( $badge['data']['class'] ) ? $badge['data']['class'] : '';
                    $badge_type  = ! empty( $badge['data']['badge_data']['badge_type'] ) ? $badge['data']['badge_data']['badge_type'] : 'custom';
                    $display_type = ! empty( $badge['data']['display_type'] ) ? $badge['data']['display_type'] : 'label';
                    $display_type = in_array( $display_type, array( 'label', 'image' ), true ) ? $display_type : 'label';
                    $badge_image_url = ! empty( $badge['data']['image_url'] ) ? $badge['data']['image_url'] : '';
                    if ( ! $badge_image_url && ! empty( $badge['data']['image_id'] ) ) {
                        $badge_image_url = wp_get_attachment_image_url( absint( $badge['data']['image_id'] ), 'full' );
                    }
                    $badge_image_width = ! empty( $badge['data']['image_width'] ) ? absint( $badge['data']['image_width'] ) : 30;
                    $badge_image_width = $badge_image_width > 0 ? $badge_image_width : 30;
                    $style_attr  = '';
                    if ( $badge_color || $badge_text_color ) {
                        $style_attr = ' style="';
                        $style_attr .= $badge_color ? 'background-color:' . esc_attr( $badge_color ) . ';' : '';
                        $style_attr .= $badge_text_color ? 'color:' . esc_attr( $badge_text_color ) . ';' : '';
                        $style_attr .= 'font-size:' . esc_attr( $badge_label_font_size ) . 'px;';
                        $style_attr .= '"';
                    } else {
                        $style_attr = ' style="font-size:' . esc_attr( $badge_label_font_size ) . 'px;"';
                    }
                ?>
                <?php if ( 'tags' === $badge_type ) : ?>
                    <?php
                    $tags = defined( 'ATBDP_TAGS' ) ? get_the_terms( get_the_ID(), ATBDP_TAGS ) : array();
                    if ( is_wp_error( $tags ) || empty( $tags ) || ! is_array( $tags ) ) {
                        continue;
                    }
                    $maximum_tags = isset( $badge['data']['maximum_tags'] ) ? absint( $badge['data']['maximum_tags'] ) : 0;
                    if ( $maximum_tags > 0 ) {
                        $tags = array_slice( $tags, 0, $maximum_tags );
                    }
                    ?>
                    <span class="directorist-tags-badge directorist-info-item <?php echo esc_attr( $badge_class ); ?>">
                        <?php foreach ( $tags as $tag ) : ?>
                            <span class="directorist-tag-badge-item"<?php echo $style_attr; ?>>
                                <?php if ( $badge_icon ) : ?>
                                    <?php echo function_exists( 'directorist_icon' ) ? directorist_icon( $badge_icon ) : '<i class="' . esc_attr( $badge_icon ) . '"></i>'; ?>
                                <?php endif; ?>
                                <?php echo esc_html( $tag->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    </span>
                    <?php continue; ?>
                <?php endif; ?>
                <?php if ( 'image' === $display_type && $badge_image_url ) : ?>
                    <img src="<?php echo esc_url( $badge_image_url ); ?>" alt="<?php echo esc_attr( $badge_label ); ?>" width="<?php echo esc_attr( $badge_image_width ); ?>">
                    <?php continue; ?>
                <?php endif; ?>
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
