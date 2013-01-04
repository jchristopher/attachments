<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Text extends Attachments_Field implements Attachments_Field_Template
{

    function __construct( $name = 'name', $label = 'Name', $value = null )
    {
        parent::__construct( $name, $label, $value );
    }

    function html( $field )
    {
    ?>
        <input type="text" name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" value="<?php esc_attr_e( $field->value ); ?>" />
    <?php
    }

    function format_value_for_input( $value, $field = null  )
    {
        return htmlspecialchars( $value, ENT_QUOTES );
    }

    public function assets( $field = null )
    {
        return;
    }

}