This is a WordPress plugin. [Official download available on WordPress Extend](http://wordpress.org/extend/plugins/attachments/).

# Attachments

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

* [Description](#description)
* [Installation](#installation)
* **[Upgrade Notice](#upgrade-notice)** *Pay specific attention if upgrading from a version before 3.0*
* [Usage](#usage)
* [Screenshots](#screenshots)
* [Frequently Asked Questions](#frequently-asked-questions)
* [Changelog](#changelog)
* [Roadmap](#roadmap)

## Description

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

### Updated for WordPress 3.5!

WordPress 3.5 ships with an amazing new Media workflow and Attachments 3.0 makes great use of it. *If you are not running WordPress 3.5, version 1.6.2.1 will be used until you upgrade to WordPress 3.5.*

### Associate Media items with posts

The idea behind Attachments is to give developers the ability to directly associate Media items with any post. This is accomplished by adding a meta box to post edit screens as determined by the developer. Once Media items have been associated with a post, you're able to retrieve those Attachments and include them directly within your template files using any specific markup you wish.

### Integrate Attachments within your theme with fine grained control

**Attachments does not automatically integrate itself with your theme.** Since the idea behind Attachments is to allow integration of Media within posts using developer-crafted, unique markup, *it's up to you to integrate with your theme*. The most basic integration includes editing the [appropriate template file](http://codex.wordpress.org/Template_Hierarchy) and adding your call(s) to Attachments. For example, if you have set up Attachments to be used with your Posts entries, edit `single.php` to include the following within The Loop:

```php
<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
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
        Caption Field: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

That snippet will request all of the existing Attachments defined for the current Post within The Loop, and retrieve each itemized property for that Attachment. Using the provided details you're able to integrate the attached Media items in any way you please.

## Installation

1. Download the plugin and extract the files
1. Upload `attachments` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Implement Attachments in your theme's `functions.php` or your own plugin (see **[Usage](#usage)**)
1. Update your templates where applicable (see **[Usage](#usage)**)

## Upgrade Notice

#### 3.0
**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been *fully deprecated* but is still available. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your `wp-config.php`:

```php
define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments
```

Version 3 is a **major** rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

## Usage

Attachments ships with a `Settings` screen (found under the `Settings` menu in the main WordPress admin navigation) that facilitates data migration from version 1.x and also offers some code snippets. If you would like to **disable the Settings screen** add the following to your theme's `functions.php`:

```php
define( 'ATTACHMENTS_SETTINGS_SCREEN', false ); // disable the Settings screen
```

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

If you would like to *disable the default instance* (meta box titled 'Attachments' with a 'Title' and 'Caption' field) add the following to your `wp-config.php`:

```php
define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );
```

You may create instances with your own custom fields by using the `attachments_register` action. To create your own instance add the following to your theme's `functions.php` or your own plugin:

```php
<?php

function my_attachments( $attachments )
{
  $args = array(

    // title of the meta box (string)
    'label'         => 'My Attachments',

    // all post types to utilize (string|array)
    'post_type'     => array( 'post', 'page' ),

    // allowed file type(s) (array) (image|video|text|audio|application)
    'filetype'      => null,  // no filetype limit

    // include a note within the meta box (string)
    'note'          => 'Attach files here!',

    // text for 'Attach' button in meta box (string)
    'button_text'   => __( 'Attach Files', 'attachments' ),

    // text for modal 'Attach' button (string)
    'modal_text'    => __( 'Attach', 'attachments' ),

    /**
     * Fields for the instance are stored in an array. Each field consists of
     * an array with three keys: name, type, label.
     *
     * name    - (string) The field name used. No special characters.
     * type    - (string) The registered field type.
     *                  Fields available: text, textarea, wysiwyg
     * label   - (string) The label displayed for the field.
     * caption - (string) The default WordPress metadata to use when initially adding the Attachment
     *                  Defaults available: title, caption, alt, description
     */

    'fields'        => array(
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
      )
    ),

  );

  $attachments->register( 'my_attachments', $args ); // unique instance name
}

add_action( 'attachments_register', 'my_attachments' );
```

Once your instances are set up and working, you'll also need to edit your theme's template files to pull the data to the front end. To retrieve the Attachments for the current post, add this within The Loop:

```php
<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
  <h3>Attachments</h3>
  <ul>
    <?php while( $attachment = $attachments->get() ) : ?>
      <li>
        <pre><?php print_r( $attachment ); ?></pre>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

If you want to get the Attachments for a post **outside The Loop**, add a second parameter with the post ID when instantiating Attachments:

```php
<?php
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
<?php endif; ?>
```

You can also retrieve various attributes of the current Attachment directly using these utility functions:

```php
<?php $attachments = new Attachments( 'attachments' ); ?>
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
        Caption Field: Name: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

If you don't want to use the above implementation to loop through your Attachments, can also retrieve them explicitly:

```php
<?php $attachments = new Attachments( 'attachments' ); ?>
<?php if( $attachments->exist() ) : ?>
  <?php if( $attachment = $attachments->get_single( 0 ) ) : ?>
    <h3>Attachment at index 0:</h3>
    <pre><?php print_r( $attachment ); ?></pre>
  <?php endif; ?>
<?php endif; ?>
```

## Screenshots

##### An Attachments meta box sitting below the content editor
![An Attachments meta box sitting below the content editor](http://mondaybynoon.com/images/attachments/screenshot-1.png)

##### Direct integration with WordPress 3.5+ Media
![Direct integration with WordPress 3.5+ Media](http://mondaybynoon.com/images/attachments/screenshot-2.png)

##### Attach multiple files at once
![Attach multiple files at once](http://mondaybynoon.com/images/attachments/screenshot-3.png)

##### Custom fields for each Attachment
![Custom fields for each Attachment](http://mondaybynoon.com/images/attachments/screenshot-4.png)

##### Drag and drop to sort
![Drag and drop to sort](http://mondaybynoon.com/images/attachments/screenshot-5.png)

## Frequently Asked Questions

#### Attachments isn't showing up on my edit screens

You will need to tell Attachments which instances you'd like to use. Please reference the **[Usage](#usage)** instructions.

#### Attachments are not showing up in my theme

You will need to edit your theme files where applicable. Please reference the **[Usage](#usage)** instructions.

#### How do I disable the default Attachments meta box?

You will need to edit your Attachments configuration. Please reference the **[Usage](#usage)** instructions.

#### How do I change the fields for each Attachment?

You will need to edit your Attachments configuration. Please reference the **[Usage](#usage)** instructions.

#### Where are uploads saved?

Attachments uses WordPress' built in Media library for uploads and storage.

#### I lost my Attachments after upgrading!

***DO NOT update any Post/Page/CPT with Attachments***, the data has not been lost. Please [contact me](http://mondaybynoon.com/contact/) to begin a bugfix

## Changelog

<dl>

    <dt>3.1.4</dt>
    <dd>Changed 'Delete' to 'Remove' so as to not make it sound like the file itself would be deleted from Media (props Lane Goldberg)</dd>
    <dd>Better handling of posts that have no Attachments when saving</dd>

    <dt>3.1.3</dt>
    <dd>Fixed a potential issue with the WYSIWYG field not working on CPT without editor support</dd>
    <dd>Field assets are less aggressive and only fire when necessary</dd>
    <dd>Reorganized the migration process a bit in prep for Attachments Pro support</dd>

    <dt>3.1.2</dt>
    <dd>Fixed a regression that prevented successful migration of legacy Attachments data</dd>

    <dt>3.1.1</dt>
    <dd>Fixed a Fatal Error when registering the text field</dd>

    <dt>3.1</dt>
    <dd>New field: wysiwyg</dd>
    <dd>Fields will now properly respect line breaks</dd>
    <dd>Fields will now properly return HTML instead of escaped HTML</dd>

    <dt>3.0.9</dt>
    <dd>Fixed an issue where special characters would break title/caption fields during migration</dd>

    <dt>3.0.8.2</dt>
    <dd>Fixed a CSS issue with only one text field</dd>

    <dt>3.0.8.1</dt>
    <dd>Better storage of special characters for PHP 5.4+</dd>

    <dt>3.0.8</dt>
    <dd>Fixed an issue in Firefox where you weren't able to focus inputs unless you clicked their label</dd>
    <dd>New field: textarea</dd>

    <dt>3.0.7</dt>
    <dd>Proper sanitization of Custom Post Type names (as WordPress does it)</dd>

    <dt>3.0.6</dt>
    <dd>Fixed a possible JavaScript error if an Attachment that's an image doesn't have a proper thumbnail URL</dd>
    <dd>Added a total() method that will return the number of Attachments for the current instance</dd>
    <dd>When requesting the image() for a non-image Attachment, the WordPress-defined icon will be returned</dd>
    <dd>Added an icon() method that will return the WordPress-defined icon for the Attachment</dd>
    <dd>Cleaned up a PHP Warning when trying to save for an undefined field type</dd>
    <dd>Fixed an issue where template tags would be output for non-image Attachments after saving</dd>

    <dt>3.0.5</dt>
    <dd>Fixed a regression in handling Custom Post Type names that would too aggressively interfere with instance regustration</dd>
    <dd>Fixed an issue when working with non-image Attachments</dd>

    <dt>3.0.4</dt>
    <dd>Fixed an issue that prevented the choosing of a Featured Image for a Custom Post Type if Attachments was activated</dd>
    <dd>Attachments now only enqueues its assets on edit screens that actually utilize Attachments</dd>
    <dd>Fixed a potential JavaScript error triggered when a 'thumbnail' image size was not available</dd>
    <dd>Prevented incorrect usage of dashes used in CPT names for post_type argument when registering Attachments instances (fixes an integration issue with WP e-Commerce)</dd>
    <dd>Prevented re-running of migration process to avoid duplicates (e.g. on browser reload)</dd>

    <dt>3.0.3</dt>
    <dd>Fixed an issue that prevented defining a post ID when retrieving Attachments outside The Loop</dd>
    <dd>Cleaned up potential PHP warning when Attachments were requested for a post that had none</dd>

    <dt>3.0.2</dt>
    <dd>Fixed an issue where some HTML entities were not properly stored</dd>

    <dt>3.0.1</dt>
    <dd>Fixed an issue where legacy mode was always enabled</dd>

    <dt>3.0</dt>
    <dd> <strong>Major</strong> rewrite. After three years of development, Attachments has been rewritten to make
          even better use of what WordPress has to offer</dd>
    <dd> Utilizes the brand spanking new 3.5 Media workflow</dd>
    <dd> Configuration now takes place within your theme or a plugin</dd>
    <dd> Multiple meta boxes! You can segment groups of Attachments with new instances, each unique</dd>
    <dd> Dynamic fields! You can manipulate which fields each instance uses</dd>
    <dd> File type limits. Limit which files are available to Attachments (e.g. images, audio, video)</dd>

</dl>

## Roadmap

Planned feature additions include:

* Additional field type: checkbox
* Additional field type: radio
* Additional field type: select
* User-defined limiting the number of Attachments per instance
* User-defined custom field types
* Additional hooks/actions from top to bottom
* Shortcode(s)
* Output templates
