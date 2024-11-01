<?php

/**
 * @package: WordPress Replace Unlicensed and Broken Images
 * @Version: 1.0.5
 * @Date: October 2016
 * @Author: CK MacLeod
 * @Author: URI: http://ckmacleod.com
 * @License: GPL3
 */

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' ) ;

/**
 * Initialize admin actions
 */
if ( is_admin() ) {
    
    //call register settings function
    add_action( 'admin_init', 'cks_rui_register_settings' ) ;
    add_action( 'admin_menu', 'cks_rui_create_menu' ) ;
    add_action( 'admin_init', 'cks_rui_check_reset' ) ;
    
}

/* 
 * Create custom plugin settings menu
 */
function cks_rui_create_menu() {

    //create new settings page menu
    $menu_page = add_options_page( 'Replace Unlicensed and Broken Images Page', 'Replace Unlicensed and Broken Images', 'manage_options', 'cks_rui_main_menu', 'cks_rui_settings_page' ) ;
 
    //load admin menu specific js (to avoid conflicts)
    add_action( 'load-' . $menu_page, 'load_admin_js' ) ;
    
}

/**
 * ENQUEUE ADMIN SCRIPTS AND STYLES
 */
function cks_rui_admin_scripts() {
        
    wp_register_script(

        'cks_rui_scripts',
        plugins_url( 'js/cks_rui_scripts.js', __FILE__ ),
        array( 'jquery', 'inline-edit-post' )

    ) ;
    
    wp_enqueue_script( 'jquery' ) ;

    wp_enqueue_script( 'cks_rui_scripts' ) ;

}

function cks_rui_admin_styles() {

    wp_enqueue_style( 'thickbox' ) ;
    
    $screen = get_current_screen() ;
    
    //enqueue stylesheet only where necessary
    if ( $screen->base == 'edit' || $screen->base == 'post' || ( isset($_GET['page']) && $_GET['page'] == "cks_rui_main_menu" ) ) { 

        wp_enqueue_style( 'cks_rui_admin_style', plugin_dir_url( __FILE__ ) . 'cks_rui_admin.css' . '?v=' . WPRUBI_VERSION ) ;
 
    }

}

add_action( 'admin_print_scripts', 'cks_rui_admin_scripts' ) ;

add_action( 'admin_print_styles', 'cks_rui_admin_styles' ) ;

/**
 * Menu Page-Specific JS Script
 */
function load_admin_js() {
    
    add_action( 'admin_enqueue_scripts', 'enqueue_admin_js' ) ;
}

function enqueue_admin_js() {
    
    //pre-registered
    wp_enqueue_script( 'media-upload' ) ;
    wp_enqueue_script( 'thickbox' ) ;
    
    //own
    wp_register_script(

        'cks_media_upload',
        plugins_url( 'js/cks_media_upload.js', __FILE__ ),
        array( 'jquery', 'media-upload', 'thickbox'  )

    ) ;
    wp_enqueue_script( 'cks_media_upload' ) ;
    
    wp_register_script(

        'cks_admin_url',
        plugins_url( 'js/cks_admin_url_select.js', __FILE__ ),
        array( 'jquery' )

    ) ;
    wp_enqueue_script( 'cks_admin_url' ) ;
        
}
     
/**
 * USE SETTINGS API
 */
function cks_rui_register_settings() {

    //register our settings
    register_setting( 'cks_rui_settings_group', 'cks_rui_options', 'cks_rui_sanitize_options' ) ;

}

/**
 * Create settings page
 * Utilizes unnecessary "section" possibly useful for future
 * multi-page menu, conforms to "CK's" css styles
 */
function cks_rui_settings_page() {
    
    $version = get_option( 'cks_rui_version' ) ;
    
    ?>
    
    <div class="wrap cks_plugins" >
        
        <h1><?php _e( 'REPLACE UNLICENSED AND BROKEN IMAGES', 'cks_rui' ) ; ?></h1>
        
        <div id="cks_plugins-main">

        <div id="cks_plugins-sections">
            
            <section>
                
                <p id="goto-menu-top" class="goto-menu"><?php printf( __('<a href="%s">Image/Link Matches</a> | <a href="%s">Set Replacement</a> | <a href="%s">Capability</a> | <a href="%s">Add Badge</a> | <a href="%s">Reset</a>', 'cks_rui'), '#image-link-matches', '#replacement-image', '#capability', '#daa-link', '#reset' ) ; ?></p>
           
                <h2><?php _e( 'Inclusions and Exclusions', 'cks_rui' ) ; ?></h2>
                        
                <p><?php _e( 'Image links and image anchor links will be matched according to the settings below and replaced on the site\'s public-facing Front End. Actual content in site database will remain unaffected, so can be recovered or retrieved, and viewed when editing posts.', 'cks_rui' ) ; ?></p> 
                
                <p><?php _e( 'You can also, or alternatively, set specific Posts to be excluded from or included in image removal and replacement from <a href="edit.php">All Posts</a> via Quick or Bulk Editing, or from any particular Post or Page editing screen. <b>Exclusions (clearances for display) typically take priority, but individual Post or Page settings override all.</b>', 'cks_rui' ) ; ?></p>
                
                <p><?php _e( 'Use <a href="#image-link-matches">Image/Link Matches</a> below to refine the selection further if necessary.', 'cks_rui' ) ; ?></p>
       
            <?php cks_rui_main_settings_form() ; ?>
            
            <hr id="reset">
             
            <?php cks_rui_reset_form() ; ?>
            
            <p id="goto-menu-bottom" class="goto-menu"><?php printf( __('<a href="%s">Inclusions/Exclusions</a> | <a href="%s">Image/Link Matches</a> | <a href="%s">Set Replacement</a> | <a href="%s">Capability</a> | <a href="%s">Add Badge</a>', 'cks_rui'), '#goto-menu-top', '#image-link-matches', '#replacement-image', '#capability', '#daa-link' ) ; ?></p>
           
        </section>
            
        </div>      
            
        </div>

        <?php cks_rui_sidebar( $version ) ; ?>
        
        <?php cks_plugins_footer( $version ) ; ?>
        
    </div> <!--Settings Page HTML "wrap" -->

    <?php

}

/**
 * Main Settings Form
 * Submits All Default Matching Options
 * as well as Replacement Image
 */
function cks_rui_main_settings_form() {
    
    $options = get_option( 'cks_rui_options' ) ; 
    
    ?>
    
    <form method="post" action="options.php">

        <?php submit_button( 'Save All Changes', 'primary top', 'submit', false ) ; ?>

        <?php settings_fields( 'cks_rui_settings_group' ) ; ?>

        <table class="form-table">

        <tbody>
            <tr>
                <th scope="row"><?php _e( '1. Remove if Prior to Date', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[prior_to_date]" value="<?php echo esc_attr($options['prior_to_date']) ; ?>" /><span class="description">
                    <?php _e( 'Matched images and links in Posts and Pages published prior to this date will be removed and replaced.', 'cks_rui' ) ; ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '2. Remove if After Date', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[after_date]" value="<?php echo esc_attr($options['after_date']) ; ?>" /><span class="description">
                    <?php _e( 'Matched images and links in Posts and Pages published after this date will be removed and replaced.', 'cks_rui' ) ; ?></span>
                </td> 
            </tr>
            <tr>
                <th scope="row"><?php _e( '<i>-- Treat Dates as Timespan</i>', 'cks_rui' ) ; ?></th>
            <?php if ( ! isset($options['timespan']) ) { $options['timespan'] = '' ; } ?>
                <td><input type="checkbox" name="cks_rui_options[timespan]" value="1" <?php checked( $options['timespan'], 1 ) ; ?>" /><span class="description">
                    <?php _e( 'If checked, only Posts and Pages whose publication dates fall <b>both</b> prior to Date #1 and after Date #2 will be included in image removal and replacement.', 'cks_rui' ) ; ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '3. Safe Categories', 'cks_rui' ) ; ?></th>
                <td><textarea name="cks_rui_options[exclude_cats]"><?php echo esc_attr($options['exclude_cats']) ; ?></textarea><span class="description">
                    <?php _e( 'Images and image-links in posts in this Category or Categories will be <b>excluded</b> from image removal and replacement, regardless of publication date. Use Category name, or comma-separated list of Category names  - <code>Science,Politics,Technology</code>. Includes sub-categories. (Try HTML character code for problematic <a href="http://www.w3schools.com/html/html_entities.asp" target="_blank" />"HTML Entities"</a>.)', 'cks_rui' ) ; ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '4. Remove from Categories', 'cks_rui' ) ; ?></th>
                <td><textarea name="cks_rui_options[include_cats]" ><?php echo esc_attr($options['include_cats']) ; ?></textarea><span class="description">
                    <?php _e( 'Matching images and image-links in posts in this Category or Categories will be removed and replaced, regardless of publication date. Use Category name, or comma-separated list of Category names, as in #3.', 'cks_rui' ) ; ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '5. Safe Authors', 'cks_rui' ) ; ?></th>
                <td><textarea name="cks_rui_options[exclude_authors]" ><?php echo esc_attr($options['exclude_authors']) ; ?></textarea><span class="description">
                    <?php _e( 'Matching images and image-links in posts by this Author or Authors will be <b>excluded</b> from image removal and replacement, regardless of publication date. Use name, username, email address, URL, or ID number, or comma-separated list - <code>John, pepperpotts, sabu@indiatimes.com,23</code>.', 'cks_rui' ) ; ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '6. Remove from Posts By', 'cks_rui' ) ; ?></th>
                <td><textarea name="cks_rui_options[include_authors]"><?php echo esc_attr($options['include_authors']) ; ?></textarea><span class="description">
                    <?php _e( 'Matching images and image-link in posts by this Author or Authors will be removed and replaced, regardless of publication date. Use author name, username, email address, URL, or ID number, or comma-separated list, as in #5.', 'cks_rui' ) ; ?></span>
                </td>    
            </tr>
            <tr>
                <th scope="row"><?php _e( '7. Preserve Thumbnails and Featured Images', 'cks_rui' ) ; ?></th>
            <?php if ( ! isset($options['exclude_featureds']) ) { $options['exclude_featureds'] = '' ; } ?>
                <td><input type="checkbox" name="cks_rui_options[exclude_featureds]" value="1" <?php checked( $options['exclude_featureds'], 1 ) ; ?>" /><span class="description">
                    <?php _e( 'Check this box if you do <b>NOT</b> want "Thumbnail" and "Featured" images to be replaced. (An exception for Fair Use of "thumbnail"-size images has been recognized in some copyright litigation, but the designation may be misleading, since in many themes it will be applied to large-sized images.)', 'cks_rui' ) ; ?></span>
                </td>
            </tr>

        </tbody>

        </table>

        <p class="submit">

            <input type="submit" class="button-primary" value="<?php _e( 'Save All Changes', 'cks_rui' ) ; ?>" />

        </p>
        
        <hr id="image-link-matches">

        <h3 ><?php _e( 'Image/Link Matches', 'cks_rui' ) ; ?></h3>
        
        <p><b><?php _e( 'These settings are case-sensitive, literal, and sequential, and must exactly match the characters that appear in your target filenames. See further instructions below.', 'cks_rui') ; ?></b></p>

        <table class="form-table" >

            <tbody>
            <tr>
                <th scope="row"><?php _e( '1. First String Match', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[match_1]" value="<?php echo esc_attr($options['match_1']) ; ?>" />
                    <span class="description">
                        <?php printf( __( 'Letters or characters, or comma- or space-separated list of character strings - <code>%s, othersite</code> - to be matched in target URLs. Default: "<b><code>%s</code></b>" (host URL). Can also be left blank.', 'cks_rui' ), $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST'])  ; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( '2. Second String Match', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[match_2]" value="<?php echo esc_attr($options['match_2']) ; ?>" /><span class="description">
                    <?php _e( 'Letters or characters, or comma- or space-separated list of character strings - <code>uploads files 2015</code> - to be matched in target URLs. Default: "<b><code>uploads</code></b>" (typical WordPress uploads directory). Can also be left blank.', 'cks_rui' ) ; ?>
                    </span>
                </td>
            </tr>  
            <tr>
                <th scope="row"><?php _e( '3. Matched Image Types', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[image_matches]" value="<?php echo esc_attr($options['image_matches']) ; ?>" /><span class="description">
                    <?php _e( 'List all image file extensions to be matched. Default: "<b><code>jpg jpeg png gif JPG JPEG</code></b>." These extensions are <b>CASE-SENSITIVE</b> - use "JPG" to match uppercase extensions if they appear on your site. If you leave matches #1 and #2 above blank, then all images exposed with these extensions will be removed.', 'cks_rui' ) ; ?></span>
                </td>
            </tr> 
            <tr>
                <th scope="row"><?php _e( '4. Remove and Replace ALL Static Images', 'cks_rui' ) ; ?></th>
            <?php if ( ! isset($options['global_image_del']) ) { $options['global_image_del'] = '' ; } ?>
                <td><input type="checkbox" name="cks_rui_options[global_image_del]" value="1" <?php checked( $options['global_image_del'], 1 ) ; ?>" /><span class="description">
                    <?php _e( 'Check this box to remove and replace all static images in Posts and Pages regardless of type or origin (though still subject to exclusions and inclusions as in prior section), as long as they are displayed using <code>&lt;img&gt;</code> tags. ', 'cks_rui' )  ; ?></span>
                </td>
            </tr>
            
            </tbody>

        </table>
        
        <p class="submit">

            <input type="submit" class="button-primary" value="<?php _e( 'Save All Changes', 'cks_rui' ) ; ?>" />

        </p>
        
        <p><?php _e( 'By default, the plug-in will match the filenames and locations of images in the Media Library of a typical WordPress installation. So, the default for "First String Match" (#1) is the base URL for your site; the default for "Second String Match" (#2) is "uploads," the name of the folder containing "Media Library" files at most WordPress sites; and "Matched Image Types" (#3) uses the most common image "file extensions."', 'cks_rui' ) ; ?></p>  
        
        <p><?php _e( 'If a typical image you wished to target looked like <code>http://yoursite.com/wp-content/uploads/2016/05/sample_image.jpg</code>, it would be <b>matched</b> by (1) <code>yoursite</code>, (2) <code>uploads</code> and (3) <code>jpg</code>, or (1) <code>site</code>, (2) <code>loads</code> and (3) <code>jpg gif png</code>, but would be <b>missed</b>  by (1) <code>Yoursite</code> (because there is no uppercase "Y" in target), or by (1) <code>uploads</code>, (2) <code>yoursite</code>, (3) <code>jpg</code> (because "yoursite" comes before "uploads" in the target). Similarly, it would be (narrowly) <b>matched</b> by (1) <code>yoursite</code>, (2) <code>2016/05</code> and (3) <code>jpg jpeg</code>, or (1) <code>wp-cont,uploads</code>, (2) <code>2015,2016</code> and (3) <code>jpg jpeg gif png</code>, but would be <b>missed</b> by (1) <code>yoursite</code>, (2) <code>uploads</code>, (3) <code>png gif jpeg</code> (because "jpg" is missing from #3).', 'cks_rui' ) ; ?></p>
        
        <p><?php _e( 'You can use multiple terms for matching ("yoursite,othersite"), but be careful, and, if fine-tuning, as specific as you can be, since a combination of #1 <code>yoursite,othersite</code> and #2 <code>uploads,2016</code> will match both "yoursite... uploads" and "yoursite... 2016," and so on.', 'cks_rui' ) ; ?></p>  
        
        <p><?php _e( 'For the broadest inclusion, click the checkbox at #4. This setting will affect "served" and copy-protected images that do not expose file extensions, but may produce awkward replacement of trivial images like icons, video-player controllers, avatars, and even invisible tracking icons in post content.If you use this option, you should still pattern match in options #1 - #3 to replace thumbnail or featured images, and to prevent "crawl" and "load" errors from broken anchor-links.', 'cks_rui' ) ; ?></p>
        
        <hr id="replacement-image">
        
        <h3><?php _e( 'Set Replacement', 'cks_rui' ) ; ?></h3>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e( '1. Select Replacement Image', 'cks_rui' ) ; ?></th>
                    <td>
                        <label for="upload_image">

                            <input id="upload_image" type="url" name="cks_rui_options[image_url]" value="<?php echo $options['image_url']; ?>" />

                            <input id="upload_image_button" type="button" value="Upload Image" />

                            <p>
                                
                                <?php printf( __( 'Enter a URL or upload an image to be used for image replacement. %sSet Mode (#2) and Save Changes to Complete Processing!%s', 'cks_rui' ),'<br><b>','</b>' ) ; ?>

                            </p>

                            <p><small><i>

                                <?php printf( __( 'The default image is "<b><code>%s</code></b>." <br><br>With dark-colored themes try (click to add): <b><code>%s</code></b> or <b><code>%s</code></b>.', 'cks_rui' ), 
                    '<a href="' . plugins_url( 'images/image_removed.svg', __FILE__ ) . '" class="def_image_link">' . plugins_url( 'images/image_removed.svg', __FILE__ ) . '</a>', 
                    '<a href="' . plugins_url( 'images/image_removed_white.svg', __FILE__ ) . '" class="def_image_link">' . plugins_url( 'images/image_removed_white.svg', __FILE__ ) . '</a>', 
                    '<a href="' . plugins_url( 'images/image_removed_background.svg', __FILE__ )  . '" class="def_image_link">' . plugins_url( 'images/image_removed_background.svg', __FILE__ )  . '</a>'
                                 ) ; ?>

                            </i></small></p>

                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row" id="replacement-mode-label"><?php _e( '2. Set Replacement Mode', 'cks_rui' ) ; ?></th>
                    <td>

                        <?php $display_mode =  $options['display_mode'] ? $options['display_mode'] : '' ; ?>

                        <?php cks_rui_display_mode_buttons( $display_mode ) ; ?>

                    </td>
                </tr>
            </tbody>
        </table>

        <p>

            <input type="submit" class="button-primary" value="<?php _e( 'Save All Changes', 'cks_rui' ) ; ?>" />

        </p>

         <div id="cks_rui-show-image">

            <h4 ><?php _e( 'Current Replacement Image (Save Changes to URL/Image to Complete Processing!):', 'cks_rui' ) ; ?></h4>

            <img id="cks_rui-replacement-image" src="<?php echo cks_rui_get_static_image_url( $options ) ; ?>">
            
            <?php if ( $display_mode === 'empty' ) { 
                
                printf( __( '<p>Displaying "empty image" from <code>%s</code>.</p>', 'cks_rui' ), plugins_url( 'images/empty_image.png', __FILE__ ) ) ; 
                
            } ?>
            
            <?php if ( $display_mode === 'none' ) { 
                
                _e( '<p>Replacement image is set to <b>non-display</b>. </p>', 'cks_rui' ) ;
                
            } ?>
            
            <?php if ( $display_mode === 'daa' ) { 
                
                _e( '<p>Replacement image is set to the Digital Arts Alliance image.!</p>', 'cks_rui' ) ;
                
            } ?>

        </div>

        <hr id="capability">

        <h3 id="capability-setting"><?php _e( 'Capability', 'cks_rui' ) ; ?></h3>

        <p>
            <?php _e( '<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank">WordPress Capability</a> required to view and set Removal and Replacement status of posts. ', 'cks_rui' ) ; ?> 
        </p>

        <table class="form-table">
            <tbody>

            <tr>
                <th scope="row"><?php _e( 'To View and Set Image Status, User Must Be Able to', 'cks_rui' ) ; ?></th>
                <td><input type="text" name="cks_rui_options[editor_capability]" value="<?php echo esc_attr($options['editor_capability']) ; ?>" /><span class="description">
                    <?php _e( 'Examples: <code>edit_others_posts</code>, the default, restricts the capability to Editors and Admins. <code>publish_posts</code> would enable Authors, too, though they still could not edit image status on other Authors\'s posts.', 'cks_rui' ) ; ?></span>
                </td>
            </tr>   

            </tbody>

        </table>
        
        <hr>
        
        <h3 id="daa-link"><?php _e( 'Digital Artists Alliance Links', 'cks_rui' ) ; ?></h3>
        
        <table class="form-table">
            
            <tbody>
        
            <tr>
                <th scope="row"><?php _e( 'Automatic Badge-Link', 'cks_rui' ) ; ?></th><div id="daa-badge" style="width:100%;">
        <img style="display: block; width: auto; margin: 0 auto 12px;" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/provisional_daa_badge.png' ; ?>">
        </div>
            <?php if ( ! isset($options['add_footer_badge']) ) { $options['add_footer_badge'] = '' ; } ?>
                <td><input type="checkbox" name="cks_rui_options[add_footer_badge]" value="1" <?php checked( $options['add_footer_badge'], 1 ) ; ?>" /><span class="description">
                    <?php _e( 'Check this box to place a discrete badge-link below and to the right of a typical WordPress site footer.', 'cks_rui' ) ; ?>
                        
                    </span>
                </td>
            </tr>
            
            </tbody>

        </table>
       
        
        <p>
            
            <?php _e( 'The image above is a "Digital Artists Alliance" badge, intended to link a site to a network of other sites, groups, and individuals defending and advancing the interests of all digital artists and publishers. Alternatively, add "<code>&lt;?php if ( function_exists( \'cks_rui_add_badge\' ) ) { echo cks_rui_add_badge() ; } ?&gt;</code> " to your theme, or badge shortcode <code>[add_cks_rui_badge]</code> in a Page or Post, where you want the badge to appear.', 'cks_rui' ) ; ?>
            
        </p>

        <p class="submit">

            <input type="submit" class="button-primary" value="<?php _e( 'Save All Changes', 'cks_rui' ) ; ?>" />

        </p>

    </form>
    
    <?php 
    
}

/**
 * RADIO BUTTONS FOR CHOOSING DISPLAY MODE
 * @param string $display_mode
 */
function cks_rui_display_mode_buttons( $display_mode ) {
    
    ?>
    
    <p class="admin-radio">
            
            <input id="cks_plugins_use_default" type="radio" name="cks_rui_options[display_mode]" title="<?php _e( 'Selected Image', 'cks_rui' ) ; ?>" value="default" <?php if ( $display_mode === 'default' ) { echo 'checked="checked"'; } ?> /><?php _e( 'Selected Image', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Display the Replacement Image selected above in place of removed images.', 'cks_rui' ) ; ?></span>
        </p>
        <p class="admin-radio">
            <input id="cks_plugins_use_daa" type="radio" name="cks_rui_options[display_mode]" title="<?php _e( 'Digital Arts Alliance SVG Image', 'cks_rui' ) ; ?>" value="daa" <?php if ( $display_mode === 'daa' ) { echo 'checked="checked"'; } ?>/><?php _e( 'Digital Arts Alliance SVG Image', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Replace with supplied Digital Arts Alliance Image.', 'cks_rui' ) ; ?></span>
        </p>
        <p class="admin-radio">
            <input id="cks_plugins_use_png" type="radio" name="cks_rui_options[display_mode]" title="<?php _e( 'Default PNG Image', 'cks_rui' ) ; ?>" value="png" <?php if ( $display_mode === 'png' ) { echo 'checked="checked"'; } ?>/><?php _e( 'Default PNG Image', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Replace with supplied PNG Image (if SVG Images like the plug-in default are not permitted).', 'cks_rui' ) ; ?></span>
        </p>
        <p class="admin-radio">
            <input id="cks_plugins_use_empty" type="radio" name="cks_rui_options[display_mode]" title="<?php _e( 'Empty Image', 'cks_rui' ) ; ?>" value="empty" <?php if ( $display_mode === 'empty' ) { echo 'checked="checked"'; } ?>/><?php _e( 'Empty Image', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Replace unwanted images with a blank 1-pixel image, typically assuming dimensions of replaced image.', 'cks_rui' ) ; ?></span>
        </p>
        <p class="admin-radio">
            <input id="cks_plugins_display_none" type="radio" name="cks_rui_options[display_mode]" title="<?php _e( 'Non-Display', 'cks_rui' ) ; ?>" value="none" <?php if ( $display_mode === 'none' ) { echo 'checked="checked"'; } ?>/><?php _e( 'Non-Display', 'cks_rui' ) ; ?><span class="description"><?php printf( __('Erase unwanted images  - replaces matched images and links with "selected image" (as above) set to <code>"display: none"</code>. (Use an actually existing image, like the default, to avoid <a href="%s">load errors</a>.)', 'cks_rui' ), 'http://ckmacleod.com/2016/07/14/comparative-page-loads-without-image-errors/' ) ; ?></span>
        </p>
        
        <?php  
    
}

/*
 * SANITIZE USER-SUPPLIED OPTIONS BEFORE ADDING TO DATABASE
 */
function cks_rui_sanitize_options( $input ) {

    $input['prior_to_date']     = sanitize_text_field($input['prior_to_date']) ;
    $input['after_date']        = sanitize_text_field($input['after_date']) ;
    $input['exclude_cats']      = sanitize_text_field($input['exclude_cats']) ;
    $input['include_cats']      = sanitize_text_field($input['include_cats']) ;
    $input['exclude_authors']   = sanitize_text_field($input['exclude_authors']) ;
    $input['include_authors']   = sanitize_text_field($input['include_authors']) ;
    $input['editor_capability'] = sanitize_text_field($input['editor_capability']) ;
    $input['image_url']         = esc_url($input['image_url']) ;
    $input['match_1']           = sanitize_text_field($input['match_1']) ;
    $input['match_2']           = sanitize_text_field($input['match_2']) ;
    $input['image_matches']     = sanitize_text_field($input['image_matches']) ;
    
    return $input;

}

/**
 * RESET FORM
 */
function cks_rui_reset_form() {
    
    ?>
    
    <form action="" method="post" name="reset_button">

        <h3 id="reset-choices"><?php _e( 'Reset', 'cks_rui' ) ; ?></h3>  

        <p><?php _e( 'Start over again? Be careful: Changes made here are irreversible.', 'cks_rui' ) ; ?></p>
        <p>
            <input id="cks_plugins_reset_options" type="radio" name="_reset" title="<?php _e( 'Reset Options', 'cks_rui' ) ; ?>" value="reset_options" checked /><?php _e( 'Reset Options to Default Settings', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Affects settings that appear on this page.', 'cks_rui' ) ; ?></span>
        </p>
        <p>
            <input id="cks_plugins_reset_post_meta" type="radio" name="_reset" title="<?php _e( 'Reset Posts', 'cks_rui' ) ; ?>" value="reset_posts" /><?php _e( 'Remove Individual Post Settings', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Affects individual Post options set via Edit (or Quick or Bulk Edit).', 'cks_rui' ) ; ?></span>
        </p>
        <p>
            <input id="cks_plugins_reset_all" type="radio" name="_reset" title="<?php _e( 'Reset All', 'cks_rui' ) ; ?>" value="reset_all" /><?php _e( 'Reset ALL - Options and Posts', 'cks_rui' ) ; ?><span class="description"><?php _e( 'Like a fresh installation - only more so.', 'cks_rui' ) ; ?></span>
        </p>

        <input id="cks_plugins-reset-to-defaults" type="submit" onclick="return confirm( '<?php _e( 'Are you sure you want to reset the chosen settings? (This step is irreversible.)', 'cks_rui' ) ?>' )" value="<?php _e( ' - Reset - ', 'cks_rui' ) ; ?> " title="<?php _e( 'Reset to Default Settings', 'cks_rui' )?>" />

        <?php wp_nonce_field( 'cks_rui_reset_options', '_rui_reset_nonce' ) ; ?>

    </form>
    
    <?php
    
}

/**
 * RUBI Sidebar
 * @param string $version
 */
function cks_rui_sidebar( $version ) {
    
    $options = get_option( 'cks_rui_options' ) ;
    
    ?>
    
    <div id="cks_plugins-sidebar">
            
        <?php cks_rui_usage_notes() ; ?>

        <?php cks_rui_illustrations( $options ) ; ?>
        
        <?php cks_rui_disclaimer() ; ?>
        
        <?php daa_list() ; ?>
        
        <div id="cks_plugins-version" class="sidebar-version">

            <p>-<br>WP Replace Unlicensed and Broken Images<br>Version <?php echo $version ; ?><br><i>by CK MacLeod</i><br>-</p>

        </div>
        
        <?php cks_tip_jar() ; ?>

    </div>

    <?php  
    
}

/**
 * USAGE NOTES
 */
function cks_rui_usage_notes() {
    
    ?>
        
    <div id="cks_rui-usage-notes" class="ck-sidebar-notes">

        <h3><?php _e( 'Getting Started', 'cks_rui' ) ; ?></h3>
        
        <p><b><?php _e( 'This plugin will not affect image display at your site until you adjust the default settings. At some sites, changing a single simple setting, like specifying a "prior to date," will be enough. At others, initial settings will just be a starting point.', 'cks_rui' ) ; ?></b></p>
        
        <p><?php printf( __( 'WP-RUBI is a powerful plug-in, potentially allowing for far-reaching alterations in your site\'s appearance, but it makes no permanent changes to your posts database or image library or any other files: Changes can be immediately rolled back by resetting to defaults via Main Settings, or in extreme situations by de-activating (or uninstalling). Still, as generally with WordPress Plug-Ins, %sthe further your installation diverges from a "basic" WordPress site - by employing unique frameworks, complex themes or plug-ins, or specialized customizations - and the higher your traffic, the more care and caution you should employ when installing, activating, and configuring WP-RUBI%s.', 'cks_rui' ), '<b>','</b>' ) ; ?></p>
        
        <p><?php _e( 'Though the plug-in adds a "cache-busting" query to image replacements, it may not work in all systems: Be sure to check results, and, if matched images are still showing, try clearing (or "deleting," "flushing," "purging") site, browser, and Content Delivery Network caches. If your cache or CDN does not allow you to clear it, you may need to find a different cache or CDN, or to disable the one you\'re using until your site is fully cleared of problematic images.', 'cks_rui' ) ; ?></p>
        
        <h3><?php _e( 'General Usage Notes', 'cks_rui' ) ; ?></h3>
        
        <p><?php _e( 'Settings can be set very broadly or tuned very specifically.', 'cks_rui' ) ; ?></p>

        <p><?php _e( 'One typical way to use this plug-in, for a site that has undergone a policy change regarding use of "non-rights-cleared" images, would be first to identify a publication date before which, at least provisionally, all images should be removed-and-replaced (for visitors viewing the "Front End"). If other default settings (which match file locations and image types in a typical WordPress installation) capture problematic images, some site operators might stop there. Others might proceed to identify particular Posts, Pages, Authors, Categories, or image types that can be cleared for display either immediately or after editing.', 'cks_rui' ) ; ?></p>

        <p><?php _e( 'If only a narrow set of Posts, Pages, Authors, Categories, or image types are considered problematic, an Admin can work in the opposite direction instead. Alternatively, an Admin can set all images on a site not to display until and unless cleared individually.', 'cks_rui' ) ;?> </p>
        
        <p><?php _e( '<p>Discrepancies between images found (from any source) and images matched (according to your settings, so subject to removal and replacement) will be highlighted in yellow in <a href="edit.php">All Posts</a> or <a href="edit.php?post_type=page">Pages</a> display. In general these indications should be taken as clues, not as definitive: Non-matching images will sometimes be minor tokens that turn up in post content - like avatars, video-player controls, credit-links, invisible tracking icons, and so on, as well as images from ad-servers or 3rd-party applications.</p>', 'cks_rui' ) ; ?></p>

        <h3 id="cks_rui-recommended-head" class="recommended-head"><?php _e( 'Getting Rights-Cleared Images', 'cks_rui' ) ;?></h3>

        <p><?php printf( __( 'The developer recommends <a href="%s">ImageInject</a>, a plugin by Thomas Hoefter and WPscoop. There are many other sources for free images - like <a href="%s">Wikimedia Commons</a>.', 'cks_rui' ), 'https://wordpress.org/plugins/wp-inject/', 'https://commons.wikimedia.org/wiki/Main_Page' ) ; ?></p>
        
        <p style="text-align:center;font-style:italic;font-weight:800;"> - <?php printf( __( 'Find more suggestions at <a target="_blank" id="ck-home" href="%s">WP-RUBI FAQ</a> and <a href="%s">documentation</a>.', 'cks_rui' ), 'http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/faq/', 'http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/usage-notes/#case-study') ; ?> - </p>
        

    </div>
        
    <?php
        
}

/**
 * Learn about or join the Digital Artists Alliance
 */
function daa_list() {
    
    $plugin_home_page = 'http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/';
    
    ?>
        
        <div id="daa-list">
            
            
            <a href="<?php echo $plugin_home_page ; ?>digital-artists-alliance/"><img src="<?php echo plugins_url( 'images/digital_arts_alliance.png', __FILE__ ) ; ?>" alt="Digital Artists Alliance"></a>
            
            <p id="daa-list-offer">
                
                <a href="<?php echo $plugin_home_page ; ?>digital-artists-alliance/"><?php _e( 'Learn about or join the Digital Artists Alliance.', 'cks_rui' ) ; ?></a>
                
            </p>
            
            
        </div>
        
    <?php
    
}

/**
 * CK'S DONATION FORM
 * Outputs Paypal "Tip Jar"
 */
function cks_tip_jar() {
    
    ?> 
    
    <div class="ck-donation">
                
        <p><?php _e( 'If you think this plug-in saved you time, or work, or anxiety,<br>or money, or anyway<br>you\'d like to see more work like this...', 'cks_rui' ) ; ?></p>

        <div id="sos-button">

            <form id="sos-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                
                <input name="cmd" type="hidden" value="_xclick" />
                <input name="business" type="hidden" value="ckm@ckmacleod.com" />
                <input name="lc" type="hidden" value="US" /><input name="item_name" type="hidden" value="Tip CK!" />
                <input name="item_number" type="hidden" value="WP Replace Images" />
                <input name="button_subtype" type="hidden" value="services" />
                <input name="no_note" type="hidden" value="0" />
                <input name="cn" type="hidden" value="Add special instructions or message:" />
                <input name="no_shipping" type="hidden" value="1" />
                <input name="currency_code" type="hidden" value="USD" />
                <input name="weight_unit" type="hidden" value="lbs" />

                <div id="ck-donate-submit-line">
                    
                    <input id="sos-amount" title="Confirm or not when you get there..." name="amount" type="text" value="" placeholder="$xx.xx" />
                    <input id="sos-submit" title="Any amount is very cool..." alt="Go to Paypal to complete" name="submit" type="submit" value="<?php _e( '...tip him!', 'cks_rui' ) ?>" />
                </div>

            </form>

        </div>

    </div>
    
    <?php
}

/**
 * SIDEBAR ILLUSTRATIONS
 * captions and images change 
 * depending on display mode of image replacements
 * @param array $options
 */
function cks_rui_illustrations( $options ) {
    
    $display_mode = $options['display_mode'] ;
    
    //text and image variables
    $subtitle = '';
    
    $img_add = '';    
    
    $display_mode = $options['display_mode'] ;
    
    switch ( $display_mode ) {
        
        case 'default' :
            
            $subtitle = __( ' - "Default SVG" ', 'cks_rui' ) ;
        
            $img_add = '' ;
            
            break ;
        
        case 'daa' :
            
            $subtitle = __( ' - "Digital Artists Alliance" ', 'cks_rui' ) ;
        
            $img_add = '-daa' ;
            
            break ;
        
        case 'png ' :
            
            $subtitle = __( ' - "Default PNG" ', 'cks_rui' ) ;
        
            $img_add = '-png' ;
            
            break ;
        
        case 'empty' :
            
            $subtitle = __( ' - "Empty Image" ', 'cks_rui' ) ;
        
            $img_add = '-empty' ;
            
            break ;
        
        case 'none' :
            
            $subtitle = __( ' - "Non-Display" ', 'cks_rui' ) ;
        
            $img_add = '-none' ;
            
            break ;
            
    }
    
    ?>
    
    <div class="ck-illustrations">

            <img src="<?php echo plugin_dir_url( __FILE__ ) ; ?>images/screenshot-2.jpg" alt="<?php _e('Before Image', 'cks_rui') ; ?>" > 

            <p class="cks_plugins_admin-caption"><?php _e( 'Before', 'cks_rui' ) ; ?></p>  

            <img src="<?php echo plugin_dir_url( __FILE__ ) ; ?>images/screenshot-3<?php echo $img_add ; ?>.jpg" alt="<?php _e('After Image 1', 'cks_rui') ; ?>" > 

            <p class="cks_plugins_admin-caption"><?php echo __( 'After', 'cks_rui' ) . $subtitle ; ?></p>

            <img src="<?php echo plugin_dir_url( __FILE__ ) ; ?>images/screenshot-4<?php echo $img_add ; ?>.jpg" alt="<?php _e('After Image 2', 'cks_rui') ; ?>" > 

            <p class="cks_plugins_admin-caption"><?php echo __( 'After - Selective', 'cks_rui' ) . $subtitle ; ?></p>

        </div>
    
    <?php
    
}

/**
 * DISCLAIMER
 */
function cks_rui_disclaimer() {
    
    ?>
    
    <div id="cks_rui-disclaimer" class="general-notes">
                
        <h3 id="cks_rui-disclaimer-head" class="disclaimer-head">Disclaimer</h3>
        
            <p>
                
                <?php printf( __('Proper use of this plug-in will prevent display of identified unwanted images and broken image links, reducing or eliminating legal exposure and also reducing or eliminating <a href="%s">"load" and "crawl" errors</a> from time of implementation forward. The developer does not and cannot promise that this plug-in will secure a site from all legal risks or search ranking disadvantages, especially when they stem from past exposure.', 'cks_rui') , 'http://ckmacleod.com/2016/07/14/comparative-page-loads-without-image-errors/') ; ?>
                
            </p>
                
    </div>
    
    <?php
    
}

/**
 * CK'S PLUGINS FOOTER
 * @param string $version
 */
function cks_plugins_footer( $version ) {
    
    $plugin_home_page = 'http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/'; 
    
    ?>
    
    <div id="cks_plugins_admin-footer">

        <a target="_blank" id="link-to-cks-plugins" href="http://ckmacleod.com/wordpress-plugins/">
            <img src="<?php echo plugin_dir_url( __FILE__ ) ; ?>images/cks_wp_plugins_200x40.jpg">
        </a>
        <a target="_blank" id="link-to-cks-plugins-text" href="http://ckmacleod.com/wordpress-plugins/">
            All CK's Plug-Ins
        </a>
        <a target="_blank" id="ck-home" href="<?php echo $plugin_home_page ; ?>">
            Plug-In Home Page
        </a>
         <a target="_blank" id="ck-faq" href="<?php echo $plugin_home_page ; ?>faq/">
            FAQ
        </a>
        <a target="_blank" id="ck-style" href="<?php echo $plugin_home_page ; ?>download-with-changelog">
            Changelog
        </a>
        <a target="_blank" id="ck-help" href="<?php echo $plugin_home_page ; ?>get-replace-images-support/">
            Feedback and Requests:<br>Contact CK
        </a>
        <a id="ck-support" class="<?php echo ($version < 1 ) ? 'pre-wp-beta' : 'wordpress-link' ; ?>" href="<?php echo ($version < 1) ? '#" title="Beta: Not Yet at Wordpress.org"' : 'http://wordpress.org/support/plugin/wp-replace-unlicensed-and-broken-images/" target="_blank"' ?>">
            Support at Wordpress
        </a>
        <a id="ck-rate" class="last-link<?php echo ($version < 1 ) ? ' pre-wp-beta' : ' wordpress-link' ; ?>" href="<?php echo ($version < 1) ? '#" title="Beta: Not Yet at Wordpress.org"' : 'http://wordpress.org/support/view/plugin-reviews/wp-replace-unlicensed-and-broken-images/" target="_blank"' ; ?>" >&#9733; &#9733; &#9733; &#9733; &#9733;<br>Rate This Plugin!
        </a> 

    </div>
    
    <?php
    
}

