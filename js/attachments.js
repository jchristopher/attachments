function attachments_uniqid (prefix, more_entropy) {
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +    revised by: Kankrelune (http://www.webfaktory.info/)
  // %        note 1: Uses an internal counter (in php_js global) to avoid collision
  // *     example 1: uniqid();
  // *     returns 1: 'a30285b160c14'
  // *     example 2: uniqid('foo');
  // *     returns 2: 'fooa30285b1cd361'
  // *     example 3: uniqid('bar', true);
  // *     returns 3: 'bara20285b23dfd1.31879087'
  if (typeof prefix == 'undefined') {
    prefix = "";
  }

  var retId;
  var formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16); // to hex str
    if (reqWidth < seed.length) { // so long we split
      return seed.slice(seed.length - reqWidth);
    }
    if (reqWidth > seed.length) { // so short we pad
      return Array(1 + (reqWidth - seed.length)).join('0') + seed;
    }
    return seed;
  };

  // BEGIN REDUNDANT
  if (!this.php_js) {
    this.php_js = {};
  }
  // END REDUNDANT
  if (!this.php_js.uniqidSeed) { // init seed with big random int
    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
  }
  this.php_js.uniqidSeed++;

  retId = prefix; // start with prefix, add current milliseconds hex string
  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
  if (more_entropy) {
    // for more entropy we add a float lower to 10
    retId += (Math.random() * 10).toFixed(8).toString();
  }

  return retId;
}

function attachments_isset () {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: FremyCompany
  // +   improved by: Onno Marsman
  // +   improved by: Rafał Kukawski
  // *     example 1: isset( undefined, true);
  // *     returns 1: false
  // *     example 2: isset( 'Kevin van Zonneveld' );
  // *     returns 2: true
  var a = arguments,
    l = a.length,
    i = 0,
    undef;

  if (l === 0) {
    throw new Error('Empty isset');
  }

  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false;
    }
    i++;
  }
  return true;
}

jQuery(document).ready(function($){
    $( '.attachments-container' ).sortable({
        placeholder: 'attachments-attachment-highlight',
        opacity: 0.5,
        forceHelperSize: true,
        forcePlaceholderSize: true,
        items: '> .attachments-attachment',
        scroll: true,
        tolerance: 'intersect',
        axis: 'y',
        containment: 'parent',
        handle: '.attachments-handle img',
        start: function(event, ui) {
            $(document).trigger('attachments/sortable_start');
        },
        stop: function(event, ui) {
            $(document).trigger('attachments/sortable_stop');
        }
    });
    $('body').on('click','.attachments-attachment-fields-toggle > a', function(){
        $(this).parents('.attachments-attachment').find('.attachments-fields').toggle();
        return false;
    });
    $('.attachments-container').each(function(){
      $(this).append('<span class="attachments-toggler">Collapse</span>');
    });
    $('body').on('click', '.attachments-toggler', function(e) {
      e.stopPropagation();
      var $this = $(this);
      var $parent = $this.parents('.postbox');
      var $attachments = $parent.find('.attachments-attachment');
      if(!$parent.data('collapsed')){
        $attachments.find('.attachments-fields').height(105)
        $parent.data('collapsed', true);
        $this.text('Expand');
      } else {
        $attachments.find('.attachments-fields').css('height','');
        $parent.data('collapsed', false);
        $this.text('Collapse');
      }
      return false;
    });

    $('body').on('click','.select-attachment', function showBatchRemoveBtnIfNeeded(e){
        //select all selected attachment checkboxes
        if( $('.select-attachment:checked').length ){
            $('.attachments-batch-remove').show();
        }else{
            $('.attachments-batch-remove').hide();
        }
    });
});
