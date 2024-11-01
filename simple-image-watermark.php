<?php
/*
Plugin Name: Simple Image Watermark
Plugin URI: http://www.2klabs.com/siw
Description: Add watermark to images
Version: 1.0
Author: Amir Ahmic
Author URI: http://www.2klabs.com
License: GPL2
Copyright: Amir Ahmic
*/

define( 'SIW_PATH', plugin_dir_url(__FILE__) );

add_action('admin_enqueue_scripts', 'siw_enqueue_scripts');
function siw_enqueue_scripts() {
    wp_register_script( 'siw-upload', SIW_PATH.'/js/scripts.js', array('jquery','media-upload','thickbox') );
    if ( 'settings_page_siw_options_page' == get_current_screen() -> id ) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('siw-upload');
    }
}

// Specify Hooks/Filters
register_activation_hook(__FILE__, 'add_defaults_fn');

// Define default option settings
function add_defaults_fn() {
    $tmp = get_option('siw_plugin_options');
    
    $arr = array('image' => '', 'watermark_position' => '1', 'margin_leftright' => '0', 'margin_topdown' => '0');
    add_option('siw_plugin_options', $arr);
}

add_action( 'admin_menu', 'siw_menu' );

function siw_menu() {
    add_options_page( 'Simple Image Watermark options', 'Watermark options', 'manage_options', 'siw_options_page', 'siw_options' );
}

function siw_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    
    ?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>Simple Image Watermark - Options Page</h2>
        
        <form action="options.php" method="post">
        
        <div id="poststuff">
            <div class="postbox" style="">
                <h3>Info</h3>
                <div style="margin: 10px !important;">
                    <p>- Check image sizes on which you want to apply watermark. Watermark will be apllied sfter image is uploaded.</p>
                    <p>- Select image for watermark. You can upload image from your computer or choose one from media library. Allowed image types are JPG and PNG. Do not choose large images becouse it won't be resized.</p>
                    <p>- Choose watermark position on images.</p>
                    <p>- If you want you can adjust watermark position with margins. Margin type depend on position you already choose. Margins will pe applied to border sides of watermark.</p><br />
                    <p>- Save settings and go to media library to test :) If you have any questions go to <a href="http://www.2klabs.com/siw" target="_blank">Plugin homepage</a>.</p>
                    
                </div>
            </div>
            
            <div id="" class="postbox " style="">
                
                <?php settings_fields('siw_plugin_options'); ?>
                <?php do_settings_sections(__FILE__); ?>
                <p class="submit" style="margin-left: 10px;">
                    <input id="submit-siw-options" name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
            </div>
            
            
            
        </div>
        </form>
               
        
    </div>
<?php
}

add_action('admin_init', 'siw_options_init_fn' );
// Register our settings. Add the settings section, and settings fields
function siw_options_init_fn(){
    register_setting('siw_plugin_options', 'siw_plugin_options', 'siw_plugin_options_validate');
    add_settings_section('main_section', 'Main Settings', 'section_text_fn', __FILE__);
    add_settings_field('image_sizes', 'Image sizes', 'image_sizes_fn', __FILE__, 'main_section');
    //add_settings_field('text_string', 'Text Input', 'setting_string_fn', __FILE__, 'main_section');
    add_settings_field('image', 'Watermark image', 'watermark_fn', __FILE__, 'main_section');
    add_settings_field('watermark_position', 'Watermark position', 'watermark_position_fn', __FILE__, 'main_section');
    add_settings_field('watermark_margins', 'Watermark margins', 'watermark_margins_fn', __FILE__, 'main_section');
}

function section_text_fn(){
       
}

function setting_string_fn() {
    $options = get_option('siw_plugin_options');
    //var_dump($options);
    echo "<input id='text_string' name='siw_plugin_options[text_string]' size='40' type='text' value='{$options['text_string']}' class='regular-text' />";
}

function watermark_fn(){
    $options = get_option('siw_plugin_options');
    if(isset($options['image']) && $options['image'] != ''){
        echo '<img class="current_watermark_image" src="'.$options['image'].'" /><br>';
    }
    if($options['image'] == '') echo '<p class="no_image_flag">No image selected!</p>';
    
    echo '<input type="text" id="image_url" name="siw_plugin_options[image]" value="'.$options['image'].'" class="regular-text" />  
        <input id="upload_image_button" type="button" class="button" value="Select image" />  
        <br><p class="description">Select image (.png or .jpg) from your computer or Media library and click "Use image as watermark button".</p>';
    
}

function image_sizes_fn(){
    $sizes = get_intermediate_image_sizes();
    $sizes[] = 'full size';
    $options = get_option('siw_plugin_options');
    
    $checked = 'chk';
    if(isset($options['sizes'])){
        $options = $options['sizes'];
        $checked = '';
    }
    
    //var_dump($options);
    
    foreach($sizes as $s){
        if($checked != 'chk'){
            if(in_array($s, $options)) $checked = 'checked';
            else $checked = '';
        }
        
        echo "<label><input value='".$s."' name='siw_plugin_options[sizes][".$s."]' type='checkbox' ".$checked."/> ".ucfirst($s)."</label><br />";
    }
    echo '<p class="description">Check image sizes on which you want to apply watermark.</p>';
}

function watermark_position_fn(){
    echo "<style>
        .watermark_position_container{
            float: left;
            background: #BBB;
        }
        .watermark_position_container label{
            float: left;
        }
        .watermark_position_container .cleft{
            clear: left;
        }
        </style>";
    
    $chk = array('', '', '', '', '', '', '', '', '');
    
    $pos = get_option('siw_plugin_options');
    if(isset($pos['watermark_position'])){
        $chk[(int)$pos['watermark_position'] - 1] = 'checked';
    }
    
    echo "<div class='watermark_position_container'>";
    echo "<label style='padding: 7px 30px 10px 10px;'><input value='1' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[0]."/> </label>";
    echo "<label style='padding: 7px 30px 10px 30px;'><input value='2' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[1]."/> </label>";
    echo "<label style='padding: 7px 10px 10px 30px;'><input value='3' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[2]."/> </label>";
    echo "<label style='padding: 10px 30px 10px 10px;' class='cleft'><input value='4' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[3]."/> </label>";
    echo "<label style='padding: 10px 30px 10px 30px;'><input value='5' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[4]."/> </label>";
    echo "<label style='padding: 10px 10px 10px 30px;'><input value='6' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[5]."/> </label>";
    echo "<label style='padding: 10px 30px 10px 10px;' class='cleft'><input value='7' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[6]."/> </label>";
    echo "<label style='padding: 10px 30px 10px 30px;'><input value='8' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[7]."/> </label>";
    echo "<label style='padding: 10px 10px 10px 30px;'><input value='9' name='siw_plugin_options[watermark_position]' type='radio' ".$chk[8]."/> </label>";
    echo "</div>";
    echo '<p style="clear: left;" class="description">Choose image segment you wish to place watermark.</p>';
}

function watermark_margins_fn(){
    $options = get_option('siw_plugin_options');
    if(isset($options['margin_leftright']) && isset($options['margin_topdown'])){
        $lr = (int)$options['margin_leftright'];
        $td = (int)$options['margin_topdown'];
    }
    else{
        $lr = 0;
        $td = 0;
    }
    echo '<label>Margin (left or right)&nbsp;&nbsp;&nbsp;<input name="siw_plugin_options[margin_leftright]" type="number" step="1" min="0" max="200" id="margin_leftright" value="'.$lr.'" class="small-text"></label>';
    echo '<br /><label>Margin (top or down)&nbsp;<input name="siw_plugin_options[margin_topdown]" type="number" step="1" min="0" max="200" id="margin_topdown" value="'.$td.'" class="small-text"></label>';
    echo '<p class="description">Margin alignment depends on position of watermark. If you choose to place watermark on the top right corner margin will be applied to top and right.</p>';
}

function siw_plugin_options_validate($input) {
	
        if(isset($input['image']) && $input['image'] != ''){
            $test = getimagesize($input['image']);
            if($test['mime'] != 'image/png' && $test['mime'] != 'image/jpeg') $input['image'] = '';
        }
        if(isset($input['margin_leftright']) && isset($input['margin_topdown'])){
            if((int)$input['margin_leftright'] < 0 || (int)$input['margin_leftright'] > 200) $input['margin_leftright'] = '0';
            if((int)$input['margin_topdown'] < 0 || (int)$input['margin_topdown'] > 200) $input['margin_topdown'] = '0';
        }
        
	return $input; // return validated input
}

function siw_media_options() {  
    global $pagenow;  
    if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {  
        // Now we'll replace the 'Insert into Post Button' inside Thickbox  
        add_filter( 'gettext', 'replace_thickbox_text'  , 1, 3 ); 
    } 
} 
add_action( 'admin_init', 'siw_media_options' ); 

function replace_thickbox_text($translated_text, $text, $domain) { 
    if ('Insert into Post' == $text) { 
        $referer = strpos( wp_get_referer(), 'siw_options_page' ); 
        if ( $referer != '' ) { 
            return 'Use image as watermark';  
        }  
    }  
    return $translated_text;  
}  

// Image processing

function siw_add_watermark($meta){
    $referer = strpos( wp_get_referer(), 'siw_options_page' ); 
    if ( $referer != '' ) { 
        return $meta;  
    }  
    //var_dump($meta);
    $options = get_option('siw_plugin_options');
    if(!isset($options['sizes']) || !isset($options['image']) || $options['image'] == '') return $meta;
    
    foreach($options['sizes'] as $size){ // Cycle trough checked image sizes to crop
        if(isset($meta['sizes'][$size]) || $size == 'full size'){ // Check if given size exist
            
            $pos = strpos($options['image'], 'wp-content/');
            $watermark_path = ABSPATH.substr($options['image'], $pos);
            
            $test = getimagesize($watermark_path);
            //var_dump($test);
            
            if($test['mime'] == 'image/png'){
                $stamp = imagecreatefrompng($watermark_path);
            }elseif($test['mime'] == 'image/jpeg'){
                $stamp = imagecreatefromjpeg($watermark_path);
            }
        
            // Url of processed file
            $file = wp_upload_dir();
            
            if($size == 'full size'){
                $pos = strrpos($meta['file'], '/');
                $img_name = substr($meta['file'], $pos + 1);
                
                $file = trailingslashit($file['path']).$img_name;
                
            }else $file = trailingslashit($file['path']).$meta['sizes'][$size]['file'];

            $file_params = getimagesize($file);
            
            if($file_params['mime'] == 'image/png'){
                $im = imagecreatefrompng($file);
            }elseif($file_params['mime'] == 'image/jpeg'){
                $im = imagecreatefromjpeg($file);
            }

            // Set the margins for the stamp and get the height/width of the stamp image
            $marginlr = (int)$options['margin_leftright'];
            $margintd = (int)$options['margin_topdown'];
            $stampw = imagesx($stamp);
            $stamph = imagesy($stamp);
            $mainw = imagesx($im);
            $mainh = imagesy($im);
            
            switch($options['watermark_position']){
                case '1':
                    $posx = $marginlr;
                    $posy = $margintd;
                break;
                case '2':
                    $posx = ($mainw - $stampw) / 2;
                    $posy = $margintd;
                break;
                case '3':
                    $posx = $mainw - $stampw - $marginlr;
                    $posy = $margintd;                    
                break;
                case '4':
                    $posx = $marginlr;
                    $posy = ($mainh - $stamph) / 2;
                break;
                case '5':
                    $posx = ($mainw - $stampw) / 2;
                    $posy = ($mainh - $stamph) / 2;
                break;
                case '6':
                    $posx = $mainw - $stampw - $marginlr;
                    $posy = ($mainh - $stamph) / 2;
                break;
                case '7':
                    $posx = $marginlr;
                    $posy = $mainh - $stamph - $margintd;
                break;
                case '8':
                    $posx = ($mainw - $stampw) / 2;
                    $posy = $mainh - $stamph - $margintd;
                break;
                case '9':
                    $posx = $mainw - $stampw - $marginlr;
                    $posy = $mainh - $stamph - $margintd;
                break;
                
                default:
                    
       
                    
            }

            // Copy the stamp image onto our photo using the margin offsets and the photo 
            // width to calculate positioning of the stamp. 
            imagecopy($im, $stamp, $posx, $posy, 0, 0, imagesx($stamp), imagesy($stamp));


            imagejpeg($im, $file, 90);
            imagedestroy($im);
        }	
        
    }
	
    return $meta;
		
}

add_filter('wp_generate_attachment_metadata', 'siw_add_watermark');


?>