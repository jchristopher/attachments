<?php
    function attachments_migrate( $instance = null, $title = null, $caption = null )
    {
        // sanitize
        if( is_null( $instance ) || empty( $instance ) || is_null( $title ) || is_null( $caption ) )
            return false;

        $instance   = str_replace( '-', '_', sanitize_title( $instance ) );
        $title      = empty( $title ) ? false : str_replace( '-', '_', sanitize_title( $title ) );
        $caption    = empty( $caption ) ? false : str_replace( '-', '_', sanitize_title( $caption ) );

        // we need our deprecated functions
        include_once( ATTACHMENTS_DIR . '/deprecated/get-attachments.php' );

        // grab all of the posts we need to migrate
        $query = new WP_Query( 'post_type=any&post_status=any&posts_per_page=-1&meta_key=_attachments' );

        $count = 0;

        // loop through each post
        while( $query->have_posts() )
        {
            // set up postdata
            $query->the_post();

            // let's first decode our Attachments data
            $existing_attachments = get_post_meta( $query->post->ID, '_attachments', false );

            $post_attachments = array();

            // check to make sure we've got data
            if( is_array( $existing_attachments ) && count( $existing_attachments ) > 0 )
            {
                // loop through each existing attachment
                foreach( $existing_attachments as $attachment )
                {
                    // decode and unserialize the data
                    $data = unserialize( base64_decode( $attachment ) );

                    array_push( $post_attachments, array(
                        'id'        => stripslashes( $data['id'] ),
                        'title'     => stripslashes( $data['title'] ),
                        'caption'   => stripslashes( $data['caption'] ),
                        'order'     => stripslashes( $data['order'] )
                        ));
                }

                // sort attachments
                if( count( $post_attachments ) > 1 )
                {
                    usort( $post_attachments, 'attachments_cmp' );
                }
            }

            // we have our Attachments entries

            // let's check to see if we're migrating after population has taken place
            $existing_attachments = get_post_meta( $query->post->ID, 'attachments', false );

            if( !isset( $existing_attachments[0] ) )
                $existing_attachments[0] = '';

            $existing_attachments = json_decode( $existing_attachments[0] );

            // we'll loop through the legacy Attachments and save them in the new format
            foreach( $post_attachments as $legacy_attachment )
            {

                // convert to the new format
                $converted_attachment = array( 'id' => $legacy_attachment['id'] );

                // fields are technically optional so we'll add those separately
                // we're also going to encode them in the same way the main class does
                if( $title )
                    $converted_attachment['fields'][$title] = htmlentities( stripslashes( $legacy_attachment['title'] ), ENT_QUOTES );

                if( $caption )
                    $converted_attachment['fields'][$caption] = htmlentities( stripslashes( $legacy_attachment['caption'] ), ENT_QUOTES );

                // check to see if the existing Attachments have our target instance
                if( !isset( $existing_attachments->$instance ) )
                {
                    // the instance doesn't exist so we need to create it
                    $existing_attachments->$instance = array();
                }

                // we need to convert our array to an object
                $converted_attachment['fields'] = (object) $converted_attachment['fields'];
                $converted_attachment = (object) $converted_attachment;

                // append this legacy attachment to the existing instance
                array_push( $existing_attachments->$instance, $converted_attachment );
            }

            // we're done! let's save everything in our new format
            $existing_attachments = json_encode( $existing_attachments );

            // save it to the database
            update_post_meta( $query->post->ID, 'attachments', $existing_attachments );

            // increment our counter
            $count++;
        }

        return $count;
    }
?>

<div class="wrap">

    <div id="icon-options-general" class="icon32"><br /></div>

    <h2>Attachments</h2>

    <?php

        // check for any legacy Attachments
        $legacy = new WP_Query( 'post_type=any&post_status=any&posts_per_page=1&meta_key=_attachments' );

        // check to see if we're migrating
        if( isset( $_GET['migrate'] ) )
        {
            switch( intval( $_GET['migrate'] ) )
            {
                case 1:
                    if( !wp_verify_nonce( $_GET['nonce'], 'attachments-migrate-1') ) wp_die( 'Invalid request' );
                    ?>
                        <h3>Migration Step 1</h3>
                        <p>In order to migrate Attachments 1.x data, you need to set which instance and fields in version 3.0+ you'd like to use:</p>
                        <form action="options-general.php" method="get">
                            <input type="hidden" name="page" value="attachments" />
                            <input type="hidden" name="migrate" value="2" />
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'attachments-migrate-2' ); ?>" />
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="attachments-instance">Attachments 3.x Instance</label>
                                        </th>
                                        <td>
                                            <input name="attachments-instance" id="attachments-instance" value="attachments" class="regular-text" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="attachments-title">Attachments 3.x Title</label>
                                        </th>
                                        <td>
                                            <input name="attachments-title" id="attachments-title" value="title" class="regular-text" />
                                            <p class="description">The <code>Title</code> field data will be migrated to this field name in Attachments 3.x. Leave empty to disregard.</p>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">
                                            <label for="attachments-caption">Attachments 3.x Caption</label>
                                        </th>
                                        <td>
                                            <input name="attachments-caption" id="attachments-caption" value="caption" class="regular-text" />
                                            <p class="description">The <code>Caption</code> field data will be migrated to this field name in Attachments 3.x. Leave empty to disregard.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="submit">
                                <input type="submit" name="submit" id="submit" class="button button-primary" value="Start Migration" />
                            </p>
                        </form>
                    <?php
                    break;

                case 2:
                    if( !wp_verify_nonce( $_GET['nonce'], 'attachments-migrate-2') ) wp_die( 'Invalid request' );

                    $total = attachments_migrate( $_GET['attachments-instance'], $_GET['attachments-title'], $_GET['attachments-caption'] );
                    ?>
                        <h3>Migration Complete!</h3>
                        <p>The migration has completed. <strong><?php echo $total; ?> record<?php if( $total !== 1 ) : ?>s<?php endif; ?></strong> <?php if( $total !== 1 ) : ?>have<?php else: ?>has<?php endif; ?> been migrated.</p>
                    <?php

                    // make sure the database knows the migration has run
                    update_option( 'attachments_migrated', true );

                    break;
            }
        }
        else
        { ?>
            <?php if( false == get_option( 'attachments_migrated' ) && $legacy->found_posts ) : ?>
                <p>Attachments has found records from version 1.x. Would you like to migrate them to version 3?</p>
                <p><a href="?page=attachments&amp;migrate=1&amp;nonce=<?php echo wp_create_nonce( 'attachments-migrate-1' ); ?>" class="button-primary button">Migrate legacy Attachments</a></p>
            <?php elseif( true == get_option( 'attachments_migrated' ) ) : ?>
                <p>You have already migrated your legacy Attachments. No settings necessary!</p>
            <?php endif; ?>
        <?php }

    ?>

</div>
