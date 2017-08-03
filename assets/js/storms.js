/*
$(function () {
	if( $.isFunction( $.fn.someMethod ) ) {

	}
});
*/

// Check the existence of an element in jQuery
// if ($(selector).exists()) { // Do something }
// Fonte: http://stackoverflow.com/a/31047/1003020
jQuery.fn.exists = function () {
    return this.length > 0;
};

// Return the element id
//var el_id = $(selector).id();
jQuery.fn.id = function () {
    return this.get(0).id;
};

$(document).ready(function () {
    // Recupera a altura da tela do usuario
    //var window_height = $(window).height();

    // Verifica se o acesso esta sendo feito por dispositivo mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $('body').addClass('mobile');
    }

	// Define $.browser function
	if (!$.browser) {
		$.browser = {};
		$.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
		$.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
		$.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
		$.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
	}
});

$(function () {
	// Prevent default behaviour on links with #
	$( 'a[href="#"]' ).on( 'click', function( e ) {
		e.preventDefault();
	});
});

// Copy to Clipboard
$(function () {
    // Prompt para copiar texto
    function copyToClipboard(text) {
        window.prompt('Para copiar, aperte Ctrl+C e Enter', text);
    }

    // Ao clickar no numero do processo, o numero eh copiado para a area de transferencia do usuario
    $(document).on('click', '.click-to-copy', function () {
        copyToClipboard($(this).html());
    });
});

// Seleciona o texto de um input ao clica-lo
// Source: http://stackoverflow.com/a/4067488/1003020
$(function () {
    /* Seleciona o texto do campo pagina atual */
    $(document).on('click', '.select-all-text', function () {
        var txt = $(this).get(0);
        txt.setSelectionRange(0, txt.value.length);
    });
});

// Unblock Screen
hideCarregando = function() {
	if( $.isFunction( $.fn.unblockUI ) ) {
		$.unblockUI();
	}
};
// Block Screen
showCarregando = function() {
	if( $.isFunction( $.fn.blockUI ) ) {
		$.blockUI({
			css: { backgroundColor: 'transparent', border: 'none' },
			message: '<img id="imgLoading" alt="Carregando..." src="../img/loading.gif" />'
		});
	}
};

// Smooth Scrolling To Internal Links
// Source: http://www.paulund.co.uk/smooth-scroll-to-internal-links-with-jquery
$(document).ready(function(){
	$('a[href^="#"]').on('click',function (e) {
	    e.preventDefault();

	    var target = this.hash;
	    var $target = $(target);

		if( $target.exists() ) {
			$('html, body').stop().animate({
				'scrollTop': $target.offset().top
			}, 900, 'swing', function () {
				// Include Anchor In URL
				window.location.hash = target;
			});
		}
	});
});

$(function () {
	/**
	 * Identify what media screen we working on
	 * Source: http://stackoverflow.com/a/22708436/1003020
	 * 
	 * @media (max-width: 767px) { }                         // Extra Small
	 * @media (min-width: 768px) and (max-width: 991px) { }  // Small
	 * @media (min-width: 992px) and (max-width: 1199px) { } // Medium
	 * @media (min-width: 1200px) { }                        // Large
	 */
	function checkScreenSize() {
	  if (window.matchMedia('(max-width: 767px)').matches) {
		console.log('Media: Extra Small');
	  }
	  if (window.matchMedia('(min-width: 768px) and (max-width: 991px)').matches) {
		console.log('Media: Small');
	  }
	  if (window.matchMedia('(min-width: 992px) and (max-width: 1199px)').matches) {
		console.log('Media: Medium');
	  }
	  if (window.matchMedia('(min-width: 1200px)').matches) {
		console.log('Media: Large');
	  }
	}
	$(window).resize(checkScreenSize);
	checkScreenSize();
});

// FitVids, Responsive wp_video_shortcode, Bootstrap Shortcodes
$(document).ready(function(){
	// fitVids
	$( '.entry-content' ).fitVids();

	// Responsive wp_video_shortcode()
	$( '.wp-video-shortcode' ).parent( 'div' ).css( 'width', 'auto' );
	
	/**
	 * Odin Core shortcodes
	 */
	/*
	// Tabs.
	$( '.odin-tabs a' ).click(function(e) {
		e.preventDefault();
		$(this).tab( 'show' );
	});

	// Tooltip.
	$( '.odin-tooltip' ).tooltip();
	*/
});