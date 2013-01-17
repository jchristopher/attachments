<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Textarea extends Attachments_Field implements Attachments_Field_Template
{

    function __construct( $name = 'name', $label = 'Name', $value = null )
    {
        parent::__construct( $name, $label, $value );
    }

    function html( $field )
    {
    ?>
        <textarea type="text" name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" data-default="<?php esc_attr_e( $field->default ); ?>"><?php echo esc_textarea( $field->value ); ?></textarea>
    <?php
    }

    function format_value_for_input( $value, $field = null )
    {
        return $value;
    }

    public function assets()
    {
        return;
    }

    function init()
    {
        return;
    }

}