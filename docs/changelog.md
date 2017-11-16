This is a WordPress plugin. [Official download available on WordPress.org](http://wordpress.org/extend/plugins/attachments/).

## [Docs TOC](TOC.md) / Changelog

<dl>
	<dt>3.5.8</dt>
	<dd>Fixed a potential issue when migrating from Attachments Pro</dd>
	<dt>3.5.7</dt>
	<dd>Fixed a regression that prevented attaching multiple files at once</dd>
	<dt>3.5.6</dt>
	<dd>Fixed an issue where changing an Attachment on more than one Attachment would continually update the first Attachment</dd>
	<dd>Media modal now includes filters (props marcochiesi)</dd>
	<dd>Added German translation (props bessl)</dd>
	<dd>Added filter to manipulate Attachments metadata before it's saved: <code>attachments_meta_before_save</code></dd>
	<dd>Underscores are no longer enforced over hyphens</dd>
	<dd>More entropy for Attachments uid's to prevent collisions (props sketchpad)</dd>
	<dt>3.5.5</dt>
	<dd>Fixed an issue where field values were improperly overwritten when the instance was set to prepend in some cases</dd>
	<dt>3.5.4</dt>
	<dd>Fixed assumption of field keys (props bukka)</dd>
	<dd>Improved documentation (props Lane Goldberg, Roman Kokarev, Ore Landau)</dd>
	<dd>Added <code>rewind()</code> method to reset Attachments reference array (props joost de keijzer)</dd>
	<dd>TinyMCE fix to support WordPress 3.9+</dd>
	<dd>Fixed an issue where nonce was potentially wrongly flagged as sent if an instance was filtered</dd>
	<dd>Added <code>post_parent</code> argument support for instances, setting to <code>true</code> will populate the <strong>Uploaded to</strong> column in Media</dd>
	<dd>New filter: <code>attachments_default_instance</code> to disable/enable the default instance (default is <code>true</code>, <code>ATTACHMENTS_DEFAULT_INSTANCE</code> constant is deprecated)</dd>
	<dd>New filter: <code>attachments_settings_screen</code> to hide/show the settings screen (default is <code>true</code>, <code>ATTACHMENTS_SETTINGS_SCREEN</code> constant is deprecated)</dd>
	<dd>Fixed an issue where Attachments meta box(es) would not show up when creating new posts whose <code>post_type</code> had a dash in it</dd>
	<dd>Updated Italian translation (props Luca Speranza)</dd>
	<dt>3.5.3</dt>
	<dd>Fixed a Fatal error when deleting Media that was attached to a post (props Clearsite)</dd>
	<dd>Warning cleanup</dd>
	<dt>3.5.2</dt>
	<dd>Added ability to force an instance name</dd>
	<dd>Documentation updates</dd>
	<dd>Warning and Notice cleanup</dd>
	<dd>Fixed an issue with newline character retrieval</dd>
	<dd>Fixed assumption of array (props Jakub Zelenka)</dd>
	<dt>3.5.1.1</dt>
	<dd>Fixed an issue where Featured Images may have become inadvertently disabled, props @deborre</dd>
	<dt>3.5.1</dt>
	<dd>Fixed an issue where changing an Attachment changed all attachments</dd>
	<dd>Fixed an issue where certain Unicode characters weren't decoded properly</dd>
	<dt>3.5</dt>
	<dd>Initial implementation of limiting the number of Attachments</dd>
	<dd>You can now change an Attachment asset without having to remove the entire Attachment and re-add something new</dd>
	<dd>New filter: <code>attachments_location_{my_instance}</code> (where <code><strong>{my_instance}</strong></code> is your instance name) allows for more fine-grained control over where meta boxes show up (e.g. limiting to your Home page)</dd>
	<dd>New action: <code>attachments_extension</code> facilitates Attachments extensions</dd>
	<dd>New extension: <a href="http://mondaybynoon.com/members/plugins/attachments-ui/?utm_campaign=Attachments&utm_term=changelog">Attachments UI</a> to create "code-free" Instances in the WordPress admin</dd>
	<dd>New method <code>width( $size )</code> to retrieve the width of the current Attachment</dd>
	<dd>New method <code>height( $size )</code> to retrieve the height of the current Attachment</dd>
	<dd>New document structure, various additions to documentation</dd>
	<dd>Attachments Pro migration script. If you've been waiting to migrate from Attachments Pro please <strong>back up your database</strong> and run the migration script.</dd>
	<dd>Fixed an asset URL issue if Attachments is added as a must-use plugin</dd>
	<dd>Italian translation (props Marco Chiesi)</dd>
	<dt>3.4.3</dt>
	<dd>Attachments now takes into account media deleted outside Attachments meta boxes and removes deleted attachments automatically</dd>
	<dd>Added working Polish translation, props <a href="https://github.com/mleczakm">@mleczakm</a></dd>
	<dt>3.4.2.1</dt>
	<dd>Fixed a regression that prevented the <code>type</code> method from returning</dd>
	<dt>3.4.2</dt>
	<dd>Fixed an issue where the <code>languages</code> directory wouldn't be utilized for l10n</dd>
	<dd>Search now respects custom <code>meta_key</code></dd>
	<dd>You can now pass in a <code>filetype</code> parameter when searching to limit results in that way</dd>
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

-----

#### Next: [Roadmap](roadmap.md)
