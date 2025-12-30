<?php

/**
 * Attachments WYSIWYG field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_WYSIWYG extends Attachments_Field {

    /**
     * Constructor
     * @param string $name  Field name
     * @param string $label Field label
     * @param mixed $value Field value
     */
    function __construct( $name = 'name', $label = 'Name', $value = null, $meta = array() ) {
        parent::__construct( $name, $label, $value, $meta );

        add_filter( 'wp_default_editor',    array( $this, 'wp_default_editor' ) );
    }



    /**
     * Hook into WordPress' init action
     * @return void
     */
    function init() {
        global $post;

        // ensure we've got TinyMCE to work with
        $has_editor = post_type_supports( $post->post_type, 'editor' );
        add_post_type_support( $post->post_type, 'editor' );

        if ( ! $has_editor ) {
            echo '<style type="text/css">#poststuff .postarea { display:none; }</style>';
        }
    }



    /**
     * Outputs the HTML for the field
     * @param  Attachments_Field $field The field object
     * @return void
     */
    function html( $field ) {
    ?>
        <div class="wp-editor-wrap attachments-field-wysiwyg-editor-wrap">
            <div class="wp-editor-container">
                <textarea name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="wp-editor-area attachments attachments-field attachments-field-wysiwyg attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" rows="10" data-default="<?php echo esc_attr( $field->default ); ?>"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }



    /**
     * Fires once per field type per instance and outputs any additional assets (e.g. external JavaScript)
     * @return void
     */
    function assets() {
        if ( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) :
        ?>
            <style type="text/css">
                .attachments-field-wysiwyg-editor-wrap { background:#fff; }
            </style>
            <script>
                (function($) {

	                    var wpautop = true;

	                    // handle both initial and subsequent additions
	                    $(function() {
		                    if (typeof tinyMCE !== 'undefined') {
		                        wpautop = tinyMCE.settings.wpautop;
		                        $(document).on( 'attachments/new', function( event ) {
		                            $('.attachments-field-wysiwyg:not(.ready)').init_wysiwyg();
		                        });
		                        $('.attachments-field-wysiwyg').init_wysiwyg();
		                    }
	                    });

	                    $.fn.init_wysiwyg = function() {
		                    if (typeof tinyMCE !== 'undefined') {
		                        this.each(function() {

		                            $(this).addClass('ready');

		                            var input_id = $(this).attr('id');

		                            // create wysiwyg

		                            tinyMCE.settings.theme_advanced_buttons2 += ',code';
		                            tinyMCE.settings.wpautop = false;
		                            tinyMCE.execCommand('mceAddEditor', false, input_id);
		                            tinyMCE.settings.wpautop = wpautop;
		                        });
		                    }
	                    };

	                    $(document).on('attachments/sortable_start', function(event, ui) {
		                    if (typeof tinyMCE !== 'undefined') {
		                        tinyMCE.settings.wpautop = false;
		                        $('.attachments-field-wysiwyg').each(function() {
		                            tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr('id'));
		                        });
		                    }
	                    });

	                    $(document).on('attachments/sortable_stop', function(event, ui) {
		                    if (typeof tinyMCE !== 'undefined') {
		                        $('.attachments-field-wysiwyg').each(function() {
		                            tinyMCE.execCommand('mceAddEditor', false, $(this).attr('id'));
		                        });
		                        tinyMCE.settings.wpautop = wpautop;
		                    }
	                    });

                })(jQuery);
                </script>
        <?php
        endif;
    }



    /**
     * Filter the field value to appear within the input as expected
     * @param  string $value The field value
     * @param  Attachments_field $field The field object
     * @return string        The formatted value
     */
    function format_value_for_input( $value, $field = null  ) {
        return wp_richedit_pre( $value );
    }



    /**
     * Callback for 'wp_default_editor' action in constructor. Sets the default editor to TinyMCE.
     * @return string Editor name
     */
    function wp_default_editor() {
        return 'tinymce'; // html or tinymce
    }

}
