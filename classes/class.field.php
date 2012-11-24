<?php

/**
 * Attachments Field Base Class
 *
 * @package Attachments
 * @subpackage Main
 */

// Declare our class
if ( !class_exists( 'Attachments_Field' ) ) :

    class Attachments_Field
    {
        public $name;
        public $label;

        function __construct()
        {
            $this->name     = 'text';
            $this->label    = __( 'Text', 'attachments' );
        }

        function html( $field )
        {
        ?>
            <input type="text" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
        <?php
        }

        function format_value_for_input( $value, $field = null )
        {
            return $value;
        }

    }

endif; // class_exists check