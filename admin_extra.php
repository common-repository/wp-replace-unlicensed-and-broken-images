<?php

/**
 * @package: WordPress Replace Unlicensed and Broken Images
 * @Since: 1.0.5
 * @Date: October 2016
 * @Author: CK MacLeod
 * @Author: URI: http://ckmacleod.com
 * @License: GPL3
 */

/**
 * ADD METABOX, IMAGE STATUS COLUMNS, QUICK/BULK EDIT
 * only for "editor" level users and above 
 */
add_action( 'plugins_loaded', 'cks_rui_load_columns_and_metaboxes' ) ;

function cks_rui_load_columns_and_metaboxes() {
    
    $options = get_option( 'cks_rui_options' ) ;
    
    $cap = $options['editor_capability'];
    
    if ( current_user_can( $cap ) ) {  

        /* METABOX */
        add_action( 'add_meta_boxes', 'cks_rui_display_meta_box' ) ;

        /* POSTS */
        add_filter( 'manage_posts_columns', 'cks_rui_columns_head' ) ;
        add_action( 'manage_posts_custom_column', 'cks_rui_columns_content', 10, 2 ) ;

        /* PAGES */
        add_filter( 'manage_pages_columns', 'cks_rui_page_columns_head' ) ;
        add_action( 'manage_pages_custom_column', 'cks_rui_page_column_content', 10, 2 ) ;

        /* ADD QUICK AND BULK EDITING, WORKS FOR BOTH PAGES AND POSTS */

        add_action( 'quick_edit_custom_box', 'display_cks_rui_clear_images' ) ;
        add_action( 'bulk_edit_custom_box', 'display_cks_rui_clear_images_bulk' ) ; 

    }
    
}

/**
 * CREATE METABOX 
 * FOR SETTING INDIVIDUAL POST IMAGE REMOVAL/REPLACEMENT
 */
function cks_rui_display_meta_box() {

    add_meta_box(
       'cks_rui_display_meta_box', 
			'Image Removal/Replacement', 'cks_rui_meta_box_callback', array( 'post', 'page' ), 'side',
		 'low'
    ) ;
    
}

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function cks_rui_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cks_rui_meta_box_data', 'cks_rui_meta_box_nonce' ) ;
    
    $options = get_option( 'cks_rui_options' ) ;

    if ( ! get_post_meta( $post->ID, '_is_image_safe', true) ) { 
        
        $image_safe = 'unset' ; 
        
    } else {
        
        $image_safe = get_post_meta( $post->ID, '_is_image_safe', true ) ;
    
    }
    
    $removed_globally = cks_rui_remove_global( $post, $options ) ? cks_rui_remove_global( $post, $options ) : '' ;
    $images_being_removed = cks_rui_remove_images_from_post( $post, $options ) ? TRUE : FALSE ;
    
          
    if ( $image_safe == 'safe' && $removed_globally ) {     
            
        $notice = __( '<b>This post\'s images are set to be displayed</b>, with global settings having been overriden above.', 'cks_rui' ) ;

    }
        
    if ( $image_safe == 'safe' && ! $removed_globally ) {     

        $notice = __( '<b>This post\'s images are set to be displayed</b>, regardless of settings elsewhere now or in the future.', 'cks_rui' ) ;

    }    
    
    if ( $image_safe == 'unsafe' && ! $removed_globally ) {     

        $notice = __( '<b>This post\'s images are set to be removed and replaced</b>, regardless of settings elsewhere now or in the future.', 'cks_rui' ) ;

    }    
    
    if ( $image_safe == 'unsafe' && $removed_globally ) {
        
        $notice = __( '<b>This post\'s images are set to be removed and replaced.</b> To override global settings, choose "Clear Images to Display."', 'cks_rui' ) ;
        
    }
    
    if ( $image_safe == 'unset' && $removed_globally ) {
        
        $notice = __( '<b>This post\'s images are set to be removed and replaced.</b> To override global settings, choose "Clear Images to Display." You can also set it to be Removed and Replaced on its own, overriding global settings if they are changed.', 'cks_rui' ) ;
        
    }
    
    if ( $image_safe == 'unset' && ! $removed_globally ) {
        
        $notice = __( '<b>This post\'s images are set to be displayed.</b> You can also set them to remain cleared to display or to be removed and replaced regardless of settings elsewhere', 'cks_rui' ) ;
        
    }

    ?>
    
    <div id="cks_rui-meta-labels" class="<?php echo $images_being_removed ? 'cks_rui-images-removed' : 'cks_rui-images-displayed' ; ?>" >
    
        <label for="cks_rui-meta-safe">
            <input type="radio" name="_is_image_safe" id="cks_rui-radio-1" value="safe" <?php if ( $image_safe == 'safe' ){ echo 'checked="checked"' ; } ?> />
                <?php _e( 'Clear Images for Display' , 'cks_rui' ) ; ?>
        </label>
        
        <br>
        
        <label for="cks_rui-meta-unsafe">
                <input type="radio" name="_is_image_safe" id="cks_rui-radio-2" value="unsafe" <?php if  ( $image_safe == 'unsafe' ) { echo 'checked="checked"' ; } ?> />
                    <?php _e( 'Remove and Replace Post Images' , 'cks_rui' ) ; ?>
        </label> 
        
        <br>
        
        <label for="cks_rui-meta-unset">
                <input type="radio" name="_is_image_safe" id="cks_rui-radio-3" value="unset"  <?php if ( $image_safe == 'unset' ) { echo 'checked="checked"' ; } ?> />
                    <?php _e( 'Do Not Override Global Settings' , 'cks_rui' ) ; ?>
        </label>    
 
        <p class="description"><?php echo $notice ; ?></p>
    
    </div>
		
<?php }

/**
 * When the post is saved, saves our custom data.
 * @param int $post_id The ID of the post being saved.
 */
function cks_rui_save_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    $nonce = filter_input( INPUT_POST,'cks_rui_meta_box_nonce', FILTER_SANITIZE_STRING ) ;
    $post_type = filter_input( INPUT_POST,'post_type', FILTER_SANITIZE_STRING ) ;
    $_is_image_safe = filter_input( INPUT_POST,'_is_image_safe', FILTER_SANITIZE_STRING ) ;
    
    // Check if our nonce is set.
    if ( ! $nonce )  {
        
            return ;
            
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce, 'cks_rui_meta_box_data' ) ) {
        
            return ;
            
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        
            return ;
            
    }

    // Check the user's permissions.
    if ( $post_type && 'page' == $post_type ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            
            return ;
                
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            
            return ;
            
        }

    }

    // OK, it's safe for us to save the data now.
    if ( $_is_image_safe ) {
        
        update_post_meta( $post_id, '_is_image_safe', $_is_image_safe ) ;

    } 
	
}

add_action( 'save_post', 'cks_rui_save_meta_box_data' ) ;

/**
 *  ALL POSTS COLUMN HEADINGS 
 * @param array of Default Column Headings in Edit Posts
 */
function cks_rui_columns_head( $defaults ) {
    
    $defaults['featured_image'] = 'Featured Image' ;
    $defaults['images_cleared'] = 'Image Remove/Replace' ;
    
    return $defaults ;
}

/**
 * PAGES COLUMN HEADINGS
 * in testing must be done separately from Posts
 * simply adding same function to different hooks fails
 * @param array of Default Column Headings in Pages
 */
function cks_rui_page_columns_head( $columns ) {
 
    $columns['featured_image'] = __( 'Featured Image' ) ;
    $columns['images_cleared'] = __( 'Image Remove/Replace' ) ;

    return $columns ;
}

/** 
 * PREPARE FEATURED IMAGE COLUMN 
 * highlighted to show whether post is subject to IR/R
 * based on http://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
 */

/**
 * add image size to defaults
 */
add_image_size( 'featured_preview', 55, 55, true ) ;

/**
 * Get Featured Image
 * @param int $post_ID of post with featured image
 */
function cks_rui_get_featured_image( $post_ID ) {
    
    $post_thumbnail_id = get_post_thumbnail_id( $post_ID) ?  get_post_thumbnail_id( $post_ID ) : '' ;
    
    if ( $post_thumbnail_id ) {
        
        $post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, 'featured_preview' )  ;
        
        return $post_thumbnail_img[0] ;   
        
    } else {
        
        return false ;
    
    }
    
}

/**
 * UTILITY FUNCTION FOR EDIT.PHP (POSTS AND PAGES) IMAGES COLUMN
 * @param object $post
 * @param array $options
 * @return array of counts
 */
function cks_rui_count_matched_images( $post, $options ) {
    
    $static_image_url = cks_rui_get_static_image_url( $options ) ;
    
    $content = $post->post_content;

    $new_content = cks_rui_match_image_links( $content, $options );
  
    //counts number of replacements
    $new_new_content = str_replace( ' src="' . $static_image_url, ' src=#', $new_content, $count) ;
    
    //counts "src=" tags in posts
    $new_new_new_content = str_replace( '<img ', '', $new_new_content, $count2) ; 
        
    return array( $count, $count2 ) ;
        
}

/**
 * COLUMNS FOR ALL POSTS
 * Creates two new columns in edit.php for Posts
 * @param string $column_name
 * @param int $post_id
 */
function cks_rui_columns_content( $column_name, $post_id ) {
    
    global $post;
    
    $options = get_option( 'cks_rui_options' ) ;
    
    $fallback_img = plugin_dir_url( __FILE__ ) . 'images/no_featured_image.png' ;
    
    $num_images_arr = cks_rui_count_matched_images( $post, $options ) ;
    
    $global_img_del = isset($options['global_image_del']) ? $options['global_image_del'] : '' ;
    
    //if all images regardless of extension are being removed, set matches = found
    if ( $global_img_del) {
        
        $num_images_arr[0] = $num_images_arr[1] ;
    }
    
    //add classes based on results
    if ($num_images_arr[1] == 0) {
        
        $class = 'no-images' ;
        
    } else {
        
        if ( $num_images_arr[1] !== $num_images_arr[0] ) {
            
            $class = 'images-found mismatch' ;
            
        } else {
        
        $class = 'images-found' ;
        
        }
    }
    
    if (cks_rui_remove_images_from_post( $post, $options ) ) {
        
        $class .= ' cks_rui-images-removed' ;
        $images_cleared = false ;
        
    } else {
        
        $class .= ' cks_rui-images-displayed' ;
        $images_cleared = true ;
        
    }
    
    //fill first, "Featured Image" column
    if ( $column_name === 'featured_image' ) {
             
        if ( cks_rui_get_featured_image( $post_id ) ) {
            
            $post_featured_image = cks_rui_get_featured_image( $post_id ) ;
            
            echo '<img class="cks_rui-edit-posts-img ' . $class . '" src="' . $post_featured_image . '" />' ;
            
        } else {
            
            echo '<img class="cks_rui-edit-posts-img ' . $class . '" src="' . $fallback_img . '" />' ;
        }
        
    }
    
    //fill second, "Image Removal/Replacment Column
    if ( $column_name === 'images_cleared' ) {
       
        if ( ! get_post_meta( $post->ID, '_is_image_safe', true ) ) { 
        
            $image_safe = 'unset' ; 
        
        } else {
        
            $image_safe = get_post_meta( $post->ID, '_is_image_safe', true ) ;
    
        }
        
        $checked_1 = '' ;
        $checked_2 = '' ;
        $checked_3 = '' ;
        
        if ( $image_safe == 'safe' ) { $checked_1 = 'checked' ; }
        if ( $image_safe == 'unsafe' ) { $checked_2 = 'checked' ; } 
        if ( $image_safe == 'unset' ) { $checked_3 = 'checked' ; }
            
        //output hidden, but "stolen" by Quick/Bulk edit jQuery functions    
        echo "<input id='checked_1' style='display: none' type='radio' readonly $checked_1/>";
        echo "<input id='checked_2' style='display: none' type='radio' readonly $checked_2/>";
        echo "<input id='checked_3' style='display: none' type='radio' readonly $checked_3/>";
        echo '<div class="' . $class . '-text">' ; 

        if ($num_images_arr[1] == 0 ) { 
            
            echo 'No Images Found in Post' ;
            
        } 

        if ( $num_images_arr[1] == 1 )  {

            echo '1 Image Found in Post' ;
            
        } 

        if ( $num_images_arr[1] > 1 ) {

            echo $num_images_arr[1] . ' Images Found in Post' ;

        }
        
        if ( ! $global_img_del ) {

            if ($num_images_arr[0] == 0 ) { 
                
                echo '<br>No Images Matched in Post' ;
                
            } 

            if ( $num_images_arr[0] == 1 )  {

                echo '<br>1 Image Matched in Post' ;
                
            } 

            if ( $num_images_arr[0] > 1 ) {

                echo '<br>' . $num_images_arr[0] . ' Images Matched in Post' ;

            }
        
        }

        echo $images_cleared ? ',<br>Set to Display' : ', <br>Set for Removal & Replacement' ; 
        
        echo '</div>' ;

    }
     
}

/** 
 * COLUMNS FOR ALL PAGES 
 * see prior note: needs to be done separately
 * @param string $column_name
 * @param int $post_id
 */
function cks_rui_page_column_content( $column_name, $post_id ) {

    global $post;
    
    $options = get_option( 'cks_rui_options' ) ;
    
    $fallback_img = plugin_dir_url( __FILE__ ) . 'images/no_featured_image.png' ;
    
    $num_images_arr = cks_rui_count_matched_images( $post, $options ) ;
    
    $global_img_del = isset($options['global_image_del']) ? $options['global_image_del'] : '' ;
    
    if ( $global_img_del) {
        
        $num_images_arr[0] = $num_images_arr[1] ;
    }
    
    if ($num_images_arr[1] == 0 ) {
        
        $class = 'no-images' ;
        
    } else {
        
        if ( $num_images_arr[1] !== $num_images_arr[0] ) {
            
            $class = 'images-found mismatch' ;
            
        } else {
        
        $class = 'images-found' ;
        
        }
    }
    
    if (cks_rui_remove_images_from_post( $post, $options ) ) {
        
        $class .= ' cks_rui-images-removed' ;
        $images_cleared = false;
        
    } else {
        
        $class .= ' cks_rui-images-displayed' ;
        $images_cleared = true;
        
    }
    
    if ( $column_name == 'featured_image' ) {
             
        if ( cks_rui_get_featured_image( $post_id ) ) {
            
            $post_featured_image = cks_rui_get_featured_image( $post_id ) ;
            
            echo '<img class="cks_rui-edit-posts-img ' . $class . '" src="' . $post_featured_image . '" />' ;
            
        } else {
            
            echo '<img class="cks_rui-edit-posts-img ' . $class . '" src="' . $fallback_img . '" />' ;
            
        }
        
    }
    
    if ( $column_name == 'images_cleared' ) {
       
        if ( ! get_post_meta( $post->ID, '_is_image_safe', true) ) { 
        
            $image_safe = 'unset' ; 
        
        } else {
        
            $image_safe = get_post_meta( $post->ID, '_is_image_safe', true ) ;
    
        }
        
        $checked_1 = '' ;
        $checked_2 = '' ;
        $checked_3 = '' ;
        
        if ( $image_safe == 'safe' ) { $checked_1 = 'checked' ; }
        if ( $image_safe == 'unsafe' ) { $checked_2 = 'checked' ; } 
        if ( $image_safe == 'unset' ) { $checked_3 = 'checked' ; }
            
            
        echo "<input id='checked_1' style='display: none' type='radio' readonly $checked_1/>";
        echo "<input id='checked_2' style='display: none' type='radio' readonly $checked_2/>";
        echo "<input id='checked_3' style='display: none' type='radio' readonly $checked_3/>";
        echo '<div class="' . $class . '-text">' ; 

        if ($num_images_arr[1] == 0 ) { 
            
            echo 'No Images Found in Page' ;
            
        } 

        if ( $num_images_arr[1] == 1 )  {

            echo '1 Image Found in Page' ;
            
        } 

        if ( $num_images_arr[1] > 1 ) {

            echo $num_images_arr[1] . ' Images Found in Page' ;

        }
        
        if ( ! $global_img_del ) {

            if ($num_images_arr[0] == 0 ) { 
                
                echo '<br>No Images Matched in Page' ;
                
            } 

            if ( $num_images_arr[0] == 1 )  {

                echo '<br>1 Image Matched in Page' ;
            
            } 

            if ( $num_images_arr[0] > 1 ) {

                echo '<br>' . $num_images_arr[0] . ' Images Matched in Page' ;

            }
        
        }

        echo ( $images_cleared ) ? ',<br>Cleared for Display' : ', <br>Set for Removal & Replacement' ; 
        
        echo '</div>' ;

    }

} 

/**
 * PRODUCES RADIO BUTTONS FOR QUICK EDITOR
 * with verification nonce on post save
 * @param type $column_name
 */
function display_cks_rui_clear_images( $column_name ) {
    
    wp_nonce_field( 'display_cks_rui_clear_images', 'clear_images_nonce' ) ;  
    
    if ( $column_name == 'images_cleared' ) {  ?>

        <fieldset class="inline-edit-col-right inline-edit-clear-images">

            <div class="inline-edit-col column-<?php echo $column_name; ?>">

                <span class="title">Image Replacement and Removal</span>

                <label class="inline-edit-group">

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-1" value="safe" />
                    <?php _e( 'Clear Images for Display | ' , 'cks_rui' ) ; ?>

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-2" value="unsafe" />
                    <?php _e( 'Remove and Replace Post Images | ' , 'cks_rui' ) ; ?>

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-3" value="unset" />
                    <?php _e( 'Do Not Override Global Settings' , 'cks_rui' ) ; ?>

                </label>

            </div>

        </fieldset>
    
    <?php   
    
    }
        
}

/**
 * PRODUCE RADIO BUTTONS FOR BULK EDITOR
 * no nonce because not relevant/produces error since variables
 * process via Ajax function, not "save post"
 * @param string $column_name
 */
function display_cks_rui_clear_images_bulk( $column_name ) {
    
    if ( $column_name == 'images_cleared' ) {  ?>
    
        <fieldset class="inline-edit-col-left"> &nbsp; </fieldset>

        <fieldset class="inline-edit-col-center"> &nbsp; </fieldset>

        <fieldset class="inline-edit-col-right inline-edit-clear-images">

            <div class="inline-edit-col column-<?php echo $column_name; ?>">

                <span class="title">Image Replacement and Removal</span>

                <label class="inline-edit-group">

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-1" value="safe" />
                    <?php _e( 'Clear Images for Display | ' , 'cks_rui' ) ; ?>

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-2" value="unsafe" />
                    <?php _e( 'Remove and Replace Post Images | ' , 'cks_rui' ) ; ?>

                    <input type="radio" name="_is_image_safe" id="cks_rui-radio-3" value="unset" />
                    <?php _e( 'Do Not Override Global Settings' , 'cks_rui' ) ; ?>

                </label>

            </div>

        </fieldset>
    
    <?php   
    
    }
        
}

/* SAVE QUICK EDIT */
add_action( 'save_post', 'save_quick_edit_clear_images' ) ;

/**
 * QUICK EDIT SAVE FUNCTION
 * @param int $post_id
 * process post edits if user can edit and nonce verified
 * otherwise return/exit
 */
function save_quick_edit_clear_images( $post_id ) {
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        
        return;
        
    }
    
    if ( 
        
        isset ($_POST['clear_images_nonce']) && 
            
        wp_verify_nonce( $_POST['clear_images_nonce'], 'display_cks_rui_clear_images' ) && 
            
        isset( $_REQUEST['_is_image_safe'] ) 
            
        ) {
                
        update_post_meta( $post_id, '_is_image_safe', $_REQUEST['_is_image_safe'] ) ;

    }

}


/* SAVE BULK EDIT VIA AJAX */
add_action( 'wp_ajax_cks_rui_save_bulk_edit', 'cks_rui_save_bulk_edit' ) ;

function cks_rui_save_bulk_edit() {
    
    // get our variables
    $post_ids = ( isset( $_POST[ 'post_ids' ] ) && ! empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : NULL;
                 
    if ( 
            
        ! empty( $post_ids ) && 
            
        is_array( $post_ids ) && 
            
        isset( $_POST['image_safe'] ) && 
            
        ! empty( $_POST[ 'image_safe'] ) 
            
        ) {          
         
        foreach( $post_ids as $post_id ) {
              
            update_post_meta( $post_id, '_is_image_safe', $_POST[ 'image_safe' ] ) ;
                  
        }
            
    }
        
}