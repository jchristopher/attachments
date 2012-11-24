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

        function __construct()
        {
            // establish our environment variables
            $this->version                  = '3.0';
            $this->url                      = (string) plugins_url( 'attachments' );

            // include our fields
            include( 'class.field.php' );
            include( 'fields/class.field.text.php' );

            // register our instances
            // TODO: determine how to flag whether or not user wants default instance
            // TODO: only register if user wants
            $this->register();

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
            $post_type = get_post_type();
            if( in_array( $post_type, $this->instances_for_post_type ) )
            {
                add_meta_box( 'attachments', __( 'Attachments', 'attachments' ), array( $this, 'meta_box_markup' ), $post_type, 'normal' );
            }
        }

        function meta_box_markup( $post )
        { ?>
            <a id="attachments-insert" class="button">Attach</a>
        <?php }

        function register( $name = 'attachments', $params = array() )
        {
            $defaults   = array(
                    'label'         => __( 'Attachments', 'attachments' ),
                    'post_type'     => array( 'post', 'page' ),
                    'fields'        => array(
                         'title'    => new Attachments_Field_Text( __( 'Title', 'attachments' ) ),
                         'caption'  => new Attachments_Field_Text( __( 'Caption', 'attachments' ) ),
                    )
                );

            $params     = array_merge( $defaults, $params );

            if( !is_array( $params['post_type'] ) )
                $params['post_type'] = array( $params['post_type'] );   // we always want an array

            $instance   = str_replace( '-', '_', sanitize_title( $name ) );

            $this->instances[$instance] = $params;
        }

        function get_instances_for_post_type( $post_type = null )
        {
            $post_type = ( !is_null( $post_type ) && post_type_exists( $post_type ) ) ? $post_type : get_post_type();

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

        function set_instances_for_current_post_type()
        {
            global $post;

            // TODO: Retrieving the post_type at this point is ugly to say the least. This needs major cleanup.
            if( empty( $post->ID ) )
            {
                $post_type = str_replace( '-', '_', sanitize_title( $_GET['post_type'] ) );
            }
            else
            {
                $post_type = get_post_type( $post->ID );
            }

            if( empty( $post_type ) )
                $post_type = 'post';

            // store the applicable instances for this post type
            $this->instances_for_post_type  = $this->get_instances_for_post_type( $post_type );
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
                        <script type="text/template" id="tmpl-attachments-field-<?php echo $field->name; ?>">
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

$attachments           = new Attachments();