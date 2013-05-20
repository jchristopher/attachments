=== Attachments ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, posts, pages, images, PDF, doc, Word, image, jpg, jpeg, picture, pictures, photos, attachment
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

== Description ==

**Extensive** usage instructions are [available on GitHub](https://github.com/jchristopher/attachments/#attachments)

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

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

**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been **fully deprecated** but is still available *and included with Attachments 3.x*. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your `wp-config.php`:

`define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments`

Version 3 is a *major* rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your database prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

== Frequently Asked Questions ==

Please see [Issues on GitHub](https://github.com/jchristopher/attachments/issues)

== Screenshots ==

1. An Attachments meta box sitting below the content editor
2. Direct integration with WordPress 3.5+ Media
3. Attach multiple files at once
4. Custom fields for each Attachment
5. Drag and drop to sort

== Changelog ==

Please see [Attachments' changelog on GitHub](https://github.com/jchristopher/attachments/docs/changelog.md)

= 3.5.1 =
* Fixed an issue where changing an Attachment changed all attachments

= 3.5 =
* Initial implementation of limiting the number of Attachments
* You can now change an Attachment asset without having to remove the entire Attachment and re-add something new
* New filter: `attachments_location_{my_instance}` (where `**{my_instance}**` is your instance name) allows for more fine-grained control over where meta boxes show up (e.g. limiting to your Home page)
* New action: `attachments_extension` facilitates Attachments extensions
* New method `width( $size )` to retrieve the width of the current Attachment
* New method `height( $size )` to retrieve the height of the current Attachment
* New document structure, various additions to documentation
* Attachments Pro migration script. If you've been waiting to migrate from Attachments Pro please  **back up your database** and run the migration script.
* Fixed an asset URL issue if Attachments is added as a must-use plugin
* Italian translation (props Marco Chiesi)

= 3.4.3 =
* Attachments now takes into account media deleted outside Attachments meta boxes and removes deleted attachments automatically
* Added working Polish translation, props <a href="https://github.com/mleczakm">@mleczakm</a>

= 3.4.2.1 =
* Fixed a regression that prevented the `type` method from returning

= 3.4.2 =
* Fixed an issue where the `languages` directory wouldn't be utilized for l10n
* Search now respects custom `meta_key`
* You can now pass in a `filetype` parameter when searching to limit results in that way

= 3.4.1 =
* Class abstraction and cleanup
* Better support for plugin-created custom image sizes

= 3.4 =
* New filter: `attachments_meta_key` facilitates using a different meta key for Attachments storage
* New filter: `attachments_get_ **{my_instance}**` (where `**{my_instance}**` is your instance name) allows you to filter Attachments per instance once they've been retrieved
* Fixed an issue where retrieving single Attachments didn't properly pass the index to attribute methods
* Fixed PHP Warnings when Network Activating
* You can now have new Attachments <em>prepend</em> the list instead of append by setting `append => false` in your instance

= 3.3.3 =
* Fixed a PHP Warning when activated using Multisite
* Slightly modified the migration process to better handle plugins like WPML (props sebastian.friedrich)

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
* Added a `total()` method that will return the number of Attachments for the current instance
* When requesting the `image()` for a non-image Attachment, the WordPress-defined icon will be returned
* Added an `icon()` method that will return the WordPress-defined icon for the Attachment
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
*   **Major** rewrite. After three years of development, Attachments has been rewritten to make
      even better use of what WordPress has to offer
*  Utilizes the brand spanking new 3.5 Media workflow
*  Configuration now takes place within your theme or a plugin
*  Multiple meta boxes! You can segment groups of Attachments with new instances, each unique
*  Dynamic fields! You can manipulate which fields each instance uses
*  File type limits. Limit which files are available to Attachments (e.g. images, audio, video)

== Upgrade Notice ==

= 3.0 =
Now piggybacking the awesome Media workflow introduced in WordPress 3.5

== Roadmap ==

Please see [Attachments on GitHub](https://github.com/jchristopher/attachments/docs/roadmap.md)

== Usage ==

**Extensive** usage instructions are [available on GitHub](https://github.com/jchristopher/docs/usage.md)
