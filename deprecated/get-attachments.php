<?php

/**
 * Compares two array values with the same key "order"
 *
 * @param string $a First value
 * @param string $b Second value
 * @return int
 * @author Jonathan Christopher
 */
function attachments_cmp($a, $b)
{
    $a = intval( $a['order'] );
    $b = intval( $b['order'] );

    if( $a < $b )
    {
        return -1;
    }
    else if( $a > $b )
    {
        return 1;
    }
    else
    {
        return 0;
    }
}


/**
 * Returns a formatted filesize
 *
 * @param string $path Path to file on disk
 * @return string $formatted formatted filesize
 * @author Jonathan Christopher
 */
function attachments_get_filesize_formatted( $path = NULL )
{
    $formatted = '0 bytes';
    if( file_exists( $path ) )
    {
        $formatted = size_format( @filesize( $path ) );
    }
    return $formatted;
}


/**
 * Retrieves all Attachments for provided Post or Page
 *
 * @param int $post_id (optional) ID of target Post or Page, otherwise pulls from global $post
 * @return array $post_attachments
 * @author Jonathan Christopher
 * @author JR Tashjian
 */

function attachments_get_attachments( $post_id=null )
{
    global $post;

    if( $post_id==null )
    {
        $post_id = $post->ID;
    }

    // get all attachments
    $existing_attachments = get_post_meta( $post_id, '_attachments', false );

    // We can now proceed as normal, all legacy data should now be upgraded

    $post_attachments = array();

    if( is_array( $existing_attachments ) && count( $existing_attachments ) > 0 )
    {

        foreach ($existing_attachments as $attachment)
        {
            // decode and unserialize the data
            $data = unserialize( base64_decode( $attachment ) );

            array_push( $post_attachments, array(
                'id'            => stripslashes( $data['id'] ),
                'name'          => stripslashes( get_the_title( $data['id'] ) ),
                'mime'          => stripslashes( get_post_mime_type( $data['id'] ) ),
                'title'         => stripslashes( $data['title'] ),
                'caption'       => stripslashes( $data['caption'] ),
                'filesize'      => stripslashes( attachments_get_filesize_formatted( get_attached_file( $data['id'] ) ) ),
                'location'      => stripslashes( wp_get_attachment_url( $data['id'] ) ),
                'order'         => stripslashes( $data['order'] )
                ));
        }

        // sort attachments
        if( count( $post_attachments ) > 1 )
        {
            usort( $post_attachments, "attachments_cmp" );
        }
    }

    return $post_attachments;
}