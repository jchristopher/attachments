<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
* Migration class for legacy Attachments data
*
* @since 3.1.3
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

        $query = false;

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
                    'suppress_filters'  => true,
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
            $existing_attachments = get_post_meta( $query->post->ID, $this->get_meta_key(), false );

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



    /**
     * Step 1 of the Pro migration process. Allows the user to define the target instance and field names.
     *
     * @since 3.5
     */
    function prepare_pro_migration()
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-pro-migrate-1') ) wp_die( __( 'Invalid request', 'attachments' ) );
        ?>
        <h3><?php _e( 'Migration Step 1', 'attachments' ); ?></h3>
        <form action="options-general.php" method="get">
            <input type="hidden" name="page" value="attachments" />
            <input type="hidden" name="migrate-pro" value="2" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'attachments-pro-migrate-2' ); ?>" />

            <?php
                /**
                 * We need to use the stored Attachments Pro settings to generate a dynamic form encompassing all
                 * Pro instances, their fields, their rules, and their auto-append statuses
                 */
                $attachments_pro_settings = get_option( '_iti_apro_settings' );
            ?>

            <?php if( is_array( $attachments_pro_settings['positions'] ) ) : ?>
                <p><?php _e( 'The following Attachments Pro Instances will be migrated:', 'attachments' ); ?></p>
                <ul style="padding-left:32px;list-style:disc;">
                    <?php foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance ) : ?>
                        <li><?php echo $attachments_pro_instance['label']; ?></li>
                    <?php endforeach; ?>
                </ul>
                <h2><?php _e( 'Note: this is a multi-step process', 'attachments' ); ?></h2>
                <p><?php _e( 'The data from each Pro Instance will be migrated to an equivalent Attachments Instance, and you will be provided code to copy and paste into your <code>functions.php</code>.', 'attachments' ); ?></p>
                <p><?php _e( '<em>This data migration is only the first step. <strong>You must add the code from the following page to your <code>functions.php</code></strong> to restore your meta boxes</em>.', 'attachments' ); ?></p>
            <?php endif; ?>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Start Migration', 'attachments' ); ?>" />
            </p>
        </form>
    <?php
    }



    /**
     * Step 2 of the Pro migration process. Validates migration requests and fires the migration method.
     *
     * @since 3.5
     */
    function init_pro_migration()
    {
        if( !wp_verify_nonce( $_GET['nonce'], 'attachments-pro-migrate-2') )
            wp_die( __( 'Invalid request', 'attachments' ) );

        $attachments_pro_settings = get_option( '_iti_apro_settings' );
        if( is_array( $attachments_pro_settings['positions'] ) )
        {
            $totals = array();

            foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance )
                $totals[] = $this->migrate_pro( $attachments_pro_instance );

            $total_attachments = 0;

            if( !empty( $totals ) )
                foreach( $totals as $instance_total )
                    $total_attachments += $instance_total['total'];

            if( false == get_option( 'attachments_pro_migrated' ) ) :
                ?>
                <h3><?php _e( 'Data conversion complete!', 'attachments' ); ?></h3>
                <p><?php _e( 'The data conversion has been completed successfully.', 'attachments' ); ?> <strong><?php _e( 'Converted', 'attachments'); ?>: <?php echo $total_attachments; ?> <?php echo ( $total_attachments == 1 ) ? __( 'Attachment', 'attachments' ) : __( 'Attachments', 'attachments' ); ?></strong></p>
                <h2><?php _e( 'The migration is NOT COMPLETE', 'attachments' ); ?></h2>
                <p><?php _e( "While the data migration has taken place, you still need to add the following to your <code>functions.php</code> in order to have Attachments' meta boxes show up where appropriate:", 'attachments' ); ?></p>
                <textarea style="font-family:monospace; display:block; width:100%; height:300px;">
if( !function_exists( 'migrated_pro_attachments' ) )
{
    function migrated_pro_attachments( $attachments )
    {<?php foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance ) : ?>

        $fields = array(<?php if( is_array( $attachments_pro_instance['fields'] ) ) : foreach( $attachments_pro_instance['fields'] as $field ) : if( $field['type'] == 'textfield' ) { $field['type'] = 'text'; } ?>

            array(
                'name'      => '<?php echo str_replace( '-', '_', sanitize_title( $field['label'] ) ); ?>',
                'type'      => '<?php echo $field['type']; ?>',
                'label'     => '<?php echo $field['label']; ?>',
                'default'   => '<?php echo isset( $field['mapped_to'] ) ? $field['mapped_to'] : ''; ?>',
            ),<?php endforeach; echo "\n"; endif; ?>
        );
<?php
    $post_types = array();
    if( isset( $attachments_pro_instance['conditions'] ) && is_array( $attachments_pro_instance['conditions'] ) && !empty( $attachments_pro_instance['conditions'] ) )
        foreach( $attachments_pro_instance['conditions'] as $condition )
            if( $condition['param'] == 'post_type' && $condition['operator'] == 'is' )
                $post_types[] = $condition['limiter'];
?>
        $args = array(
            'label'         => '<?php echo $attachments_pro_instance['label']; ?>',
            'post_type'     => array( '<?php echo implode( "', '", array_unique( $post_types ) ); ?>' ),
            'note'          => '<?php echo $attachments_pro_instance['description']; ?>',
            'fields'        => $fields,
        );

        $attachments->register( '<?php echo $attachments_pro_instance['name']; ?>', $args );<?php endforeach; ?>

    }
}

add_action( 'attachments_register', 'migrated_pro_attachments' );

<?php
    $integrate_conditions = false;
    foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance )
    {
        if( isset( $attachments_pro_instance['conditions'] ) && is_array( $attachments_pro_instance['conditions'] ) && !empty( $attachments_pro_instance['conditions'] ) )
        {
            $integrate_conditions = true;
            break;
        }
    }

    if( $integrate_conditions ) : ?>
/**
 * Facilitate Attachments conditional inclusion of meta boxes for existing instances
 */

if( !function_exists( 'attachments_is_allowed' ) )
{
    function attachments_is_allowed( $conditions = array(), $match = 'any' )
    {
        global $post;

        $match              = ( $match == 'any' ) ? 'any' : 'all';
        $allowed            = false;
        $short_circuit      = false;

        if( is_array( $conditions ) && !empty( $conditions ) )
        {
            foreach( $conditions as $condition )
            {
                if( ( $match == 'all' && !$short_circuit ) || $match == 'any' )
                {
                    $parameter  = $condition['param'];
                    $operator   = $condition['operator'];
                    $limiter    = $condition['limiter'];

                    switch( $parameter )
                    {
                        case 'post_type':
                                if( $operator == 'is' )
                                {
                                    if( $post->post_type == $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( $post->post_type != $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        case 'post_format':
                                $post_format = get_post_format( $post->ID );
                                if( $operator == 'is' )
                                {
                                    if( $post_format == $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( $post_format != $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        case 'page':
                                if( $operator == 'is' )
                                {
                                    if( $post->ID == $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( $post->ID != $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        case 'role':
                                $current_user = wp_get_current_user();
                                if( $operator == 'is' )
                                {
                                    if( array_search( strtolower( $limiter ), $current_user->roles ) !== false )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( array_search( strtolower( $limiter ), $current_user->roles ) === false )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        case 'template':
                                $current_template = get_post_meta( $post->ID, '_wp_page_template', true );
                                if( $operator == 'is' )
                                {
                                    if( $current_template == $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( $current_template != $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        case 'parent':
                                if( $operator == 'is' )
                                {
                                    if( $post->post_parent == $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                                else
                                {
                                    if( $post->post_parent != $limiter )
                                    {
                                        $allowed = true;
                                    }
                                    elseif( $match == 'all' )
                                    {
                                        $short_circuit = true;
                                    }
                                }
                            break;

                        default:
                            break;
                    }
                }
            }
        }
        return ( $allowed && !$short_circuit ) ? true : false;
    }
}
<?php endif; ?>

<?php
    foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance )
    {
        if( isset( $attachments_pro_instance['conditions'] ) && is_array( $attachments_pro_instance['conditions'] ) && !empty( $attachments_pro_instance['conditions'] ) )
        { ?>
function attachments_check_<?php echo $attachments_pro_instance['name']; ?>()
{
    $conditions = array(<?php foreach( $attachments_pro_instance['conditions'] as $condition ) : ?>

        array(
            'param'     => '<?php echo $condition['param']; ?>',
            'operator'  => '<?php echo $condition['operator']; ?>',
            'limiter'   => '<?php echo $condition['limiter']; ?>',
        ),<?php endforeach; ?>

    );

    return attachments_is_allowed( $conditions, '<?php echo $attachments_pro_instance['match']; ?>' );
}

add_filter( "attachments_location_<?php echo $attachments_pro_instance; ?>", 'attachments_check_<?php echo $attachments_pro_instance['name']; ?>' );
        <?php }
    }
?>

<?php
    $integrate_auto_append = false;
    foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance )
    {
        if( isset( $attachments_pro_instance['auto_append_enable'] ) )
        {
            $integrate_auto_append = true;
            break;
        }
    }

    if( $integrate_auto_append ) : ?>
/**
 * Facilitate Attachments auto-append template parsing
 */

if( !function_exists( 'attachments_magic_tags_processor' ) )
{
    function attachments_magic_tags_processor( $in )
    {
        global $attachments_magic_tag_index, $attachments_auto_append_ref;
        $name = $in[2];

        switch( $name )
        {
            case 'index':
                $value = $attachments_magic_tag_index;
                break;

            case 'id':
                $value = $attachments_auto_append_ref->id();
                break;

            case 'thumb':
                $value = $attachments_auto_append_ref->image( 'thumbnail' );
                break;

            case 'url':
                $value = $attachments_auto_append_ref->url();
                break;

            case 'mime':
            case 'type':
                $value = $attachments_auto_append_ref->type();
                break;

            case 'subtype':
                $value = $attachments_auto_append_ref->subtype();
                break;

            case 'filesize':
                $value = $attachments_auto_append_ref->filesize();
                break;

            default:
                $value = '';
                break;
        }

        // we might be dealing with fields
        if( empty( $value ) )
        {
            if( strpos( $name, 'field_' ) !== false )
            {
                // we are dealing with a field
                $field = explode( '_', $name );

                if( isset( $field[1] ) )
                    $value = $attachments_auto_append_ref->field( $field[1] );
            }
        }

        // image details
        if( empty( $value ) )
        {
            $request = explode( '.', $name );

            if( is_array( $request ) && count( $request ) == 2 )
            {
                switch ( $request[1] )
                {
                    case 'url':
                    case 'src':
                        $value = $attachments_auto_append_ref->src( $request[0] );
                        break;

                    case 'width':
                        $value = $attachments_auto_append_ref->width( $request[0] );
                        break;

                    case 'height':
                        $value = $attachments_auto_append_ref->height( $request[0] );
                        break;

                    case 'filesize':
                        $value = $attachments_auto_append_ref->filesize( null, $request[0] );
                        break;

                    default:
                        $value = '';
                        break;
                }
            }
        }

        return $value;
    }
}
<?php endif; ?>

<?php foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance ) : if( isset( $attachments_pro_instance['auto_append_enable'] ) && isset( $attachments_pro_instance['auto_append_template'] ) ) : ?>
if( !is_admin() )
{
    function attachments_auto_append_<?php echo $attachments_pro_instance['name']; ?>( $content )
    {
        global $attachments_magic_tag_index, $attachments_auto_append_ref;

        $original   = $content;
        $output     = '';
        $template   = <<<EOD
<?php echo $attachments_pro_instance['auto_append_template']; ?>

EOD;

        $attachments_auto_append_ref = new Attachments( '<?php echo $attachments_pro_instance['name']; ?>' );
        if( $attachments_auto_append_ref->exist() )
        {
            $pattern = '/\s*\{@begin_attachments_loop\}\s*(.*?)\s*\{@end_attachments_loop\}\s*/xmis';
            preg_match( $pattern, $template, $matches );
            $loop_content = '';
            if( isset( $matches[1] ) )
            {
                $loop_content = $matches[1];
                $attachments_magic_tag_index = 1;
                while( $attachments_auto_append_ref->get() )
                {
                    $output .= preg_replace_callback( "/({@(.*?)})/m", 'attachments_magic_tags_processor', $loop_content );
                    $attachments_magic_tag_index++;
                }
                $output = preg_replace( $pattern, $output, $template );
            }
        }

        return $original . $output;
    }

    add_filter( 'the_content', 'attachments_auto_append_<?php echo $attachments_pro_instance['name']; ?>' );
}
<?php endif; endforeach; ?>
                </textarea>
                <p><?php _e( "This code snippet has also been emailed to you for future reference as the migration only runs once. When you have verified your meta boxes have been restored and Attachments is operating as expected, you should <strong>deactivate Attachments Pro</strong>.", 'attachments' ); ?></p>
                <h2><?php _e( 'The migration is STILL NOT COMPLETE', 'attachments' ); ?></h2>
                <p><?php _e( 'While the data has been migrated and the meta boxes restored, <em>you still need to edit your template files where appropriate</em>. Please see the documentation for more information. The following sample code can be used as a starting point:', 'attachments' ); ?></p>
                <textarea style="display:block; width:100%; font-family:monospace; height:200px;"><?php foreach( $attachments_pro_settings['positions'] as $attachments_pro_instance ) : ?>
&lt;?php $attachments = new Attachments( '<?php echo $attachments_pro_instance['name']; ?>' ); ?&gt;
&lt;?php if( $attachments-&gt;exist() ) : ?&gt;
    &lt;h3&gt;<?php echo $attachments_pro_instance['label']; ?>&lt;/h3&gt;
    &lt;p&gt;Total <?php echo $attachments_pro_instance['label']; ?>: &lt;?php echo $attachments-&gt;total(); ?&gt;&lt;/p&gt;
    &lt;ul&gt;
        &lt;?php while( $attachments-&gt;get() ) : ?&gt;
            &lt;li&gt;
                ID: &lt;?php echo $attachments-&gt;id(); ?&gt;&lt;br /&gt;
                Type: &lt;?php echo $attachments-&gt;type(); ?&gt;&lt;br /&gt;
                Subtype: &lt;?php echo $attachments-&gt;subtype(); ?&gt;&lt;br /&gt;
                URL: &lt;?php echo $attachments-&gt;url(); ?&gt;&lt;br /&gt;
                Image: &lt;?php echo $attachments-&gt;image( 'thumbnail' ); ?&gt;&lt;br /&gt;
                Source: &lt;?php echo $attachments-&gt;src( 'full' ); ?&gt;&lt;br /&gt;
                Size: &lt;?php echo $attachments-&gt;filesize(); ?&gt;&lt;br /&gt;<?php if( is_array( $attachments_pro_instance['fields'] ) ) : ?><?php foreach( $attachments_pro_instance['fields'] as $field ) : ?><?php if( $field['type'] == 'textfield' ) { $field['type'] = 'text'; } ?>

                <?php echo $field['label']; ?>: &lt;?php echo $attachments-&gt;field( '<?php echo str_replace( '-', '_', sanitize_title( $field['label'] ) ); ?>' ); ?&gt;&lt;br /&gt;<?php endforeach; endif; ?>

            &lt;/li&gt;
        &lt;?php endwhile; ?&gt;
    &lt;/ul&gt;
&lt;?php endif; ?&gt;
<?php endforeach; ?>
                    </textarea>
            <?php else : ?>
                <h3><?php _e( 'Migration has already Run!', 'attachments' ); ?></h3>
                <p><?php _e( 'The migration has already been run. The migration process has not been repeated.', 'attachments' ); ?></p>
            <?php endif;
        }

        // make sure the database knows the migration has run
        add_option( 'attachments_pro_migrated', true, '', 'no' );
    }



    function migrate_pro( $instance = array() )
    {
        if( !is_array( $instance ) || empty( $instance ) )
            return false;

        $post_types = get_post_types();

        // set up our WP_Query args to grab anything (really anything) with legacy data
        $args = array(
            'post_type'         => !empty( $post_types ) ? $post_types : array( 'post', 'page' ),
            'post_status'       => 'any',
            'posts_per_page'    => -1,
            'meta_key'          => '_attachments_pro',
            'suppress_filters'  => true,
        );

        $query = new WP_Query( $args );

        $count = array( 'instance' => $instance['name'], 'total' => 0 );

        if( $query )
        {
            while( $query->have_posts() )
            {
                // set up postdata
                $query->the_post();

                // let's first decode our Attachments Pro data
                $existing_instances = get_post_meta( $query->post->ID, '_attachments_pro', true );
                $existing_instances = isset( $existing_instances['attachments'] ) ? $existing_instances['attachments'] : false;

                // check to make sure we've got data
                if( is_array( $existing_instances ) && count( $existing_instances ) > 0 )
                {
                    $post_attachments = array();
                    foreach( $existing_instances as $instance_name => $instance_attachments )
                    {
                        if( $instance_name == $instance['name'] )
                        {
                            $post_attachments[$instance_name] = array();
                            $converted_attachment = array();
                            foreach( $instance_attachments as $instance_attachment )
                            {
                                $converted_attachment['id'] = $instance_attachment['id'];
                                if( is_array( $instance_attachment['fields'] ) )
                                {
                                    $converted_attachment['fields'] = array();
                                    foreach( $instance_attachment['fields'] as $instance_attachment_field_key => $instance_attachment_field )
                                    {
                                        $destination_field_name = $instance['fields'][$instance_attachment_field_key]['label'];
                                        $destination_field_name = str_replace( '-', '_', sanitize_title( $destination_field_name ) );

                                        $converted_attachment['fields'][$destination_field_name] = $instance_attachment_field;
                                    }
                                    unset( $instance_attachment_field_key );
                                }
                                unset( $instance_attachment );
                                $post_attachments[$instance_name][] = $converted_attachment;
                                $count['total']++;
                            }
                        }
                    }

                    if( !empty( $post_attachments ) )
                    {
                        // before saving the converted data, we need to see if there is existing Attachments data
                        $this->apply_init_filters();
                        $existing_attachments = get_post_meta( $query->post->ID, $this->get_meta_key(), false );

                        if( !isset( $existing_attachments[0] ) )
                            $existing_attachments[0] = '';

                        $existing_attachments = json_decode( $existing_attachments[0] );

                        if( !is_object( $existing_attachments ) )
                            $existing_attachments = new stdClass();

                        foreach( $post_attachments as $instance => $attachments )
                        {
                            // check to see if the existing Attachments have our target instance
                            if( !isset( $existing_attachments->$instance ) )
                            {
                                // the instance doesn't exist so we need to create it
                                $existing_attachments->$instance = array();
                            }

                            // loop through the instance attachments and integrate new and old
                            foreach( $attachments as $attachment )
                            {
                                // we need to convert our array to an object
                                $attachment['fields'] = (object) $attachment['fields'];
                                $attachment = (object) $attachment;

                                array_push( $existing_attachments->$instance, $attachment );
                            }
                        }

                        // now we can save
                        $existing_attachments = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $existing_attachments, JSON_UNESCAPED_UNICODE ) : json_encode( $existing_attachments );
                        update_post_meta( $query->post->ID, $this->get_meta_key(), $existing_attachments );

                    }
                }
            }
        }

        return $count;
    }

}
