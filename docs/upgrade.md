This is a WordPress plugin. [Official download available on WordPress.org](http://wordpress.org/extend/plugins/attachments/).

## [Docs TOC](TOC.md) / Upgrade Notice

#### 3.0
**You will need to update your theme files that use Attachments 3.0**. Version 1.x of Attachments has been **fully deprecated** but is still available *and included with Attachments 3.x*. If you would like to continue to use the (no longer supported) 1.x version you may add the following to your `wp-config.php`:

```php

define( 'ATTACHMENTS_LEGACY', true ); // force the legacy version of Attachments

```

Version 3 is a **major** rewrite. While I've taken precautions in ensuring you won't lose any saved data it is important to back up your databse prior to upgrading in case something goes wrong. This version is a complete rewrite so all legacy data will be left in place, but a migration must take place to match the new data storage model and workflow.

-----

#### Next: [Usage](usage.md)
