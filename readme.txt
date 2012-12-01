=== Attachments ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, posts, pages, images, PDF, doc, Word, image, jpg, jpeg, picture, pictures, photos, attachment
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

== Description ==

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

== Installation ==

1. Download the plugin and extract the files
1. Upload `attachments` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Implement Attachments in your theme's `functions.php` or your own plugin (see **Usage**)
1. Update your templates where applicable (see **Usage**)

== Frequently Asked Questions ==

= Attachments isn't showing up on my edit screens =

You need to turn on Attachments for your post types. View the Attachments settings under the main Settings menu in the WordPress admin.

= Attachments are not showing up in my theme =

You will need to edit your theme files where applicable. Please reference the **Usage** instructions.

= How do I disable the default Attachments meta box? =

You will need to edit your Attachments configuration. Please reference the **Usage** instructions.

= How do I change the fields for each Attachment? =

You will need to edit your Attachments configuration. Please reference the **Usage** instructions.

= Where are uploads saved? =

Attachments uses WordPress' built in Media library for uploads and storage.

= I lost my Attachments after upgrading! =

***DO NOT update any Post/Page/CPT with Attachments***, the data has not been lost. Please [contact me](http://mondaybynoon.com/contact/) to begin a bugfix

== Screenshots ==


== Changelog ==

= 3.0 =
* **Major** rewrite. After three years of development, Attachments has been rewritten to make
      even better use of what WordPress has to offer
* Utilizes the brand spanking new 3.5 Media workflow
* Configuration now takes place within your theme or a plugin
* Multiple meta boxes! You can segment groups of Attachments with new instances, each unique
* Dynamic fields! You can manipulate which fields each instance uses
* File type limits. Limit which files are available to Attachments (e.g. images, audio, video, all)

= 1.6.2.1 =
* Fixed an issue with Handlebars in Firefox
* Better handling of Attachment name

= 1.6.2 =
* Fixed an issue when you both add and delete Attachments prior to saving posts
* Cleaned up the JavaScript that powers the file browse interaction
* Swapped out some custom code for WordPress native function calls
* Better handling of asset inclusion

= 1.6.1 =
* Fixed a conflict with WP-Ecommerce

= 1.6 =
* Updated settings to use the Settings API
* Tested with WordPress 3.3
* Removed support for extremely legacy Attachments storage. If you have upgraded from a version before 1.0.7, please downgrade to 1.5.10 and let me know.

= 1.5.10 =
* WordPress 3.3 compatibility
* Updated Polish translation
* Removed soon-to-be deprecated jQuery methods in prep for 1.7

= 1.5.9 =
* Retrieve file size when `firing attachments_get_attachments()`

= 1.5.8 =
* Code cleanup

= 1.5.7 =
* Translation update

= 1.5.6 =
* Better restriction of JavaScript assets as a preventative measure for potential plugin conflicts

= 1.5.5 =
* Re-implemented bulk Attach

= 1.5.4 =
* Updated the way Thickbox is hijacked in an effort to be more stable among tab switching. As an unfortunate result, bulk attaching is no longer possible.
* Added environment check in preparation for future feature updates
* Updated Polish translation, courtesy of Wiktor Maj

= 1.5.3.1 =
* Hotfix for an oversight where Attachments no longer display with Custom Post Types

= 1.5.3.1 =
* PHP warning cleanup
* Settings now respect Custom Post Types that are set to show_ui

= 1.5.3 =
* Added Polish translation, courtesy of Wiktor Maj
* Added Posts and Pages to Settings
* Added new setting to natively 'Attach' Attachments via $post->post_parent


= 1.5.2 =
* Added Swedish translation, courtesy of Sebastian Johansson
* 'Attach' button is now localized
* Fixed a couple of other miscellaneous localization issues
* Added Italian translation, courtesy of Andrea Bersi
* Fixed a number of PHP notices/warnings in more strict environments

= 1.5.1.2 =
* Fixed bug with handling legacy Attachments data store
* Updated localization hook for options screen

= 1.5.1.1 =
* Fixed JS var naming error in IE
* Hid NextGen tab in browser

= 1.5.1 =
* Fixed thumbnail rendering issue
* Fixed issue where browse modal included extraneous items after filtering or searching

= 1.5 =
* Completely revamped the upload/browse experience. Attachments now uses WordPress default modal dialogs.

= 1.1.1 =
* Fixed a bug with storing foreign characters
* Added live search to Browse Existing Dialog

= 1.1 =
* Fixed a bug where Attachments meta box would display on Custom Post Types even when set not to
* Fixed a bug where special characters were lost on save
* Fixed a bug where Browse/Add buttons failed to work when an Editor was not available on a Custom Post Type

= 1.0.9 =
* Support for Custom Post Types (found in Settings)
* Revised Portuguese Translation by [Miriam de Paula](http://www.tecsite.com.br)

= 1.0.8 =
* Fixed possible bug with images not thumbnailing properly
* Tabbed media browsing implemented

= 1.0.7.2 =
* Revised Portuguese Translation by [Nicolas Mollet](http://www.nicolasmollet.com)
* Added French Translation

= 1.0.7.1 =
* Added Portuguese Translation (rough)

= 1.0.7 =
* Numerous fixes to enhance data integrity
* Implemented a change to improve data portability
* Moved to Thickbox (from Shadowbox) as to be more in line with WordPress

= 1.0.5 =
* Added the option to limit available Attachments to the current user (defaults to *false*)

= 1.0.4.1 =
* Removed all shortcodes in an effort to boost compatibility

= 1.0.4 =
* Fixed a potential error resulting in PHP issuing a Warning when trying to attach Attachments

= 1.0.3 =
* Fixed an issue when `attachments_get_attachments()` returning no Attachments generating a warning

= 1.0.2 =
* Fixed an issue with deleting Attachments

= 1.0.1 =
* Fixed an error when adding only one attachment
* Added MIME type array value (`mime`) to available attachments

= 1.0 =
* First stable release

== Upgrade Notice ==

= 3.0 =
**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been *fully deprecated* but is still available. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your wp-config.php:

`define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments`

Version 3 is a *major* rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

= 1.0.8 =
As always, be sure to back up your database and files before upgrading.

= 1.0.7 =
Attachments are now stored in such a way that removes an in-place limitation on string lengths for both titles and captions.

== Roadmap ==

Planned feature additions include:

* Additional field type: textarea
* Additional field type: WYSIWYG
* User-defined limiting the number of Attachments per instance
* User-defined custom fields

== Usage ==

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

You may create additional instances with custom fields by using the `attachments_register` action. To create your own instance add the following to your theme's `functions.php` or your own plugin:

`function my_attachments( $attachments )
{
  $args = array(

    // title of the meta box (string)
    'label'         => 'My Attachments',

    // all post types to utilize (string|array)
    'post_type'     => array( 'post', 'page' ),

    // allowed file type(s) (array) (image|video|text|audio|application)
    'filetype'      => null  // no filetype limit

    // include a note within the meta box (string)
    'note'          => 'Attach files here!',

    // text for 'Attach' button (string)
    'button_text'   => __( 'Attach Files', 'attachments' ),

    // text for modal 'Attach' button (string)
    'modal_text'    => __( 'Attach', 'attachments' ),

    // fields for this instance (array)
    'fields'        => array(
      array(
        'name'  => 'title',                          // unique field name
        'type'  => 'text',                           // registered field type
        'label' => __( 'Title', 'attachments' ),     // label to display
      ),
      array(
        'name'  => 'caption',                        // unique field name
        'type'  => 'text',                           // registered field type
        'label' => __( 'Caption', 'attachments' ),   // label to display
      ),
      array(
        'name'  => 'copyright',                      // unique field name
        'type'  => 'text',                           // registered field type
        'label' => __( 'Copyright', 'attachments' ), // label to display
      ),
    ),

);

  $attachments->register( 'my_attachments', $args ); // unique instance name
}

add_action( 'attachments_register', 'my_attachments' );`

If you would like to **disable the default Attachments instance** add the following to your theme's `functions.php` or plugin:

`function my_disable_attachments_default_instance()
{
  return false;
}

add_filter( 'attachments_disable_default_instance', 'my_disable_attachments_default_instance' );`

Once your instances are set up and working, you'll also need to edit your theme's template files to pull the data to the front end. To retrieve the Attachments for the current post:

`<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
  <h3>Attachments</h3>
  <ul>
    <?php while( $attachment = $attachments->get() ) : ?>
      <li>
        <pre><?php print_r( $attachment ); ?></pre>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>`

You can also retrieve various attributes of the current Attachment using these utility functions:

`<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
  <h3>Attachments</h3>
  <ul>
    <?php while( $attachments->get() ) : ?>
      <li>
        ID: <?php echo $attachments->id(); ?><br />
        Type: <?php echo $attachments->type(); ?><br />
        Subtype: <?php echo $attachments->subtype(); ?><br />
        URL: <?php echo $attachments->url(); ?><br />
        Image: <?php echo $attachments->image( 'thumbnail' ); ?><br />
        Source: <?php echo $attachments->src( 'full' ); ?><br />
        Size: <?php echo $attachments->filesize(); ?><br />
        Title Field: <?php echo $attachments->field( 'title' ); ?><br />
        Caption Field: Name: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>`