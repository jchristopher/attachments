This is a WordPress plugin. [Official download available on WordPress Extend](http://wordpress.org/extend/plugins/attachments/).

# Attachments

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

## Description

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types. This plugin *does not* directly interact with your theme, you will need to edit your template files.

## Installation

1. Download the plugin and extract the files
1. Upload `attachments` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Implement Attachments in your theme's `functions.php` or your own plugin (see **Usage**)
1. Update your templates where applicable (see **Usage**)

## Usage

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

If you would like to *disable the default instance* (meta box titled 'Attachments' with a 'Title' and 'Caption' field) add the following to your `wp-config.php`:

```php
<?php
function my_disable_attachments_default_instance()
{
  return false;
}

add_filter( 'attachments_disable_default_instance', 'my_disable_attachments_default_instance' );
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

add_action( 'attachments_register', 'my_attachments' );
```

If you would like to **disable the default Attachments instance** add the following to your theme's `functions.php` or plugin:

```php
<?php
function my_disable_attachments_default_instance()
{
  return false;
}

add_filter( 'attachments_disable_default_instance', 'my_disable_attachments_default_instance' );
```

Once your instances are set up and working, you'll also need to edit your theme's template files to pull the data to the front end. To retrieve the Attachments for the current post:

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

You can also retrieve various attributes of the current Attachment using these utility functions:

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
        Caption Field: Name: <?php echo $attachments->field( 'caption' ); ?>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
```

## Screenshots

![An Attachments meta box sitting below the content editor](http://mondaybynoon.com/wp-content/uploads/attachments-1.png)

![Direct integration with WordPress 3.5+ Media](http://mondaybynoon.com/wp-content/uploads/attachments-2.png)

![Attach multiple files at once](http://mondaybynoon.com/wp-content/uploads/attachments-3.png)

![Custom fields for each Attachment](http://mondaybynoon.com/wp-content/uploads/attachments-4.png)

![Drag and drop to sort](http://mondaybynoon.com/wp-content/uploads/attachments-5.png)

## Frequently Asked Questions

#### Attachments isn't showing up on my edit screens

You will need to tell Attachments which instances you'd like to use. Please reference the **Usage** instructions.

#### Attachments are not showing up in my theme

You will need to edit your theme files where applicable. Please reference the **Usage** instructions.

#### How do I disable the default Attachments meta box?

You will need to edit your Attachments configuration. Please reference the **Usage** instructions.

#### How do I change the fields for each Attachment?

You will need to edit your Attachments configuration. Please reference the **Usage** instructions.

#### Where are uploads saved?

Attachments uses WordPress' built in Media library for uploads and storage.

#### I lost my Attachments after upgrading!

***DO NOT update any Post/Page/CPT with Attachments***, the data has not been lost. Please [contact me](http://mondaybynoon.com/contact/) to begin a bugfix

## Changelog

<dl>

    <dt>3.0</dt>
    <dd> <strong>Major</strong> rewrite. After three years of development, Attachments has been rewritten to make
          even better use of what WordPress has to offer</dd>
    <dd> Utilizes the brand spanking new 3.5 Media workflow</dd>
    <dd> Configuration now takes place within your theme or a plugin</dd>
    <dd> Multiple meta boxes! You can segment groups of Attachments with new instances, each unique</dd>
    <dd> Dynamic fields! You can manipulate which fields each instance uses</dd>
    <dd> File type limits. Limit which files are available to Attachments (e.g. images, audio, video, all)</dd>

</dl>

## Upgrade Notice

<dl>
<dt>3.0</dt>
<dd><p><strong>You will need to update your theme files that use Attachments 3.0</strong>. Version 1.x of Attachments has been *fully deprecated* but is still available. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your wp-config.php:</p>

<pre><code>define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments</code></pre>

<p>Version 3 is a <strong>major</strong> rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.</p></dd>
</dl>

## Roadmap

Planned feature additions include:

* Additional field type: textarea
* Additional field type: WYSIWYG
* User-defined limiting the number of Attachments per instance
* User-defined custom fields