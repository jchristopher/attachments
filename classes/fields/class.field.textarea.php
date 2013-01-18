<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Textarea extends Attachments_Field
{

    /**
     * Constructor
     * @param string $name  Field name
     * @param string $label Field label
     * @param mixed $value Field value
     */
    function __construct( $name = 'name', $label = 'Name', $value = null, $meta = array() )
    {
        parent::__construct( $name, $label, $value, $meta );
    }



    /**
     * Outputs the HTML for the field
     * @param  Attachments_Field $field The field object
     * @return void
     */
    function html( $field )
    {
    ?>
        <textarea type="text" name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" data-default="<?php esc_attr_e( $field->default ); ?>"><?php echo esc_textarea( $field->value ); ?></textarea>
    <?php
    }



    /**
     * Filter the field value to appear within the input as expected
     * @param  string $value The field value
     * @param  Attachments_field $field The field object
     * @return string        The formatted value
     */
    function format_value_for_input( $value, $field = null )
    {
        return $value;
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
