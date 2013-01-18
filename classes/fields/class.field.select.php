<?php

/**
 * Attachments select field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_Select extends Attachments_Field
{

    private $allow_null;    // whether null is allowed
    private $multiple;      // whether it's a multiple <select>
    private $options;       // the <options> for the <select>


    /**
     * Constructor
     * @param string $name  Field name
     * @param string $label Field label
     * @param mixed $value Field value
     */
    function __construct( $name = 'name', $label = 'Name', $value = null, $meta = array() )
    {
        $defaults = array(
                'allow_null'    => true,
                'multiple'      => false,
                'options'       => array(),       // no <option>s by default
            );

        $meta = array_merge( $defaults, $meta );

        $this->options      = is_array( $meta['options'] ) ? $meta['options'] : array();
        $this->allow_null   = is_bool( $meta['allow_null'] ) ? $meta['allow_null'] : true;
        $this->multiple     = is_bool( $meta['multiple'] ) ? $meta['multiple'] : false;

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
        <select name="<?php esc_attr_e( $field->field_name ); ?><?php if( $this->multiple ) : ?>[]<?php endif; ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="attachments attachments-field attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>"<?php if( $this->multiple ) : ?> multiple<?php endif; ?>>
            <?php if( $this->allow_null && !$this->multiple ) : ?><option value="">&mdash;</option><?php endif; ?>
            <?php foreach ( $this->options as $option_value => $option_label ) : ?>
                <?php
                    $selected = selected( $field->value, $option_value ) ? ' selected' : '';

                    if( is_array( $field->value ) )
                        $selected = in_array( $option_value, $field->value ) ? ' selected' : '';

                    if( is_object( $field->value ) )
                    {
                        $values     = get_object_vars( $field->value );
                        $selected   = in_array( $option_value, $values ) ? ' selected' : '';
                    }

                ?>
                <option value="<?php esc_attr_e( $option_value ); ?>"<?php echo $selected; ?>>
                    <?php echo $option_label; ?>
                </option>
            <?php endforeach; ?>
        </select>
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