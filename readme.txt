=== Attachments ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, posts, pages, images, PDF, doc, Word, image, jpg, jpeg, picture, pictures, photos, attachment
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

== Description ==

**Extensive** usage instructions are [available on GitHub](https://github.com/jchristopher/attachments#usage)

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

= Updated for WordPress 3.5! =

WordPress 3.5 ships with an amazing new Media workflow and Attachments 3.0 makes great use of it. *If you are not running WordPress 3.5, the (now deprecated) version 1.6.2.1 (included with Attachments 3.x) will be used until you upgrade to WordPress 3.5+*

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

Version 3 is a *major* rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

== Frequently Asked Questions ==

Please see [Issues on GitHub](https://github.com/jchristopher/attachments/issues)

== Screenshots ==

1. An Attachments meta box sitting below the content editor
2. Direct integration with WordPress 3.5+ Media
3. Attach multiple files at once
4. Custom fields for each Attachment
5. Drag and drop to sort

== Changelog ==

Please see [Attachments on GitHub](https://github.com/jchristopher/attachments#changelog)

== Upgrade Notice ==

= 3.0 =
Now piggybacking the awesome Media workflow introduced in WordPress 3.5

== Roadmap ==

Please see [Attachments on GitHub](https://github.com/jchristopher/attachments#roadmap)

== Usage ==

**Extensive** usage instructions are [available on GitHub](https://github.com/jchristopher/attachments#usage)
