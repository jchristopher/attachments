<?php

// exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// exit if we can't extend Attachments
if ( !class_exists( 'Attachments' ) ) return;




/**
* Search Attachments metadata based on a number of parameters
*
* @since 3.4.1
*/
class AttachmentsSearch extends Attachments
{

    public $results;

    /**
     * Facilitates searching for Attachments
     *
     * @since 3.3
     */
    function __construct( $query = null, $params = array() )
    {
        parent::__construct();
        $this->apply_init_filters();

        $defaults = array(
                'attachment_id' => null,            // not searching for a single attachment ID
                'instance'      => 'attachments',   // default instance
                'post_type'     => null,            // search 'any' post type
                'post_id'       => null,            // searching all posts
                'post_status'   => 'publish',       // search only published posts
                'fields'        => null,            // search all fields
                'filetype'      => null,            // search all filetypes
            );

        $query  = is_null( $query ) ? null : sanitize_text_field( $query );
        $params = array_merge( $defaults, $params );

        // sanitize parameters
        $params['attachment_id']    = is_null( $params['attachment_id'] ) ? null : intval( $params['attachment_id'] );
        $params['instance']         = !is_string( $params['instance'] ) ? 'attachments' : sanitize_text_field( $params['instance'] );
        $params['post_type']        = is_null( $params['post_type'] ) ? 'any' : sanitize_text_field( $params['post_type'] );
        $params['post_id']          = is_null( $params['post_id'] ) ? null : intval( $params['post_id'] );

        $params['post_status']      = sanitize_text_field( $params['post_status'] );

        if( is_string( $params['fields'] ) )
            $params['fields']       = array( $params['fields'] );   // we always want an array

        if( is_string( $params['filetype'] ) )
            $params['filetype']     = array( $params['filetype'] ); // we always want an array

        // since we have an array for our fields, we need to loop through and sanitize
        for( $i = 0; $i < count( $params['fields'] ); $i++ )
            $params['fields'][$i] = sanitize_text_field( $params['fields'][$i] );

        // prepare our search args
        $args = array(
                'nopaging'      => true,
                'post_status'   => $params['post_status'],
                'meta_query'    => array(
                        array(
                                'key'       => parent::get_meta_key(),
                                'value'     => $query,
                                'compare'   => 'LIKE'
                            )
                    ),
            );

        // append any applicable parameters that got passed to the original method call
        if( $params['post_type'] )
            $args['post_type'] = $params['post_type'];

        if( $params['post_id'] )
            $args['post__in'] = array( $params['post_id'] );    // avoid using 'p' or 'page_id'

        // we haven't utilized all parameters yet because they're meta-value based so we need to
        // do some parsing on our end to validate the returned results

        $possible_posts         = new WP_Query( $args );
        $potential_attachments  = false;  // stores valid attachments as restrictions are added

        if( $possible_posts->found_posts )
        {
            // we have results from the reliminary search, we need to quantify them
            while( $possible_posts->have_posts() )
            {
                $possible_posts->next_post();
                $possible_post_ids[] = $possible_posts->post->ID;
            }

            // now that we have our possible post IDs we can grab all Attachments for all of those posts
            foreach( $possible_post_ids as $possible_post_id )
            {
                $possible_attachments = parent::get_attachments( $params['instance'], $possible_post_id );

                foreach( $possible_attachments as $possible_attachment )
                    $potential_attachments[] = $possible_attachment;
            }

        }

        // if there aren't even any potential attachments, we'll just short circuit
        if( !$potential_attachments )
            return;

        // first we need to make sure that our query matches each attachment
        // we need to do this because the LIKE query returned the entire meta record,
        // not necessarily tied to any specific Attachment
        $total_potentials = count( $potential_attachments );
        for( $i = 0; $i < $total_potentials; $i++ )
        {
            $valid = false;

            // if we need to limit our search to specific fields, we'll do that here
            if( $params['fields'] )
            {
                // we only want to check certain fields
                foreach( $params['fields'] as $field )
                    if( isset( $potential_attachments[$i]->fields->$field ) )   // does the field exist?
                        if( !$query || strpos( strtolower( $potential_attachments[$i]->fields->$field ),
                                    strtolower( $query ) ) !== false ) // does the value match?
                            if( !is_null( $params['filetype'] ) && in_array( parent::get_mime_type( $potential_attachments[$i]->id ), $params['filetype'] ) )
                                $valid = true;
            }
            else
            {
                // we want to check all fields
                foreach( $potential_attachments[$i]->fields as $field_name => $field_value )
                    if( !$query || strpos( strtolower( $field_value) , strtolower( $query ) ) !== false )
                        if( !is_null( $params['filetype'] ) && in_array( parent::get_mime_type( $potential_attachments[$i]->id ), $params['filetype'] ) )
                            $valid = true;
            }

            if( !$valid )
                unset( $potential_attachments[$i] );

            // now our potentials have been limited to each match the query based on any field
        }

        // limit to attachment ID if applicable
        if( $params['attachment_id'] )
        {
            $total_potentials = count( $potential_attachments );
            for( $i = 0; $i < $total_potentials; $i++ )
                if( $potential_attachments[$i]->id != $params['attachment_id'] )
                    unset( $potential_attachments[$i] );
        }

        $this->results = array_values( $potential_attachments );
    }
}
