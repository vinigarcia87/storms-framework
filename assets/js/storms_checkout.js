jQuery( function( $ ) {
	jQuery(document).on('click', '.ro-btn-2', function() {
		var $checkout_form = jQuery('form.checkout');

		$.blockUI.defaults.overlayCSS.cursor = 'default';
		$checkout_form.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		jQuery.ajax({
			url: storms.ajax_url,
			type: 'post',
			data: $checkout_form.serialize() + '&action=storms_save_address&ajax_nonce=' + storms.ajax_nonce,
			success: function ( response ) {
				$('.woocommerce-message,.woocommerce-error').remove();

				var result = response.split('|');

				$checkout_form.before( result[2] );

				if(result[0] == 'success') {
					var process2 = $('.ro-checkout-process .ro-hr-line .ro-tab-2');
					process2.parent().parent().removeClass('ro-process-1');
					process2.parent().parent().addClass('ro-process-2');
					$('.ro-checkout-panel .ro-panel-1').css('display', 'none');
					$('.ro-checkout-panel .ro-panel-2').css('display', 'block');

					var fields = '';
					var shipping_data = JSON.parse( result[1] );
					for ( index = 0; index < shipping_data['billing'].length; index++ ) {
						var item = shipping_data['billing'][index];
						if( item.value != '' ) {
							if( item.type != 'checkbox' )
								fields += '<div class="ro-info"><p><span id="field_' + item.key + '">' + item.label + '</span>' + item.value + '</p></div>';
							else if( ( item.type == 'checkbox' ) && ( item.value == 1 ) )
								fields += '<div class="ro-info"><p><span id="field_' + item.key + '">' + item.label + '</span></p></div>';
						}
					}
					$('.ro-customer-info .ro-content').html( fields );
				} else if(result[0] == 'fail') {
					$('.ro-checkout-process .ro-hr-line .ro-tab-1, .ro-customer-info .ro-edit-customer-info').click();
				}
				$checkout_form.unblock();
			}
		});
	});

	$('.ro-checkout-process .ro-hr-line .ro-tab-1, .ro-customer-info .ro-edit-customer-info').click(function(){
		var process1 = $('.ro-checkout-process .ro-hr-line .ro-tab-1');
		process1.parent().parent().removeClass('ro-process-2');
		process1.parent().parent().addClass('ro-process-1');
		$('.ro-checkout-panel .ro-panel-1').css('display', 'block');
		$('.ro-checkout-panel .ro-panel-2').css('display', 'none');
	});
});
