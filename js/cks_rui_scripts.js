(function($) {

    // we create a copy of the WP inline edit post function
    var $wp_inline_edit = inlineEditPost.edit;

    // and then we overwrite the function with our own code
    inlineEditPost.edit = function( id ) {

        // "call" the original WP edit function
        // we don't want to leave WordPress hanging
        $wp_inline_edit.apply( this, arguments );

        // now we take care of our business
        //NEED TO RESTORE POST-ROW, INFO, USE IT TO DETERMINE CHECKED-NESS

        // get the post ID
        var $post_id = 0;
        if ( typeof( id ) == 'object' ) {

            $post_id = parseInt( this.getId( id ) );

        }

        if ( $post_id > 0 ) {

            // define the edit row
            var $edit_row = $( '#edit-' + $post_id );
            var $post_row = $( '#post-' + $post_id );

            // get the data
            var $images_safe    = !! $( '#checked_1', $post_row ).prop('checked');
            var $images_unsafe  = !! $( '#checked_2', $post_row ).prop('checked');
            var $images_unset   = !! $( '#checked_3', $post_row ).prop('checked');

            // populate the data
            $( '#cks_rui-radio-1', $edit_row ).prop('checked', $images_safe );
            $( '#cks_rui-radio-2', $edit_row ).prop('checked', $images_unsafe );
            $( '#cks_rui-radio-3', $edit_row ).prop('checked', $images_unset );

        }

    };

$( '#bulk_edit' ).live( 'click', function() {

        // define the bulk edit row
        var $bulk_row = $( '#bulk-edit' );

        // get the selected post ids that are being edited
        var $post_ids = new Array();
        $bulk_row.find( '#bulk-titles' ).children().each( function() {
           $post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
        });

        // get the radio result
        var $image_safe = $bulk_row.find( 'input[name="_is_image_safe"]:checked' ).val();

        // save the data
        $.ajax({
           url: ajaxurl, // this is a variable that WordPress has already defined for us
           type: 'POST',
           async: false,
           cache: false,
           data: {
              action: 'cks_rui_save_bulk_edit', // this is the name of our WP AJAX function that we'll set up next
              post_ids: $post_ids, 
              image_safe: $image_safe
           }
        });

    });

})(jQuery);

// @Since: 1.0.4 // @Date: October 2016 // @Author: CK MacLeod // @Author: URI: http://ckmacleod.com // @License: GPL3
// based on WordPress Codex https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box