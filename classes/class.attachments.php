<?php

/**
 * Attachments
 *
 * Attachments allows you to simply append any number of items from your WordPress
 * Media Library to Posts, Pages, and Custom Post Types
 *
 * @package Attachments
 * @subpackage Main
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Declare our class
if ( !class_exists( 'Attachments' ) ) :

    /**
     * Main Attachments Class
     *
     * @since 3.0
     */

    class Attachments {

        private $version;                   // stores Attachments' version number
        private $url;                       // stores Attachments' URL
        private $dir;                       // stores Attachments' directory
        private $instances;                 // all registered Attachments instances
        private $instances_for_post_type;   // instance names that apply to the current post type
        private $fields;                    // stores all registered field types
        private $attachments;               // stores all of the Attachments for the given instance

        private $legacy             = false;            // whether or not there is legacy Attachments data
        private $legacy_pro         = false;            // whether or not there is legacy Attachment Pro data
        private $image_sizes        = array( 'full' );  // store all registered image sizes
        private $default_instance   = true;             // use the default instance?
        private $attachments_ref    = -1;               // flags where a get() loop last did it's thing
        private $meta_key           = 'attachments';    // our meta key
        private $valid_filetypes    = array(            // what WordPress considers to be valid file types
                    'image',
                    'video',
                    'text',
                    'audio',
                    'application'
                );



        /**
         * Constructor
         *
         * @since 3.0
         */
        function __construct( $instance = null, $post_id = null )
        {
            global $_wp_additional_image_sizes;

            // establish our environment variables

            $this->version  = '3.3';
            $this->url      = ATTACHMENTS_URL;
            $this->dir      = ATTACHMENTS_DIR;

            // includes
            include_once( ATTACHMENTS_DIR . 'upgrade.php' );
            include_once( ATTACHMENTS_DIR . '/classes/class.field.php' );

            // include our fields
            $this->fields = $this->get_field_types();

            // deal with our legacy issues if the user hasn't dismissed or migrated already
            $this->check_for_legacy_data();

            // set our image sizes
            $this->image_sizes = array_merge( $this->image_sizes, get_intermediate_image_sizes() );

            // hook into WP
            add_action( 'admin_enqueue_scripts',        array( $this, 'assets' ), 999, 1 );
            add_action( 'admin_enqueue_scripts',        array( $this, 'admin_pointer' ), 999 );

            // register our user-defined instances
            add_action( 'init',                         array( $this, 'do_actions_filters' ) );

            // determine which instances apply to the current post type
            add_action( 'init',                         array( $this, 'set_instances_for_current_post_type' ) );

            add_action( 'add_meta_boxes',               array( $this, 'meta_box_init' ) );
            add_action( 'admin_footer',                 array( $this, 'admin_footer' ) );
            add_action( 'save_post',                    array( $this, 'save' ) );

            // only show the Settings screen if it hasn't been explicitly disabled
            if( !( defined( 'ATTACHMENTS_SETTINGS_SCREEN' ) && ATTACHMENTS_SETTINGS_SCREEN === false ) )
                add_action( 'admin_menu',               array( $this, 'admin_page' ) );

            // with version 3 we'll be giving at least one admin notice
            add_action( 'admin_notices',                array( $this, 'admin_notice' ) );

            add_action( 'admin_head',                   array( $this, 'field_inits' ) );
            add_action( 'admin_print_footer_scripts',   array( $this, 'field_assets' ) );

            // set our attachments if necessary
            if( !is_null( $instance ) )
                $this->attachments = $this->get_attachments( $instance, $post_id );
        }



        /**
         * Stores whether or not this environment has active legacy Attachments/Pro data
         *
         * @since 3.1.3
         */
        function check_for_legacy_data()
        {
            // deal with our legacy issues if the user hasn't dismissed or migrated already
            if( false == get_option( 'attachments_migrated' ) && false == get_option( 'attachments_ignore_migration' ) )
            {
                $legacy_attachments_settings = get_option( 'attachments_settings' );

                if( $legacy_attachments_settings && is_array( $legacy_attachments_settings['post_types'] ) && count( $legacy_attachments_settings['post_types'] ) )
                {
                    // we have legacy settings, so we're going to use the post types
                    // that Attachments is currently utilizing

                    // the keys are the actual CPT names, so we need those
                    foreach( $legacy_attachments_settings['post_types'] as $post_type => $value )
                        if( $value )
                            $post_types[] = $post_type;

                    // set up our WP_Query args to grab anything with legacy data
                    $args = array(
                            'post_type'         => isset( $post_types ) ? $post_types : array(),
                            'post_status'       => 'any',
                            'posts_per_page'    => 1,
                            'meta_key'          => '_attachments',
                        );

                    $legacy         = new WP_Query( $args );
                    $this->legacy   = empty( $legacy->found_posts ) ? false : true;
                }
            }

            // deal with our legacy Pro issues if the user hasn't dismissed or migrated already
            if( false == get_option( 'attachments_pro_migrated' ) && false == get_option( 'attachments_pro_ignore_migration' ) )
            {
                // set up our WP_Query args to grab anything (really anything) with legacy data
                $args = array(
                        'post_type'         => get_post_types(),
                        'post_status'       => 'any',
                        'posts_per_page'    => 1,
                        'meta_key'          => '_attachments_pro',
                    );

                $legacy_pro         = new WP_Query( $args );
                $this->legacy_pro   = empty( $legacy_pro->found_posts ) ? false : true;
            }
        }



        /**
         * Facilitates searching for Attachments
         *
         * @since 3.3
         */
        function search( $query = null, $params = array() )
        {
            $defaults = array(
                    'attachment_id' => null,            // not searching for a single attachment ID
                    'instance'      => 'attachments',   // default instance
                    'post_type'     => null,            // search 'any' post type
                    'post_id'       => null,            // searching all posts
                    'post_status'   => 'publish',       // search only published posts
                    'fields'        => null,            // search all fields
                );

            $query  = is_null( $query ) ? null : sanitize_text_field( $query );
            $params = array_merge( $defaults, $params );

            // sanitize parameters
            $params['attachment_id']    = is_null( $params['attachment_id'] ) ? null : intval( $params['attachment_id'] );
            $params['instance']         = !is_string( $params['instance'] ) ? 'attachments' : sanitize_text_field( $params['instance'] );
            $params['post_type']        = is_null( $params['post_type'] ) ? 'any' : sanitize_text_field( $params['post_type'] );
            $params['post_id']          = is_null( $params['post_id'] ) ? null : intval( $params['post_id'] );

            $params['post_status']      = sanitize_text_field( $params['post_status'] );

            if( is_string( $params['fields'] ) )
                $params['fields']       = array( $params['fields'] );   // we always want an array

            // since we have an array for our fields, we need to loop through and sanitize
            for( $i = 0; $i < count( $params['fields'] ); $i++ )
                $params['fields'][$i] = sanitize_text_field( $params['fields'][$i] );

            // prepare our search args
            $args = array(
                    'nopaging'      => true,
                    'post_status'   => $params['post_status'],
                    'meta_query'    => array(
                            array(
                                    'key'       => 'attachments',
                                    'value'     => $query,
                                    'compare'   => 'LIKE'
                                )
                        ),
                );

            // append any applicable parameters that got passed to the original method call
            if( $params['post_type'] )
                $args['post_type'] = $params['post_type'];

            if( $params['post_id'] )
                $args['post__in'] = array( $params['post_id'] );    // avoid using 'p' or 'page_id'

            // we haven't utilized all parameters yet because they're meta-value based so we need to
            // do some parsing on our end to validate the returned results

            $possible_posts         = new WP_Query( $args );
            $potential_attachments  = false;  // stores valid attachments as restrictions are added

            if( $possible_posts->found_posts )
            {
                // we have results from the reliminary search, we need to quantify them
                while( $possible_posts->have_posts() )
                {
                    $possible_posts->next_post();
                    $possible_post_ids[] = $possible_posts->post->ID;
                }

                // now that we have our possible post IDs we can grab all Attachments for all of those posts
                foreach( $possible_post_ids as $possible_post_id )
                {
                    $possible_attachments = $this->get_attachments( $params['instance'], $possible_post_id );

                    foreach( $possible_attachments as $possible_attachment )
                        $potential_attachments[] = $possible_attachment;
                }

            }

            // if there aren't even any potential attachments, we'll just short circuit
            if( !$potential_attachments )
                return;

            // first we need to make sure that our query matches each attachment
            // we need to do this because the LIKE query returned the entire meta record,
            // not necessarily tied to any specific Attachment
            $total_potentials = count( $potential_attachments );
            for( $i = 0; $i < $total_potentials; $i++ )
            {
                $valid = false;

                // if we need to limit our search to specific fields, we'll do that here
                if( $params['fields'] )
                {
                    // we only want to check certain fields
                    foreach( $params['fields'] as $field )
                        if( isset( $potential_attachments[$i]->fields->$field ) )   // does the field exist?
                            if( strpos( strtolower( $potential_attachments[$i]->fields->$field ),
                                        strtolower( $query ) ) !== false ) // does the value match?
                                $valid = true;
                }
                else
                {
                    // we want to check all fields
                    foreach( $potential_attachments[$i]->fields as $field_name => $field_value )
                        if( strpos( strtolower( $field_value) , strtolower( $query ) ) !== false )
                            $valid = true;
                }

                if( !$valid )
                    unset( $potential_attachments[$i] );

                // now our potentials have been limited to each match the query based on any field
            }

            // limit to attachment ID if applicable
            if( $params['attachment_id'] )
            {
                $total_potentials = count( $potential_attachments );
                for( $i = 0; $i < $total_potentials; $i++ )
                    if( $potential_attachments[$i]->id != $params['attachment_id'] )
                        unset( $potential_attachments[$i] );
            }

            $this->attachments = array_values( $potential_attachments );
        }



        /**
         * Returns whether or not the current object has any Attachments
         *
         * @since 3.0
         */
        function exist()
        {
            return !empty( $this->attachments );
        }



        /**
         * Returns the number of Attachments
         *
         * @since 3.0.6
         */
        function total()
        {
            return count( $this->attachments );
        }



        /**
         * Returns the next Attachment for the current object and increments the index
         *
         * @since 3.0
         */
        function get()
        {
            $this->attachments_ref++;

            if( !count( $this->attachments ) || $this->attachments_ref >= count( $this->attachments ) )
                return false;

            return $this->attachments[$this->attachments_ref];
        }



        /**
         * Returns a specific Attachment
         *
         * @since 3.2
         */
        function get_single( $index )
        {
            return isset( $this->attachments[$index] ) ? $this->attachments[$index] : false;
        }



        /**
         * Returns the asset (array) for the current Attachment
         *
         * @since 3.0.6
         */
        function asset( $size = 'thumbnail', $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            // do we have our meta yet?
            if( !isset( $this->attachments[$index]->meta ) )
                $this->attachments[$index]->meta = wp_get_attachment_metadata( $this->attachments[$index]->id );

            // is it an image?
            if(
                isset( $this->attachments[$index]->meta['sizes'] ) &&  // is it an image?
                in_array( $size, $this->image_sizes ) )                                // do we have the right size?
            {
                $asset = wp_get_attachment_image_src( $this->attachments[$index]->id, $size );
            }
            else
            {
                // either it's not an image or we don't have the proper size, so we'll use the icon
                $asset = $this->icon();
            }

            return $asset;
        }



        /**
         * Returns the icon (array) for the current Attachment
         *
         * @since 3.0.6
         */
        function icon( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );
            $asset = wp_get_attachment_image_src( $this->attachments[$index]->id, null, true );
            return $asset;
        }



        /**
         * Returns an appropriate <img /> for the current Attachment if it's an image
         *
         * @since 3.0
         */
        function image( $size = 'thumbnail', $index = null )
        {
            $asset = $this->asset( $size );

            $image_src      = $asset[0];
            $image_width    = $asset[1];
            $image_height   = $asset[2];
            $index          = is_null( $index ) ? $this->attachments_ref : intval( $index );
            $image_alt      = get_post_meta( $this->attachments[$index]->id, '_wp_attachment_image_alt', true );

            $image = '<img src="' . $image_src . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $image_alt . '" />';

            return $image;
        }



        /**
         * Returns the URL for the current Attachment if it's an image
         *
         * @since 3.0
         */
        function src( $size = 'thumbnail', $index = null )
        {
            $asset = $this->asset( $size );
            return $asset[0];
        }



        /**
         * Returns the formatted filesize of the current Attachment
         *
         * @since 3.0
         */
        function filesize( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            if( !isset( $this->attachments[$index]->id ) )
                return false;

            $url        = wp_get_attachment_url( $this->attachments[$index]->id );
            $uploads    = wp_upload_dir();
            $file_path  = str_replace( $uploads['baseurl'], $uploads['basedir'], $url );

            $formatted = '0 bytes';
            if( file_exists( $file_path ) )
            {
                $formatted = size_format( @filesize( $file_path ) );
            }
            return $formatted;
        }



        /**
         * Returns the type of the current Attachment
         *
         * @since 3.0
         */
        function type( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            if( !isset( $this->attachments[$index]->id ) )
                return false;

            $attachment_mime = explode( '/', get_post_mime_type( $this->attachments[$index]->id ) );
            return isset( $attachment_mime[0] ) ? $attachment_mime[0] : false;
        }



        /**
         * Returns the subtype of the current Attachment
         *
         * @since 3.0
         */
        function subtype( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            if( !isset( $this->attachments[$index]->id ) )
                return false;

            $attachment_mime = explode( '/', get_post_mime_type( $this->attachments[$index]->id ) );
            return isset( $attachment_mime[1] ) ? $attachment_mime[1] : false;
        }



        /**
         * Returns the id of the current Attachment
         *
         * @since 3.0
         */
        function id( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            return isset( $this->attachments[$index]->id ) ? $this->attachments[$index]->id : false;
        }



        /**
         * Returns the $post->ID of the current Attachment
         *
         * @since 3.3
         */
        function post_id( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            return isset( $this->attachments[$index]->post_id ) ? $this->attachments[$index]->post_id : false;
        }



        /**
         * Returns the URL for the current Attachment
         *
         * @since 3.0
         */
        function url( $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            return isset( $this->attachments[$index]->id ) ? wp_get_attachment_url( $this->attachments[$index]->id ) : false;
        }



        /**
         * Returns the field value for the submitted field name
         *
         * @since 3.0
         */
        function field( $name = 'title', $index = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            return isset( $this->attachments[$index]->fields->$name ) ? $this->attachments[$index]->fields->$name : false;
        }



        /**
         * Fires all of our actions
         *
         * @since 3.0
         */
        function do_actions_filters()
        {
            // implement our default instance if appropriate
            if( !defined( 'ATTACHMENTS_DEFAULT_INSTANCE' ) )
                $this->register();

            // facilitate user-defined instance registration
            do_action( 'attachments_register', $this );
        }



        /**
         * Enqueues our necessary assets
         *
         * @since 3.0
         */
        function assets( $hook )
        {
            global $post;

            // we only want our assets on edit screens
            if( !empty( $this->instances_for_post_type ) && 'edit.php' != $hook && 'post.php' != $hook && 'post-new.php' != $hook )
                return;

            // we only want to enqueue if appropriate
            if( empty( $this->instances_for_post_type ) )
                return;

            $post_id = isset( $post->ID ) ? $post->ID : null;
            wp_enqueue_media( array( 'post' => $post_id ) );

            wp_enqueue_style( 'attachments', trailingslashit( $this->url ) . 'css/attachments.css', null, $this->version, 'screen' );

            wp_enqueue_script( 'attachments', trailingslashit( $this->url ) . 'js/attachments.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );
        }



        /**
         * Registers meta box(es) for the current edit screen
         *
         * @since 3.0
         */
        function meta_box_init()
        {
            $nonce_sent = false;

            if( !empty( $this->instances_for_post_type ) )
            {
                foreach( $this->instances_for_post_type as $instance )
                {
                    $instance_name      = $instance;
                    $instance           = (object) $this->instances[$instance];
                    $instance->name     = $instance_name;
                    $position           = isset($instance->position) ? $instance->position : 'normal';
                    $priority           = isset($instance->priority) ? $instance->priority : 'high';

                    add_meta_box( 'attachments-' . $instance_name, __( esc_attr( $instance->label ) ), array( $this, 'meta_box_markup' ), $this->get_post_type(), $position, $priority, array( 'instance' => $instance, 'setup_nonce' => !$nonce_sent ) );

                    $nonce_sent = true;
                }
            }
        }



        /**
         * Callback that outputs the meta box markup
         *
         * @since 3.0
         */
        function meta_box_markup( $post, $metabox )
        {
            // single out our $instance
            $instance = (object) $metabox['args']['instance'];

            if( $metabox['args']['setup_nonce'] )
                wp_nonce_field( 'attachments_save', 'attachments_nonce' );

            ?>

            <div id="attachments-<?php echo $instance->name; ?>" class="attachments-parent-container">
                <?php if( !empty( $instance->note ) ) : ?>
                    <div class="attachments-note"><?php echo apply_filters( 'the_content', $instance->note ); ?></div>
                <?php endif; ?>
                <div class="attachments-container attachments-<?php echo $instance->name; ?>"><?php
                        if( isset( $instance->attachments ) && !empty( $instance->attachments ) )
                        {
                            foreach( $instance->attachments as $attachment )
                            {
                                // we need to give our Attachment a uid to carry through to all the fields
                                $attachment->uid = uniqid();

                                // we'll create the attachment
                                $this->create_attachment( $instance->name, $attachment );
                            }
                        }
                    ?></div>
                <div class="attachments-invoke-wrapper">
                    <a class="button attachments-invoke"><?php _e( esc_attr( $instance->button_text ), 'attachments' ); ?></a>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var $element     = $('#attachments-<?php echo esc_attr( $instance->name ); ?>'),
                        title        = '<?php echo __( esc_attr( $instance->label ) ); ?>',
                        button       = '<?php echo __( esc_attr( $instance->modal_text ) ); ?>',
                        attachmentsframe;

                    $element.on( 'click', '.attachments-invoke', function( event ) {
                        var options, attachment;

                        event.preventDefault();

                        // if the frame already exists, open it
                        if ( attachmentsframe ) {
                            attachmentsframe.open();
                            return;
                        }

                        // set our seetings
                        attachmentsframe = wp.media({

                            title: title,

                            <?php if( $instance->limit < 0 || $instance->limit > 1 ) : ?>
                                multiple: true,
                            <?php endif; ?>

                            library: {
                                type: '<?php echo esc_attr( implode( ",", $instance->filetype ) ); ?>'
                            },

                            // Customize the submit button.
                            button: {
                                // Set the text of the button.
                                text: button
                            }
                        });

                        // set up our select handler
                        attachmentsframe.on( 'select', function() {

                            selection = attachmentsframe.state().get('selection');

                            if ( ! selection )
                                return;

                            // compile our Underscore template using Mustache syntax
                            _.templateSettings = {
                                variable : 'attachments',
                                interpolate : /\{\{(.+?)\}\}/g
                            }

                            var template = _.template($('script#tmpl-attachments-<?php echo $instance->name; ?>').html());

                            // loop through the selected files
                            selection.each( function( attachment ) {

                                // set our attributes to the template
                                attachment.attributes.attachment_uid = attachments_uniqid( 'attachmentsjs' );

                                // by default use the generic icon
                                attachment.attributes.attachment_thumb = attachment.attributes.icon;

                                // only thumbnails have sizes which is what we're on the hunt for
                                // TODO: this is a mess
                                if(attachments_isset(attachment.attributes)){
                                    if(attachments_isset(attachment.attributes.sizes)){
                                        if(attachments_isset(attachment.attributes.sizes.thumbnail)){
                                            if(attachments_isset(attachment.attributes.sizes.thumbnail.url)){
                                                // use the thumbnail
                                                attachment.attributes.attachment_thumb = attachment.attributes.sizes.thumbnail.url;
                                            }
                                        }
                                    }
                                }

                                var templateData = attachment.attributes;

                                // append the template
                                $element.find('.attachments-container').append(template(templateData));

                                // if we're in a sidebar we DO want to show the fields which are normally hidden on load via CSS
                                if($element.parents('#side-sortables')){
                                    $element.find('.attachments-attachment:last .attachments-fields').show();
                                }

                                // see if we need to set a default
                                // TODO: can we tie this into other field types (select, radio, checkbox)?
                                $element.find('.attachments-attachment:last .attachments-fields input, .attachments-attachment:last .attachments-fields textarea').each(function(){
                                    if($(this).data('default')){
                                        var meta_for_default = $(this).data('default');
                                        if(attachments_isset(attachment.attributes)){
                                            if(attachments_isset(attachment.attributes[meta_for_default])){
                                                $(this).val(attachment.attributes[meta_for_default]);
                                            }
                                        }
                                    }
                                });

                                $('body').trigger('attachments/new');

                                // if it wasn't an image we need to ditch the dimensions
                                if(!attachments_isset(attachment.attributes.width)||!attachments_isset(attachment.attributes.height)){
                                    $element.find('.attachments-attachment:last .dimensions').hide();
                                }
                            });
                        });

                        // open the frame
                        attachmentsframe.open();

                    });

                    $element.on( 'click', '.delete-attachment a', function( event ) {

                        var targetAttachment;

                        event.preventDefault();

                        targetAttachment = $(this).parents('.attachments-attachment');

                        targetAttachment.find('.attachment-meta').fadeOut(125);
                        targetAttachment.css('min-height',0).animate(
                            {
                                padding: 0,
                                margin: 0,
                                height: 0
                            },
                            600,
                            function(){
                                targetAttachment.remove();
                            }
                        );

                    } );

                });
            </script>
        <?php }



        /**
         * Support the inclusion of custom, user-defined field types
         * Borrowed implementation from Custom Field Suite by Matt Gibbs
         *      https://uproot.us/docs/creating-custom-field-types/
         *
         * @since 3.0
         **/
        function get_field_types()
        {
            $field_types = array(
                'text'      => ATTACHMENTS_DIR . 'classes/fields/class.field.text.php',
                'textarea'  => ATTACHMENTS_DIR . 'classes/fields/class.field.textarea.php',
                'select'    => ATTACHMENTS_DIR . 'classes/fields/class.field.select.php',
                'wysiwyg'   => ATTACHMENTS_DIR . 'classes/fields/class.field.wysiwyg.php',
            );

            // support custom field types
            // $field_types = apply_filters( 'attachments_fields', $field_types );

            $field_index = 0;
            foreach( $field_types as $type => $path )
            {
                // proceed with inclusion
                if( file_exists( $path ) )
                {
                    // include the file
                    include_once( $path );

                    // store the registered classes so we can single out what gets added
                    $existing_classes = get_declared_classes();

                    // we're going to use our Attachments class as a reference because
                    // during subsequent instantiations of Attachments (e.g. within template files)
                    // these field classes WILL NOT be added to the array again because
                    // we're using include_once() so that strategy is no longer useful

                    // determine it's class
                    $flag = array_search( 'Attachments_Field', $existing_classes );

                    // the field's class is next
                    $field_class = $existing_classes[$flag + $field_index + 1];

                    // create our link using our new field class
                    $field_types[$type] = $field_class;

                    $field_index++;
                }
            }

            // send it back
            return $field_types;
        }



        /**
         * Registers a field type for use within an instance
         *
         * @since 3.0
         */
        function register_field( $params = array() )
        {
            $defaults = array(
                    'name'      => 'title',
                    'type'      => 'text',
                    'label'     => __( 'Title', 'attachments' ),
                    'meta'      => array(),
                );

            $params = array_merge( $defaults, $params );

            // ensure it's a valid type
            if( !isset( $this->fields[$params['type']] ) )
               return false;

           // sanitize
           if( isset( $params['name'] ) )
               $params['name'] = str_replace( '-', '_', sanitize_title( $params['name'] ) );

            if( isset( $params['label'] ) )
                $params['label'] = __( esc_html( $params['label'] ) );

            if( !isset( $params['meta'] ) || !is_array( $params['meta'] ) )
                 $params['meta'] = array();

            // instantiate the class for this field and send it back
            return new $this->fields[ $params['type'] ]( $params['name'], $params['label'], $params['meta'] );
        }



        /**
         * Registers an Attachments instance
         *
         * @since 3.0
         */
        function register( $name = 'attachments', $params = array() )
        {
            $defaults = array(

                    // title of the meta box (string)
                    'label'         => __( 'Attachments', 'attachments' ),

                    // all post types to utilize (string|array)
                    'post_type'     => array( 'post', 'page' ),

                    // maximum number of Attachments (int) (-1 is unlimited)
                    'limit'         => -1,

                    // allowed file type(s) (array) (image|video|text|audio|application)
                    'filetype'      => null,    // no filetype limit

                    // include a note within the meta box (string)
                    'note'          => null,    // no note

                    // text for 'Attach' button (string)
                    'button_text'   => __( 'Attach', 'attachments' ),

                    // text for modal 'Attach' button (string)
                    'modal_text'    => __( 'Attach', 'attachments' ),

                    // meta box position (normal, side or advanced)
                    'position'      => 'normal',

                    // meta box priority (high, default, low, core)
                    'priority'      => 'high',

                    // fields for this instance (array)
                    'fields'        => array(
                        array(
                            'name'      => 'title',                         // unique field name
                            'type'      => 'text',                          // registered field type
                            'label'     => __( 'Title', 'attachments' ),    // label to display
                            'default'   => 'title',                         // default value upon selection
                        ),
                        array(
                            'name'      => 'caption',                       // unique field name
                            'type'      => 'wysiwyg',                      // registered field type
                            'label'     => __( 'Caption', 'attachments' ),  // label to display
                            'default'   => 'caption',                       // default value upon selection
                        ),
                    ),

                );

            $params = array_merge( $defaults, $params );

            // sanitize
            if( !is_array( $params['post_type'] ) )
                $params['post_type'] = array( $params['post_type'] );   // we always want an array

            if( !is_array( $params['filetype'] ) )
                $params['filetype'] = array( $params['filetype'] );     // we always want an array

            $params['label']        = esc_html( $params['label'] );
            $params['limit']        = intval( $params['limit'] );
            $params['note']         = esc_sql( $params['note'] );
            $params['button_text']  = esc_attr( $params['button_text'] );
            $params['modal_text']   = esc_attr( $params['modal_text'] );

            // make sure we've got valid filetypes
            if( is_array( $params['filetype'] ) )
            {
                foreach( $params['filetype'] as $key => $filetype )
                {
                    if( !in_array( $filetype, $this->valid_filetypes ) )
                    {
                        unset( $params['filetype'][$key] );
                    }
                }
            }

            // WordPress sanitizes post type names when registering, so we will too
            foreach( $params['post_type'] as $key => $post_type )
                $params['post_type'][$key] = sanitize_key( $post_type );


            // make sure the instance name is proper
            $instance = str_replace( '-', '_', sanitize_title( $name ) );

            // register the fields
            if( isset( $params['fields'] ) && is_array( $params['fields'] ) && count( $params['fields'] ) )
            {
                foreach( $params['fields'] as $field )
                {
                    // register the field
                    $this->register_field( $field );
                }
            }

            // set the instance
            $this->instances[$instance] = $params;

            // set the Attachments for this instance
            $this->instances[$instance]['attachments'] = $this->get_attachments( $instance );

        }



        /**
         * Gets the applicable Attachments instances for the current post type
         *
         * @since 3.0
         */
        function get_instances_for_post_type( $post_type = null )
        {
            $post_type = ( !is_null( $post_type ) && post_type_exists( $post_type ) ) ? $post_type : $this->get_post_type();

            $instances = array();

            if( !empty( $this->instances ) )
            {
                foreach( $this->instances as $name => $params )
                {
                    if( in_array( $post_type, $params['post_type'] ) )
                    {
                        $instances[] = $name;
                    }
                }
            }

            return $instances;
        }



        /**
         * Our own implementation of WordPress' get_post_type() as it's not
         * functional when we need it
         *
         * @since 3.0
         */
        function get_post_type()
        {
            global $post;

            // TODO: Retrieving the post_type at this point is ugly to say the least. This needs major cleanup.
            if( empty( $post->ID ) && isset( $_GET['post_type'] ) )
            {
                $post_type = str_replace( '-', '_', sanitize_title( $_GET['post_type'] ) ); // TODO: Better sanitization
            }
            elseif( !empty( $post->ID ) )
            {
                $post_type = get_post_type( $post->ID );
            }
            elseif( isset( $_GET['post'] ) )
            {
                $post_type = get_post_type( intval( $_GET['post'] ) );
            }
            else
            {
                $post_type = 'post';
            }

            return $post_type;
        }



        /**
         * Sets the applicable Attachments instances for the current post type
         *
         * @since 3.0
         */
        function set_instances_for_current_post_type()
        {
            // store the applicable instances for this post type
            $this->instances_for_post_type = $this->get_instances_for_post_type( $this->get_post_type() );
        }



        /**
         * Outputs HTML for a single Attachment within an instance
         *
         * @since 3.0
         */
        function create_attachment_field( $instance, $field, $attachment = null )
        {

            // the $field at this point is just the user-declared array
            // we need to make it a field object
            $type = $field['type'];

            if( isset( $this->fields[$type] ) )
            {
                $name           = sanitize_title( $field['name'] );
                $label          = esc_html( $field['label'] );
                $default        = isset( $field['default'] ) ? $field['default'] : false; // validated in the class
                $meta           = isset( $field['meta'] ) ? $field['meta'] : array();
                $value          = isset( $attachment->fields->$name ) ? $attachment->fields->$name : null;

                $field          = new $this->fields[$type]( $name, $label, $value, $meta );
                $field->value   = $field->format_value_for_input( $field->value );

                // does this field already have a unique ID?
                $uid = ( isset( $attachment->uid ) ) ? $attachment->uid : null;

                // TODO: make sure we've got a registered instance
                $field->set_field_instance( $instance, $field );
                $field->set_field_identifiers( $field, $uid );
                $field->set_field_type( $type );
                $field->set_field_default( $default );

                ?>
                <div class="attachments-attachment-field attachments-attachment-field-<?php echo $instance; ?> attachments-attachment-field-<?php echo $field->type; ?> attachment-field-<?php echo $field->name; ?>">
                    <div class="attachment-label attachment-label-<?php echo $instance; ?>">
                        <label for="<?php echo $field->field_id; ?>"><?php echo $field->label; ?></label>
                    </div>
                    <div class="attachment-field attachment-field-<?php echo $instance; ?>">
                        <?php echo $this->create_field( $instance, $field ); ?>
                    </div>
                </div>
            <?php
            }
            else
            {
                $field = false;
            }

            return $field;
        }



        /**
         * Outputs HTML for submitted field
         *
         * @since 3.0
         */
        function create_field( $instance, $field )
        {
            $field = (object) $field;

            // with all of our attributes properly set, we can output
            $field->html( $field );
        }



        /**
         * Outputs all the necessary markup for an Attachment
         *
         * @since 3.0
         */
        function create_attachment( $instance, $attachment = null )
        {
            ?>
                <div class="attachments-attachment attachments-attachment-<?php echo $instance; ?>">
                    <?php $array_flag = ( isset( $attachment->uid ) ) ? $attachment->uid : '{{ attachments.attachment_uid }}'; ?>

                    <input type="hidden" name="attachments[<?php echo $instance; ?>][<?php echo $array_flag; ?>][id]" value="<?php echo isset( $attachment->id ) ? $attachment->id : '{{ attachments.id }}' ; ?>" />

                    <?php
                        // since attributes can change over time (image gets replaced, cropped, etc.) we'll pull that info
                        if( isset( $attachment->id ) )
                        {
                            // we'll just use the full size since that's what Media in 3.5 uses
                            $attachment_meta        = wp_get_attachment_metadata( $attachment->id );

                            // only images return the 'file' key
                            if( !isset( $attachment_meta['file'] ))
                                $attachment_meta['file'] = get_attached_file( $attachment->id );

                            $attachment->width      = isset( $attachment_meta['width'] ) ? $attachment_meta['width'] : null;
                            $attachment->height     = isset( $attachment_meta['height'] ) ? $attachment_meta['height'] : null;
                            $attachment->filename   = end( explode( "/", $attachment_meta['file'] ) );

                            $attachment_mime        = explode( '/', get_post_mime_type( $attachment->id ) );
                            $attachment->type       = isset( $attachment_mime[0] ) ? $attachment_mime[0] : null;
                            $attachment->subtype    = isset( $attachment_mime[1] ) ? $attachment_mime[1] : null;
                        }
                    ?>

                    <div class="attachment-meta media-sidebar">
                        <?php
                            $thumbnail = isset( $attachment->id ) ? wp_get_attachment_image_src( $attachment->id, 'thumbnail', true ) : false;

                            $image = $thumbnail ? $thumbnail[0] : '{{ attachments.attachment_thumb }}';
                        ?>
                        <div class="attachment-thumbnail">
                            <img src="<?php echo $image; ?>" alt="Thumbnail" />
                        </div>
                        <div class="attachment-details attachment-info details">
                            <div class="filename"><?php echo isset( $attachment->filename ) ? $attachment->filename : '{{ attachments.filename }}' ; ?></div>
                            <?php if( ( isset( $attachment->id ) && isset( $attachment->width ) ) || !isset( $attachment->id ) ) : ?>
                                <div class="dimensions"><?php echo isset( $attachment->width ) ? $attachment->width : '{{ attachments.width }}' ; ?> &times; <?php echo isset( $attachment->height ) ? $attachment->height : '{{ attachments.height }}' ; ?></div>
                            <?php endif; ?>
                            <div class="delete-attachment"><a href="#"><?php _e( 'Remove', 'attachments' ); ?></a></div>
                            <div class="attachments-attachment-fields-toggle"><a href="#"><?php _e( 'Toggle Fields', 'attachments' ); ?></a></div>
                        </div>
                    </div>

                    <div class="attachments-handle"><img src="<?php echo trailingslashit( $this->url ) . 'images/handle.gif'; ?>" alt="Handle" width="20" height="20" /></div>

                    <div class="attachments-fields">
                        <?php
                            foreach( $this->instances[$instance]['fields'] as $field )
                                $field_ref = $this->create_attachment_field( $instance, $field, $attachment );
                        ?>
                    </div>

                </div>
            <?php
        }



        /**
         * Callback to fire the assets() function for reach registered field
         *
         * @since 3.1
         */
        function field_assets()
        {
            global $post;

            // we only want to enqueue if we're on an edit screen and it's applicable
            if( empty( $this->instances_for_post_type ) || empty( $post ) )
                return;

            // all metaboxes have been put in place, we can now determine which field assets need to be included

            // first we'll get a list of the field types on screen
            $fieldtypes = array();
            foreach( $this->instances_for_post_type as $instance )
                foreach( $this->instances[$instance]['fields'] as $field )
                    $fieldtypes[] = $field['type'];

            // we only want to dump out assets once for each field type
            $fieldtypes = array_unique( $fieldtypes );

            // loop through and dump out all the assets
            foreach( $fieldtypes as $fieldtype )
            {
                $field = new $this->fields[$fieldtype];
                $field->assets();
            }
        }



        /**
         * Callback to fire the init() function for reach registered field
         *
         * @since 3.1
         */
        function field_inits()
        {
            global $post;

            // we only want to enqueue if we're on an edit screen and it's applicable
            if( empty( $this->instances_for_post_type ) || empty( $post ) )
                return;

            // all metaboxes have been put in place, we can now determine which field assets need to be included

            // first we'll get a list of the field types on screen
            $fieldtypes = array();
            foreach( $this->instances_for_post_type as $instance )
                foreach( $this->instances[$instance]['fields'] as $field )
                    $fieldtypes[] = $field['type'];

            // we only want to dump out assets once for each field type
            $fieldtypes = array_unique( $fieldtypes );

            // loop through and dump out all the assets
            foreach( $fieldtypes as $fieldtype )
            {
                $field = new $this->fields[$fieldtype];
                $field->init();
            }
        }



        /**
         * Outputs all necessary Backbone templates
         * Each Backbone template includes each field present in an instance
         *
         * @since 3.0
         */
        function admin_footer()
        {
            if( !empty( $this->instances_for_post_type ) )
            { ?>
                <script type="text/javascript">
                    var ATTACHMENTS_VIEWS = {};
                </script>
            <?php
                foreach( $this->instances_for_post_type as $instance ) : ?>
                    <script type="text/template" id="tmpl-attachments-<?php echo $instance; ?>">
                        <?php $this->create_attachment( $instance ); ?>
                    </script>
                <?php endforeach;
            }
        }



        /**
         * Saves our Attachments metadata when the post is saved
         *
         * @since 3.0
         */
        function save( $post_id )
        {
            // is the user logged in?
            if( !is_user_logged_in() )
                return $post_id;

            // is the nonce set?
            if( !isset( $_POST['attachments_nonce'] ) )
                return $post_id;

            // is the nonce valid?
            if( !wp_verify_nonce( $_POST['attachments_nonce'], 'attachments_save' ) )
                return $post_id;

            // can this user edit this post?
            if( !current_user_can( 'edit_post', $post_id ) )
                return $post_id;

            // passed authentication, proceed with save

            // if the user deleted all Attachments we won't have our key
            $attachments_meta = isset( $_POST['attachments'] ) ? $_POST['attachments'] : array();

            // final data store
            $attachments = array();

            // loop through each submitted instance
            foreach( $attachments_meta as $instance => $instance_attachments )
            {
                // loop through each Attachment of this instance
                foreach( $instance_attachments as $key => $attachment )
                {
                    // since we're using JSON for storage in the database, we need
                    // to make sure that characters are encoded that would otherwise
                    // break the JSON
                    if( isset( $attachment['fields'] ) && is_array( $attachment['fields'] ) )
                    {

                        foreach( $attachment['fields'] as $key => $field_value )
                        {
                            // take care of our returns
                            $field_value = str_replace( "\r\n", "\n", $field_value );
                            $field_value = str_replace( "\r", "\n", $field_value );

                            // we dont want to strip out our newlines so we're going to flag them
                            $field_value = str_replace("\n", "%%ATTACHMENTS_NEWLINE%%", $field_value );

                            // slashes were already added so we're going to strip them
                            $field_value = stripslashes_deep( $field_value );

                            // put back our newlines
                            $field_value = str_replace("%%ATTACHMENTS_NEWLINE%%", "\\n", $field_value );

                            // encode the whole thing
                            $field_value = $this->encode_field_value( $field_value );

                            // encode things properly
                            $attachment['fields'][$key] = $field_value;
                        }
                    }

                    $attachments[$instance][] = $attachment;
                }
            }

            if( !empty( $attachments ) )
            {
                // we're going to store JSON (JSON_UNESCAPED_UNICODE is PHP 5.4+)
                $attachments = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $attachments, JSON_UNESCAPED_UNICODE ) : json_encode( $attachments );

                // we're going to wipe out any existing Attachments meta (because we'll put it back)
                update_post_meta( $post_id, $this->meta_key, $attachments );
            }
            else
            {
                // there are no attachments so we'll clean up the record
                delete_post_meta( $post_id, $this->meta_key );
            }

            return $post_id;
        }



        /**
         * Recursive function to encode a field type before saving
         * @param  mixed $field_value The field value
         * @return mixed              The encoded field value
         *
         * @since 3.3
         */
        function encode_field_value( $field_value = null )
        {
            if( is_null( $field_value ) )
                return;

            if( is_object( $field_value ) )
            {
                $input = get_object_vars( $field_value );

                foreach( $input as $key => $val )
                    $field_value[$key] = $this->encode_field_value( $val );

                $field_value = (object) $field_value;
            }
            elseif( is_array( $field_value ) )
            {
                foreach( $field_value as $key => $val )
                    $field_value[$key] = $this->encode_field_value( $val );
            }
            else
                $field_value = htmlentities( $field_value, ENT_QUOTES, 'UTF-8' );

            return $field_value;
        }



        /**
         * Retrieves all Attachments for the submitted instance and post ID
         *
         * @since 3.0
         */
        function get_attachments( $instance = null, $post_id = null )
        {
            $post_id = $this->determine_post_id( $post_id );

            if( !$post_id )
                return false;

            $attachments        = array();
            $attachments_raw    = $this->get_attachments_metadata( $post_id );

            // we need to decode the fields (that were encoded during save) and run them through
            // their format_value_for_input as defined in it's class
            if( !is_null( $instance ) && is_string( $instance ) && isset( $attachments_raw->$instance ) )
            {
                foreach( $attachments_raw->$instance as $attachment )
                    $attachments[] = $this->process_attachment( $attachment, $instance );
            }
            elseif( is_null( $instance ) )
            {
                // return them all, regardless of instance
                foreach( $attachments_raw as $instance => $attachments_unprocessed )
                    foreach( $attachments_unprocessed as $unprocessed_attachment )
                        $attachments[] = $this->process_attachment( $unprocessed_attachment, $instance );
            }

            // tack on the post ID for each attachment
            for( $i = 0; $i < count( $attachments ); $i++ )
                $attachments[$i]->post_id = $post_id;

            return $attachments;
        }



        /**
         * Retrieves the post_meta record (saved as JSON) and processes it
         * @param  int $post_id The post ID
         * @return object          Attachments for the post
         *
         * @since 3.3
         */
        function get_attachments_metadata( $post_id )
        {
            $post_id = intval( $post_id );

            // grab our JSON and decode it
            $attachments_json   = get_post_meta( $post_id, $this->meta_key, true );
            $attachments_raw    = is_string( $attachments_json ) ? json_decode( $attachments_json ) : false;

            return $attachments_raw;
        }



        /**
         * Determines a proper post ID based on whether one was passed, the global $post object, or a GET variable
         * @param  int $post_id Desired post ID
         * @return mixed          The understood post ID
         */
        function determine_post_id( $post_id = null )
        {
            global $post;

            // if a post id was passed, we'll use it
            if( !is_null( $post_id ) )
            {
                $post_id = intval( $post_id );
            }
            elseif( is_null( $post_id ) && is_object( $post ) && isset( $post->ID ) )
            {
                $post_id = $post->ID;
            }
            elseif( isset( $_GET['post'] ) )
            {
                $post_id = intval( $_GET['post'] );
            }
            else
            {
                // no post ID, nothing to do...
                $post_id = false;
            }

            return $post_id;
        }



        /**
         * Processes Attachment (including fields) in preparation for return
         * @param  object $attachment An Attachment object as it was stored as post metadata
         * @return object             Post-processed Attachment data
         *
         * @since 3.3
         */
        function process_attachment( $attachment, $instance )
        {
            if( !is_object( $attachment ) || !is_string( $instance ) )
                return $attachment;

            if( is_object( $attachment->fields ) )
            {
                foreach( $attachment->fields as $key => $value )
                {
                    // loop through the instance fields to get the type
                    if( isset( $this->instances[$instance]['fields'] ) )
                    {
                        $type = '';
                        foreach( $this->instances[$instance]['fields'] as $field )
                        {
                            if( isset( $field['name'] ) && $field['name'] == $key )
                            {
                                $type = isset( $field['type'] ) ? $field['type'] : false;
                                break;
                            }
                        }
                        if( isset( $this->fields[$type] ) )
                        {
                            // we need to decode the html entities that were encoded for the save
                            $attachment->fields->$key = $this->decode_field_value( $attachment->fields->$key );
                        }
                        else
                        {
                            // the type doesn't exist
                            $attachment->fields->$key = false;
                        }
                    }
                    else
                    {
                        // this was a theme file request, just grab it
                        $attachment->fields->$key = $this->decode_field_value( $attachment->fields->$key );
                    }
                }
            }
            return $attachment;
        }



        /**
         * Recursive function to decode a field value when retrieving it
         * @param  mixed $field_value The field value
         * @return mixed              The encoded field value
         *
         * @since 3.3
         */
        function decode_field_value( $field_value = null )
        {
            if( is_null( $field_value ) )
                return;

            if( is_object( $field_value ) )
            {
                $input = get_object_vars( $field_value );

                foreach( $input as $key => $val )
                    $field_value[$key] = $this->decode_field_value( $val );

                $field_value = (object) $field_value;
            }
            elseif( is_array( $field_value ) )
            {
                foreach( $field_value as $key => $val )
                    $field_value[$key] = $this->decode_field_value( $val );
            }
            else
                $field_value = html_entity_decode( $field_value, ENT_QUOTES, 'UTF-8' );

            return $field_value;
        }



        /**
         * Determines whether or not there is 'active' legacy data the user may not know about
         *
         * @since 3.0
         */
        function has_outstanding_legacy_data()
        {
            if(
               // migration has not taken place and we have legacy data
               ( false == get_option( 'attachments_migrated' ) && !empty( $this->legacy ) )

               &&

               // we're not intentionally ignoring the message
               ( false == get_option( 'attachments_ignore_migration' ) )
            )
            {
                return true;
            }
            else
            {
                return false;
            }
        }



        /**
         * Outputs a WordPress message to notify user of legacy data
         *
         * @since 3.0
         */
        function admin_notice()
        {

            if( $this->has_outstanding_legacy_data() && ( isset( $_GET['page'] ) && $_GET['page'] !== 'attachments' || !isset( $_GET['page'] ) ) ) : ?>
                <div class="message updated" id="message">
                    <p><?php _e( '<strong>Attachments has detected legacy Attachments data.</strong> A lot has changed since Attachments 1.x.' ,'attachments' ); ?> <a href="options-general.php?page=attachments&amp;overview=1"><?php _e( 'Find out more', 'attachments' ); ?>.</a></p>
                </div>
            <?php endif;
        }



        /**
         * Implements our WordPress pointer if necessary
         *
         * @since 3.0
         */
        function admin_pointer( $hook_suffix )
        {

            // Assume pointer shouldn't be shown
            $enqueue_pointer_script_style = false;

            // Get array list of dismissed pointers for current user and convert it to array
            $dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

            // Check if our pointer is not among dismissed ones
            if( $this->legacy && !in_array( 'attachments_legacy', $dismissed_pointers ) ) {
                $enqueue_pointer_script_style = true;

                // Add footer scripts using callback function
                add_action( 'admin_print_footer_scripts', array( $this, 'pointer_legacy' ) );
            }

            // Enqueue pointer CSS and JS files, if needed
            if( $enqueue_pointer_script_style ) {
                wp_enqueue_style( 'wp-pointer' );
                wp_enqueue_script( 'wp-pointer' );
            }
        }



        /**
         * Pointer that calls attention to legacy data
         *
         * @since 3.0
         */
        function pointer_legacy()
        {
            $pointer_content  = "<h3>". __( esc_html( 'Attachments 3.0 brings big changes!' ), 'attachments' ) ."</h3>";
            $pointer_content .= "<p>". __( esc_html( 'It is very important that you take a few minutes to see what has been updated. The changes will affect your themes/plugins.' ), 'attachments' ) ."</p>";
            ?>

            <script type="text/javascript">
            jQuery(document).ready( function($) {
                $('#message a').pointer({
                    content:'<?php echo $pointer_content; ?>',
                    position:{
                        edge:'top',
                        align:'center'
                    },
                    pointerWidth:350,
                    close:function() {
                        $.post( ajaxurl, {
                            pointer: 'attachments_legacy',
                            action: 'dismiss-wp-pointer'
                        });
                    }
                }).pointer('open');
            });
            </script>
            <?php
        }



        /**
         * Callback to implement our Settings page
         *
         * @since 3.0
         */
        function admin_page()
        {
            add_options_page( 'Settings', __( 'Attachments', 'attachments' ), 'manage_options', 'attachments', array( $this, 'options_page' ) );
        }



        /**
         * Callback to output our Settings page markup
         *
         * @since 3.0
         */
        function options_page()
        {
            include_once( ATTACHMENTS_DIR . '/views/options.php' );
        }

    }

endif; // class_exists check

new Attachments();
