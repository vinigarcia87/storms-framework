/**
 * Open/close the wc user menu - and keep it opened unless you click outside the element
 */
jQuery( function( $ ) {

	var $body = $( 'body' );

	$body.on( 'click', '.storms_wc_user_menu a.user-menu-link', function ( event ) {

		event.preventDefault();

        var $user_menu_container = $(this).closest('.storms-user-menu-content');
        var $dropdown = $('.user_menu_dropdown', $user_menu_container);
        if( ! $dropdown.hasClass('active') ) {
			$dropdown.addClass('active');
        } else {
			$dropdown.removeClass('active');
        }

    } );

	$body.on( 'click', function( event ) {

		var $user_menu_container = $(event.target).closest('.storms-user-menu-content');
		if( ! $user_menu_container.length ) {
			// The click was outside the User Menu...
			var $dropdown = $('.storms-user-menu-content .user_menu_dropdown.active');
			// The User Menu was open, so we going to close it
			$dropdown.removeClass('active');
		}

	} );

} );
