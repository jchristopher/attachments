<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Text extends Attachments_Field
{

    /**
     * Constructor
     * @param string $name  Field name
     * @param string $label Field label
     * @param mixed $value Field value
     */
    function __construct( $name = 'name', $label = 'Name', $value = null )
    {
        parent::__construct( $name, $label, $value );
    }



    /**
     * Outputs the HTML for the field
     * @param  Attachments_Field $field The field object
     * @return void
     */
    function html( $field )
    {
    ?>
        <input type="text" name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" value="<?php esc_attr_e( $field->value ); ?>" data-default="<?php esc_attr_e( $field->default ); ?>" />
    <?php
    }



    /**
     * Filter the field value to appear within the input as expected
     * @param  string $value The field value
     * @param  Attachments_field $field The field object
     * @return string        The formatted value
     */
    function format_value_for_input( $value, $field = null  )
    {
        return htmlspecialchars( $value, ENT_QUOTES );
    }



    /**
     * Fires once per field type per instance and outputs any additional assets (e.g. external JavaScript)
     * @return void
     */
    public function assets()
    {
        return;
    }



    /**
     * Hook into WordPress' init action
     * @return void
     */
    function init()
    {
        return;
    }

}
