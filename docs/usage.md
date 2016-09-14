This is a WordPress plugin. [Official download available on WordPress.org](http://wordpress.org/extend/plugins/attachments/).

## [Docs TOC](TOC.md) / Usage

Attachments is based on *instances* which correlate directly with the meta boxes that appear on edit screens of Posts, Pages, and Custom Post Types. By default Attachments ships with a single meta box that appears *only on Posts and Pages*. It has two fields: one for Title and one for Caption. If you would like to disable or customize the default instance, or you'd like to create additional instances with custom fields for different post types, please see [Setting Up Instances](#setting-up-instances).

### Disable Settings Screen

Attachments ships with a `Settings` screen (found under the `Settings` menu in the main WordPress admin navigation) that facilitates data migration from version 1.x and also offers some code snippets. If you would like to *disable the Settings screen* add the following to your `wp-config.php` *before* `require_once(ABSPATH . 'wp-settings.php');`

```php
define( 'ATTACHMENTS_SETTINGS_SCREEN', false ); // disable the Settings screen
```

### Setting Up Instances

When Attachments is first activated, a default instance is created titled Attachments. It has two fields:

1. Title
1. Caption

#### Disable the Default Instance

If you would like to *disable the default instance* (meta box titled 'Attachments' with a 'Title' and 'Caption' field) add the following to your theme's `functions.php`:

```php
add_filter( 'attachments_default_instance', '__return_false' ); // disable the default instance
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

    // whether Attachments should set 'Uploaded to' (if not already set)
	'post_parent'   => false,

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

$fields         = array(
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
        Size: <?php echo $attachments->filesize( $my_index ); ?><br />'
        Width: <?php echo $attachments->width('thumbnail', $my_index ); ?><br />
        Height: <?php echo $attachments->height('thumbnail', $my_index ); ?><br />
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
      'instance'      => 'attachments',       // search 'attachments' instance
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
      'fields'        => null,            // (string|array) search all Attachment fields
      'filetype'      => null,            // (string|array) search all Attachment filetypes
  );
```

Once you've performed your search, you can loop through the returned Attachments as you normally would.

-----

#### Next: [Hooks](hooks.md)
