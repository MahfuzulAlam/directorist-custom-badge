<?php

/**
 * @author  wpWax
 * @since   6.6
 * @version 6.7
 */

if (! defined('ABSPATH')) exit;

?>

<div class="directorist-form-group directorist-custom-field-radio">

    <?php \Directorist\Directorist_Listing_Form::instance()->field_label_template($data); ?>

    <div class="directorist-radio directorist-radio-circle directorist-mb-10">
        <input type="radio" id="<?php echo esc_attr($data['field_key']); ?>_yes" name="<?php echo esc_attr($data['field_key']); ?>" value="yes" <?php checked("yes", $data['value']); ?>>
        <label for="<?php echo esc_attr($data['field_key']); ?>_yes" class="directorist-radio__label">Yes</label>
    </div>

    <div class="directorist-radio directorist-radio-circle directorist-mb-10">
        <input type="radio" id="<?php echo esc_attr($data['field_key']); ?>_no" name="<?php echo esc_attr($data['field_key']); ?>" value="no" <?php checked("no", $data['value']); ?>>
        <label for="<?php echo esc_attr($data['field_key']); ?>_no" class="directorist-radio__label">No</label>
    </div>

    <?php \Directorist\Directorist_Listing_Form::instance()->field_description_template($data); ?>

</div>