<?php
/*
 * Plugin Name: WP Replace Unlicensed and Broken Images
 * Description: Replace broken, copyrighted, and other problematic images on a site's public-facing "Front End" - highly customizable, leaves data intact.
 * Version: 1.0.5
 * Author: CK MacLeod
 * Date: March 2017
 * Author URI: http://ckmacleod.com/
 * Plugin URI: https://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/
 * License: GPLv2
 * Text Domain: cks_rui
 * Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' ) ;

if ( ! defined( 'WPRUBI_VERSION' ) ) {
    
    define( 'WPRUBI_VERSION', '1.0.5') ;
    
}

register_activation_hook( __FILE__, 'cks_rui_set_default_options' ) ;

/* VERSION CHECK AND UPGRADE ROUTINE */

add_action( 'plugins_loaded', 'wp_rubi_check_version' ) ;

function wp_rubi_check_version() {
    
    if ( WPRUBI_VERSION !== get_option( 'cks_rui_version') ) {
        
        cks_rui_set_default_options() ;
        
    }
    
}

/**
 * SET DEFAULT OPTIONS
 * 
 */
function cks_rui_set_default_options() {
    
    if ( get_option( 'cks_rui_version' ) ) {
        
        $installed_version =  get_option( 'cks_rui_version' ) ;
        
    }
    
    //set basic defaults
    $default_prior_to       =   date_i18n( get_option( 'date_format' ), strtotime( 'January 1, 1970' ) ) ;
    $default_after_date     =   date_i18n( get_option( 'date_format' ), strtotime( '18 January 2038' ) ) ;
    $default_match_1        =   filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING) ;
    $default_match_2        =   'uploads' ;
    $default_image_url      =   plugins_url( 'images/image_removed.svg', __FILE__ ) ;

    //migrate ROI Options if exist and preserve "blank" choices 
    
    if ( get_option( 'roi_options') ) {
    
        $roi_options = get_option( 'roi_options' ) ;   

        $default_prior_to   =   $roi_options['roi_cut_off_date']        ?   $roi_options['roi_cut_off_date']                            :   $default_prior_to ;     
        $default_image_url  =   $roi_options['roi_image_url']           ?   cks_migrate_roi_image_url( $roi_options['roi_image_url'] )  :   $default_image_url ;
        $default_match_1    =   $roi_options['roi_match_1']             ?   $roi_options['roi_match_1']                                 :   '' ;
        $default_match_2    =   $roi_options['roi_match_2']             ?   $roi_options['roi_match_2']                                 :   '' ;
    
    }
    
    //preserve certain CKS RUI OPTIONS even if blank
    //get old image location if necessary
    if (get_option( 'cks_rui_options' ) ) {
        
        $cks_rui_legacy_options = get_option( 'cks_rui_options' ) ;
        
        $default_prior_to       =   $cks_rui_legacy_options['prior_to_date']    ?   $cks_rui_legacy_options['prior_to_date']    : '' ;
        $default_after_date     =   $cks_rui_legacy_options['after_date']       ?   $cks_rui_legacy_options['after_date']       : '' ;
        $default_match_1        =   $cks_rui_legacy_options['match_1']          ?   $cks_rui_legacy_options['match_1']          : '' ;
        $default_match_2        =   $cks_rui_legacy_options['match_2']          ?   $cks_rui_legacy_options['match_2']          : '' ;
        
    }
    
    //default options    
    $default_options = array(
        
        'prior_to_date'     => $default_prior_to,   //publication date before which a post will become subject to image removal/replacement
        'after_date'        => $default_after_date, //publication date after which a post will become subject to image r/r
        'timespan'          => 0,                   //treat before and after dates as timespan; checkbox: 0 = do not treat as timespan
        'image_url'         => $default_image_url,  //link to replacement image
        'exclude_cats'      => '',                  //category(ies) by category name excluding posts from image r/r, comma-separated list
        'include_cats'      => '',                  //category(ies) by category name including posts in image r/r, comma-separated list
        'exclude_authors'   => '',                  //author(s) by author display name whose posts will be excluded from image r/r, comma-separated list
        'include_authors'   => '',                  //author(s) by author display name whose posts will be included in image r/r, comma-separated list
        'exclude_featureds' => 0,                   //whether or not to excluded "Featured/Thumbnail" images in r/r; checkbox: 0 = exclude
        'match_1'           => $default_match_1,    //first part of pattern to restrict image r/r, optional - default is site URL
        'match_2'           => $default_match_2,    //second part of pattern to restrict image r/r, optional - default is "uploads"
        'image_matches'     => 'jpg jpeg png gif JPG JPEG',  //file extensions matched, case-sensitive
        'global_img_del'    => 0,                   //match all images display via html "src" arguments, with or without standard file extensions
        'display_mode'      => 'default',           //options: "default," "png", "empty," "none" - set by radio button
        'editor_capability' => 'edit_others_posts', //capability required to see and use metabox and edit.php columns and menus
        'add_footer_badge'  => 0,                   //whether to add a DAA/This site respects... badge-link at far right/bottom of site pages
            
    ) ;  
    
    //if cks_rui_already installed and we're just upgrading	
    if ( get_option( 'cks_rui_options' ) ) {
        
        //migrate to new formats if pre-0.92 Betas
        if ( $installed_version === '0.91 Beta' || $installed_version === '0.9 Beta' ) { 
            
            migrate_09and91() ; 
            
        }
        
        $old_options = get_option( 'cks_rui_options' ) ;
        
        if ( $old_options ) {
            
            $new_image_url = cks_rui_migrate_old_image_url( $old_options ) ;
            
            if ( $new_image_url ) {
                
                $old_options['image_url'] = $new_image_url ;
                
            }
            
        }

        $default_options = array_merge( $default_options, $old_options ) ;
        
    }
       
    update_option( 'cks_rui_options', $default_options ) ;
    
    update_option( 'cks_rui_version', WPRUBI_VERSION ) ;
     
}


/**
 * RESET FUNCTION
 * Check for reset - if 'Reset' reset to default settings
 */
function cks_rui_check_reset() {
    
    $reset = filter_input( INPUT_POST, '_reset', FILTER_SANITIZE_STRING ) ;

    if ( $reset ) {
        
        $nonce = filter_input( INPUT_POST, '_rui_reset_nonce', FILTER_SANITIZE_STRING ) ;
    
        if ( wp_verify_nonce( $nonce, 'cks_rui_reset_options' ) ) {

            if ( 'reset_options' === $reset || 'reset_all' === $reset ) {

                delete_option( 'cks_rui_options' ) ;

                delete_option( 'cks_rui_version' ) ;

                delete_option( 'roi_options' ) ;

                cks_rui_set_default_options() ;

            }

            if ( 'reset_posts' === $reset || 'reset_all' === $reset ) {

                $allposts = get_posts( 'numberposts=-1&post_status=any' ) ;

                foreach( $allposts as $postinfo ) {

                    delete_post_meta( $postinfo->ID, '_is_image_safe' ) ;

                }
                
                $allpages = get_pages() ;
                
                foreach( $allpages as $pageinfo ) {
                   
                    delete_post_meta( $pageinfo->ID, '_is_image_safe' ) ;
                    
                }
            }
           
        } else {

        print 'Sorry: No nonce, no access.' ;
        exit;

        }
        
    }
            
}

/**
 * Admin Notice for Reset Options
 */
add_action( 'admin_notices', 'cks_reset_notice__success' );

function cks_reset_notice__success() {
    
    $reset = filter_input( INPUT_POST, '_reset', FILTER_SANITIZE_STRING ) ;
    $nonce = filter_input( INPUT_POST, '_rui_reset_nonce', FILTER_SANITIZE_STRING ) ;
    
    if ( $reset && wp_verify_nonce( $nonce, 'cks_rui_reset_options' ) ) {
        
        $message_text = '' ;
        
        if ( 'reset_all' === $reset )     { $message_text = __( 'All Options and Post Data Reset', 'cks_rui' ) ; }
        if ( 'reset_options' === $reset ) { $message_text = __( 'Options Reset to Defaults', 'cks_rui' ) ; }
        if ( 'reset_posts' === $reset )   { $message_text = __( 'Post Data Reset', 'cks_rui' ) ; }
        
        $class = 'notice notice-success is-dismissible';

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message_text );
        
    }
    
}

/**
 * migrate from previous versions
 */
include_once(dirname(__FILE__) . '/migrate.php' ) ;

/**
 * include Admin settings
 */
include_once(dirname(__FILE__) . '/admin.php' ) ;

/**
 * add Metabox and Columns/Forms to Edit Post and Posts/Pages
 */
include_once(dirname(__FILE__) . '/admin_extra.php' ) ;

/**
 * PRIOR TO DATE IMAGE REMOVAL REPLACEMENT FUNCTION
 * Remove images from post if before date in natural language
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_remove_prior_to_date( $post, $options ) {
    
    $prior_to_date = $options['prior_to_date'] ? $options['prior_to_date'] : '' ;
    
    if ( $prior_to_date ) {
    
        $prior_to_date_secs = strtotime($options['prior_to_date']) ;

        $post_date = strtotime( $post->post_date ) ;

        if ( $post_date < $prior_to_date_secs ) {

             return true ;

        }
         
    }
     
}

/**
 * AFTER DATE IMAGE REMOVAL REPLACEMENT FUNCTION
 * Remove images from posts dated after indicated date in natural language
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_remove_after_date( $post, $options ) {
    
    /*date is set by day, not time of day - therefore comparison of datetimes means
    posts published on day are "greater than" date converted into datetime in seconds */
    
    $after_date = $options['after_date'] ? $options['after_date'] : '' ;
    
    if ( $after_date ) {
    
        $after_date_secs = strtotime( $after_date ) + (24*60*60)  ;
            
        $post_date = strtotime( $post->post_date ) ;

        if ( $post_date > $after_date_secs ) {

             return true ;
     
        }
        
    }
    
}

/**
 * TREAT DATES AS TIMESPAN FUNCTION
 * @param object $post
 * @param array $options
 * @return boolean
 */
 function cks_rui_timespan( $post, $options ) {
    
    if (
            
        isset( $options['timespan'] ) &&

        isset( $options['after_date'] ) &&

        isset( $options['prior_to_date'] )
            
        ) { 
        
        $timespan = $options['timespan'] ; } 

    else {

        $timespan = '' ;
    }
    
    if ( $timespan ) {
        
        $post_date =    strtotime( $post->post_date ) ;
        
        $after_date =   strtotime( $options['after_date'] ) + (24*60*60)  ;
        
        $before_date =  strtotime( $options['prior_to_date'] ) - (24*60*60)  ;
        
        if ( $post_date > $after_date && $post_date < $before_date ) {
            
            return true ;
        }
        
    }
    
}

 /**
 * CATEGORIES INCLUDED IN IMAGE REMOVAL AND REPLACEMENT
 * categories to include "child" categories
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_include_cats( $post, $options) {
    
    $incl_cat_str = $options['include_cats'] ;
    
    $incl_cat_arr = explode( ',', $incl_cat_str ) ;
    
    $litters = array() ;
    
    foreach ( $incl_cat_arr as $incl_cat_name ) {
        
        $incl_cat = get_cat_ID( $incl_cat_name ) ;
        
        $kittens =  get_term_children( $incl_cat, 'category' ) ;
            
        $litters =  array_merge( $litters, $kittens ) ;
        
    }
    
    $whole_ward = array_merge( $litters, $incl_cat_arr ) ;
    
    $postid = $post->ID;
    
    if ( in_category( $whole_ward, $postid ) ) {
        
        return true ;
        
    } 
    
}
 
/**
 * CATEGORIES EXCLUDED FROM IMAGE REMOVAL AND REPLACEMENT
 * categories to include "child" categories
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_exclude_cats( $post, $options) {
    
    $excl_cat_str = $options['exclude_cats'] ;
    
    $excl_cat_arr = explode( ',', $excl_cat_str ) ;
    
    $litters = array() ;
    
    foreach ( $excl_cat_arr as $excl_cat_name ) {
        
        $excl_cat = get_cat_ID( $excl_cat_name ) ;
        
        $kittens =  get_term_children( $excl_cat, 'category' ) ;
            
        $litters =  array_merge( $litters, $kittens ) ;
        
    }
    
    $whole_ward = array_merge( $litters, $excl_cat_arr ) ;
    
    $postid = $post->ID;
    
    if ( in_category( $whole_ward, $postid ) ) {
        
        return true ;
        
    } 
    
}

/**
 * AUTHORS BY email address, URL, ID, username or display_name 
 * excluded from image removal and replacement
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_exclude_authors( $post, $options ) {
    
    $excl_post_str = $options['exclude_authors'] ;
    
    if ( $excl_post_str ) {
        
        $excl_arr = explode( ',', $excl_post_str ) ;
    
        foreach ($excl_arr as $excl_author_name) {

            $users = get_users( array(
                //allow rough matches
                'search' => $excl_author_name
            ) ) ;

            $user = ( isset( $users[0] ) ? $users[0] : false ) ;

            $user_id = ( $user ? $user->ID : false ) ;

            $user_ids[] = $user_id;

        }

        if ( in_array( $post->post_author, $user_ids ) ) {

            return true ;

        }
        
    }
    
}

/**
 * AUTHORS BY email address, URL, ID, username or display_name 
 * included in image removal and replacement
 * @param object $post
 * @param array $options
 * @return boolean 
 */
function cks_rui_include_authors( $post, $options ) {
    
    $incl_post_str = $options['include_authors'] ;
    
    if ( $incl_post_str ) {

        $incl_arr = explode( ',', $incl_post_str ) ;

        foreach ($incl_arr as $incl_author_name) {

            $users = get_users( array(
                'search' => $incl_author_name,
            ) ) ;

            $user = ( isset( $users[0] ) ? $users[0] : false ) ;

            $user_id = ( $user ? $user->ID : false ) ;

            $user_ids[] = $user_id;

        }

        if ( in_array( $post->post_author, $user_ids ) ) {

            return true ;
        }
    
    }
    
}

/**
 * POST SET INDIVIDUALLY TO HAVE IMAGES REMOVED AND REPLACED
 * @param object $post
 * @return string
 */
function cks_rui_remove_by_post_meta( $post ) {
    
    $image_safe = get_post_meta($post->ID, '_is_image_safe', true ) ? get_post_meta($post->ID, '_is_image_safe', true ) : '' ;

    return $image_safe;  
    
}

/**
 * REMOVE AND REPLACE IMAGES BY GLOBAL SETTINGS
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_remove_global( $post, $options ) {
    
    $timespan = isset( $options['timespan'] ) ? $options['timespan'] : '' ;

    if ( 

            //exclusions
        (   ! cks_rui_exclude_cats( $post, $options) && 
            
            ! cks_rui_exclude_authors( $post, $options ) 
            
        ) 
            
            && 

        (   
                    
            //inclusions
            cks_rui_include_cats( $post, $options) || 
                    
            cks_rui_include_authors( $post, $options) ||        

            //date inclusion/exclusion
            ( cks_rui_remove_prior_to_date( $post, $options ) && ! $timespan ) ||

            ( cks_rui_remove_after_date( $post, $options ) && ! $timespan ) ||

            cks_rui_timespan( $post, $options ) 

        ) 
            
        ) {

        return true ; 

    } 
        
}

/**
 * MAIN REMOVAL HELPER FUNCTION
 * returns true if image removal to occur
 * @param object $post
 * @param array $options
 * @return boolean
 */
function cks_rui_remove_images_from_post( $post, $options ) {
    
    if ( cks_rui_remove_by_post_meta( $post ) === 'safe' ) {
        
        return false ;
        
    } else {
    
        if ( cks_rui_remove_global( $post, $options ) || cks_rui_remove_by_post_meta( $post ) === 'unsafe' ) {
        
            return true ;
        
        } 
        
    }
    
}

/**
 * MATCH AND REPLACE IN POST CONTENT
 * @param string $content
 * @param array $options
 * @return string
 */
function cks_rui_match_image_links( $content, $options ) {
    
    $static_image_url = cks_rui_get_static_image_url( $options ) ;

    $url_match_1_list = $options['match_1'] ? $options['match_1'] : '' ;

    $url_match_1_trimmed = str_replace( array(' ',','), '|', trim( $url_match_1_list ) ) ;

    $url_match_1_match = $url_match_1_trimmed ? '(?:' . str_replace( '/', '\/', $url_match_1_trimmed ) . ')([^"\']*)' : '';

    $url_match_2_list = $options['match_2'] ? $options['match_2'] : '' ;

    $url_match_2_trimmed = str_replace( array(' ',','), '|', trim( $url_match_2_list ) ) ;

    $url_match_2_match = $url_match_2_trimmed ? '(?:' . str_replace( '/', '\/', $url_match_2_trimmed ) . ')([^"\']*)' : '';

    $image_match_list = $options['image_matches'] ? $options['image_matches'] : '' ;

    $image_matches_trimmed = str_replace( array(' ',','), '|\.', trim( $image_match_list ) ) ;

    $image_matches = $image_matches_trimmed ? '\.' . $image_matches_trimmed : '' ;

    $image_matches_to_match = $image_matches ? '(?:' . $image_matches . ')([^"\']*)' : '';
    
    $new_content = preg_replace( '/https?:([^"\']*)' . $url_match_1_match . $url_match_2_match . $image_matches_to_match . '/', $static_image_url, $content )  ;

    return $new_content ;
    
}

 /**
  * IMAGE IN CONTENT REMOVAL FILTER FUNCTION
  * Replace All Unwanted Images according to default or user set options
  * at posting time
  * @ hooks 'the_content'
  * @ returns string $content
 */   

add_filter( 'the_content', 'cks_replace_content_images', 100 ) ;

function cks_replace_content_images( $content ) {

    $options = get_option( 'cks_rui_options' ) ;
   
    if  ( ! $options || is_admin() ) {
        
        return $content ;
        
    }
    
    global $post;

    //check for global image del in function
    if ( ! cks_rui_remove_images_from_post( $post, $options ) ) {
        
        return $content ;
        
    } else { 
        
        $global_img_del = isset( $options['global_image_del'] ) ? $options['global_image_del'] : '' ;
        
        $content = cks_rui_match_image_links( $content, $options ) ;
         
        if ( $global_img_del ) {
            
            $static_image_url = cks_rui_get_static_image_url( $options ) ;
        
            $content = str_replace( 'src=', 'src="' . $static_image_url . '"', $content ) ;

            $content = str_replace( 'srcset=', '', $content ) ;
            
        }
        
        return $content ; 
        
    }
    
}

/**
 * GET REPLACEMENT IMAGE URL
 * @param array $options
 * @return string - the url used for replacement
 */
function cks_rui_get_static_image_url( $options ) {
    
    //version query helps "cache-busting"
    $version = get_option( 'cks_rui_version' ) ;
    $display_mode = $options['display_mode'] ;
    $static_image_url = '' ;
    
    switch ( $display_mode ) {
        
        case 'default' :
            
            $static_image_url = $options['image_url'] ;
            
            break ;
        
        case 'daa' :
            
            $static_image_url = plugins_url( 'images/digital_arts_alliance.svg', __FILE__ ) ;
            
            break ;
        
        case 'png' :
            
            $static_image_url = plugins_url( 'images/image_removed.png', __FILE__ ) ;
            
            break ;
        
        case 'empty' :
            
            $static_image_url = plugins_url( 'images/empty_image.png', __FILE__ ) ;
            
            break ;
        
        case 'none' :
            
            #$static_image_url = 'http://' . $options['match_1'] . '/' . $options['match_2'] . '/none.svg' ;
            
            $static_image_url = $options['image_url'] ;
            
            break ;
            
    }
    
    $static_image_url = $static_image_url . '?v=' . $version;
    
    if ( 'none' === $display_mode ) {
        
        $static_image_url = str_replace($static_image_url, $static_image_url . '" style="display: none;' , $static_image_url )  ;
        
    }
    
    return $static_image_url ;
    
}

/**
 * REMOVE FEATURED IMAGES
 * filter function
 * uses filter available since WP 4.3
 * @returns array $image
 */
add_filter( 'wp_get_attachment_image_src' , 'cks_rui_remove_featured_image', 20, 1 ) ;

function cks_rui_remove_featured_image( $image ) {
    
    $options = get_option('cks_rui_options') ;
    
    #if ( ! is_admin() && 'none' !== $options['display_mode'] ) {
        
    if ( ! is_admin() ) {
        
        global $post;
        
        $orig_url = $image['0'] ;
        
        $exclude_featureds = isset( $options['exclude_featureds'] ) ? $options['exclude_featureds'] : '' ;
        
        if ( ! $exclude_featureds && cks_rui_remove_images_from_post( $post, $options ) &&  cks_rui_match_thumbnail( $orig_url, $options ) ) 
                
                 {
            
            $replacement_url = cks_rui_get_static_image_url( $options ) ;
            
            $replacement_img = array( $replacement_url, '' , '' ) ;
            
            $image = $replacement_img;
            
        }

    }

    return $image;

}

/**
 * SET FEATURED IMAGE NOT TO DISPLAY
 * @param array $attr
 * @param object $attachment
 */
add_filter('wp_get_attachment_image_attributes', 'cks_rui_set_thumbnail_display_none', 10, 2) ;
    
function cks_rui_set_thumbnail_display_none( $attr, $attachment ) {
    
    if ( ! is_admin() ) {
        
        $options = get_option( 'cks_rui_options' ) ;
        
        $display_mode = $options['display_mode'] ;
        
        $parent = get_post_ancestors( $attachment->ID ) ;
        
        $image = wp_get_attachment_image_src($attachment->ID);
        
        //just to avoid PHP Undefined Offset Notice
        $post_id = isset( $parent[0] ) ? $parent[0] : NULL;
        $exclude_featureds = isset( $options['exclude_featureds'] ) ? $options['exclude_featureds'] : '' ;
            
        $post = get_post( $post_id ) ;
        
        if ( 'none' === $display_mode 
                
                && ! $exclude_featureds 
                
                && cks_rui_remove_images_from_post( $post, $options )
                        
                && $image[0] === cks_rui_get_static_image_url( $options ) ) //works because if removal, image[0] will be the replacement image
                
            {

            $attr['style'] = 'display: none' ;   

        }

    }

    return $attr;

}

/***
 * CHECK IF THUMBNAIL SATISFIES LINK MATCHING
 * @param string $orig_url - will be in state prior to replacement
 * @param array $options
 * @return boolean true/false
 */
function cks_rui_match_thumbnail( $orig_url, $options ) {

    $match1 =           isset( $options['match_1'] )          ?   $options['match_1'] : '' ;
    $match2 =           isset( $options['match_2'] )          ?   $options['match_2'] : '' ;
    $global_image_del = isset( $options['global_image_del'] ) ?   $options['global_image_del'] : '' ;
    $image_match_list = isset( $options['image_matches'] )    ?   $options['image_matches'] : '' ;
    
    $matched1 = false ;
    $matched2 = false ;
    $matched_list1 = false;
    $matched_list2 = false;
    $extension_match = false;

    if ( $match1 ) {
    
        $match_1_list = str_replace( ',', ' ', $match1 ) ;

        $match_1_array = explode( ' ', $match_1_list ) ;

        foreach ( $match_1_array as $match_1 ) {

            if ( strpos( $orig_url, $match_1 ) ) {

                $matched_list1 = true ;

            }

        }
    
    } 
    
    if ( ! $match1 || $matched_list1 ) {

        $matched1 = true ;

    }

    if ( $match2 ) {
    
        $match_2_list = str_replace( ',', ' ', $match2 ) ;

        $match_2_array = explode( ' ', $match_2_list ) ;

        foreach ( $match_2_array as $match_2 ) {

            if ( strpos( $orig_url, $match_2 ) ) {

                $matched_list2 = true ;

            }

        }
    
    } 
    
    if ( ! $match2 || $matched_list2 ) {

        $matched2 = true ;

    }
    
    if ( $global_image_del ) {
        
        $extension_match = true; 
        
    } else {

        $image_match_list = str_replace( ',', ' ', $image_match_list ) ;

        $img_match_array = explode( ' ', $image_match_list ) ;

        foreach ( $img_match_array as $match ) {

            if ( strpos( $orig_url, '.' . $match ) ) {

                $extension_match = true ;

            }
        }
        
    }


    if (  $matched1  &&  $matched2  &&  $extension_match  ) {

        return true ;

    }
        
}

/***
 * CUSTOMIZE PLUGIN OVERVIEW PAGE DISPLAY
 * Add settings link on plugin page
 * @param array $links
 */
function add_cks_rui_settings_link( $links ) { 
    
  $settings_link = '<a href="options-general.php?page=cks_rui_main_menu">Settings</a>' ; 
  
  array_unshift( $links, $settings_link ) ; 
  
  return $links; 
  
}
 
/**
 * set variable for action links filter
 */
$plugin = plugin_basename( __FILE__ ) ; 

add_filter( "plugin_action_links_$plugin", 'add_cks_rui_settings_link' ) ;

/***
 * ADD DAA BADGE TO FOOTER
 * echoes html
 */

function cks_rui_add_after_footer_badge() {
    
    $options = get_option( 'cks_rui_options' ) ;
    
    $add_footer_badge =  isset( $options['add_footer_badge'] ) ? $options['add_footer_badge'] : '' ;
    
    if ( $add_footer_badge ) {
    
        $html = '<div id="cks_rui-footer-badge" style="float:right;height:56px; "><a href="http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/digital-artists-alliance/"><img style="height: 34px; width: 248px;" alt="' . __( 'Digital Artists Alliance Badge', 'cks_rui') . '" title="' . __( 'Digital Artists Alliance Badge', 'cks_rui') . '" src="' .  plugin_dir_url( __FILE__ ) . 'images/provisional_daa_badge.png'  . '"></a></div>' ;
        
        echo $html;
        
    }
    
}

add_action( 'wp_footer', 'cks_rui_add_after_footer_badge' ) ;

/**
 * ADD BADGE FOR SHORTCODE OR TEMPLATE FUNCTION
 * @return string
 */
function cks_rui_add_badge() {
    
    $html = '<div class="cks_daa-badge"><a href="http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/digital-artists-alliance/"><img style="height: 34px; width: 248px;" alt="' . __( 'Digital Artists Alliance Badge', 'cks_rui') . '" title="' . __( 'Digital Artists Alliance Badge', 'cks_rui') . '" src="' .  plugin_dir_url( __FILE__ ) . 'images/provisional_daa_badge.png'  . '"></a></div>' ;
        
    return $html;
    
}

add_shortcode('add_cks_rui_badge','cks_rui_add_badge', 20 ) ;

/* FOR POSSIBLE TRANSLATION */
add_action( 'init', 'cks_rui_load_translation_file' ) ;
 
function cks_rui_load_translation_file() {
    
    // relative path to WP_PLUGIN_DIR where the translation files will sit:
    $plugin_path = plugin_basename( dirname( __FILE__ ) . '/languages' ) ;
    load_plugin_textdomain( 'cks_rui', '', $plugin_path ) ;
    
}