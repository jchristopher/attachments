jQuery(document).ready(function($){
    var context = false;
    var thickbox_modder;

    // init sortability
    if($('div#attachments-list ul:data(sortable)').length==0&&$('div#attachments-list ul li').length>0){
        $('div#attachments-list ul').sortable({
            containment: 'parent',
            stop: function(e, ui) {
                $('#attachments-list ul li').each(function(i, id) {
                    $(this).find('input.attachment_order').val(i+1);
                });
            }
        });
    }

    // delete link handler
    function attachments_hook_delete_links(theparent){
        attachment_parent = theparent.parent().parent().parent();
        attachment_parent.slideUp(function() {
            attachment_parent.remove();
            $('#attachments-list ul li').each(function(i, id) {
                $(this).find('input.attachment_order').val(i+1);
            });
            if($('div#attachments-list li').length == 0) {
                $('#attachments-list').slideUp(function() {
                    $('#attachments-list').hide();
                });
            }
        });
    }

    // Hook our delete links
    if(parseFloat($.fn.jquery)>=1.7){
        // 'live' is deprecated
        $(document).on("click", "span.attachment-delete a", function(event){
            theparent = $(this);
            attachments_hook_delete_links(theparent);
            return false;
        });
    }else{
        $('span.attachment-delete a').live('click',function(event){
            theparent = $(this);
            attachments_hook_delete_links(theparent);
            return false;
        });
    }



    // thickbox handler
    if(parseFloat($.fn.jquery)>=1.7){
        // 'live' is deprecated
        $(document).on("click", "a#attachments-thickbox", function(event){
            event.preventDefault();
            theparent = $(this);
            attachments_handle_thickbox(event,theparent);
            return false;
        });
    }else{
        $('a#attachments-thickbox').live('click',function(event){
            event.preventDefault();
            theparent = $(this);
            attachments_handle_thickbox(event,theparent);
            return false;
        });
    }

    function attachments_handle_thickbox(event,theparent){
        var href = theparent.attr('href'), width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
        if ( ! href ) return;
        href = href.replace(/&width=[0-9]+/g, '');
        href = href.replace(/&height=[0-9]+/g, '');
        theparent.attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
        context = true;
        thickbox_modder = setInterval( function(){
            if( context ){
                modify_thickbox();
            }
        }, 500 );
        tb_show('Attach a file', event.target.href, false);
        return false;
    }

    // handle the attachment process
    function handle_attach(title,caption,id,thumb){
        var source   = $('#attachment-template').html();
        var template = Handlebars.compile(source);

        var order = $('#attachments-list > ul > li').length + 1;

        $('div#attachments-list ul', top.document).append(template({title:title,caption:caption,id:id,thumb:thumb,order:order}));

        $('#attachments-list > ul > li').each(function(i, id) {
            $(this).find('input.attachment_order').val(i+1);
        });

        return false;
    }


    function modify_thickbox(){

        the_thickbox = jQuery('#TB_iframeContent').contents();

        // our new click handler for the attach button
        the_thickbox.find('td.savesend input').unbind('click').click(function(e){

            jQuery(this).after('<span class="attachments-attached">Attached!</span>');

            // grab our meta as per the Media library
            var wp_media_meta       = $(this).parent().parent().parent();
            var wp_media_title      = wp_media_meta.find('tr.post_title td.field input').val();
            var wp_media_caption    = wp_media_meta.find('tr.post_excerpt td.field input').val();
            var wp_media_id         = wp_media_meta.find('td.imgedit-response').attr('id').replace('imgedit-response-','');
            var wp_media_thumb      = wp_media_meta.parent().find('img.thumbnail').attr('src');

            handle_attach(wp_media_title,wp_media_caption,wp_media_id,wp_media_thumb);

            the_thickbox.find('span.attachments-attached').delay(1000).fadeOut('fast');
            return false;
        });
        // update button
        if(the_thickbox.find('.media-item .savesend input[type=submit], #insertonlybutton').length){
            the_thickbox.find('.media-item .savesend input[type=submit], #insertonlybutton').val('Attach');
        }
        if(the_thickbox.find('#tab-type_url').length){
            the_thickbox.find('#tab-type_url').hide();
        }
        if(the_thickbox.find('tr.post_title').length){
            // we need to ALWAYS get the fullsize since we're retrieving the guid
            // if the user inserts an image somewhere else and chooses another size, everything breaks
            the_thickbox.find('tr.image-size input[value="full"]').prop('checked', true);
            the_thickbox.find('tr.post_title,tr.image_alt,tr.post_excerpt,tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link').hide();
        }

        // was the thickbox closed?
        if(the_thickbox.length==0 && context){
            clearInterval(thickbox_modder);
            context = false;
        }
    }

});


