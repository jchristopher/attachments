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

        public $version;
        public $url;
        public $instances;
        public $instances_for_post_type;
        public $fields;

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

            add_action( 'admin_head',               array( $this, 'admin_head' ) );
        }

        function assets( $hook )
        {
            // we only want our assets on edit screens
            if( !empty( $this->instances_for_post_type ) && 'edit.php' != $hook && 'post.php' != $hook && 'post-new.php' != $hook )
                return;

            wp_enqueue_media();

            wp_enqueue_style( 'attachments', trailingslashit( $this->url ) . 'css/attachments.css', null, $this->version, 'screen' );

            wp_enqueue_script( 'attachments', trailingslashit( $this->url ) . 'js/attachments.js', array( 'jquery', 'backbone', 'media-gallery' ), $this->version, true );
        }

        function meta_box_init()
        {
            if( !empty( $this->instances_for_post_type ) )
            {
                foreach( $this->instances_for_post_type as $instance )
                {
                    add_meta_box( 'attachments', __( 'Attachments', 'attachments' ), array( $this, 'meta_box_markup' ), $this->get_post_type(), 'normal' );
                }
            }
        }

        function meta_box_markup( $post )
        { ?>
            <a id="attachments-insert" class="button"><?php _e( 'Attach', 'attachments' ); ?></a>
        <?php }

        /**
         * Support the inclusion of custom, user-defined field types
         * Borrowed implementation from Custom Field Suite by Matt Gibbs
         *      https://uproot.us/docs/creating-custom-field-types/
         **/
        function get_field_types()
        {
            $field_types = array(
                'text' => ATTACHMENTS_DIR . '/classes/fields/class.field.text.php'
            );

            // support custom field types
            $field_types = apply_filters( 'attachments_fields', $field_types );

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
                        $field_class = end( $classes );

                        // create our link
                        $field_types[$type] = $field_class;
                    }
                }
            }

            return $field_types;
        }

        function field( $params = array() )
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

           if( isset( $params['name'] ) )
               $params['name'] = sanitize_title( $params['name'] );

            if( isset( $params['type'] ) )
                $params['type'] = sanitize_title( $params['type'] );

            if( isset( $params['label'] ) )
                $params['label'] = __( $params['label'] );

            return new $this->fields[ $params['type'] ]( $params['name'], $params['label'] );
        }


        function register( $name = 'attachments', $params = array() )
        {
            $defaults = array(

                    // title of the meta box
                    'label'         => __( 'Attachments', 'attachments' ),

                    // all post types to utilize
                    'post_type'     => array( 'post', 'page' ),

                    // maximum number of Attachments (-1 is unlimited)
                    'limit'         => -1,

                    // include a note within the meta box
                    'note'          => null,

                    // fields for this instance
                    'fields'        => array(
                        $this->field( array(
                            'name'  => 'title',
                            'type'  => 'text',
                            'label' => __( 'Title', 'attachments' ),
                        ) ),
                        $this->field( array(
                            'name'  => 'caption',
                            'type'  => 'text',
                            'label' => __( 'Caption', 'attachments' ),
                        ) ),
                    ),

                );

            $params = array_merge( $defaults, $params );

            if( !is_array( $params['post_type'] ) )
                $params['post_type'] = array( $params['post_type'] );   // we always want an array

            $instance   = str_replace( '-', '_', sanitize_title( $name ) ); // TODO: Better sanitization

            $this->instances[$instance] = $params;
        }

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

        function set_instances_for_current_post_type()
        {
            // store the applicable instances for this post type
            $this->instances_for_post_type  = $this->get_instances_for_post_type( $this->get_post_type() );
        }

        function create_field( $field )
        {
            $field = (object) $field;
            $field->html( $field );
        }

        function admin_head()
        {
            if( !empty( $this->instances_for_post_type ) )
            {
                foreach( $this->instances_for_post_type as $instance ) :
                    foreach( $this->instances[$instance]['fields'] as $field ) : ?>
                        <script type="text/template" id="tmpl-attachments-field-<?php echo $instance; ?>-<?php echo $field->name; ?>">
                            <div class="attachment">
                                <div class="attachment-label">
                                    <label><?php echo $field->label; ?></label>
                                </div>
                                <div class="attachment-field">
                                    <?php echo $this->create_field( $field ); ?>
                                </div>
                            </div>
                        </script>
                    <?php endforeach;
                endforeach;
            }
        }

    }

endif; // class_exists check

$attachments = new Attachments();