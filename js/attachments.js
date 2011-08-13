function str_replace (search, replace, subject, count) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Gabriel Paderni
    // +   improved by: Philip Peterson
    // +   improved by: Simon Willison (http://simonwillison.net)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   bugfixed by: Anton Ongson
    // +      input by: Onno Marsman
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    tweaked by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   input by: Oleg Eremeev
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Oleg Eremeev
    // %          note 1: The count parameter must be passed as a string in order
    // %          note 1:  to find a global variable in which the result will be given
    // *     example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
    // *     returns 1: 'Kevin.van.Zonneveld'
    // *     example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
    // *     returns 2: 'hemmo, mars'

    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
    f = [].concat(search),
    r = [].concat(replace),
    s = subject,
    ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;}
            }
        }
        return sa ? s : s[0];
    }




    function init_attachments_sortable() {
        if(jQuery('div#attachments-list ul:data(sortable)').length==0&&jQuery('div#attachments-list ul li').length>0){
            jQuery('div#attachments-list ul').sortable({
                containment: 'parent',
                stop: function(e, ui) {
                    jQuery('#attachments-list ul li').each(function(i, id) {
                        jQuery(this).find('input.attachment_order').val(i+1);
                    });
                }
            });
        }
    }




    function attachments_handle_attach(title,caption,id,thumb){

        attachment_index = jQuery('li.attachments-file', top.document).length;
        new_attachments = '';

        attachment_name 		= title;
        attachment_caption 		= caption;
        attachment_id			= id;
        attachment_thumb 		= thumb;

        attachment_index++;
        new_attachments += '<li class="attachments-file">';
        new_attachments += '<h2><a href="#" class="attachment-handle"><span class="attachment-handle-icon"><img src="' + attachments_base + '/images/handle.gif" alt="Drag" /></span></a><span class="attachment-name">' + attachment_name + '</span><span class="attachment-delete"><a href="#">Delete</a></span></h2>';
        new_attachments += '<div class="attachments-fields">';
        new_attachments += '<div class="textfield" id="field_attachment_title_' + attachment_index + '"><label for="attachment_title_' + attachment_index + '">Title</label><input type="text" id="attachment_title_' + attachment_index + '" name="attachment_title_' + attachment_index + '" value="' + attachment_name + '" size="20" /></div>';
        new_attachments += '<div class="textfield" id="field_attachment_caption_' + attachment_index + '"><label for="attachment_caption_' + attachment_index + '">Caption</label><input type="text" id="attachment_caption_' + attachment_index + '" name="attachment_caption_' + attachment_index + '" value="' + attachment_caption + '" size="20" /></div>';
        new_attachments += '</div>';
        new_attachments += '<div class="attachments-data">';
        new_attachments += '<input type="hidden" name="attachment_id_' + attachment_index + '" id="attachment_id_' + attachment_index + '" value="' + attachment_id + '" />';
        new_attachments += '<input type="hidden" class="attachment_order" name="attachment_order_' + attachment_index + '" id="attachment_order_' + attachment_index + '" value="' + attachment_index + '" />';
        new_attachments += '</div>';
        new_attachments += '<div class="attachment-thumbnail"><span class="attachments-thumbnail">';


        new_attachments += '<img src="' + attachment_thumb + '" alt="Thumbnail" />';
        new_attachments += '</span></div>';
        new_attachments += '</li>';

        jQuery('div#attachments-list ul', top.document).append(new_attachments);

        if(jQuery('#attachments-list li', top.document).length > 0) {

            // We've got some attachments
            jQuery('#attachments-list', top.document).show();

        }
    }


    jQuery(document).ready(function() {

        if(attachments_is_attachments_context)
        {
            jQuery('body').addClass('attachments-media-upload');

            // we need to hijack the Attach button
            jQuery('td.savesend input').live('click',function(e){
                theparent = jQuery(this).parent().parent().parent();
                thetitle = theparent.find('tr.post_title td.field input').val();
                thecaption = theparent.find('tr.post_excerpt td.field input').val();
                theid = str_replace( 'imgedit-response-', '', theparent.find('td.imgedit-response').attr('id') );
                thethumb = theparent.parent().parent().find('img.pinkynail').attr('src');
                attachments_handle_attach(thetitle,thecaption,theid,thethumb);
                jQuery(this).after('<span class="attachments-attached-note"> Attached</span>').parent().find('span.attachments-attached-note').delay(1000).fadeOut();
                return false;
            });
        }

        // we're going to handle the Thickbox
        jQuery("body").click(function(event) {
            if (jQuery(event.target).is('a#attachments-thickbox')) {
                tb_show("Attach a file", event.target.href, false);
                return false;
            }
        });

        // Modify thickbox link to fit window. Adapted from wp-admin\js\media-upload.dev.js.
        jQuery('a#attachments-thickbox').each( function() {
            var href = jQuery(this).attr('href'), width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
            if ( ! href ) return;
            href = href.replace(/&width=[0-9]+/g, '');
            href = href.replace(/&height=[0-9]+/g, '');
            jQuery(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
        });

        // If there are no attachments, let's hide this thing...
        if(jQuery('div#attachments-list li').length == 0) {
            jQuery('#attachments-list').hide();
        }

        // Hook our delete links
        jQuery('span.attachment-delete a').live('click', function() {
            attachment_parent = jQuery(this).parent().parent().parent();
            attachment_parent.slideUp(function() {
                attachment_parent.remove();
                jQuery('#attachments-list ul li').each(function(i, id) {
                    jQuery(this).find('input.attachment_order').val(i+1);
                });
                if(jQuery('div#attachments-list li').length == 0) {
                    jQuery('#attachments-list').slideUp(function() {
                        jQuery('#attachments-list').hide();
                    });
                }
            });		
            return false;
        });

        // we also need to get a bit hacky with sortable...
        setInterval('init_attachments_sortable()',500);

    });