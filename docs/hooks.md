This is a WordPress plugin. [Official download available on WordPress.org](http://wordpress.org/extend/plugins/attachments/).

# [Docs TOC](TOC.md) / Hooks

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

### Fine Tune Instance Location

Many times simply defining a post type for your Instance is too broad of a rule. You'll often want to limit your Attachments Instance to a single page or user role. The attachments_location_{my_instance} facilitates just that.

```php
function my_attachments_location_limit( $val )
{
    global $post;

    // our home page ID is 10 and we only want this instance to show up there
    return ( $post->ID == 10 ) ? true : false;
}

add_filter( 'attachments_location_my_attachments', 'my_attachments_location_limit' );
```

You can have your callback be as elaborate as you'd like, using any attributes WordPress has to offer at runtime.

## Actions

Actions allow you to integrate with the internals of Attachments without effecting the codebase itself.

### Extend Attachments

Attachments will load any extensible code you've written to enhance what you're aiming to do with the `attachments_extension` action.

```php
function init_my_attachments_extension( $attachments )
{
    // do your thing
}

add_action( 'attachments_extension', 'init_my_attachments_extension', 10, 1 );
```