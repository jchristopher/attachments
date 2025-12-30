<?php

 /**
  * Plugin Name: Attachments
  * Plugin URI:  https://github.com/jchristopher/attachments
  * Description: Attachments gives the ability to append any number of Media Library items to Pages, Posts, and Custom Post Types
  * Author:      Jon Christopher
  * Author URI:  https://jonchristopher.us/
  * Version:     3.5.10
  * Text Domain: attachments
  * Domain Path: /languages/
  * License:     GPLv2 or later
  * License URI: http://www.gnu.org/licenses/gpl-2.0.html
  */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$wp_version = get_bloginfo( 'version' );

if ( ( defined( 'ATTACHMENTS_LEGACY' ) && ATTACHMENTS_LEGACY === true ) || version_compare( $wp_version, '3.5', '<' ) ) {
  // load deprecated version of Attachments
  require_once dirname( __FILE__ ) . '/deprecated/attachments.php';
} else {
  define( 'ATTACHMENTS_DIR', plugin_dir_path( __FILE__ ) );
  define( 'ATTACHMENTS_URL', plugin_dir_url( __FILE__ ) );

  // load current version of Attachments
  require_once dirname( __FILE__ ) . '/classes/class.attachments.php';
}
