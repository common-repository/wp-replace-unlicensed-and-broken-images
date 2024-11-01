<?php

/**
 * @package: WordPress Replace Unlicensed and Broken Images
 * @Since: 1.0.5
 * @Date: October 2016
 * @Author: CK MacLeod
 * @Author: URI: http://ckmacleod.com
 * @License: GPL3
 */

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' ) ;

/**
 * MIGRATE OLD IMAGE URLS
 * @param string $old_image_url
 */
function cks_migrate_roi_image_url( $old_image_url ) {
    
    $img_substr = 'old-images/image_removed' ;  
    $old_defunct = strpos( $old_image_url, $img_substr ) ;
    
    if ( $old_defunct ) {
        
        $old_defunct_png = strpos( $old_image_url , '.png' ) ;
        
        if ( $old_defunct_png ) { 
            
            $old_image_url = plugins_url( 'image_removed.png', __FILE__ ) ; 

        } else { 
            
            $old_image_url = plugins_url( 'image_removed.svg', __FILE__ ) ;   
        
        }
        
    }
    
    return $old_image_url;
    
}

/**
 * MIGRATE 0.9 and .91 options
 */
function migrate_09and91() {
    
    $old_options = get_option( 'cks_rui_options' ) ;
    
    if ( isset( $old_options['exclude_cats'] ) ) {

        $old_cat_ids = $old_options['exclude_cats'] ;

        $new_cats = cks_rui_convert_categories( $old_cat_ids ) ;

        $old_options['exclude_cats'] = $new_cats ;

    } 

    if ( isset( $old_options['include_cats'] ) ) {

        $old_cat_ids = $old_options['include_cats'] ;

        $new_cats = cks_rui_convert_categories( $old_cat_ids ) ;

        $old_options['include_cats'] = $new_cats ;

    }     

    if ( isset( $old_options['exclude_posts'] ) ) {

        exclude_posts_by_id_to_meta( $old_options ) ;

        $old_options['exclude_posts'] = '' ;

    }

    if ( isset( $old_options['include_posts'] ) ) {

        include_posts_by_id_to_meta( $old_options ) ;

        $old_options['include_posts'] = '' ;

    }
            
    update_option( 'cks_rui_options', $old_options ) ;
    
}

function cks_rui_migrate_old_image_url( $old_options ) {
    
        $new_image_url = '' ;
    
        $legacy_image_url =  $old_options['image_url'] ?  $old_options['image_url'] : '' ;   
        
        if ( $legacy_image_url == plugins_url( 'image_removed.svg', __FILE__ ) ) {
        
            $new_image_url = plugins_url( 'images/image_removed.svg', __FILE__ ) ;
        
        }
        
        if ( $legacy_image_url == plugins_url( 'image_removed.png', __FILE__ ) ) {
        
            $new_image_url = plugins_url( 'images/image_removed.png', __FILE__ ) ;
        
        }
        
        if ( $legacy_image_url == plugins_url( 'empty_image.png', __FILE__ ) ) {
        
            $new_image_url = plugins_url( 'images/empty_image.png', __FILE__ ) ;
        
        }
    
    return $new_image_url ;
    
}

/**
 * CONVERT EXCLUDE POST STRING 
 * into post-meta
 * @param array $options
 */
function exclude_posts_by_id_to_meta( $options ) {

    $excl_post_arr = explode( ',', $options['exclude_posts']) ;
    
    foreach( $excl_post_arr as $excl_post ) {
        
        update_post_meta( $excl_post, '_is_image_safe', 'safe' ) ;
        
    }
        
}

/**
 * CONVERT INCLUDE POST STRING 
 * into post-meta
 * @param array $options
 */
function include_posts_by_id_to_meta( $options ) {

    $incl_post_arr = explode( ',', $options['include_posts']) ;
    
    foreach( $incl_post_arr as $incl_post ) {
        
        update_post_meta( $incl_post, '_is_image_safe', 'unsafe' ) ;
        
    }
    
}

/**
 * CONVERT CATEGORY IDS INTO CATEGORY NAMES
 * @param string $cat_ids
 * @return string $cat_names
 */
function cks_rui_convert_categories( $cat_ids ) {
    
    $cat_id_arr = explode( ',', $cat_ids ) ;
    
    foreach( $cat_id_arr as $cat_id ) {
        
        $cat_names_arr[] = get_cat_name( $cat_id ) ;
        
    }
    
    $cat_names = implode( ',', $cat_names_arr ) ;
    
    return $cat_names ;
    
}

