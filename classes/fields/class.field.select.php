<?php

/**
 * Attachments text field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Select extends Attachments_Field implements Attachments_Field_Template
{
	private $options;
	
    function __construct( $name = 'name', $label = 'Name', $value = null, $meta = array() )
    {
		
		$this->options		= $meta;
		
        parent::__construct( $name, $label, $value, $meta );
    }

    function html( $field )
    {
    ?>
    	<select name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>">
        	<option value="">&mdash;</option>
            <?php
			foreach ( $this->options as $opt_value => $opt_label )
			{
			?>
            <option value="<?php esc_attr_e( $opt_value ); ?>"<?php if( esc_attr( $opt_value ) == esc_attr( $field->value ) ) echo " selected"; ?>><?php esc_attr_e( $opt_label ); ?> (<?php esc_attr_e( $opt_value ); ?>)</option>
            <?php
			}
			?>
        </select>
    <?php
    }

    function format_value_for_input( $value, $field = null  )
    {
        return htmlspecialchars( $value, ENT_QUOTES );
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