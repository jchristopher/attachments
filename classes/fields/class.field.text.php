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

        function __construct( $name = 'text', $label = 'Text' )
        {
            $this->name     = sanitize_title( $name );
            $this->label    = __( $label, 'attachments' );
        }

        function html( $field )
        {
        ?>
            <input type="text" name="<?php echo $this->get_full_field_name( $field->name ); ?>" id="<?php echo $this->get_full_field_name( $field->name ); ?>" class="attachments attachments-field attachments-field-<?php echo $field->name; ?>" value="<?php echo $field->value; ?>" />
        <?php
        }

        function format_value_for_input( $value )
        {
            return htmlspecialchars( $value, ENT_QUOTES );
        }

    }

endif; // class_exists check