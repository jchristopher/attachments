<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>Attachments Options</h2>
    <form action="options.php" method="post">
        <?php wp_nonce_field('update-options'); ?>

        <?php if( function_exists( 'get_post_types' ) ) : ?>
            
            <?php 
                $args           = array(
                                    'public'    => true,
                                    'show_ui'   => true,
                                    '_builtin'  => false
                                    ); 
                $output         = 'objects';
                $operator       = 'and';
                $post_types     = get_post_types( $args, $output, $operator );

                // we also want to optionally enable Pages and Posts
                $post_types['post']->labels->name   = 'Posts';
                $post_types['post']->name           = 'post';
                $post_types['page']->labels->name   = 'Pages';
                $post_types['page']->name           = 'page';

            ?>
            
            <?php if( count( $post_types ) ) : ?>
            
                <h3><?php _e("Post Type Settings", "attachments"); ?></h3>
                <p><?php _e("Include Attachments in the following Post Types:", "attachments"); ?></p>
                <?php foreach($post_types as $post_type) : ?>

                    <div class="attachments_checkbox">
                        <input type="checkbox" name="attachments_cpt_<?php echo $post_type->name; ?>" id="attachments_cpt_<?php echo $post_type->name; ?>" value="true"<?php if (get_option('attachments_cpt_' . $post_type->name)=='true') : ?> checked="checked"<?php endif ?> />
                        <label for="attachments_cpt_<?php echo $post_type->name; ?>"><?php echo $post_type->labels->name; ?></label>
                    </div>

                <?php endforeach ?>

            <?php else: ?>

                <p><?php _e('Attachments can be integrated with your Custom Post Types. Unfortunately, there are none to work with at this time.', 'attachments'); ?></p>

            <?php endif ?>

        <?php endif ?>

        <h3><?php _e("Miscellaneous", "attachments"); ?></h3>
        <div class="attachments_checkbox">
            <input type="checkbox" name="attachments_store_native" id="attachments_store_native" value="true"<?php if (get_option('attachments_store_native')=='true') : ?> checked="checked"<?php endif ?> />
            <label for="attachments_store_native">Make WordPress-level attachment relationships</label>
            <p class="note">If checked, Attachments will tell WordPress that all Attachments for the entry should be marked as such as though it were included in the main editor. The association will be made as though it were. Changing this option <strong>will not</strong> update existing Attachments, it only effects future saves.</p>
        </div>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="attachments_store_native,<?php if( !empty( $post_types ) ) : foreach( $post_types as $post_type ) : ?>attachments_cpt_<?php echo $post_type->name; ?>,<?php endforeach; endif; ?>" />
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e("Save", "attachments");?>" />
        </p>

    </form>
</div>