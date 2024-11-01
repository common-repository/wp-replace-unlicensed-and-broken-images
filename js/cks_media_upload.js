
var upload_image_button = false;

jQuery(document).ready(function() {

    jQuery( '#upload_image_button' ).click(function() {
        
        upload_image_button =true;
        formfieldID = jQuery(this).prev().attr("id");
        formfield = jQuery("#"+formfieldID).attr('name');
        tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
        
        if( upload_image_button === true ) {

            var oldFunc = window.send_to_editor;
            window.send_to_editor = function(html) {

            imgurl = jQuery('img', html).attr('src');
            jQuery("#"+formfieldID).val(imgurl);
            tb_remove();
            window.send_to_editor = oldFunc;

            };
        }
        
        upload_image_button = false;
        
    });

});

// solution based on http://stackoverflow.com/questions/17320802/how-can-i-use-multi-media-uploader-in-the-wordpress-plugins
// @Since: 1.0.4 // @Date: October 2016 // @Author: CK MacLeod // @Author: URI: http://ckmacleod.com // @License: GPL3

