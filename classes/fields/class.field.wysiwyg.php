<?php

/**
 * Attachments WYSIWYG field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_WYSIWYG extends Attachments_Field implements Attachments_Field_Template
{

    function __construct( $name = 'name', $label = 'Name', $value = null )
    {
        parent::__construct( $name, $label, $value );

        add_filter( 'wp_default_editor', array( $this, 'wp_default_editor' ) );
    }

    function html( $field )
    {
    ?>
        <div class="wp-editor-wrap attachments-field-wysiwyg-editor-wrap">
            <div class="wp-editor-container">
                <textarea name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="wp-editor-area attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" rows="10"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }

    function assets( $field = null )
    {
        ?>
            <script>
                jQuery(document).ready(function($){
                    $this = $('#<?php esc_attr_e( $field->field_id ); ?>');

                    tinyMCE.settings.theme_advanced_buttons2 += ',code';
                    tinyMCE.execCommand('mceAddControl', false, '<?php esc_attr_e( $field->field_id ); ?>');
                });
            </script>
        <?php
    }

    function format_value_for_input( $value, $field = null  )
    {
        return wp_richedit_pre( $value );
    }

    function wp_default_editor()
    {
        return 'tinymce'; // html or tinymce
    }

}