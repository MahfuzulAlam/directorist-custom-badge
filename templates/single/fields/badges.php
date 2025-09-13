<?php
/**
 * @author  wpWax
 * @since   6.7
 * @version 6.7
 */

use \Directorist\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! $listing->has_badge( $data ) ) {
    //return;
}

$qualified_badges = get_post_meta( get_the_ID(), '_qualified_badges', true );

?>

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

    <?php
    if ( is_array($qualified_badges) && count($qualified_badges) > 0 ):
        $all_badges = get_qualified_badges_info();
        foreach ( $all_badges as $badge_key => $badge_atts ) {
            if ( is_array($qualified_badges) && in_array($badge_key, $qualified_badges) ):
                ?>
                    <span id="<?php echo $badge_atts[ 'id' ]; ?>" class="directorist-badge directorist-custom-badge <?php echo $badge_atts[ 'class' ]; ?>">
                        <?php echo $badge_atts[ 'title' ]; ?>
                    </span>
                <?php
            endif;
        }
    endif;
    ?>

</div>