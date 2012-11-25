<?php

/**
 * Attachments Field Base Class
 *
 * @package Attachments
 * @subpackage Main
 */

// Declare our class
if ( !class_exists( 'Attachments_Field' ) ) :

    interface Attachments_Field_Template
    {
        public function html( $field );
        public function format_value_for_input( $value, $field );
    }

    class Attachments_Field implements Attachments_Field_Template
    {
        public $instance;       // the instance this field is used within
        public $name;           // the user-defined field name
        public $field_name;     // the name attribute to be used
        public $field_id;       // the id attribute to be used
        public $label;          // the field label
        public $type;           // field type as it was registered

        function __construct()
        {
            $this->name     = 'text';
            $this->label    = __( 'Text', 'attachments' );
        }

        function set_field_instance( $instance, $field )
        {
            $field->instance = $instance;
        }

        function set_field_identifiers( $field )
        {
            // we MUST have an instance
            if( empty( $field->instance ) )
                return false;

            // set the name
            $field->field_name = "attachments[$field->instance][][$field->name]";

            // set the id
            $field->field_id = uniqid( $this->field_name );
        }

        function set_field_type( $field_type )
        {
            $this->type = $field_type;
        }

        function html( $field )
        {
        ?>
            <input type="text" name="<?php echo $field->field_name; ?>" id="<?php echo $field->field_id; ?>" class="attachments attachments-field attachments-field-<?php echo $field->field_name; ?> attachments-field-<?php echo $field->field_id; ?>" value="<?php echo $field->value; ?>" />
        <?php
        }

        function format_value_for_input( $value, $field = null )
        {
            return $value;
        }

    }

endif; // class_exists check