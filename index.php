<?php

 /**
  * Plugin Name: Attachments
  * Plugin URI:  https://github.com/jchristopher/attachments
  * Description: Attachments gives the ability to append any number of Media Library items to Pages, Posts, and Custom Post Types
  * Author:      Jonathan Christopher
  * Author URI:  http://mondaybynoon.com/
  * Version:     3.0
  * Text Domain: attachments
  * Domain Path: /languages/
  * License:     GPLv2 or later
  * License URI: http://www.gnu.org/licenses/gpl-2.0.html
  */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Store whether or not we're in the admin
if( !defined( 'IS_ADMIN' ) ) define( 'IS_ADMIN',  is_admin() );

// Environment check
$wp_version = get_bloginfo( 'version' );

if( !version_compare( PHP_VERSION, '5.2', '>=' ) && IS_ADMIN && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
{
    // failed PHP requirement
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
    deactivate_plugins( __FILE__ );
    wp_die( esc_attr( __( 'Attachments requires PHP 5.2+. Attachments has been automatically deactivated.' ) ) );
}
else
{

    if( ( defined( 'ATTACHMENTS_LEGACY' ) && ATTACHMENTS_LEGACY === true ) || version_compare( $wp_version, '3.5', '<' ) )
    {
        // load deprecated version of Attachments
        require_once 'deprecated/attachments.php';
    }
    else
    {
        define( 'ATTACHMENTS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'ATTACHMENTS_URL', plugin_dir_url( __FILE__ ) );

        // load current version of Attachments
        require_once 'classes/class.attachments.php';
    }
}
