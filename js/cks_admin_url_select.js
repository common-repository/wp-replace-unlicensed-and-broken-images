(function( $ ) {
    
    $( '.def_image_link' ).live( 'click', function( event ) {
        
        event.preventDefault();
        
        $( '#upload_image' ).val($( this ).html()).addClass( 'url-rtl' );
        
    });
    
    $( '#upload_image' ).live( 'click', function() {
        
                $( this ).removeClass( 'url-rtl' );
    });
    
})( jQuery );

//  @Since: 1.0.4 // @Date: October 2016 // @Author: CK MacLeod // @Author: URI: http://ckmacleod.com // @License: GPL3