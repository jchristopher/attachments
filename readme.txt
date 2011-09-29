=== Attachments ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, posts, pages, images, PDF, doc, Word, image, jpg, jpeg, picture, pictures, photos, attachment
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.5.9

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

== Description ==

= Attachments Pro Now Available! =

Attachments Pro brings a number of frequently requested features:

* Multiple Attachments instances on edit screens
* Customizable, limitless fields and labels
* Ability to define rules limiting the availability of Attachments on edit screens
* Limit the number of Attachments that can be added
* Limit Attach-able Media items by file/mime type
* Shortcode support
* Auto-inclusion of Attachments content within `the_content()`

**Much more information** available about [Attachments Pro](http://mondaybynoon.com/store/attachments-pro/)

= About Attachments =

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

== Installation ==

1. Download the plugin and extract the files
1. Upload `attachments` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. View the Attachments settings (located under the main Settings menu in the WordPress admin) and turn on Attachments for your desired post types
1. Update your templates where applicable (see **Usage**)

== Frequently Asked Questions ==

= Attachments isn't showing up on my edit screens =

You need to turn on Attachments for your post types. View the Attachments settings under the main Settings menu in the WordPress admin.

= Attachments are not showing up in my theme =

You will need to edit your theme files where applicable. Please reference the **Usage** instructions.

= Where are uploads saved? =

Attachments uses WordPress' built in Media library for uploads and storage.

= I lost my Attachments after upgrading! =

***DO NOT update any Post/Page/CPT with Attachments***, the data has not been lost. Please [contact me](http://mondaybynoon.com/contact/) to begin a bugfix

== Screenshots ==

1. Attachments meta box as it appears on Posts, Pages, or Custom Post Types
2. Native WordPress browse modal dialog, slightly customized for Attachments. Upload straight from your computer.
4. Once assets have been attached, you can customize the title, caption, and order

== Changelog ==

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

= 1.0.8 =
As always, be sure to back up your database and files before upgrading.

= 1.0.7 =
Attachments are now stored in such a way that removes an in-place limitation on string lengths for both titles and captions.

== Roadmap ==

Planned feature additions include:

* Update Settings to use official Settings API

== Usage ==

After installing Attachments, you will need to update your template files in order to pull the data to the front end.

To pull all Attachments for a Post or Page, fire `attachments_get_attachments()`. There is one optional parameter which can force a Post ID if `attachments_get_attachments()` is fired outside The Loop. If used inside The Loop, all Attachments will be pulled for the current Post or Page.

Firing `attachments_get_attachments()` returns an array consisting of all available Attachments. Currently each Attachment has four pieces of data available:

* **title** - The attachment Title
* **caption** - The attachment Caption
* **id** - The WordPress assigned attachment id (for use with other WordPress media functions)
* **location** - The attachment URI
* **mime** - The attachment MIME type (as defined by WordPress)
* **filesize** - Formatted file size

Here is a basic implementation:

`<?php
  if( function_exists( 'attachments_get_attachments' ) )
  {
    $attachments = attachments_get_attachments();
    $total_attachments = count( $attachments );
    if( $total_attachments ) : ?>
      <ul>
      <?php for( $i=0; $i<$total_attachments; $i++ ) : ?>
        <li><?php echo $attachments[$i]['title']; ?></li>
        <li><?php echo $attachments[$i]['caption']; ?></li>
        <li><?php echo $attachments[$i]['id']; ?></li>
        <li><?php echo $attachments[$i]['location']; ?></li>
        <li><?php echo $attachments[$i]['mime']; ?></li>
        <li><?php echo $attachments[$i]['filesize']; ?></li>
      <?php endfor; ?>
      </ul>
    <?php endif; ?>
<?php } ?>`