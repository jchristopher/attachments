<?php

/**
 * Attachments Field Base Class
 *
 * @package Attachments
 * @subpackage Main
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare our class
if ( ! class_exists( 'Attachments_Field' ) ) :

    /**
     * Attachments_Field
     */
    abstract class Attachments_Field {

        public $instance;       // the instance this field is used within
        public $name;           // the user-defined field name
        public $field_name;     // the name attribute to be used
        public $field_id;       // the id attribute to be used
        public $label;          // the field label
        public $type;           // field type as it was registered
        public $uid;            // unique id for field
        public $value;          // the value for the field
        public $defaults;       // stores possible defaults the user can use which correlate with WP Media meta
        public $default;        // the user-defined default value when first selected from the modal
        public $meta;           // houses any metadata necessary for the field



        /**
         * Constructor
         * @param string $name  Field name
         * @param string $label Field label
         * @param mixed $value Field value
         */
        function __construct( $name = 'name', $label = 'Name', $value = null, $meta = array() ) {
            $this->name     = sanitize_title( $name );
            $this->label    = __( esc_attr( $label) );
            $this->value    = $value;
            $this->default  = '';
            $this->meta     = $meta;
            $this->defaults = array( 'title', 'caption', 'alt', 'description' ); // WordPress-specific Media meta
            // TODO: determine how to integrate with custom metadata that was added to Media
        }



        /**
         * Sets the field instance
         * @param string $instance The instance name
         * @param Attachments_Field $field    The field object
         */
        function set_field_instance( $instance, $field ) {
            $field->instance = $instance;
        }



        /**
         * Sets the UID, name, and id of the field
         * @param Attachments_Field $field The field object
         * @param string $uid   Existing UID if applicable
         */
        function set_field_identifiers( $field, $uid = null ) {
            // we MUST have an instance
            if ( empty( $field->instance ) ) {
                return false;
            }

            // if we're pulling an existing Attachment (field has a value) we're going to use
            // a PHP uniqid to set up our array flags but if we're setting up our Underscore
            // template we need to use a variable flag to be processed later
            $this->uid = ! is_null( $uid ) ? $uid : '{{ attachments.attachment_uid }}';

            // set the name
            $field->field_name = "attachments[$field->instance][$this->uid][fields][$field->name]";

            // set the id
            $field->field_id = $this->field_name . $this->uid;
        }



        /**
         * Sets the field type of the field
         * @param string $field_type Registered field type name
         */
        function set_field_type( $field_type ) {
            $this->type = $field_type;
        }



        /**
         * Sets the WordPress meta attribute to be used as the default
         * @param string $default One of the approved defauls (title, caption, alt, description)
         */
        function set_field_default( $default = '' ) {
            if ( is_string( $default ) && ! empty( $default ) && in_array( strtolower( $default ), $this->defaults ) ) {
                $this->default = strtolower( $default );
            }
        }



        /**
         * Outputs the HTML for the field
         * @param  Attachments_Field $field The field object
         * @return void
         */
        abstract public function html( $field );



        /**
         * Filter the field value to appear within the input as expected
         * @param  string $value The field value
         * @param  Attachments_field $field The field object
         * @return string        The formatted value
         */
        abstract public function format_value_for_input( $value, $field = null );



        /**
         * Fires once per field type per instance and outputs any additional assets (e.g. external JavaScript)
         * @return void
         */
        abstract public function assets();



        /**
         * Hook into WordPress' init action
         * @return void
         */
        abstract public function init();
    }

endif; // class_exists check
