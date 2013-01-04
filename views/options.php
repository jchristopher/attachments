<?php

    // Exit if accessed directly
    if( !defined( 'ABSPATH' ) ) exit;

    /**
     * Migrate Attachments 1.x records to 3.0's format
     *
     * @since 3.0
     */
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
        // TODO: this will not retrieve posts that have exclude_from_search = true
        // TODO: make this reusable elsewhere
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
            $existing_attachments = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $attachments, JSON_UNESCAPED_UNICODE ) : json_encode( $attachments );

            // save it to the database
            update_post_meta( $query->post->ID, 'attachments', $existing_attachments );

            // increment our counter
            $count++;
        }

        return $count;
    }

    if( isset( $_GET['dismiss'] ) )
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-dismiss') ) wp_die( __( 'Invalid request', 'attachments' ) );

        add_option( 'attachments_ignore_migration', true, '', 'no' );
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

        <p><?php _e( 'Attachments 3.0 ships with what are called <em>instances</em>. An instance is equivalent to a meta box on an edit screen and it has a number of properties you can customize. Please read the README for more information.', 'attachments' ); ?> <a href="https://github.com/jchristopher/attachments/blob/master/README.md#usage"><?php _e( 'Additinoal instructions', 'attachments' ); ?>.</a></p>

        <h3><?php _e( 'Retrieving Attachments in your theme', 'attachments' ); ?></h3>

        <p><?php _e( 'As always has been the case with Attachments, editing your theme files is required. The syntax to do so has changed in Attachments 3.0. Please read the', 'attachments' ); ?> <a href="https://github.com/jchristopher/attachments/blob/master/README.md#usage"><?php _e( 'Additinoal instructions', 'attachments' ); ?></a>.</p>

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

        // check for any legacy Attachments
        // TODO: this will not retrieve posts that have exclude_from_search = true
        // TODO: make this reusable elsewhere
        $legacy = new WP_Query( 'post_type=any&post_status=any&posts_per_page=1&meta_key=_attachments' );

        // check for any legacy Attachments Pro Attachments
        $legacy_pro = new WP_Query( 'post_type=any&post_status=any&posts_per_page=1&meta_key=_attachments_pro' );

        // check to see if we're migrating
        if( isset( $_GET['migrate'] ) )
        {
            switch( intval( $_GET['migrate'] ) )
            {
                case 1:
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
                    break;

                case 2:
                    if( !wp_verify_nonce( $_GET['nonce'], 'attachments-migrate-2') ) wp_die( __( 'Invalid request', 'attachments' ) );

                    $total = attachments_migrate( $_GET['attachments-instance'], $_GET['attachments-title'], $_GET['attachments-caption'] );

                    if( false == get_option( 'attachments_migrated' ) ) :
                    ?>
                        <h3><?php _e( 'Migration Complete!', 'attachments' ); ?></h3>
                        <p><?php _e( 'The migration has completed.', 'attachments' ); ?> <strong><?php _e( 'Migrated', 'attachments'); ?>: <?php echo $total; ?></strong>.</p>
                    <?php else : ?>
                        <h3><?php _e( 'Migration Already Run!', 'attachments' ); ?></h3>
                        <p><?php _e( 'The migration has already been run. The migration process has not been repeated.', 'attachments' ); ?></p>
                    <?php endif;

                    // make sure the database knows the migration has run
                    add_option( 'attachments_migrated', true, '', 'no' );

                    break;
            }
        }
        else if( isset( $_GET['migrate_pro'] ) )
        {
            switch( intval( $_GET['migrate_pro'] ) )
            {
                case 1:
                    if( !wp_verify_nonce( $_GET['nonce'], 'attachments-pro-migrate-1') ) wp_die( __( 'Invalid request', 'attachments' ) );

                    $existing_pro_instances = get_option( '_iti_apro_settings' );
                    ?>
                        <h3><?php _e( 'Migration Step 1', 'attachments' ); ?></h3>

                        <?php if( is_array( $existing_pro_instances['positions'] ) && count( $existing_pro_instances['positions'] ) ) : ?>

                            <p><?php _e( "The following Attachments Pro instances have been found.", 'attachments' ); ?></p>

                            <ol>
                                <?php foreach( $existing_pro_instances['positions'] as $pro_instance ) : ?>
                                    <li><?php echo $pro_instance['label']; ?></li>
                                <?php endforeach; ?>
                            </ol>

                            <p><?php _e( "Please copy the following to your theme's <code>functions.php</code> to implement them in Attachments:", 'attachments' ); ?></p>

                            <?php

$attachments_instances_snippet = '<?php
function my_attachments( $attachments )
{
';

                                foreach( $existing_pro_instances['positions'] as $pro_instance )
                                {
                                    // get post types


                                    // get file type limits if applicable
                                    $file_type_limit = 'null';

$attachments_instances_snippet .= '    /**
     * ' . $pro_instance['label'] . '
     */

    $args = array(

      // title of the meta box (string)
      "label"         => "' . $pro_instance['label'] . '",

      // all post types to utilize (string|array)
      "post_type"     => array( "post", "page" ),

      // allowed file type(s) (array) (image|video|text|audio|application)
      "filetype"      => ' . $file_type_limit . ',

      // include a note within the meta box (string)
      "note"          => "' . $pro_instance['description'] . '",

      // text for "Attach" button in meta box (string)
      "button_text"   => __( "Attach Files" ),

      // text for modal "Attach" button (string)
      "modal_text"    => __( "Attach" ),

';

// parse the fields
if( is_array( $pro_instance['fields'] ) && count( $pro_instance['fields'] ) )
{
$attachments_instances_snippet .= '      "fields"        => array(';
    foreach( $pro_instance['fields'] as $field )
    {
        $field_name = str_replace( '-', '_', sanitize_title( $field['label'] ) );
$attachments_instances_snippet .= '
        array(
            "name"  => "' . $field_name . '",
            "type"  => "' . $field['type'] . '",
            "label" => __( "' . $field['label'] . '" ),
        ),
';
    }
$attachments_instances_snippet .= "      ),\n";
}
$attachments_instances_snippet .= '
    );

    $attachments->register( "' . $pro_instance['name'] . '", $args );


';
                                } // end instance foreach

$attachments_instances_snippet .= '}

add_action( "attachments_register", "my_attachments" );
';

                            ?>

                            <pre id="attachments-pro-instances"><code><?php echo htmlentities( $attachments_instances_snippet ); ?></code></pre>

                        <?php else: ?>

                            <p><?php echo _e( "<strong>ERROR:</strong> No Attachments Pro instances found.", 'attachments' ); ?></p>

                        <?php endif; ?>


                        <pre><?php print_r( $existing_pro_instances['positions'] ); ?></pre>

                        <form action="options-general.php" method="get">
                            <input type="hidden" name="page" value="attachments" />
                            <input type="hidden" name="migrate_pro" value="2" />
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'attachments-pro-migrate-2' ); ?>" />
                            <table class="form-table">
                                <tbody>

                                </tbody>
                            </table>
                            <p><?php _e( "When you have added the above snippet to your theme's <code>functions.php</code>, please proceed with the data migration:", 'attachments' ); ?></p>
                            <p class="submit">
                                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Start Data Migration', 'attachments' ); ?>" />
                            </p>
                        </form>
                    <?php
                    break;
            }
        }
        else
        { ?>

            <?php if( false == get_option( 'attachments_migrated' ) && $legacy->found_posts ) : ?>
                <h2><?php _e( 'Migrate legacy Attachments data', 'attachments' ); ?></h2>
                <p><?php _e( 'Attachments has found records from version 1.x. Would you like to migrate them to version 3?', 'attachments' ); ?></p>
                <p><a href="?page=attachments&amp;migrate=1&amp;nonce=<?php echo wp_create_nonce( 'attachments-migrate-1' ); ?>" class="button-primary button"><?php _e( 'Migrate legacy data', 'attachments' ); ?></a></p>
            <?php elseif( true == get_option( 'attachments_migrated' ) ) : ?>
                <p><?php _e( 'You have already migrated your legacy Attachments data.', 'attachments' ); ?></p>
            <?php endif; ?>

            <?php if( false == get_option( 'attachments_pro_migrated' ) && $legacy_pro->found_posts ) : ?>
                <h2><?php _e( 'Migrate legacy Attachments Pro data', 'attachments' ); ?></h2>
                <p><?php _e( 'Attachments has found records from Attachments Pro. Would you like to migrate them to version 3?', 'attachments' ); ?></p>
                <p><a href="?page=attachments&amp;migrate_pro=1&amp;nonce=<?php echo wp_create_nonce( 'attachments-pro-migrate-1' ); ?>" class="button-primary button"><?php _e( 'Migrate legacy data', 'attachments' ); ?></a></p>
            <?php elseif( true == get_option( 'attachments_pro_migrated' ) ) : ?>
                <p><?php _e( 'You have already migrated your legacy Attachments Pro data.', 'attachments' ); ?></p>
            <?php endif; ?>

            <h2><?php _e( 'Revert to version 1.x', 'attachments' ); ?></h2>
            <p><?php _e( 'If you would like to forcefully revert to the 1.x version branch of Attachments, add the following to your', 'attachments' ); ?> <code>wp-config.php</code>:</p>
            <p><code>define( 'ATTACHMENTS_LEGACY', true );</code></p>
            <h2><?php _e( 'Meta box customization', 'attachments' ); ?></h2>
            <p><?php _e( 'By default, Attachments implements a single meta box on Posts and Pages with two fields. You can disable this default instance by adding the following to your', 'attachments' ); ?> <code>wp-config.php</code>:</p>
            <p><code>define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );</code></p>
            <p><?php _e( "Your Attachments meta box(es) can be customized by adding the following to your theme's", 'attachments' ); ?> <code>functions.php</code>:</p>
            <script src="https://gist.github.com/4217475.js"> </script>
            <h2><?php _e( 'Using Attachments data in your theme', 'attachments' ); ?></h2>
            <p><?php _e( "Attachments does not directly integrate with your theme out of the box, you will need to edit your theme's template files where appropriate. You can add the following within The Loop to retrieve all Attachments data for the current post:", 'attachments' ); ?></p>
            <script src="https://gist.github.com/4217483.js"> </script>
        <?php }

    ?>

</div>
