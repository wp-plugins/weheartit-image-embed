<?php
/*
  Plugin Name: WeHeartIt Image Embed
  Plugin URI: http://anybuy.vn/lo-quay-vit.htm
  Description: This plugin help you insert image from your weheartit.com account to post very quickly.
  Version: 1.0.1
  Author: lonuongvit
  Author URI: http://anybuy.vn/lo-quay-vit.htm
 */

add_action('admin_enqueue_scripts', 'whiie_enqueue');
function whiie_enqueue($hook) {
    if (('edit.php' != $hook) && ('post-new.php' != $hook) && ('post.php' != $hook))
        return;
    wp_enqueue_script('colorbox', plugin_dir_url(__FILE__) . '/js/jquery.colorbox.js', array('jquery'));
    wp_enqueue_style('colorbox', plugins_url('css/colorbox.css', __FILE__));
}

add_action('media_buttons_context', 'add_whiie_button');
add_action('admin_footer', 'add_inline_popup_content');
function add_whiie_button($context) {
    $context = '<a href="#whiie_popup" id="whiie-btn" class="button add_media" title="WeHeartIt Image"><span class="wp-media-buttons-icon"></span> WeHeartIt Image</a><input type="hidden" id="whiie_featured_url" name="whiie_featured_url" value="" />';
    return $context;
}

function add_inline_popup_content() {
    ?>
    <style>
        .whiie-container{
            width: 640px;
            display: inline-block;
            margin-top: 10px;
        }
        .whiie-item{
            position: relative;
            display: inline-block;
            width: 150px;
            height: 150px;
            text-align: center;
            border: 1px solid #ddd;
            float: left;
            margin-right: 3px;
            margin-bottom: 3px;
            padding: 2px;
            background: #fff;
        }
        .whiie-item img{
            max-width: 150px;
            max-height: 150px;
        }
        .whiie-use-image{
            width: 100%;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dedede;
            display: none;
        }
        .whiie-item span{
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #000;
            padding: 0 4px;
            color: #fff;
            font-size: 10px;
        }
        .whiie-page{
            text-align: center;
        }
        .whiie-item-overlay{width: 150px;height: 150px;background: #000; position: absolute; top: 2px; left: 2px; z-index: 997; opacity:0.7; filter:alpha(opacity=70); display: none}
        .whiie-item-link{display: none; position: absolute; top: 50px; width: 100%; text-align: center; z-index: 998}
        .whiie-item-link a{
            display: inline-block;
            background: #fff;
            padding: 0 10px;
            height: 24px;
            line-height: 24px;
            margin-bottom: 5px;
            text-decoration: none;
            width: 90px;
            font-size: 12px;
        }
        .whiie-item:hover > .whiie-item-overlay{display: block}
        .whiie-item:hover > .whiie-item-link{display: block}
        .whiie-loading{display: inline-block; height: 20px; line-height: 20px; min-width:20px; padding-left: 25px; background: url("<?php echo plugin_dir_url(__FILE__) . '/images/spinner.gif'; ?>") no-repeat;}
    </style>
    <div style='display:none'>
        <div id="whiie_popup" style="width: 640px; height: 600px; padding: 10px; overflow: hidden">
            <div style="width:98%; display: inline-block; margin-top: 5px; height:28px; line-height: 28px;"><span style="float:left; margin-right: 10px;">WeHeartIt user name</span> <input type="text" id="whiieinput" name="whiieinput" value="" size="30"/> <input type="button" id="whiiesearch" class="button" value="Search"/> <span id="whiiespinner" style="display:none" class="whiie-loading"> </span></div>
            <div id="whiie-container" class="whiie-container"><br/><br/>WARNING: All images from weheartit.com have reserved rights, so don't use images without license! Author of plugin are not liable for any damages arising from its use.</div>
            <div id="whiie-page" class="whiie-page"></div>
            <div id="whiie-use-image" class="whiie-use-image">
                <div class="whiie-item" id="whiie-view" style="margin-right: 20px;"></div>
                Title: <input type="text" id="whiie-title" size="42" value=""><br/><br/>
                Width: <input type="text" id="whiie-width" size="8" value="0"> x Height: <input type="text" id="whiie-height" size="8" value="0"><br/><br/>
                <input type="hidden" id="whiie-url" value="">
                <input type="button" id="whiieinsert" class="button button-primary" value="Insert into post">
            </div>
        </div>
    </div>
    <script>
        function insertAtCaret(areaId, text) {
            var txtarea = document.getElementById(areaId);
            var scrollPos = txtarea.scrollTop;
            var strPos = 0;
            var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
                "ff" : (document.selection ? "ie" : false));
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                strPos = range.text.length;
            }
            else if (br == "ff")
                strPos = txtarea.selectionStart;

            var front = (txtarea.value).substring(0, strPos);
            var back = (txtarea.value).substring(strPos, txtarea.value.length);
            txtarea.value = front + text + back;
            strPos = strPos + text.length;
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                range.moveStart('character', strPos);
                range.moveEnd('character', 0);
                range.select();
            }
            else if (br == "ff") {
                txtarea.selectionStart = strPos;
                txtarea.selectionEnd = strPos;
                txtarea.focus();
            }
            txtarea.scrollTop = scrollPos;
        }
        jQuery("#whiiesearch").click(function() {
            vShowImages(0);
        });
        jQuery("#whiie-btn").colorbox({inline: true, width: "670px"});
        jQuery("#whiie-page a").live("click", function() {
            vShowImages(jQuery(this).attr("rel") - 1);
        });
        jQuery("#whiieinsert").live("click", function() {
            if(jQuery('#whiie-url').val() != '') {
                vinsert = '<img src="'+jQuery('#whiie-url').val()+'" width="'+jQuery('#whiie-width').val()+'" height="'+jQuery('#whiie-height').val()+'" title="'+jQuery('#whiie-title').val()+'" alt="'+jQuery('#whiie-title').val()+'"/>';
                if (!tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
                    insertAtCaret('content', vinsert);
                } else {
                    tinyMCE.activeEditor.execCommand('mceInsertContent', 0, vinsert);
                }
                jQuery.colorbox.close();
            } else {
                alert('Have an error! Please try again!');
            }
        });
        jQuery("#whiiefeatured").live("click", function() {
            vffurl = jQuery('#whiie-url').val();
            jQuery('#whiie_featured_url').val(vffurl);
            jQuery('#postimagediv div.inside img').remove();
            jQuery('#postimagediv div.inside').prepend('<img src="'+vffurl+'" width="270"/>');
            jQuery.colorbox.close();
        });
        jQuery("#remove-post-thumbnail").live("click", function() {
            jQuery('#whiie_featured_url').val('');
        });
        jQuery(".whiie-item-use").live("click", function() {
            jQuery("#whiie-use-image").show();
            jQuery('#whiie-title').val(jQuery(this).attr('whiietitle'));
			var image = new Image();
			image.src = jQuery(this).attr('whiieurl');
			var imageWidth = image.width;
			var imageHeight = image.height;	
            jQuery('#whiie-width').val(imageWidth);
            jQuery('#whiie-height').val(imageWidth);
            jQuery('#whiie-url').val(jQuery(this).attr('whiieurl'));
            jQuery('#whiie-view').html('<img src="' + jQuery(this).attr('whiieurl') + '"/>');
        });
        function vShowImages(page) {
            if(jQuery("#whiieinput").val() == '') {
                alert('Please enter weheartit.com username!');
            } else {
                jQuery('#whiiespinner').show();
                jQuery('#whiie-container').html("");
                vurl = "http://weheartit.com/" + jQuery("#whiieinput").val() + ".rss";
				//using ajax + fetch feed
				var data = {
					action: 'whiie_fetch_feed_action',
					feed: vurl
				};
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(response) {
					jQuery('#whiie-container').html(response);
					jQuery('#whiiespinner').hide();	
				});
				
            }
        }
    </script>
    <?php
}
add_action('wp_ajax_whiie_fetch_feed_action', 'ajax_whiie_fetch_feed');
function ajax_whiie_fetch_feed(){
	$html = '';
	include_once( ABSPATH . WPINC . '/feed.php' );
	$rss = fetch_feed( $_POST['feed'] );
	if ( ! is_wp_error( $rss ) ) :
		$maxitems = $rss->get_item_quantity( 5 ); 
		$rss_items = $rss->get_items( 0, $maxitems );
	endif;
	if ( $maxitems == 0 ) : 
			$html = 'No result! Please try again!';
	else :
		foreach ( $rss_items as $item ) : 
			$desc = esc_html($item->get_description());
			$start = strpos($desc, ' src=&quot;');
			$end = strpos($desc, '&quot; /&gt;');
			$img = substr($desc, $start + 11, $end - $start - 11);							
			$html .= '<div class="whiie-item"><div class="whiie-item-link"><a href="' . esc_html( $item->get_link() ) . '" target="_blank" title="View this image in new windows">View</a><a class="whiie-item-use" whiietitle = "' . esc_html( $item->get_title() ) . '" whiieurl="' . $img . '" href="#">Use this image</a></div><div class="whiie-item-overlay"></div>' . $item->get_description() . '</div> ';
		endforeach;
	endif;
	echo $html;
	die();
}
?>