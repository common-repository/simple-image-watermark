jQuery(document).ready(function($) {  
    $('#upload_image_button').click(function() {  
        tb_show('Upload an image', 'media-upload.php?referer=siw_options_page&type=image&TB_iframe=true&post_id=0', false); 
        return false;  
    });  
}); 

window.send_to_editor = function(html) {  
    var image_url = jQuery('img',html).attr('src');  
    jQuery('#image_url').val(image_url);  
    tb_remove();  
    jQuery('#submit-siw-options').trigger('click'); 
}