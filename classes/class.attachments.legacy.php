<?php

// exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// exit if we can't extend Attachments
if ( !class_exists( 'Attachments' ) ) return;




/**
* Check for and handle legacy data appropriately
*
* @since 3.4.1
*/
class AttachmentsLegacyHandler
{

    public $legacy             = false;    // whether or not there is legacy Attachments data
    public $legacy_pro         = false;    // whether or not there is legacy Attachment Pro data

    function __construct()
    {
        // deal with our legacy issues if the user hasn't dismissed or migrated already
        $this->check_for_legacy_data();

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_pointer' ), 999 );

        // with version 3 we'll be giving at least one admin notice
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    }



    /**
     * Stores whether or not this environment has active legacy Attachments/Pro data
     *
     * @since 3.1.3
     */
    function check_for_legacy_data()
    {
        // we'll get a warning issued if fired when Network Activated
        // since it's supremely unlikely we'd have legacy data at this point, we're going to short circuit
        if( is_multisite() )
        {
            $plugins = get_site_option( 'active_sitewide_plugins' );
            if ( isset($plugins['attachments/index.php']) )
                return;
        }

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
                        'post_type'         => isset( $post_types ) ? $post_types : array( 'post', 'page' ),
                        'post_status'       => 'any',
                        'posts_per_page'    => 1,
                        'meta_key'          => '_attachments',
                        'suppress_filters'  => true,
                    );

                $legacy         = new WP_Query( $args );
                $this->legacy   = empty( $legacy->found_posts ) ? false : true;
            }
        }

        // deal with our legacy Pro issues if the user hasn't dismissed or migrated already
        if( false == get_option( 'attachments_pro_migrated' ) && false == get_option( 'attachments_pro_ignore_migration' ) )
        {
            $post_types = get_post_types();

            // set up our WP_Query args to grab anything (really anything) with legacy data
            $args = array(
                    'post_type'         => !empty( $post_types ) ? $post_types : array( 'post', 'page' ),
                    'post_status'       => 'any',
                    'posts_per_page'    => 1,
                    'meta_key'          => '_attachments_pro',
                    'suppress_filters'  => true,
                );

            $legacy_pro         = new WP_Query( $args );
            $this->legacy_pro   = empty( $legacy_pro->found_posts ) ? false : true;
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
}

