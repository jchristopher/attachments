<?php

/**
 * Attachments WYSIWYG field
 *
 * @package Attachments
 * @subpackage Main
 */

class Attachments_Field_WYSIWYG extends Attachments_Field implements Attachments_Field_Template
{

    function __construct( $name = 'name', $label = 'Name', $value = null )
    {
        parent::__construct( $name, $label, $value );

        add_filter( 'wp_default_editor',    array( $this, 'wp_default_editor' ) );
        add_action('admin_head',            array( $this, 'admin_head' ) );
    }

    function admin_head()
    {
        global $post;

        // ensure we've got TinyMCE to work with
        $has_editor = post_type_supports( $post->post_type, 'editor' );
        add_post_type_support( $post->post_type, 'editor' );

        if( !$has_editor )
            echo '<style type="text/css">#poststuff .postarea { display: none; }</style>';
    }

    function html( $field )
    {
    ?>
        <div class="wp-editor-wrap attachments-field-wysiwyg-editor-wrap">
            <div class="wp-editor-container">
                <textarea name="<?php esc_attr_e( $field->field_name ); ?>" id="<?php esc_attr_e( $field->field_id ); ?>" class="wp-editor-area attachments attachments-field attachments-field-wysiwyg attachments-field-<?php esc_attr_e( $field->field_name ); ?> attachments-field-<?php esc_attr_e( $field->field_id ); ?>" rows="10"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }

    function assets()
    {
        if( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) :
        ?>
            <style type="text/css">
                .attachments-field-wysiwyg-editor-wrap { background:#fff; }
            </style>
            <script>
                (function($) {

                    var wpautop = true;

                    // handle both initial and subsequent additions
                    $(function() {
                        wpautop = tinyMCE.settings.wpautop;
                        $(document).on( 'attachments/new', function( event ) {
                            $('.attachments-field-wysiwyg:not(.ready)').init_wysiwyg();
                        });
                        $('.attachments-field-wysiwyg').init_wysiwyg();
                    });

                    $.fn.init_wysiwyg = function() {
                        this.each(function() {

                            $(this).addClass('ready');

                            var input_id = $(this).attr('id');

                            // create wysiwyg

                            tinyMCE.settings.theme_advanced_buttons2 += ',code';
                            tinyMCE.settings.wpautop = false;
                            tinyMCE.execCommand('mceAddControl', false, input_id);
                            tinyMCE.settings.wpautop = wpautop;
                        });
                    };

                    $(document).on('attachments/sortable_start', function(event, ui) {
                        tinyMCE.settings.wpautop = false;
                        $('.attachments-field-wysiwyg').each(function() {
                            tinyMCE.execCommand('mceRemoveControl', false, $(this).attr('id'));
                        });
                    });

                    $(document).on('attachments/sortable_stop', function(event, ui) {
                        $('.attachments-field-wysiwyg').each(function() {
                            tinyMCE.execCommand('mceAddControl', false, $(this).attr('id'));
                        });
                        tinyMCE.settings.wpautop = wpautop;
                    });
                })(jQuery);
                </script>
        <?php
        endif;
    }

    function format_value_for_input( $value, $field = null  )
    {
        return wp_richedit_pre( $value );
    }

    function wp_default_editor()
    {
        return 'tinymce'; // html or tinymce
    }

}