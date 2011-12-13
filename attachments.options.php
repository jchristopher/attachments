<?php
    if( isset( $_GET['dismisspro'] ) )
    {
        update_option( '_attachments_dismiss_pro', 1 );
    }
?>

<div class="wrap">

    <div id="icon-options-general" class="icon32"><br /></div>

    <h2>Attachments Options</h2>

    <?php $attachments_dismiss_pro = get_option( '_attachments_dismiss_pro' ); if( !$attachments_dismiss_pro ) : ?>
        <div class="updated">
            <?php _e( '<p><strong>Attachments Pro is now available!</strong> <a href="http://mondaybynoon.com/store/attachments-pro/">Find out more</a> about Attachments Pro. <a href="options-general.php?page=attachments/attachments.php&dismisspro=1">Dismiss</a>.</p>', 'attachments' ); ?>
        </div>
    <?php endif; ?>

    <form action="options.php" method="post">
        <div id="poststuff" class="metabox-holder">
            <?php settings_fields( 'attachments_settings' ); ?>
            <?php do_settings_sections( 'attachments_options' ); ?>
        </div>
        <p class="submit">
            <input type="submit" class="button-primary" value="Save Options" />
        </p>
    </form>
</div>
