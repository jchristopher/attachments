<?php
/*
 Plugin Name: Attachments
 Plugin URI: http://mondaybynoon.com/wordpress-attachments/
 Description: Attachments gives the ability to append any number of Media Library items to Pages, Posts, and Custom Post Types
 Version: 1.6.1
 Author: Jonathan Christopher
 Author URI: http://mondaybynoon.com/
*/

/*  Copyright 2009-2012 Jonathan Christopher  (email : jonathan@irontoiron.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// constant definition
if( !defined( 'IS_ADMIN' ) )
    define( 'IS_ADMIN',  is_admin() );

define( 'ATTACHMENTS_PREFIX', 'attachments_' );
define( 'ATTACHMENTS_VERSION', '1.6' );


// ===========
// = GLOBALS =
// ===========

global $wpdb;
global $units;

$units = array( ' bytes', ' KB', ' MB', ' GB', ' TB', ' PB' );

// environment check
$wp_version = get_bloginfo( 'version' );
if( !version_compare( PHP_VERSION, '5.2', '>=' ) || !version_compare( $wp_version, '3.0', '>=' ) )
{
    if( IS_ADMIN && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
    {
        require_once ABSPATH.'/wp-admin/includes/plugin.php';
        deactivate_plugins( __FILE__ );
        wp_die( __('Attachments requires PHP 5.2 or higher, as does WordPress 3.2+. Attachments has been automatically deactivated.') );
    }
    else
    {
        return;
    }
}



// =========
// = HOOKS =
// =========

if( IS_ADMIN )
{
    add_action( 'init',                                                     'attachments_pre_init' );

    add_action( 'admin_menu',                                               'attachments_init' );
    add_action( 'admin_head',                                               'attachments_init_js' );
    add_action( 'save_post',                                                'attachments_save' );
    add_action( 'admin_menu',                                               'attachments_menu' );
    add_action( 'admin_footer',                                             'attachments_footer_js' );
    add_action( 'in_plugin_update_message-attachments/attachments.php',     'attachments_update_message' );
    add_filter( 'plugin_row_meta',                                          'attachments_filter_plugin_row_meta', 10, 2 );
    add_action( 'admin_init',                                               'attachments_register_settings' );

    load_plugin_textdomain( 'attachments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}



// =============
// = FUNCTIONS =
// =============

function attachments_pre_init()
{
    // as of version 1.6 we'll be storing a proper settings array
    if( !get_option( ATTACHMENTS_PREFIX . 'settings' ) )
    {
        $settings = array();

        // we've got a version < 1.6 and therefore no real settings
        $settings['version'] = ATTACHMENTS_VERSION;

        $post_parent = get_option( 'attachments_store_native' );

        if( $post_parent === false )
        {
            // it wasn't set
            $settings['post_parent'] = false;
        }
        else
        {
            $settings['post_parent'] = true;
        }

        // grab our custom post types
        $args           = array(
            'public'    => true,
            'show_ui'   => true,
            '_builtin'  => false
            );
        $output         = 'objects';
        $operator       = 'and';
        $post_types     = get_post_types( $args, $output, $operator );

        // we also want to optionally enable Pages and Posts
        $post_types['post']->labels->name   = 'Posts';
        $post_types['post']->name           = 'post';
        $post_types['page']->labels->name   = 'Pages';
        $post_types['page']->name           = 'page';
        
        $post_types = apply_filters('attachments_post_types', $post_types);
        
        if( count( $post_types ) )
        {
            foreach( $post_types as $post_type )
            {
                $post_parent = get_option( 'attachments_cpt_' . $post_type->name );

                if( $post_parent === false )
                {
                    // it wasn't set
                    $settings['post_types'][$post_type->name] = false;
                }
                else
                {
                    $settings['post_types'][$post_type->name] = true;
                }
            }
        }

        // save our settings
        update_option( ATTACHMENTS_PREFIX . 'settings', $settings );
    }
}


function attachments_register_settings()
{
    // flag our settings
    register_setting(
        ATTACHMENTS_PREFIX . 'settings',        // group
        ATTACHMENTS_PREFIX . 'settings',        // name of options
        'attachments_validate_settings'         // validation callback
    );

    add_settings_section(
        ATTACHMENTS_PREFIX . 'options',         // section ID
        'Post Type Settings',                   // title
        'attachments_edit_options',             // display callback
        'attachments_options'                   // page name (do_settings_sections)
    );

    // post types
    add_settings_field(
        ATTACHMENTS_PREFIX . 'post_types',      // unique field ID
        'Post Types',                           // title
        'attachments_edit_post_types',          // input box display callback
        'attachments_options',                  // page name (as above)
        ATTACHMENTS_PREFIX . 'options'          // first arg to add_settings_section
    );

    // post_parent
    add_settings_field(
        ATTACHMENTS_PREFIX . 'post_parent',     // unique field ID
        'Set Post Parent',                      // title
        'attachments_edit_post_parent',         // input box display callback
        'attachments_options',                  // page name (as above)
        ATTACHMENTS_PREFIX . 'options'          // first arg to add_settings_section
    );
}

function attachments_edit_options()
{  }

function attachments_validate_settings($input)
{
    $input['version'] = ATTACHMENTS_VERSION;
    return $input;
}

function attachments_edit_post_parent()
{
    $settings = get_option( ATTACHMENTS_PREFIX . 'settings' );
    ?>
    <div>
        <label for="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_parent]">
            <input name="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_parent]" type="checkbox" id="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_parent]" value="1"<?php if( isset( $settings['post_parent'] ) && $settings['post_parent'] ) : ?> checked="checked"<?php endif; ?> /> Set the <code>post_parent</code> when Attachments are saved
        </label>
    </div>
<?php }

function attachments_edit_post_types()
{
    $settings = get_option( ATTACHMENTS_PREFIX . 'settings' );
    $args           = array(
                        'public'    => true,
                        'show_ui'   => true,
                        '_builtin'  => false
                        );
    $output         = 'objects';
    $operator       = 'and';
    $post_types     = get_post_types( $args, $output, $operator );

    // we also want to optionally enable Pages and Posts
    $post_types['post']->labels->name   = 'Posts';
    $post_types['post']->name           = 'post';
    $post_types['page']->labels->name   = 'Pages';
    $post_types['page']->name           = 'page';

    $post_types = apply_filters('attachments_post_types', $post_types);

    do_action('attachments_edit_post_types_before', $post_types);

    if( count( $post_types ) ) : foreach($post_types as $post_type) : ?>
        <div>
            <label for="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_types][<?php echo $post_type->name; ?>]">
                <input name="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_types][<?php echo $post_type->name; ?>]" type="checkbox" id="<?php echo ATTACHMENTS_PREFIX; ?>settings[post_types][<?php echo $post_type->name; ?>]" value="1"<?php if( isset( $settings['post_types'][$post_type->name] ) && $settings['post_types'][$post_type->name] ) : ?> checked="checked"<?php endif; ?> /> <?php echo $post_type->labels->name; ?>
            </label>
        </div>
    <?php endforeach; endif; ?>
    
    <?php do_action('attachments_edit_post_types_after', $post_types); ?>
<?php }

/**
 * Includes our plugin update message
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_update_message()
{ ?>
    <div style="color: #f00;padding-top:4px;">Attachments Pro is now available!</div>
    <div style="font-weight:normal;padding-top:8px;">
        <p><a href="http://mondaybynoon.com/store/attachments-pro/">Attachments Pro</a> is Attachments' big brother. With it come a number of often-requested features such as:</p>
        <ul style="list-style:disc;padding-left:20px;margin-bottom:13px;overflow:hidden;zoom:1;">
            <li style="width:48%;padding-right:2%;float:left;">Multiple Attachments instances on edit screens</li>
            <li style="width:48%;padding-right:2%;float:left;">Customizable field labels and meta box title</li>
            <li style="width:48%;padding-right:2%;float:left;">Unlimited number of fields per Attachment</li>
            <li style="width:48%;padding-right:2%;float:left;">Ability to define rules limiting the availability of Attachments on edit screens</li>
            <li style="width:48%;padding-right:2%;float:left;">Limit the number of Attachments that can be added</li>
            <li style="width:48%;padding-right:2%;float:left;">Limit Attach-able Media items by file/mime type</li>
            <li style="width:48%;padding-right:2%;float:left;">Shortcode support</li>
            <li style="width:48%;padding-right:2%;float:left;">Auto-inclusion of Attachments content within the_content()</li>
        </ul>
        <p>Attachments has always been and <em>will always be free</em>. <a href="http://mondaybynoon.com/store/attachments-pro/">Attachments Pro</a> is <strong>available now</strong>. To find out more about the new features already added, and to stay up-to-date on what's to come, <a href="http://mondaybynoon.com/store/attachments-pro/">have a look at the details</a>. From there, you can make formal support and feature requests.</p>
    </div>
<?php }


/**
 * Compares two array values with the same key "order"
 *
 * @param string $a First value
 * @param string $b Second value
 * @return int
 * @author Jonathan Christopher
 */
function attachments_cmp($a, $b)
{
    $a = intval( $a['order'] );
    $b = intval( $b['order'] );

    if( $a < $b )
    {
        return -1;
    }
    else if( $a > $b )
    {
        return 1;
    }
    else
    {
        return 0;
    }
}


/**
 * Creates the markup for the WordPress admin options page
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_options()
{
    include 'attachments.options.php';
}


/**
 * Creates the entry for Attachments Options under Settings in the WordPress Admin
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_menu()
{
    add_options_page('Settings', __('Attachments', 'attachments'), 'manage_options', __FILE__, 'attachments_options');
}


/**
 * Inserts HTML for meta box, including all existing attachments
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_add()
{?>

    <div id="attachments-inner">

        <?php
            do_action('attachments_meta_before');
            $media_upload_iframe_src = "media-upload.php?type=image&TB_iframe=1";
            $image_upload_iframe_src = apply_filters( 'image_upload_iframe_src', "$media_upload_iframe_src" );
            ?>

                <ul id="attachments-actions">
                    <li>
                        <a id="attachments-thickbox" href="media-upload.php?type=image&amp;TB_iframe=1&amp;width=640&amp;height=1500&amp;attachments_thickbox=1" title="Attachments" class="button button-highlighted">
                            <?php _e( 'Attach', 'attachments' ) ?>
                        </a>
                    </li>
                </ul>

                <div id="attachments-list">
                    <input type="hidden" name="attachments_nonce" id="attachments_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
                    <ul>
                        <?php
                    if( !empty($_GET['post']) )
                    {
                        // get all attachments
                        $existing_attachments = attachments_get_attachments( intval( $_GET['post'] ) );

                        if( is_array($existing_attachments) && !empty($existing_attachments) )
                        {
                            do_action('attachments_list_before', $existing_attachments);
                            $attachment_index = 0;
                            foreach ($existing_attachments as $attachment) : $attachment_index++; ?>
                            <li class="attachments-file">
                                <h2>
                                    <a href="#" class="attachment-handle">
                                        <span class="attachment-handle-icon"><img src="<?php echo WP_PLUGIN_URL; ?>/attachments/images/handle.gif" alt="Drag" /></span>
                                    </a>
                                    <span class="attachment-name"><?php echo $attachment['name']; ?></span>
                                    <span class="attachment-delete"><a href="#"><?php _e("Delete", "attachments")?></a></span>
                                </h2>
                                <div class="attachments-fields">
                                    <div class="textfield" id="field_attachment_title_<?php echo $attachment_index ; ?>">
                                        <label for="attachment_title_<?php echo $attachment_index; ?>"><?php _e("Title", "attachments")?></label>
                                        <input type="text" id="attachment_title_<?php echo $attachment_index; ?>" name="attachment_title_<?php echo $attachment_index; ?>" value="<?php echo $attachment['title']; ?>" size="20" />
                                    </div>
                                    <div class="textfield" id="field_attachment_caption_<?php echo $attachment_index; ?>">
                                        <label for="attachment_caption_<?php echo $attachment_index; ?>"><?php _e("Caption", "attachments")?></label>
                                        <input type="text" id="attachment_caption_<?php echo $attachment_index; ?>" name="attachment_caption_<?php echo $attachment_index; ?>" value="<?php echo $attachment['caption']; ?>" size="20" />
                                    </div>
                                </div>
                                <div class="attachments-data">
                                    <input type="hidden" name="attachment_id_<?php echo $attachment_index; ?>" id="attachment_id_<?php echo $attachment_index; ?>" value="<?php echo $attachment['id']; ?>" />
                                    <input type="hidden" class="attachment_order" name="attachment_order_<?php echo $attachment_index; ?>" id="attachment_order_<?php echo $attachment_index; ?>" value="<?php echo $attachment['order']; ?>" />
                                </div>
                                <div class="attachment-thumbnail">
                                    <span class="attachments-thumbnail">
                                        <?php echo wp_get_attachment_image( $attachment['id'], array(80, 60), 1 ); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach;
                        do_action('attachments_list_after', $existing_attachments);
                    }
                }
                ?>
            </ul>
        </div>
        <?php do_action('attachments_meta_after');
    </div>
<?php }


/**
 * Creates meta box on all Posts and Pages
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_meta_box()
{
    // for custom post types
    if( function_exists( 'get_post_types' ) )
    {
        $settings = get_option( ATTACHMENTS_PREFIX . 'settings' );

        $args = array(
            'public'    => true,
            'show_ui'   => true
            );
        $output         = 'objects';
        $operator       = 'and';
        $post_types     = get_post_types( $args, $output, $operator );

        foreach($post_types as $post_type)
        {
            if( isset( $settings['post_types'][$post_type->name] ) && $settings['post_types'][$post_type->name] )
            {
                add_meta_box( 'attachments_list', __( 'Attachments', 'attachments' ), 'attachments_add', $post_type->name, 'normal' );
            }
        }
    }
}


/**
 * Echos JavaScript that sets some required global variables
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_init_js()
{
    echo '<script type="text/javascript" charset="utf-8">';
    echo '  var attachments_base = "' . WP_PLUGIN_URL . '/attachments"; ';
    echo '  var attachments_media = ""; ';
    echo '</script>';
}


/**
 * Fired when Post or Page is saved. Serializes all attachment data and saves to post_meta
 *
 * @param int $post_id The ID of the current post
 * @return void
 * @author Jonathan Christopher
 * @author JR Tashjian
 */
function attachments_save($post_id)
{
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if( !isset( $_POST['attachments_nonce'] ) )
    {
        return $post_id;
    }

    if( !wp_verify_nonce( $_POST['attachments_nonce'], plugin_basename(__FILE__) ) )
    {
        return $post_id;
    }

    // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
    // to do anything
    if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    {
        return $post_id;
    }

    // Check permissions
    if( 'page' == $_POST['post_type'] )
    {
        if( !current_user_can( 'edit_page', $post_id ) )
        {
            return $post_id;
        }
    }
    else
    {
        if( !current_user_can( 'edit_post', $post_id ) )
        {
            return $post_id;
        }
    }

    // OK, we're authenticated: we need to find and save the data

    // delete all current attachments meta
    // moved outside conditional, else we can never delete all attachments
    delete_post_meta( $post_id, '_attachments' );

    // Since we're allowing Attachments to be sortable, we can't simply increment a counter
    // we need to keep track of the IDs we're given
    $attachment_ids = array();

    // We'll build our array of attachments
    foreach( $_POST as $key => $data )
    {
        // Arbitrarily using the id
        if( substr($key, 0, 14) == 'attachment_id_' )
        {
            array_push( $attachment_ids, substr( $key, 14, strlen( $key ) ) );
        }

    }

    // If we have attachments, there's work to do
    if( !empty( $attachment_ids ) )
    {

        foreach ( $attachment_ids as $i )
        {
            if( !empty( $_POST['attachment_id_' . $i] ) )
            {
                $attachment_id      = intval( $_POST['attachment_id_' . $i] );

                $attachment_details = array(
                    'id'                => $attachment_id,
                    'title'             => str_replace( '"', '&quot;', $_POST['attachment_title_' . $i] ),
                    'caption'           => str_replace( '"', '&quot;', $_POST['attachment_caption_' . $i] ),
                    'order'             => intval( $_POST['attachment_order_' . $i] )
                    );

                // serialize data and encode
                $attachment_serialized = base64_encode( serialize( $attachment_details ) );

                // add individual attachment
                add_post_meta( $post_id, '_attachments', $attachment_serialized );

                // save native Attach
                $settings = get_option( ATTACHMENTS_PREFIX . 'settings' );
                if( isset( $settings['post_parent'] ) && $settings['post_parent'] )
                {
                    // need to first check to make sure we're not overwriting a native Attach
                    $attach_post_ref                = get_post( $attachment_id );

                    if( $attach_post_ref->post_parent == 0 )
                    {
                        // no current Attach, we can add ours
                        $attach_post                    = array();
                        $attach_post['ID']              = $attachment_id;
                        $attach_post['post_parent']     = $post_id;

                        wp_update_post( $attach_post );
                    }
                }

            }
        }

    }

}


/**
 * Returns a formatted filesize
 *
 * @param string $path Path to file on disk
 * @return string $formatted formatted filesize
 * @author Jonathan Christopher
 */
function attachments_get_filesize_formatted( $path = NULL )
{
    global $units;
    $formatted = '0 bytes';
    if( file_exists( $path ) )
    {
        $bytes      = intval( filesize( $path ) );
        $s          = $units;
        $e          = floor( log( $bytes ) / log( 1024 ) );
        $formatted  = sprintf( '%.2f ' . $s[$e], ( $bytes / pow( 1024, floor( $e ) ) ) );
    }
    $formatted = apply_filters('attachments_get_filesize_formatted', $formatted, $path);
    return $formatted;
}


/**
 * Retrieves all Attachments for provided Post or Page
 *
 * @param int $post_id (optional) ID of target Post or Page, otherwise pulls from global $post
 * @return array $post_attachments
 * @author Jonathan Christopher
 * @author JR Tashjian
 */

function attachments_get_attachments( $post_id=null )
{
    global $post;

    if( $post_id==null )
    {
        $post_id = $post->ID;
    }

    // get all attachments
    $existing_attachments = get_post_meta( $post_id, '_attachments', false );

    // We can now proceed as normal, all legacy data should now be upgraded

    $post_attachments = array();

    if( is_array( $existing_attachments ) && count( $existing_attachments ) > 0 )
    {

        foreach ($existing_attachments as $attachment)
        {
            // decode and unserialize the data
            $data = unserialize( base64_decode( $attachment ) );

            array_push( $post_attachments, array(
                'id' 			=> stripslashes( $data['id'] ),
                'name' 			=> stripslashes( get_the_title( $data['id'] ) ),
                'mime' 			=> stripslashes( get_post_mime_type( $data['id'] ) ),
                'title' 		=> stripslashes( $data['title'] ),
                'caption' 		=> stripslashes( $data['caption'] ),
                'filesize'      => stripslashes( attachments_get_filesize_formatted( get_attached_file( $data['id'] ) ) ),
                'location' 		=> stripslashes( wp_get_attachment_url( $data['id'] ) ),
                'order' 		=> stripslashes( $data['order'] )
                ));
        }

        // sort attachments
        if( count( $post_attachments ) > 1 )
        {
            usort( $post_attachments, "attachments_cmp" );
        }
    }

    $post_attachments = apply_filters('attachments_get_attachments', $post_attachments);
    return $post_attachments;
}


/**
 * Outputs Attachments JS into the footer
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_footer_js()
{
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : NULL ;
    $file   = basename( parse_url( $uri, PHP_URL_PATH ) );

    if( $uri && in_array( $file, array( 'post.php', 'post-new.php' ) ) )
    {
        // we only want this to fire on edit screens
        echo '<script type="text/javascript">';
        include 'js/attachments.js';
        echo '</script>';
    }
}


/**
 * This is the main initialization function, it will invoke the necessary meta_box
 *
 * @return void
 * @author Jonathan Christopher
 */
function attachments_init()
{
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'thickbox' );

    wp_enqueue_style( 'thickbox' );
    wp_enqueue_style( 'attachments', WP_PLUGIN_URL . '/attachments/css/attachments.css' );

    if( function_exists( 'load_plugin_textdomain' ) )
    {
        if( !defined('WP_PLUGIN_DIR') )
        {
            load_plugin_textdomain( 'attachments', str_replace( ABSPATH, '', dirname( __FILE__ ) ) );
        }
        else
        {
            load_plugin_textdomain( 'attachments', false, dirname( plugin_basename( __FILE__ ) ) );
        }
    }

    attachments_meta_box();
    do_action('attachments_init');
}


/**
 * Modifies the plugin meta line on the WP Plugins page
 *
 * @param $plugin_meta
 * @param $plugin_file
 * @return array $plugin_meta Array of plugin meta data
 * @author Jonathan Christopher
 */
function attachments_filter_plugin_row_meta( $plugin_meta, $plugin_file )
{
    if( strstr( $plugin_file, 'attachments/attachments.php' ) )
    {
        $plugin_meta[2] = '<a title="Attachments Pro" href="http://mondaybynoon.com/store/attachments-pro/">Attachments Pro</a>';
        $plugin_meta[3] = 'Visit <a title="Iron to Iron" href="http://irontoiron.com/">Iron to Iron</a>';
        return $plugin_meta;
    }
    else
    {
        return $plugin_meta;
    }
}
