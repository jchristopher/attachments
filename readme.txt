=== Attachments ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, posts, pages, images, PDF, doc, Word, image, jpg, jpeg, picture, pictures, photos, attachment
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 3.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

== Description ==

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

= Updated for WordPress 3.5! =

WordPress 3.5 ships with an amazing new Media workflow and Attachments 3.0 makes great use of it. *If you are not running WordPress 3.5, version 1.6.2.1 will be used until you upgrade to WordPress 3.5.*

= Associate Media items with posts =

The idea behind Attachments is to give developers the ability to directly associate Media items with any post. This is accomplished by adding a meta box to post edit screens as determined by the developer. Once Media items have been associated with a post, you're able to retrieve those Attachments and include them directly within your template files using any specific markup you wish.

= Integrate Attachments within your theme with fine grained control =

**Attachments does not automatically integrate itself with your theme.** Since the idea behind Attachments is to allow integration of Media within posts using developer-crafted, unique markup, *it's up to you to integrate with your theme*. The most basic integration includes editing the [appropriate template file](http://codex.wordpress.org/Template_Hierarchy) and adding your call(s) to Attachments. For example, if you have set up Attachments to be used with your Posts entries, edit `single.php` to include the following within The Loop:

`<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
  <h3>Attachments</h3>
  <p>Total Attachments: <?php echo $attachments->total(); ?></p>
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
        Caption Field: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>`

That snippet will request all of the existing Attachments defined for the current Post within The Loop, and retrieve each itemized property for that Attachment. Using the provided details you're able to integrate the attached Media items in any way you please.

There is a lot more information on [Attachments' GitHub page](https://github.com/jchristopher/attachments). Please contribute!

== Installation ==

1. Download the plugin and extract the files
1. Upload `attachments` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Implement Attachments in your theme's `functions.php` or your own plugin (see **Other Notes > Usage**)
1. Update your templates where applicable (see **Other Notes > Usage**)

= Upgrading from version 1.x =

**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been *fully deprecated* but is still available. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your wp-config.php:

`define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments`

Version 3 is a *major* rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

== Frequently Asked Questions ==

= Attachments isn't showing up on my edit screens =

You need to turn on Attachments for your post types. View the Attachments settings under the main Settings menu in the WordPress admin.

= Attachments are not showing up in my theme =

You will need to edit your theme files where applicable. Please reference the **Other Notes > Usage** instructions.

= How do I disable the default Attachments meta box? =

You will need to edit your Attachments configuration. Please reference the **Other Notes > Usage** instructions.

= How do I change the fields for each Attachment? =

You will need to edit your Attachments configuration. Please reference the **Other Notes > Usage** instructions.

= Where are uploads saved? =

Attachments uses WordPress' built in Media library for uploads and storage.

= I lost my Attachments after upgrading! =

***DO NOT update any Post/Page/CPT that should have existing Attachments***, the data *has not been lost*.

If you disabled the default intsance after migrating, used the default values when migrating, and used a snippet from the docs to add a new one, **make sure your instance names match**. The default value during migration uses an instance name of `attachments` while many snippets use `my_attachments`.

Else: please reference the **Installation > Upgrade Notice** details.

== Screenshots ==

1. An Attachments meta box sitting below the content editor
2. Direct integration with WordPress 3.5+ Media
3. Attach multiple files at once
4. Custom fields for each Attachment
5. Drag and drop to sort

== Changelog ==

= 3.3.3 =
* Fixed a PHP Warning when activated using Multisite

= 3.3.2 =
* You can now specify which view is default when browsing the Media modal (e.g. have 'Upload Files' be default instead of 'Media Library')

= 3.3.1 =
* Added meta box positioning arguments when registering instances
* Cleaned up some CSS when Attachments instances are in the sidebar

= 3.3 =
* Added a `search()` method to allow searching for Attachments based on their attributes (e.g. attachment ID, post ID, post type, field values, etc.)
* Improved the 'Remove' animation
* New field: select
* New parameter for Attachments attributes methods. You can pass the index (`int`) of the Attachment you'd like to utilize when firing the method.

= 3.2 =
* Added option to disable the Settings screen
* Added the ability to set a default for fields using the metadata that exists in WordPress. Available defaults include: title, caption, alt, and description. If set, the metadata for the correlating field will be used as the field default when initially adding an Attachment from the Media modal. Only applies to text, textarea, and wysiwyg fields.
* Added a `get_single()` method that allows you to specifically retrieve a single Attachment
* Clarified some documentation

= 3.1.4 =
* Changed 'Delete' to 'Remove' so as to not make it sound like the file itself would be deleted from Media (props Lane Goldberg)
* Better handling of posts that have no Attachments when saving

= 3.1.3 =
* Fixed a potential issue with the WYSIWYG field not working on CPT without editor support
* Field assets are less aggressive and only fire when necessary
* Reorganized the migration process a bit in prep for Attachments Pro support

= 3.1.2 =
* Fixed a regression that prevented successful migration of legacy Attachments data

= 3.1.1 =
* Fixed a Fatal Error when registering the text field

= 3.1 =
* New field: wysiwyg
* Fields will now properly respect line breaks
* Fields will now properly return HTML instead of escaped HTML

= 3.0.9 =
* Fixed an issue where special characters would break title/caption fields during migration

= 3.0.8.2 =
* Fixed a CSS issue with only one text field

= 3.0.8.1 =
* Better storage of special characters for PHP 5.4+

= 3.0.8 =
* Fixed an issue in Firefox where you weren't able to focus inputs unless you clicked their label
* New field: textarea

= 3.0.7 =
* Proper sanitization of Custom Post Type names (as WordPress does it)

= 3.0.6 =
* Fixed a possible JavaScript error if an Attachment that's an image doesn't have a proper thumbnail URL
* Added a total() method that will return the number of Attachments for the current instance
* When requesting the image() for a non-image Attachment, the WordPress-defined icon will be returned
* Added an icon() method that will return the WordPress-defined icon for the Attachment
* Cleaned up a PHP Warning when trying to save for an undefined field type
* Fixed an issue where template tags would be output for non-image Attachments after saving

= 3.0.5 =
* Fixed a regression in handling Custom Post Type names that would too aggressively interfere with instance regustration
* Fixed an issue when working with non-image Attachments

= 3.0.4 =
* Fixed an issue that prevented the choosing of a Featured Image for a Custom Post Type if Attachments was activated
* Attachments now only enqueues its assets on edit screens that actually utilize Attachments
* Fixed a potential JavaScript error triggered when a 'thumbnail' image size was not available
* Prevented incorrect usage of dashes used in CPT names for post_type argument when registering Attachments instances (fixes an integration issue with WP e-Commerce)
* Prevented re-running of migration process to avoid duplicates (e.g. on browser reload)

= 3.0.3 =
* Fixed an issue that prevented defining a post ID when retrieving Attachments outside The Loop
* Cleaned up potential PHP warning when Attachments were requested for a post that had none

= 3.0.2 =
* Fixed an issue where some HTML entities were not properly stored

= 3.0.1 =
* Fixed an issue where legacy mode was always enabled

= 3.0 =
* **Major** rewrite. After three years of development, Attachments has been rewritten to make
      even better use of what WordPress has to offer
* Utilizes the brand spanking new 3.5 Media workflow
* Configuration now takes place within your theme or a plugin
* Multiple meta boxes! You can segment groups of Attachments with new instances, each unique
* Dynamic fields! You can manipulate which fields each instance uses
* File type limits. Limit which files are available to Attachments (e.g. images, audio, video)

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
Now piggybacking the awesome Media workflow introduced in WordPress 3.5

= 1.0.8 =
As always, be sure to back up your database and files before upgrading.

= 1.0.7 =
Attachments are now stored in such a way that removes an in-place limitation on string lengths for both titles and captions.

== Roadmap ==

Planned feature additions include:

* Additional field type: checkbox
* Additional field type: radio
* User-defined limiting the number of Attachments per instance
* User-defined custom field types
* Additional hooks/actions from top to bottom
* Shortcode(s)
* Output templates

== Usage ==

= Disable Settings Screen =

Attachments ships with a `Settings` screen (found under the `Settings` menu in the main WordPress admin navigation) that facilitates data migration from version 1.x and also offers some code snippets. If you would like to **disable the Settings screen** add the following to your theme's `functions.php`:

`define( 'ATTACHMENTS_SETTINGS_SCREEN', false ); // disable the Settings screen`

= Setting Up Instances =

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

**Disable the Default Instance**

If you would like to *disable the default instance* (meta box titled 'Attachments' with a 'Title' and 'Caption' field) add the following to your `wp-config.php`:

`define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );`

**Create Custom Instances**

You may create instances with your own custom fields by using the `attachments_register` action. To create your own instance add the following to your theme's `functions.php` or your own plugin:

`<?php

function my_attachments( $attachments )
{
  $fields         => array(
    array(
      'name'      => 'title',                         // unique field name
      'type'      => 'text',                          // registered field type
      'label'     => __( 'Title', 'attachments' ),    // label to display
      'default'   => 'title',                         // default value upon selection
    ),
    array(
      'name'      => 'caption',                       // unique field name
      'type'      => 'textarea',                      // registered field type
      'label'     => __( 'Caption', 'attachments' ),  // label to display
      'default'   => 'caption',                       // default value upon selection
    ),
  );

  $args = array(

    // title of the meta box (string)
    'label'         => 'My Attachments',

    // all post types to utilize (string|array)
    'post_type'     => array( 'post', 'page' ),

    // meta box position (string) (normal, side or advanced)
    'position'      => 'normal',

    // meta box priority (string) (high, default, low, core)
    'priority'      => 'high',

    // allowed file type(s) (array) (image|video|text|audio|application)
    'filetype'      => null,  // no filetype limit

    // include a note within the meta box (string)
    'note'          => 'Attach files here!',

    // text for 'Attach' button in meta box (string)
    'button_text'   => __( 'Attach Files', 'attachments' ),

    // text for modal 'Attach' button (string)
    'modal_text'    => __( 'Attach', 'attachments' ),

    // which tab should be the default in the modal (string) (browse|upload)
    'router'        => 'browse',

    // fields array
    'fields'        => $fields,

  );

  $attachments->register( 'my_attachments', $args ); // unique instance name
}

add_action( 'attachments_register', 'my_attachments' );`

**Fields Reference**

At this time there are **four** field types available:

1. `text`
1. `textarea`
1. `select`
1. `wysiwyg`

When declaring fields for your instance, you'll be composing an array of fields, each with an array of parameters that set the various attributes of each field. Here is a full example of all available parameters for all available fields:

`<?php
/**
 * Fields for the instance are stored in an array. Each field consists of
 * an array with three required keys: name, type, label
 * and one optional key: meta
 *
 * name    - (string) The field name used. No special characters.
 * type    - (string) The registered field type.
 *                  Fields available: text, textarea, wysiwyg, select
 * label   - (string) The label displayed for the field.
 * default - (string) The default WordPress metadata to use when initially adding the Attachment
 *                  Defaults available: title, caption, alt, description
 * meta    - (array) The field-specific parameters that apply only to that field type
 */

$fields         => array(
  array(
    'name'      => 'title',                             // unique field name
    'type'      => 'text',                              // registered field type
    'label'     => __( 'Title', 'attachments' ),        // label to display
    'default'   => 'title',                             // default value upon selection
  ),
  array(
    'name'      => 'caption',                           // unique field name
    'type'      => 'textarea',                          // registered field type
    'label'     => __( 'Caption', 'attachments' ),      // label to display
    'default'   => 'caption',                           // default value upon selection
  ),
  array(
    'name'      => 'option',                            // unique field name
    'type'      => 'select',                            // registered field type
    'label'     => __( 'Option', 'attachments' ),       // label to display
    'meta'      => array(                               // field-specific meta as defined by field class
                    'allow_null'    => true,            // allow null value? (adds 'empty' <option>)
                    'multiple'      => true,            // multiple <select>?
                    'options'       => array(           // the <option>s to use
                          '1'     => 'Option 1',
                          '2'     => 'Option 2',
                      )
                  ),
  ),
  array(
    'name'      => 'description',                       // unique field name
    'type'      => 'wysiwyg',                           // registered field type
    'label'     => __( 'Description', 'attachments' ),  // label to display
    'default'   => 'description',                       // default value upon selection
  ),
);
?>`

= Pulling Attachments to your Theme =

Once your instances are set up and working, you'll also need to edit your theme's template files to pull the data to the front end. To retrieve the Attachments for the current post, add this within The Loop:

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

**Retrieve Attachments Outside The Loop**

If you want to get the Attachments for a post **outside The Loop**, add a second parameter with the post ID when instantiating Attachments:

`<?php
  // retrieve all Attachments for the 'attachments' instance of post 123
  $attachments = new Attachments( 'attachments', 123 );
?>
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

**Retrieve Attachment Attributes**

You can also retrieve various attributes of the current Attachment using these utility functions:

`<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
  <h3>Attachments</h3>
  <p>Total Attachments: <?php echo $attachments->total(); ?></p>
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
        Caption Field: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>`

**Retrieve Single Attachments**

If you don't want to use the above implementation to loop through your Attachments, can also retrieve them explicitly:

`<?php $attachments = new Attachments( 'attachments' ); ?>
<?php if( $attachments->exist() ) : ?>
  <?php $my_index = 0; ?>
  <?php if( $attachment = $attachments->get_single( $my_index ) ) : ?>
    <h3>Attachment at index 0:</h3>
    <pre><?php print_r( $attachment ); ?></pre>
    <ul>
      <li>
        ID: <?php echo $attachments->id( $my_index ); ?><br />
        Type: <?php echo $attachments->type( $my_index ); ?><br />
        Subtype: <?php echo $attachments->subtype( $my_index ); ?><br />
        URL: <?php echo $attachments->url( $my_index ); ?><br />
        Image: <?php echo $attachments->image( 'thumbnail', $my_index ); ?><br />
        Source: <?php echo $attachments->src( 'full', $my_index ); ?><br />
        Size: <?php echo $attachments->filesize( $my_index ); ?><br />
        Title Field: <?php echo $attachments->field( 'title', $my_index ); ?><br />
        Caption Field: Name: <?php echo $attachments->field( 'caption', $my_index ); ?>
      </li>
    </ul>
  <?php endif; ?>
<?php endif; ?>`

= Search =

Attachments provides a method of searching it's own data using a number of attributes. This faciliates a search to be as widespread or as specific as you'd like.

`<?php
  $attachments = new Attachments();

  $search_args = array(
      'instance'      => 'attachments',       // search all instances
      'fields'        => array( 'caption' ),  // search the 'caption' field only
    );

  $attachments->search( 'lorem ipsum', $search_args );  // search for 'lorem ipsum'

  if( $attachments->exist() ) : ?>
    <h3>Attachments</h3>
    <ul>
      <?php while( $attachments->get() ) : ?>
        <li>
          Attachment ID: <?php echo $attachments->id(); ?><br />
          Post ID: <?php echo $attachments->post_id(); ?><br />
          Title Field: <?php echo $attachments->field( 'title' ); ?><br />
          Caption Field: <?php echo $attachments->field( 'caption' ); ?>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif;
?>`

The full list of available search arguments (and their defaults) is as follows:

`<?php
  $defaults = array(
      'attachment_id' => null,            // (int) not searching for a single attachment ID
      'instance'      => 'attachments',   // (string) the instance you want to search
      'post_type'     => null,            // (string) search 'any' post type
      'post_id'       => null,            // (int) searching all posts
      'post_status'   => 'publish',       // (string) search only published posts
      'fields'        => null,            // (string|array) search all fields
  );
?>`

Once you've performed your search, you can loop through the returned Attachments as you normally would.
