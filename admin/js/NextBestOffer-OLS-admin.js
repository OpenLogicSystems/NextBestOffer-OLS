(function( $ ) {
	'use strict';

})( jQuery );

jQuery(document).ready(function($) {
    $('.scrollable-content').on('scroll', function() {
        var scrolled = $(this).scrollTop();
        // Adjust the slider position or do any other desired actions based on the scroll position
    });
});
