/**
 * Open/close the wc user menu - and keep it opened unless you click outside the element
 */
jQuery( function( $ ) {

	var $body = $( 'body' );

	$body.on( 'click', '.storms_wc_user_menu a.user-menu-link', function ( event ) {

		// Try to identify what size is the user's device
		var body_class_list = body.classList;
		var is_device_xs = body_class_list.contains('sts-media-xs');
		var is_device_sm = body_class_list.contains('sts-media-sm');
		var is_device_md = body_class_list.contains('sts-media-md');
		var is_device_lg = body_class_list.contains('sts-media-lg');
		var is_device_xl = body_class_list.contains('sts-media-xl');
		// Maybe we couldn't identify the device's size
		var is_device_unknown = ! is_device_xs && ! is_device_sm && ! is_device_md && ! is_device_lg && ! is_device_xl;

		// Avoid this behaviour on specific media sizes
		// TODO Make this filter customizable
		if( is_device_xs || is_device_sm ) {
			return;
		}

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
