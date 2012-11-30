<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

// Declare our class
if ( !class_exists( 'Attachments_Field_Text' ) ) :

    class Attachments_Field_Text extends Attachments_Field implements Attachments_Field_Template
    {

        function __construct( $name = 'text', $label = 'Text' )
        {
            parent::__construct( $name, $label );

            $this->name     = sanitize_title( $name );
            $this->label    = __( $label, 'attachments' );
        }

        function html( $field )
        {
        ?>
            <input type="text" name="<?php echo $field->field_name; ?>" id="<?php echo $field->field_id; ?>" class="attachments attachments-field attachments-field-<?php echo $field->field_name; ?> attachments-field-<?php echo $field->field_id; ?>" value="<?php echo $field->value; ?>" />
        <?php
        }

        function format_value_for_input( $value, $field = null  )
        {
            return htmlspecialchars( $value, ENT_QUOTES );
        }

    }

endif; // class_exists check