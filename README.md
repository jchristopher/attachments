This is a WordPress plugin. [Official download available on WordPress Extend](http://wordpress.org/extend/plugins/attachments/).

# Attachments

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

* [Description](#description)
* [Installation](#installation)
* **[Upgrade Notice](#upgrade-notice)** *Pay specific attention if upgrading from a version before 3.0*
* [Usage](#usage)
    * [Disable Settings Screen](#disable-settings-screen)
    * [Setting Up Instances](#setting-up-instances) - Create meta boxes for your Posts, Pages, and Custom Post Types
        * [Disable the Default Instance](#disable-the-default-instance)
        * [Create Custom Instances](#create-custom-instances)
        * [Fields Reference](#fields-reference)
    * [Pulling Attachments to your Theme](#pulling-attachments-to-your-theme)
        * [Retrieve Attachments Outside The Loop](#retrieve-attachments-outside-the-loop)
        * [Retrieve Attachment Attributes](#retrieve-attachment-attributes)
        * [Retrieve Single Attachments](#retrieve-single-attachments)
    * [Search](#search)
* [Filters](#filters)
    * [Post Meta Key](#post-meta-key) - Change the `meta_key` used to store Attachments' data
    * [Get Attachments](#get-attachments) - Edit the order of Attachments in your theme
* [Screenshots](#screenshots)
* [Frequently Asked Questions](#frequently-asked-questions)
* [Changelog](#changelog)
* [Roadmap](#roadmap)

## Description

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

### Updated for WordPress 3.5!

WordPress 3.5 ships with an amazing new Media workflow and Attachments 3.0 makes great use of it. *If you are not running WordPress 3.5, the (now deprecated) version 1.6.2.1 (included with Attachments 3.x) will be used until you upgrade to WordPress 3.5+*

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
**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been **fully deprecated** but is still available *and included with Attachments 3.x*. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your `wp-config.php`:

```php
define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments
```

Version 3 is a **major** rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

## Usage

Attachments is based on *instances* which correlate directly with the meta boxes that appear on edit screens of Posts, Pages, and Custom Post Types. By default Attachments ships with a single meta box that appears *only on Posts and Pages*. It has two fields: one for Title and one for Caption. If you would like to disable or customize the default instance, or you'd like to create additional instances with custom fields for different post types, please see [Setting Up Instances](#setting-up-instances).

### Disable Settings Screen

Attachments ships with a `Settings` screen (found under the `Settings` menu in the main WordPress admin navigation) that facilitates data migration from version 1.x and also offers some code snippets. If you would like to **disable the Settings screen** add the following to your theme's `functions.php`:

```php
define( 'ATTACHMENTS_SETTINGS_SCREEN', false ); // disable the Settings screen
```

### Setting Up Instances

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

#### Disable the Default Instance

If you would like to *disable the default instance* (meta box titled 'Attachments' with a 'Title' and 'Caption' field) add the following to your `wp-config.php`:

```php
define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );
```

#### Create Custom Instances

You may create instances with your own custom fields by using the `attachments_register` action. To create your own instance add the following to your theme's `functions.php` or your own plugin:

```php
<?php

function my_attachments( $attachments )
{
  $fields         = array(
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

    // by default new Attachments will be appended to the list
    // but you can have then prepend if you set this to false
    'append'        => true,

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

add_action( 'attachments_register', 'my_attachments' );
```

#### Fields Reference

At this time there are **four** field types available:

1. `text`
1. `textarea`
1. `select`
1. `wysiwyg`

When declaring fields for your instance, you'll be composing an array of fields, each with an array of parameters that set the various attributes of each field. Here is a full example of all available parameters for all available fields:

```php
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
```

### Pulling Attachments to your Theme

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

#### Retrieve Attachments Outside The Loop

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

#### Retrieve Attachment Attributes

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
        Caption Field: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

#### Retrieve Single Attachments

If you don't want to use the above implementation to loop through your Attachments, can also retrieve them explicitly:

```php
<?php $attachments = new Attachments( 'attachments' ); ?>
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
        Caption Field: <?php echo $attachments->field( 'caption', $my_index ); ?>
      </li>
    </ul>
  <?php endif; ?>
<?php endif; ?>
```

### Search

Attachments provides a method of searching it's own data using a number of attributes. This faciliates a search to be as widespread or as specific as you'd like.

```php
<?php
  $attachments = new Attachments();

  $search_args = array(
      'instance'      => 'attachments',       // search all instances
      'fields'        => array( 'caption' ),  // search the 'caption' field only
    );

  $attachments->search( 'lorem ipsum', $search_args ); // search for 'lorem ipsum'

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
?>
```

The full list of available search arguments (and their defaults) is as follows:

```php
$defaults = array(
      'attachment_id' => null,            // (int) not searching for a single attachment ID
      'instance'      => 'attachments',   // (string) the instance you want to search
      'post_type'     => null,            // (string) search 'any' post type
      'post_id'       => null,            // (int) searching all posts
      'post_status'   => 'publish',       // (string) search only published posts
      'fields'        => null,            // (string|array) search all fields
  );
```

Once you've performed your search, you can loop through the returned Attachments as you normally would.

## Filters

Attachments makes use of various filters to allow customization of it's internals without having to edit any of the code within the plugin. These filters can be utilized within your theme's `functions.php`.

### Post Meta Key

Attachments stores it's data in the `postmeta` table of the WordPress database alongside your other Custom Field data. The default `meta_key` is `attachments` but you might want to change the `meta_key` Attachments uses to store it's data. You can use the `attachments_meta_key` filter to do just that:

```php
function my_attachments_meta_key()
{
    return '_my_attachments_meta_key';
}

add_filter( 'attachments_meta_key', 'my_attachments_meta_key' );
```

Adding the above to your theme's `functions.php` will tell Attachments to save all of it's data using a `meta_key` of `_my_attachments_meta_key` (keys prefixed with _ will be hidden from the Custom Fields meta box).

### Get Attachments

There may be a time where you'd like to alter Attachments' data before working with it in your theme. For example you may want to randomize Attachments before outputting them. The `attachments_get_{$instance}` filter allows you to do just that:

```php
function my_attachments_randomize( $attachments )
{
    return shuffle( $attachments );
}

add_filter( 'attachments_get_my_attachments', 'my_attachments_randomize' );
```

**NOTE** that this filter *depends on your instance name*. In the example above the filter only applies when working with the `my_attachments` instance. If your instance name was `foo_bar` and you wanted to reverse the order of your Attachments before using them, the filter would look like this:

```php
function my_attachments_reverse( $attachments )
{
    return array_reverse( $attachments );
}

add_filter( 'attachments_get_foo_bar', 'my_attachments_reverse' );
```

Please keep in mind the instance name requirement when setting up this filter.

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

***DO NOT update any Post/Page/CPT with Attachments***, the data has not been lost. Please reference the **[Upgrade notice](#upgrade-notice)**.

## Changelog

<dl>

    <dt>3.4.1</dt>
    <dd>Class abstraction and cleanup</dd>
    <dd>Better support for plugin-created custom image sizes</dd>

    <dt>3.4</dt>
    <dd>New filter: <code>attachments_meta_key</code> facilitates using a different meta key for Attachments storage</dd>
    <dd>New filter: <code>attachments_get_<strong>{my_instance}</strong></code> (where <code><strong>{my_instance}</strong></code> is your instance name) allows you to filter Attachments per instance once they've been retrieved</dd>
    <dd>Fixed an issue where retrieving single Attachments didn't properly pass the index to attribute methods</dd>
    <dd>Fixed PHP Warnings when Network Activating</dd>
    <dd>You can now have new Attachments <em>prepend</em> the list instead of append by setting <code>append => false</code> in your instance</dd>

    <dt>3.3.3</dt>
    <dd>Fixed a PHP Warning when activated using Multisite</dd>
    <dd>Slightly modified the migration process to better handle plugins like WPML (props sebastian.friedrich)</dd>

    <dt>3.3.2</dt>
    <dd>You can now specify which view is default when browsing the Media modal (e.g. have 'Upload Files' be default instead of 'Media Library')</dd>

    <dt>3.3.1</dt>
    <dd>Added meta box positioning arguments when registering instances</dd>
    <dd>Cleaned up some CSS when Attachments instances are in the sidebar</dd>

    <dt>3.3</dt>
    <dd>Added a <code>search()</code> method to allow searching for Attachments based on their attributes (e.g. attachment ID, post ID, post type, field values, etc.)</dd>
    <dd>Improved the 'Remove' animation</dd>
    <dd>New field: select</dd>
    <dd>New parameter for Attachments attributes methods. You can pass the index (<code>int</code>) of the Attachment you'd like to utilize when firing the method.</dd>

    <dt>3.2</dt>
    <dd>Added option to disable the Settings screen</dd>
    <dd>Added the ability to set a default for fields using the metadata that exists in WordPress. Available defaults include: title, caption, alt, and description. If set, the metadata for the correlating field will be used as the field default when initially adding an Attachment from the Media modal. Only applies to text, textarea, and wysiwyg fields.</dd>
    <dd>Added a <code>get_single()</code> method that allows you to specifically retrieve a single Attachment</dd>
    <dd>Clarified some documentation</dd>

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
    <dd>Added a <code>total()</code> method that will return the number of Attachments for the current instance</dd>
    <dd>When requesting the </code>image()</code> for a non-image Attachment, the WordPress-defined icon will be returned</dd>
    <dd>Added an <code>icon()</code> method that will return the WordPress-defined icon for the Attachment</dd>
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
* User-defined limiting the number of Attachments per instance
* User-defined custom field types
* Shortcode(s)
* Output templates
