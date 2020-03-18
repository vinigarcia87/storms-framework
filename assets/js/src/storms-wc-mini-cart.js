/**
 * Open/close the wc cart mini products list - and keep it opened unless you click outside the element
 */
/* global storms_wc_mini_cart_vars */
jQuery( function( $ ) {

	// storms_wc_mini_cart_vars is required to continue, ensure the object exists
	if ( typeof storms_wc_mini_cart_vars === 'undefined' ) {
		return false;
	}

	var $body = $( 'body' );

	$body.on( 'click', '.storms-cart-contents a.cart-link', function ( event ) {

		event.preventDefault();

        var $cart_container = $(this).closest('.storms-cart-contents');
        var $dropdown = $('.shopping_cart_dropdown', $cart_container);
        if( ! $dropdown.hasClass('active') ) {
			$dropdown.addClass('active');
        } else {
			$dropdown.removeClass('active');
        }

    } );

	$body.on( 'click', function( event ) {

		var $cart_container = $(event.target).closest('.storms-cart-contents');
		if( ! $cart_container.length ) {
			// The click was outside the Mini Cart...
			var $dropdown = $('.storms-cart-contents .shopping_cart_dropdown.active');
			// The Mini Cart was open, so we going to close it
			$dropdown.removeClass('active');
		}

	} );

	// Replacement for WC original "Remove Item" function
    // @see wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart.js:L71
	$body.on( 'click', '.storms-cart-contents .storms_remove_from_cart_button', function( event ) {
        event.preventDefault();

        $el = $( this );
        var product_id = $el.attr('data-product_id');
        var item_key = $el.attr('data-item_key');
        var wpnonce = $el.attr('data-wpnonce');

        var $cart_dropdown = $('.shopping_cart_dropdown');
        $cart_dropdown.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

		// Modificamos a funçao original do WC para controlar melhor o 'block' na tela
		// @see wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart.js:71
		$.post( storms_wc_mini_cart_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_from_cart' ), { cart_item_key : item_key }, function( response ) {
			if ( ! response || ! response.fragments ) {
				window.location = $el.attr( 'href' );
				return;
			}
			$( document.body ).trigger( 'removed_from_cart', [ response.fragments, response.cart_hash ] );
		}).fail( function() {
			window.location = $el.attr( 'href' );
			return;
		});
    } );

} );
