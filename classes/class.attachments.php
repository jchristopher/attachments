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

// Declare our class
if ( !class_exists( 'Attachments' ) ) :

    /**
     * Main Attachments Class
     *
     * @since 3.0
     */

    class Attachments {

        public $version;                    // stores Attachments' version number
        public $url;                        // stores Attachments' URL
        public $dir;                        // stores Attachments' directory
        public $instances;                  // all registered Attachments instances
        public $instances_for_post_type;    // instance names that apply to the current post type
        public $fields;                     // stores all registered field types

        // what WordPress considers to be valid file types
        public $valid_filetypes = array( 'image', 'video', 'text', 'audio', 'application' );



        /**
         * Constructor
         *
         * @since 3.0
         */
        function __construct()
        {
            // establish our environment variables
            $this->version  = '3.0';
            $this->url      = ATTACHMENTS_URL;
            $this->dir      = ATTACHMENTS_DIR;

            // includes
            include_once( ATTACHMENTS_DIR . 'upgrade.php' );
            include_once( ATTACHMENTS_DIR . '/classes/class.field.php' );

            // include our fields
            $this->fields = $this->get_field_types();

            // register our instances
            $this->register();
            // TODO: determine how to flag whether or not user wants default instance
            // TODO: only register if user wants

            // hook into WP
            add_action( 'admin_enqueue_scripts',    array( $this, 'assets' ) );

            add_action( 'init',                     array( $this, 'set_instances_for_current_post_type' ) );

            add_action( 'add_meta_boxes',           array( $this, 'meta_box_init' ) );

            add_action( 'admin_footer',             array( $this, 'admin_footer' ) );
        }



        /**
         * Enqueues our necessary assets
         *
         * @since 3.0
         */
        function assets( $hook )
        {
            // we only want our assets on edit screens
            if( !empty( $this->instances_for_post_type ) && 'edit.php' != $hook && 'post.php' != $hook && 'post-new.php' != $hook )
                return;

            wp_enqueue_media();

            wp_enqueue_style( 'attachments', trailingslashit( $this->url ) . 'css/attachments.css', null, $this->version, 'screen' );

            wp_enqueue_script( 'underscore' );
            wp_enqueue_script( 'backbone' );
            // wp_enqueue_script( 'attachments', trailingslashit( $this->url ) . 'js/attachments.js', array( 'jquery' ), $this->version, true );
        }



        /**
         * Registers meta box(es) for the current edit screen
         *
         * @since 3.0
         */
        function meta_box_init()
        {
            if( !empty( $this->instances_for_post_type ) )
            {
                foreach( $this->instances_for_post_type as $instance )
                {
                    $title              = $instance;
                    $instance           = (object) $this->instances[$instance];
                    $instance->name     = $title;

                    add_meta_box( 'attachments-' . esc_attr( $instance->name ), __( esc_attr( $title ) ), array( $this, 'meta_box_markup' ), $this->get_post_type(), 'normal', 'high', array( 'instance' => $instance ) );
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
            print_r($instance);
            ?>
            <div id="attachments-<?php echo $instance->name; ?>">
                <a class="button attachments-invoke"><?php _e( esc_attr( $instance->button_text ), 'attachments' ); ?></a>
                <?php if( !empty( $instance->note ) ) : ?>
                    <div class="attachments-note"><?php echo apply_filters( 'the_content', $intsance->note ); ?></div>
                <?php endif; ?>
                <div class="attachments-container attachments-<?php echo $instance->name; ?>"></div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var $element     = $('#attachments-<?php echo esc_attr( $instance->name ); ?>'),
                        title        = '<?php echo __( esc_attr( $instance->label ) ); ?>',
                        button       = '<?php echo __( esc_attr( $instance->modal_text ) ); ?>',
                        Attachment   = wp.media.model.Attachment,
                        frame;

                    $element.on( 'click', '.attachments-invoke', function( event ) {
                        var options, attachment;

                        event.preventDefault();

                        if ( frame ) {
                            frame.open();
                            return;
                        }

                        options = {
                            title:   title,
                            <?php if( $instance->limit < 0 || $instance->limit > 1 ) : ?>
                                multiple: true,
                            <?php endif; ?>
                            library: {
                                type: '<?php echo esc_attr( implode( ",", $instance->filetype ) ); ?>'
                            }
                        };

                        frame = wp.media( options );

                        frame.get('library').set( 'filterable', 'uploaded' );

                        frame.toolbar.on( 'activate:select', function() {
                            frame.toolbar.view().set({
                                select: {
                                    style: 'primary',
                                    text:  button,

                                    click: function() {
                                        var selection = frame.state().get('selection');

                                        if ( ! selection )
                                            return;

                                        // compile our Underscore template
                                        _.templateSettings.variable = 'attachments';
                                        var template = _.template($('script#tmpl-attachments-<?php echo $instance->name; ?>').html());

                                        // loop through the selected files
                                        selection.each( function( attachment ) {

                                            // set our attributes to the template
                                            var templateData = attachment.attributes;

                                            // append the template
                                            $element.find('.attachments-container').append(template(templateData));
                                        });

                                        // close out the frame
                                        frame.close();
                                    }
                                }
                            });
                        });

                        // set the environment
                        frame.toolbar.mode('select');
                    });

                    $element.on( 'click', '.remove', function( event ) {
                        event.preventDefault();
                        setFeaturedImage( -1 );
                    });
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
                'text' => ATTACHMENTS_DIR . 'classes/fields/class.field.text.php'
            );

            // support custom field types
            // $field_types = apply_filters( 'attachments_fields', $field_types );

            foreach( $field_types as $type => $path )
            {
                // store the registered classes so we can single out what gets added
                $classes_before = get_declared_classes();

                // proceed with inclusion
                if( file_exists( $path ) )
                {
                    // include the file
                    include_once( $path );

                    // determine it's class
                    $classes = get_declared_classes();
                    if( $classes_before !== $classes )
                    {
                        // the field's class is last in line
                        $field_class = end( $classes );

                        // create our link using our new field class
                        $field_types[$type] = $field_class;
                    }
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

            // instantiate the class for this field and send it back
            return new $this->fields[ $params['type'] ]( $params['name'], $params['label'] );
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

                    // fields for this instance (array)
                    'fields'        => array(
                        array(
                            'name'  => 'title',                         // unique field name
                            'type'  => 'text',                          // registered field type
                            'label' => __( 'Title', 'attachments' ),    // label to display
                        ),
                        array(
                            'name'  => 'caption',                       // unique field name
                            'type'  => 'text',                          // registered field type
                            'label' => __( 'Caption', 'attachments' ),  // label to display
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
            $params['button_text']  = esc_html( $params['button_text'] );
            $params['modal_text']   = esc_html( $params['modal_text'] );

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

            // make sure the instance name is proper
            $instance           = str_replace( '-', '_', sanitize_title( $name ) );

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
            if( !$post_type = get_post_type() )
            {
                if( empty( $post->ID ) && isset( $_GET['post_type'] ) )
                {
                    $post_type = str_replace( '-', '_', sanitize_title( $_GET['post_type'] ) ); // TODO: Better sanitization
                }
                elseif( !empty( $post->ID ) )
                {
                    $post_type = get_post_type( $post->ID );
                }
                else
                {
                    $post_type = 'post';
                }
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
        function create_attachment_field( $instance, $field )
        {

            // the $field at this point is just the user-declared array
            // we need to make it a field object
            $type = $field['type'];

            if( isset( $this->fields[$type] ) )
            {
                $name   = sanitize_title( $field['name'] );
                $label  = esc_html( $field['label'] );
                $field  = new $this->fields[$type]( $name, $label );
            }

            // TODO: make sure we've got a registered instance
            $field->set_field_instance( $instance, $field );
            $field->set_field_identifiers( $field );
            $field->set_field_type( $type );

            ?>
            <div class="attachments-attachment-field attachments-attachment-field-<?php echo $instance; ?> attachments-attachment-field-<?php echo $field->type; ?> attachment-field-<?php echo $field->name; ?>">
                <div class="attachment-label attachment-label-<?php echo $instance; ?>">
                    <label for="<?php echo $field->field_id; ?>"><?php echo $field->label; ?></label>
                </div>
                <div class="attachment-field attachment-field-<?php echo $instance; ?>">
                    <?php echo $this->create_field( $instance, $field ); ?>
                </div>
            </div>
        <?php }



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



        function create_attachment( $instance )
        {
            // TODO: get name and id for hidden fields
            ?>
                <div class="attachments-attachment attachments-attachment-<?php echo $instance; ?>">
                    <?php foreach( $this->instances[$instance]['fields'] as $field ) : ?>
                        <?php $this->create_attachment_field( $instance, $field ); ?>
                    <?php endforeach; ?>
                    <input type="hidden" name="attachments[<?php echo $instance; ?>][][id]" value="<%- attachments.id %>" />
                    <input type="hidden" name="attachments[<?php echo $instance; ?>][][filename]" value="<%- attachments.filename %>" />
                    <input type="hidden" name="attachments[<?php echo $instance; ?>][][icon]" value="<%- attachments.icon %>" />
                    <input type="hidden" name="attachments[<?php echo $instance; ?>][][subtype]" value="<%- attachments.subtype %>" />
                    <input type="hidden" name="attachments[<?php echo $instance; ?>][][type]" value="<%- attachments.type %>" />
                </div>
            <?php
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

    }

endif; // class_exists check

$attachments = new Attachments();