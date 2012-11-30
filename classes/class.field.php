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
        public $uid;            // unique id for field
        public $value;          // the value for the field

        function __construct( $name = 'name', $label = 'Name', $value = null )
        {
            $this->name     = sanitize_title( $name );
            $this->label    = __( esc_attr( $label) );
            $this->value    = $value;
        }

        function set_field_instance( $instance, $field )
        {
            $field->instance = $instance;
        }

        function set_field_identifiers( $field, $uid = null )
        {
            // we MUST have an instance
            if( empty( $field->instance ) )
                return false;

            // if we're pulling an existing Attachment (field has a value) we're going to use
            // a PHP uniqid to set up our array flags but if we're setting up our Underscore
            // template we need to use a variable flag to be processed later
            $this->uid = !is_null( $uid ) ? $uid : '<%- attachments.attachment_uid %>';

            // set the name
            $field->field_name = "attachments[$field->instance][$this->uid][fields][$field->name]";

            // set the id
            $field->field_id = $this->field_name . $this->uid;
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