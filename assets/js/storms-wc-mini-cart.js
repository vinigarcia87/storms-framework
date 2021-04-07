/**
 * Open/close the wc cart mini products list - and keep it opened unless you click outside the element
 */
var body = document.querySelector( 'body' );

body.addEventListener( 'click', function( event ) {

	if( ! event.target.closest( '.storms-cart-contents' ) ||
		( ! event.target.parentElement.classList.contains( 'cart-link' ) &&
		  ! event.target.classList.contains( 'cart-link' ) ) ) {
		return;
	}

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

	var cart_container = event.target.closest( '.storms-cart-contents' );
	var dropdown = cart_container.querySelector( '.shopping_cart_dropdown' );

	dropdown.classList.toggle( 'active' );

} );

body.addEventListener( 'click', function( event ) {

	var cart_container = event.target.closest( '.storms-cart-contents' );
	if( cart_container !== null ) {
		return;
	}

	// The click was outside the Mini Cart...
	var dropdown = document.querySelector( '.storms-cart-contents .shopping_cart_dropdown' );
	// The Mini Cart was open, so we going to close it
	dropdown.classList.remove( 'active' );

} );
