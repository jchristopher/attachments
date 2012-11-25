
(function($){

    var workflows = {};
    var attachments_wp_media = wp.media.editor;

    wp.media.pluginattachments = {

        stripUI: function() {
            // we're not utilizing some of the core media dialog so we need to strip it
            $('body').addClass('attachments-context');
            $('#myimage').bind('click.mynamespace', function() { /* Do stuff */ });
            $('.media-modal-backdrop, .media-modal-close').bind('click.attachments', function(){
                wp.media.pluginattachments.restoreUI();
            });
        },

        restoreUI: function() {
            // we need to restore the default UI
            $('body').removeClass('attachments-context');
            $('.media-modal-backdrop, .media-modal-close').unbind('click.attachments');
        },

        insert: function( h ) {
            // cleanup
            wp.media.pluginattachments.restoreUI();
        },

        add: function( id, options ) {

            var workflow = this.get( id );

            if ( workflow )
                return workflow;

            workflow = workflows[ id ] = wp.media( _.defaults( options || {}, {
                frame:    'post',
                title:    'Attach',
                multiple: true
            } ) );

            workflow.on( 'insert', function( selection ) {

                var state = workflow.state()

                selection = selection || state.get('selection');

                if ( ! selection )
                    return;

                // compile our template
                _.templateSettings.variable = 'attachments';
                var template = _.template($( "script#tmpl-attachments-attachments" ).html());

                selection.each( function( attachment ) {

                    console.log(attachment.attributes);
                    // set our attributes to the template
                    var templateData = attachment.attributes;

                    // append the template
                    $('.attachments-context').append(template(templateData));
                });

                // cleanup
                wp.media.pluginattachments.restoreUI();
                $('.attachments-context').removeClass('attachments-context');

            }, this );

            return workflow;
        },

        get: function( id ) {
            return workflows[ id ];
        },

        remove: function( id ) {
            delete workflows[ id ];
        },

        init: function() {
            $('body').on('click', '#attachments-insert', function( event ) {

                var workflow;

                event.preventDefault();

                // TODO: See: http://core.trac.wordpress.org/ticket/22445
                $(this).blur();

                workflow = wp.media.pluginattachments.get( 'pluginattachments' );

                $(this).parent().find('.attachments').addClass('attachments-context');

                // If the workflow exists, just open it.
                if ( workflow ) {
                    workflow.open();
                    wp.media.pluginattachments.stripUI();
                    return;
                }

                // Initialize the editor's workflow if we haven't yet.
                wp.media.pluginattachments.add( 'pluginattachments' );
                wp.media.pluginattachments.stripUI();

            });
        }
    }

    $( wp.media.pluginattachments.init );

}(jQuery));