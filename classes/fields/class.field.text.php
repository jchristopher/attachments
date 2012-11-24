<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

// Declare our class
if ( !class_exists( 'Attachments_Field_Text' ) ) :

    class Attachments_Field_Text extends Attachments_Field
    {

        public $name;
        public $label;
        public $value;

        function __construct( $label = 'Text' )
        {
            $this->name     = 'text';
            $this->label    = __( $label, 'attachments' );
        }

        function html( $field )
        {
        ?>
            <input type="text" name="<?php echo $field->input_name; ?>" id="<?php echo $field->input_name; ?>" class="attachments attachments-field attachments-field-text" value="<?php echo $field->value; ?>" />
        <?php
        }

        function format_value_for_input( $value, $field = null )
        {
            return htmlspecialchars( $value, ENT_QUOTES );
        }

    }

endif; // class_exists check