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
if( !class_exists( 'Attachments' ) ) :

    /**
     * Main Attachments Class
     *
     * @since 3.0
     */

    class Attachments
    {
        private $version;                   // stores Attachments' version number
        private $url;                       // stores Attachments' URL
        private $dir;                       // stores Attachments' directory
        private $instances;                 // all registered Attachments instances
        private $instances_for_post_type;   // instance names that apply to the current post type
        private $fields;                    // stores all registered field types
        private $attachments;               // stores all of the Attachments for the given instance

        private $image_sizes        = array( 'full' );      // store all registered image sizes
        private $attachments_ref    = -1;                   // flags where a get() loop last did it's thing
        private $meta_key           = 'attachments';        // our meta key
        private $valid_filetypes    = array(                // what WordPress considers to be valid file types
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

            $this->version  = '3.4.3';
            $this->url      = ATTACHMENTS_URL;
            $this->dir      = ATTACHMENTS_DIR;

            // includes
            include_once( ATTACHMENTS_DIR . 'upgrade.php' );
            include_once( ATTACHMENTS_DIR . '/classes/class.attachments.legacy.php' );
            include_once( ATTACHMENTS_DIR . '/classes/class.attachments.search.php' );
            include_once( ATTACHMENTS_DIR . '/classes/class.field.php' );

            // include our fields
            $this->fields = $this->get_field_types();

            // set our image sizes
            $this->image_sizes = array_merge( $this->image_sizes, get_intermediate_image_sizes() );

            // set up l10n
            add_action( 'plugins_loaded',               array( $this, 'l10n' ) );

            // load extensions
            add_action( 'plugins_loaded',               array( $this, 'load_extensions' ) );

            // hook into WP
            add_action( 'admin_enqueue_scripts',        array( $this, 'assets' ), 999, 1 );

            // register our user-defined instances
            add_action( 'init',                         array( $this, 'setup_instances' ) );

            // determine which instances apply to the current post type
            add_action( 'init',                         array( $this, 'set_instances_for_current_post_type' ) );

            add_action( 'add_meta_boxes',               array( $this, 'meta_box_init' ) );
            add_action( 'admin_footer',                 array( $this, 'admin_footer' ) );
            add_action( 'save_post',                    array( $this, 'save' ) );

            // only show the Settings screen if it hasn't been explicitly disabled
            if( !( defined( 'ATTACHMENTS_SETTINGS_SCREEN' ) && ATTACHMENTS_SETTINGS_SCREEN === false ) )
                add_action( 'admin_menu',               array( $this, 'admin_page' ) );

            add_action( 'admin_head',                   array( $this, 'field_inits' ) );
            add_action( 'admin_print_footer_scripts',   array( $this, 'field_assets' ) );

            add_action( 'admin_init',                   array( $this, 'admin_init' ) );

            // execution of actions varies depending on whether we're in the admin or not and an instance was passed
            if( is_admin() )
            {
                add_action( 'after_setup_theme', array( $this, 'apply_init_filters' ) );
                $this->attachments = $this->get_attachments( $instance, $post_id );
            }
            elseif( !is_null( $instance ) )
            {
                $this->apply_init_filters();
                $this->attachments = $this->get_attachments( $instance, $post_id );
            }

        }



        /**
         * Callback for WordPress' admin_init action
         *
         * @since 3.4.3
         */
        function admin_init()
        {
            if( current_user_can( 'delete_posts' ) )
                add_action( 'delete_post', array( $this, 'handle_wp_post_delete' ), 10 );
        }



        /**
         * Getter for the existing fields
         *
         * @return array
         * @since 3.5
         */
        function get_fields()
        {
            return $this->fields;
        }



        /**
         * Callback for WordPress' delete_post action. Searches all saved Attachments
         * data for any records using a deleted attachment. If found, the record is removed.
         *
         * @param int $pid Post ID
         * @since 3.4.3
         */
        function handle_wp_post_delete( $pid )
        {
            // check to make sure it was an attachment
            if( 'attachment' != get_post_type( $pid ) )
                return;

            // if a user deletes an attachment from the Media library (but it's been used
            // in Attachments somewhere else) we need to clean that up...

            // we hook into delete_post because the only other option is to filter
            // each Attachment when retrieving Attachments via get_attachments()
            // which could potentially be a ton of database calls

            // so we're going to use the search class to find all instances
            // for any occurrence of the deleted attachment (which has an ID of $pid)

            if( is_array( $this->instances ) )
            {
                foreach( $this->instances as $instance => $details )
                {
                    $search_args = array(
                      'instance'      => $instance,
                      'attachment_id' => intval( $pid ),
                    );

                    $this->search( null, $search_args );

                    if( $this->exist() )
                    {
                        // we've got a hit (e.g. an existing post uses the deleted attachment)
                        while( $attachment = $this->get() )
                        {
                            $post_id = $attachment->post_id;

                            // we'll use the post ID to snag the details
                            $post_attachments = $this->get_attachments_metadata( $post_id );

                            if( is_object( $post_attachments ) )
                            {
                                foreach( $post_attachments as $existing_instance => $existing_instance_attachments )
                                    foreach( $existing_instance_attachments as $existing_instance_attachment_key => $existing_instance_attachment )
                                        if( $pid == intval( $existing_instance_attachment->id ) )
                                            unset( $post_attachments->{$existing_instance}[$existing_instance_attachment_key] );

                                // saving routine assumes array from POST so we'll typecast it
                                $post_attachments = (array) $post_attachments;

                                // save the revised Attachments metadata
                                $this->save_metadata( $post_id, $post_attachments );
                            }
                        }
                    }
                }
            }
        }



        /**
         * Getter for the current meta_key
         *
         * @since 3.4.2
         */
        function get_meta_key()
        {
            return $this->meta_key;
        }



        /**
         * Getter for our URL
         *
         * @since 3.5
         */
        function get_url()
        {
            return $this->url;
        }



        /**
         * Register our textdomain for l10n
         *
         * @since 3.4.2
         */
        function l10n()
        {
            load_plugin_textdomain( 'attachments', false, trailingslashit( ATTACHMENTS_DIR ) . 'languages' );
        }



        function load_extensions()
        {
            do_action( 'attachments_extension', $this );
        }



        /**
         * Various initialization filter triggers
         *
         * @since 3.4
         */
        function apply_init_filters()
        {
            // allows a different meta_key to be used
            $this->meta_key = apply_filters( 'attachments_meta_key', $this->meta_key );
        }



        /**
         * Facilitates searching for Attachments
         *
         * @since 3.3
         */
        function search( $query = null, $params = array() )
        {
            $results            = new AttachmentsSearch( $query, $params );
            $this->attachments  = $results->results;
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
            if( isset( $this->attachments[$index]->meta['sizes'] ) )
            {
                $asset = wp_get_attachment_image_src( $this->attachments[$index]->id, $size );
            }
            else
            {
                // either it's not an image or we don't have the proper size, so we'll use the icon
                $asset = $this->icon( $index );
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
         * Returns the date for the current Attachment
         * @author Hasin Hayder
         *
         * @since 3.4.1
         */
        function date( $d = "d/m/Y", $index = null )
        {
            $index  = is_null( $index ) ? $this->attachments_ref : intval( $index );
            $date   = get_the_time( $d, $this->attachments[$index]->id );

            return $date;
        }



        /**
         * Returns an appropriate <img /> for the current Attachment if it's an image
         *
         * @since 3.0
         */
        function image( $size = 'thumbnail', $index = null )
        {
            $asset = $this->asset( $size, $index );

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
            $asset = $this->asset( $size, $index );
            return $asset[0];
        }



        /**
         * Returns the width of the current Attachment if it's an image
         *
         * @since 3.0
         */
        function width( $size = 'thumbnail', $index = null )
        {
            $asset = $this->asset( $size, $index );
            return $asset[1];
        }



        /**
         * Returns the height of the current Attachment if it's an image
         *
         * @since 3.0
         */
        function height( $size = 'thumbnail', $index = null )
        {
            $asset = $this->asset( $size, $index );
            return $asset[2];
        }



        /**
         * Returns the formatted filesize of the current Attachment
         *
         * @since 3.0
         */
        function filesize( $index = null, $size = null )
        {
            $index = is_null( $index ) ? $this->attachments_ref : intval( $index );

            if( !isset( $this->attachments[$index]->id ) )
                return false;

            // if an image size is passed along, use that
            $url        = is_string( $size ) ? $this->src( $size, $index ) : wp_get_attachment_url( $this->attachments[$index]->id );
            $uploads    = wp_upload_dir();
            $file_path  = str_replace( $uploads['baseurl'], $uploads['basedir'], $url );

            $formatted = '0 bytes';
            if( file_exists( $file_path ) )
                $formatted = size_format( @filesize( $file_path ) );

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

            $attachment_mime = $this->get_mime_type( $this->attachments[$index]->id );
            return $attachment_mime;
        }



        /**
         * Retrieves the mime type for a WordPress $post ID
         *
         * @since 3.4.2
         */
        function get_mime_type( $id = null )
        {
            $attachment_mime = explode( '/', get_post_mime_type( intval( $id ) ) );
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
        function setup_instances()
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
                    // facilitate more fine-grained meta box positioning than post type
                    $applicable         = apply_filters( "attachments_location_{$instance}", true, $instance );

                    // obtain other details about instance
                    $instance_name      = $instance;
                    $instance           = (object) $this->instances[$instance];
                    $instance->name     = $instance_name;
                    $position           = isset($instance->position) ? $instance->position : 'normal';
                    $priority           = isset($instance->priority) ? $instance->priority : 'high';

                    if( $applicable )
                        add_meta_box(
                            'attachments-' . $instance_name,
                            __( esc_attr( $instance->label ) ),
                            array( $this, 'meta_box_markup' ),
                            $this->get_post_type(),
                            $position,
                            $priority,
                            array(
                                'instance'      => $instance,
                                'setup_nonce'   => !$nonce_sent
                            )
                        );

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

            <div id="attachments-<?php echo $instance->name; ?>" class="attachments-parent-container<?php if( $instance->append == false ) : ?> attachments-prepend<?php endif; ?>">
                <?php if( !empty( $instance->note ) ) : ?>
                    <div class="attachments-note"><?php echo apply_filters( 'the_content', $instance->note ); ?></div>
                <?php endif; ?>
                <?php if( $instance->append == false ) : ?>
                    <div class="attachments-invoke-wrapper">
                        <a class="button attachments-invoke"><?php _e( esc_attr( $instance->button_text ), 'attachments' ); ?></a>
                    </div>
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
                <?php if( $instance->append == true ) : ?>
                    <div class="attachments-invoke-wrapper">
                        <a class="button attachments-invoke"><?php _e( esc_attr( $instance->button_text ), 'attachments' ); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var $element     = $('#attachments-<?php echo esc_attr( $instance->name ); ?>'),
                        title        = '<?php echo __( esc_attr( $instance->label ) ); ?>',
                        button       = '<?php echo __( esc_attr( $instance->modal_text ) ); ?>',
                        router       = '<?php echo __( esc_attr( $instance->router ) ); ?>',
                        limit        = <?php echo intval( $instance->limit ); ?>,
                        existing     = <?php echo ( isset( $instance->attachments ) && !empty( $instance->attachments ) ) ? count( $instance->attachments ): 0; ?>,
                        attachmentsframe;

                    $element.on( 'click', '.attachments-invoke', function( event ) {
                        var attachment;

                        event.preventDefault();

                        existing = $element.find('.attachments-container > .attachments-attachment').length;
                        if( limit > -1 && existing >= limit ){
                            alert('<?php _e( "Currently limited to", "attachments" ); ?> ' + limit);
                            return false;
                        }

                        // if the frame already exists, open it
                        if ( attachmentsframe ) {
                            attachmentsframe.open();
                            attachmentsframe.content.mode(router);
                            return;
                        }

                        // set our settings
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
                        attachmentsframe.on( 'select', function(){

                            var selection = attachmentsframe.state().get('selection');

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

                                // make sure we respect the limit
                                if( limit > -1 && existing >= limit ){
                                    return;
                                }

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
                                $element.find('.attachments-container').<?php if( $instance->append ) : ?>append<?php else : ?>prepend<?php endif; ?>(template(templateData));

                                // update the number of existing Attachments
                                existing = $element.find('.attachments-container > .attachments-attachment').length;

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
                        attachmentsframe.content.mode(router);

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

                    // meta box position (string) (normal, side or advanced)
                    'position'      => 'normal',

                    // meta box priority (string) (high, default, low, core)
                    'priority'      => 'high',

                    // maximum number of Attachments (int) (-1 is unlimited)
                    'limit'         => -1,

                    // allowed file type(s) (array) (image|video|text|audio|application)
                    'filetype'      => null,    // no filetype limit

                    // include a note within the meta box (string)
                    'note'          => null,    // no note

                    // by default new Attachments will be appended to the list
                    // but you can have then prepend if you set this to false
                    'append'        => true,

                    // text for 'Attach' button (string)
                    'button_text'   => __( 'Attach', 'attachments' ),

                    // text for modal 'Attach' button (string)
                    'modal_text'    => __( 'Attach', 'attachments' ),

                    // which tab should be the default in the modal (string) (browse|upload)
                    'router'        => 'browse',

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
                            'type'      => 'wysiwyg',                       // registered field type
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

            $this->save_metadata( $post_id, $attachments_meta );

            return $post_id;
        }



        /**
         * Processes submitted fields and saves Attachments' post metadata
         *
         * @param int $post_id The post ID
         * @param array $attachments_meta Multidimenaional array containing Attachments data
         * @return bool
         * @since 3.4.3
         */
        function save_metadata( $post_id = 0, $attachments_meta = null )
        {
            if( !is_array( $attachments_meta ) || !is_int( $post_id ) || intval( $post_id ) < 1 )
                return false;

            // final data store
            $attachments = array();

            // loop through each submitted instance
            foreach( $attachments_meta as $instance => $instance_attachments )
            {
                // loop through each Attachment of this instance
                foreach( $instance_attachments as $key => $attachment )
                {
                    $attachment_exists = get_post( $attachment['id'] );
                    // make sure the attachment exists
                    if( $attachment_exists )
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

                                // we don't want to strip out our newlines so we're going to flag them
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
            }

            if( !empty( $attachments ) )
            {
                // we're going to store JSON (JSON_UNESCAPED_UNICODE is PHP 5.4+)
                $attachments = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $attachments, JSON_UNESCAPED_UNICODE ) : json_encode( $attachments );

                // we're going to wipe out any existing Attachments meta (because we'll put it back)
                return update_post_meta( $post_id, $this->meta_key, $attachments );
            }
            else
            {
                // there are no attachments so we'll clean up the record
                return delete_post_meta( $post_id, $this->meta_key );
            }
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
                return false;

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
                if( ( is_array( $attachments_raw ) && count( $attachments_raw ) ) || is_object( $attachments_raw ) )
                {
                    // cast an object if necessary
                    if( is_object( $attachments_raw ) ) $attachments_raw = (array) $attachments_raw;

                    foreach( $attachments_raw as $instance => $attachments_unprocessed )
                        foreach( $attachments_unprocessed as $unprocessed_attachment )
                            $attachments[] = $this->process_attachment( $unprocessed_attachment, $instance );
                }
            }

            // tack on the post ID for each attachment
            for( $i = 0; $i < count( $attachments ); $i++ )
                $attachments[$i]->post_id = $post_id;

            // we don't want the filter to run on the admin side of things
            if( !is_admin() )
                $attachments = apply_filters( "attachments_get_{$instance}", $attachments );

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
         * @param string $instance The applicable instance
         * @return object Post-processed Attachment data
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
                return false;

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
