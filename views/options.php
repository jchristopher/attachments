<?php

    // instantiate our migration class
    include_once( ATTACHMENTS_DIR . '/classes/class.attachments.migrate.php' );
    $migrator = new AttachmentsMigrate();

    include_once( ATTACHMENTS_DIR . '/classes/class.attachments.legacy.php' );
    $legacy = new AttachmentsLegacyHandler();

    if( isset( $_GET['dismiss'] ) )
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-dismiss') )
            wp_die( __( 'Invalid request', 'attachments' ) );

        // set our flag that the user wants to ignore the migration message
        add_option( 'attachments_ignore_migration', true, '', 'no' );
    }

    if( isset( $_GET['dismiss-pro'] ) )
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-pro-dismiss') )
            wp_die( __( 'Invalid request', 'attachments' ) );

        // set our flag that the user wants to ignore the migration message
        add_option( 'attachments_pro_ignore_migration', true, '', 'no' );
    }
?>

<div class="wrap">

    <div id="icon-options-general" class="icon32"><br /></div>

    <h2><?php _e( 'Attachments', 'attachments' ); ?></h2>

    <?php if( isset( $_GET['overview'] ) ) : ?>

        <div class="message updated" id="message">
            <p><?php _e( "<strong>Attachments has changed significantly since it's last update.</strong> These changes <em>will affect your themes and plugins</em>.", 'attachments' ); ?></p>
        </div>

        <h4><?php _e( 'Immediate Reversal to Attachments 1.x', 'attachments' ); ?></h4>

        <p><?php _e( 'If you would like to immediately <em>revert to the old version of Attachments</em> you may do so by downgrading the plugin install itself, or adding the following to your', 'attachments' ); ?> <code>wp-config.php</code>:</p>

        <pre><code>define( 'ATTACHMENTS_LEGACY', true );</code></pre>

        <h2><?php _e( 'Overview of changes from Attachments 1.x', 'attachments' ); ?></h2>

        <p><?php _e( "A lot has changed since Attachments 1.x. The entire codebase was rewritten to not only make better use of the stellar Media updates in WordPress 3.5, but to also facilitate some exciting features coming down the line. With this rewrite came significant changes to the way you will work with Attachments. One of the biggest changes in Attachments 3.0 is the ability to create multiple meta boxes of Attachments, each with any number of custom fields you define. By default, Attachments will re-implement the meta box you've been using until now, but <strong>you will need to trigger a migration to the new format</strong>.", 'attachments' ); ?></p>

        <h3><?php _e( 'Migrating Attachments 1.x data to Attachments 3.x', 'attachments' ); ?></h3>

        <p><?php _e( "If you have existing Attachments 1.x data and are using it, a migration script has been bundled here and you can use it below. If you would like to directly migrate from Attachments 1.x to Attachments 3.x you can use the defaults put in place and your data will be migrated to the new format quickly and easily. Alternatively, if you'd like to customize the fields you're using a bit, you can do that first and then adjust the migration parameters to map the old fields to your new ones.", 'attachments' ); ?></p>

        <h3><?php _e( 'Setting up Instances', 'attachments' ); ?></h3>

        <p><?php _e( 'Attachments 3.0 ships with what are called <em>instances</em>. An instance is equivalent to a meta box on an edit screen and it has a number of properties you can customize. Please read the README for more information.', 'attachments' ); ?> <a href="https://github.com/jchristopher/attachments/#attachments"><?php _e( 'Additional instructions', 'attachments' ); ?>.</a></p>

        <h3><?php _e( 'Retrieving Attachments in your theme', 'attachments' ); ?></h3>

        <p><?php _e( 'As always has been the case with Attachments, editing your theme files is required. The syntax to do so has changed in Attachments 3.0. Please read the', 'attachments' ); ?> <a href="https://github.com/jchristopher/attachments/#attachments"><?php _e( 'Additional instructions', 'attachments' ); ?></a>.</p>

        <form action="options-general.php" method="get">
            <input type="hidden" name="page" value="attachments" />
            <input type="hidden" name="dismiss" value="1" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'attachments-dismiss' ); ?>" />

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-secondary" value="<?php esc_attr_e( 'Dismiss these notices without migrating', 'attachments' ); ?>" />
            </p>
        </form>

    <?php endif; ?>

    <?php
        // check to see if we're migrating
        if( isset( $_GET['migrate'] ) )
        {
            switch( intval( $_GET['migrate'] ) )
            {
                case 1:
                    $migrator->prepare_migration();
                    break;

                case 2:
                    $migrator->init_migration();
                    break;
            }
        }
        elseif( isset( $_GET['migrate-pro'] ) )
        {
            switch( intval( $_GET['migrate-pro'] ) )
            {
                case 1:
                    $migrator->prepare_pro_migration();
                    break;

                case 2:
                    $migrator->init_pro_migration();
                    break;
            }
        }
        else
        {

            if($_SERVER['REQUEST_METHOD']=="POST")
            {
                $option_name = "attachment_post_type";
                if ( get_option( $option_name ) !== false ) {

                    // The option already exists, so we just update it.
                    update_option( $option_name, $_POST["post_type_option"] );

                } else {

                    // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
                    $deprecated = null;
                    $autoload = 'no';
                    add_option( $option_name, $_POST["post_type_option"], $deprecated, $autoload );
                }

            }

    ?>


       <hr/>
        <h2>Add a Custom post type</h2>
        <p>separated by comma</p>
        <form action="/wp-admin/options-general.php?page=attachments" method="post">
            <input type="text" name="post_type_option" style="width: 350px" value="<?php echo get_option("attachment_post_type") ?>" />
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-secondary" value="<?php esc_attr_e( 'Custom post type', 'attachments' ); ?>" />
            </p>
        </form>


            <?php if( false == get_option( 'attachments_migrated' ) && $legacy->legacy ) : ?>
                <h2><?php _e( 'Migrate legacy Attachments data', 'attachments' ); ?></h2>
                <p><?php _e( 'Attachments has found records from version 1.x. Would you like to migrate them to version 3?', 'attachments' ); ?></p>
                <p><a href="?page=attachments&amp;migrate=1&amp;nonce=<?php echo wp_create_nonce( 'attachments-migrate-1' ); ?>" class="button-primary button"><?php _e( 'Migrate legacy data', 'attachments' ); ?></a></p>
            <?php elseif( true == get_option( 'attachments_migrated' ) ) : ?>
                <p>
                    <?php _e( 'You have already migrated your legacy Attachments data.', 'attachments' ); ?>
                    <a class="attachments-toggle-trigger" href="#migrated-legacy"><?php _e( 'View more', 'attachments' ); ?></a>
                </p>
                <div id="migrated-legacy" class="attachments-toggle-target" style="display:none;">
                    <p>Your legacy Attachments data has been migrated.</p>
                </div>
            <?php endif; ?>

            <?php if( false == get_option( 'attachments_pro_migrated' ) && $legacy->legacy_pro ) : ?>
                <h2><?php _e( 'Migrate Attachments Pro data', 'attachments' ); ?></h2>
                <p><?php _e( 'Attachments has found records stored in Attachments Pro. Would you like to migrate them to Attachments?', 'attachments' ); ?></p>
                <p><a href="?page=attachments&amp;migrate-pro=1&amp;nonce=<?php echo wp_create_nonce( 'attachments-pro-migrate-1' ); ?>" class="button-primary button"><?php _e( 'Migrate Attachments Pro data', 'attachments' ); ?></a></p>
            <?php elseif( true == get_option( 'attachments_pro_migrated' ) ) : ?>
                <p>
                    <?php _e( 'You have already migrated your Attachments Pro data.', 'attachments' ); ?>
                    <a class="attachments-toggle-trigger" href="#migrated-legacy-pro"><?php _e( 'View more', 'attachments' ); ?></a>
                </p>
                <div id="migrated-legacy-pro" class="attachments-toggle-target" style="display:none;">
                    <h2><?php _e( 'Moving from Attachments Pro: required code for your functions.php', 'attachments' ); ?></h2>
                    <textarea style="display:block; width:100%; font-family:monospace; height:300px;"><?php echo get_option( 'attachments_pro_functions '); ?></textarea>
                    <h2><?php _e( 'Moving from Attachments Pro: starter code for your theme templates', 'attachments' ); ?></h2>
                    <textarea style="display:block; width:100%; font-family:monospace; height:200px;"><?php echo get_option( 'attachments_pro_template '); ?></textarea>
                </div>
            <?php endif; ?>

            <h2><?php _e( 'Usage', 'attachments' ); ?></h2>
            <p>Full usage instructions are provided in <a href="https://github.com/jchristopher/attachments#documentation">the official documentation</a>. You can also view the <a class="attachments-toggle-trigger" href="#usage">abridged version</a>.</p>
            <div id="usage" class="attachments-toggle-target" style="display:none;">
                <h2><?php _e( 'Revert to version 1.x', 'attachments' ); ?></h2>
                <p><?php _e( 'If you would like to forcefully revert to the 1.x version branch of Attachments, add the following to your', 'attachments' ); ?> <code>wp-config.php</code>:</p>
                <p><code>define( 'ATTACHMENTS_LEGACY', true );</code></p>
                <h2><?php _e( 'Meta box customization', 'attachments' ); ?></h2>
                <p><?php _e( 'Attachments requires manual creation of meta boxes using code outlined in the documentation. If you would prefer a UI for managing Attachments Instances, please see', 'attachments' ); ?> <a href="https://mondaybynoon.com/members/plugins/attachments-ui/?utm_campaign=Attachments&utm_term=Settings">Attachments UI</a>. <?php _e( 'By default, Attachments implements a single meta box on Posts and Pages with two fields. You can disable this default instance by adding the following to your', 'attachments' ); ?> <code>wp-config.php</code>:</p>
                <p><code>define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );</code></p>
                <p><?php _e( "Your Attachments meta box(es) can be customized by adding the following to your theme's", 'attachments' ); ?> <code>functions.php</code>:</p>
                <script src="https://gist.github.com/4217475.js"> </script>
                <h2><?php _e( 'Using Attachments data in your theme', 'attachments' ); ?></h2>
                <p><?php _e( "Attachments does not directly integrate with your theme out of the box, you will need to edit your theme's template files where appropriate. You can add the following within The Loop to retrieve all Attachments data for the current post:", 'attachments' ); ?></p>
                <script src="https://gist.github.com/4217483.js"> </script>
            </div>

            <h2><?php _e( 'Extend Attachments', 'attachments' ); ?></h2>
            <p><?php _e( 'Attachments can be extended using any number of the items listed here.', 'attachmentsui' ); ?></p>
            <p><strong style="font-size:1.4em;display:block;padding:10px 0;"><a href="https://mondaybynoon.com/members/plugins/attachments-ui/?utm_campaign=Attachments&utm_term=Settings">Attachments UI</a></strong>
            <?php _e( 'Attachments is a code-focused plugin, meaning it essentially has no UI. If you prefer using a UI with Attachments, Attachments UI is for you. Features include:', 'attachments' ); ?></p>
            <ul style="list-style:disc;padding-left:40px">
                <li><?php _e( 'Visually create Instances from within the WordPress admin', 'attachments' ); ?></li>
                <li><?php _e( 'Fine-grained control over which edit screens each Instance appears on (e.g. limited to Home page)', 'attachments' ); ?></li>
                <li><?php _e( 'Auto-append Attachments content to posts using an easy to understand template system', 'attachments' ); ?></li>
                <li><?php _e( 'Automatic, custom code snippets to copy, paste, and customize', 'attachments' ); ?></li>
                <li><a href="https://mondaybynoon.com/members/plugins/attachments-ui/?utm_campaign=Attachments&utm_term=Settings"><?php _e( 'More information', 'attachments' ); ?></a></li>
            </ul>
        <?php }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.attachments-toggle-target').hide();
            $('a.attachments-toggle-trigger').click(function(){
                var $this = $(this);
                var $target = $($this.attr('href'));
                if($target.is(':visible')){
                    $target.hide();
                }else{
                    $target.show();
                }
                return false;
            });
        });
    </script>
</div>
