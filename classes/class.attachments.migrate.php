<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
* Migration class for legacy Attachments data
*
* @since 3.2
*/
class AttachmentsMigrate extends Attachments
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Migrate Attachments 1.x records to 3.0's format
     *
     * @since 3.0
     */
    function migrate( $instance = null, $title = null, $caption = null )
    {
        // sanitize
        if( is_null( $instance ) || empty( $instance ) || is_null( $title ) || is_null( $caption ) )
            return false;

        $instance   = str_replace( '-', '_', sanitize_title( $instance ) );
        $title      = empty( $title ) ? false : str_replace( '-', '_', sanitize_title( $title ) );
        $caption    = empty( $caption ) ? false : str_replace( '-', '_', sanitize_title( $caption ) );

        // we need our deprecated functions
        include_once( ATTACHMENTS_DIR . '/deprecated/get-attachments.php' );

        $legacy_attachments_settings = get_option( 'attachments_settings' );

        if( $legacy_attachments_settings && is_array( $legacy_attachments_settings['post_types'] ) && count( $legacy_attachments_settings['post_types'] ) )
        {
            // we have legacy settings, so we're going to use the post types
            // that Attachments is currently utilizing

            // the keys are the actual CPT names, so we need those
            foreach( $legacy_attachments_settings['post_types'] as $post_type => $value )
                if( $value )
                    $post_types[] = $post_type;

            // set up our WP_Query args to grab anything with legacy data
            $args = array(
                    'post_type'         => isset( $post_types ) ? $post_types : array(),
                    'post_status'       => 'any',
                    'posts_per_page'    => -1,
                    'meta_key'          => '_attachments',
                );

            $query = new WP_Query( $args );
        }

        $count = 0;

        // loop through each post
        if( $query ) { while( $query->have_posts() )
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

            if( !is_object( $existing_attachments ) )
                $existing_attachments = new stdClass();

            // we'll loop through the legacy Attachments and save them in the new format
            foreach( $post_attachments as $legacy_attachment )
            {
                // convert to the new format
                $converted_attachment = array( 'id' => $legacy_attachment['id'] );

                // fields are technically optional so we'll add those separately
                // we're also going to encode them in the same way the main class does
                if( $title )
                    $converted_attachment['fields'][$title] = htmlentities( stripslashes( $legacy_attachment['title'] ), ENT_QUOTES, 'UTF-8' );

                if( $caption )
                    $converted_attachment['fields'][$caption] = htmlentities( stripslashes( $legacy_attachment['caption'] ), ENT_QUOTES, 'UTF-8' );

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
            $existing_attachments = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $existing_attachments, JSON_UNESCAPED_UNICODE ) : json_encode( $existing_attachments );

            // save it to the database
            update_post_meta( $query->post->ID, 'attachments', $existing_attachments );

            // increment our counter
            $count++;
        } }

        return $count;
    }



    /**
     * Step 1 of the migration process. Allows the user to define the target instance and field names.
     *
     * @since 3.2
     */
    function prepare_migration()
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-migrate-1') ) wp_die( __( 'Invalid request', 'attachments' ) );
        ?>
            <h3><?php _e( 'Migration Step 1', 'attachments' ); ?></h3>
            <p><?php _e( "In order to migrate Attachments 1.x data, you need to set which instance and fields in version 3.0+ you'd like to use:", 'attachments' ); ?></p>
            <form action="options-general.php" method="get">
                <input type="hidden" name="page" value="attachments" />
                <input type="hidden" name="migrate" value="2" />
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'attachments-migrate-2' ); ?>" />
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="attachments-instance"><?php _e( 'Attachments 3.x Instance', 'attachments' ); ?></label>
                            </th>
                            <td>
                                <input name="attachments-instance" id="attachments-instance" value="attachments" class="regular-text" />
                                <p class="description"><?php _e( 'The instance name you would like to use in the migration. Required.', 'attachments' ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="attachments-title"><?php _e( 'Attachments 3.x Title', 'attachments' ); ?></label>
                            </th>
                            <td>
                                <input name="attachments-title" id="attachments-title" value="title" class="regular-text" />
                                <p class="description"><?php _e( 'The <code>Title</code> field data will be migrated to this field name in Attachments 3.x. Leave empty to disregard.', 'attachments' ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="attachments-caption"><?php _e( 'Attachments 3.x Caption', 'attachments' ); ?></label>
                            </th>
                            <td>
                                <input name="attachments-caption" id="attachments-caption" value="caption" class="regular-text" />
                                <p class="description"><?php _e( 'The <code>Caption</code> field data will be migrated to this field name in Attachments 3.x. Leave empty to disregard.', 'attachments' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Start Migration', 'attachments' ); ?>" />
                </p>
            </form>
        <?php
    }



    /**
     * Step 2 of the migration process. Validates migration requests and fires the migration method.
     *
     * @since 3.2
     */
    function init_migration()
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-migrate-2') )
            wp_die( __( 'Invalid request', 'attachments' ) );

        $total = $this->migrate( $_GET['attachments-instance'], $_GET['attachments-title'], $_GET['attachments-caption'] );

        if( false == get_option( 'attachments_migrated' ) ) :
        ?>
            <h3><?php _e( 'Migration Complete!', 'attachments' ); ?></h3>
            <p><?php _e( 'The migration has completed.', 'attachments' ); ?> <strong><?php _e( 'Migrated', 'attachments'); ?>: <?php echo $total; ?></strong>.</p>
        <?php else : ?>
            <h3><?php _e( 'Migration has already Run!', 'attachments' ); ?></h3>
            <p><?php _e( 'The migration has already been run. The migration process has not been repeated.', 'attachments' ); ?></p>
        <?php endif;

        // make sure the database knows the migration has run
        add_option( 'attachments_migrated', true, '', 'no' );
    }

}