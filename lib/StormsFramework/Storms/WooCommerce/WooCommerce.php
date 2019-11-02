<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2016, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   3.0.0
 *
 * StormsFramework\Storms\WooCommerce\WooCommerce class
 * Add WooCommerce support
 */

namespace StormsFramework\Storms\WooCommerce;

use StormsFramework\Base,
	StormsFramework\Storms\Template;

class WooCommerce extends Base\Runner
{
	public function __construct() {
		// Declare WooCommerce support
		add_theme_support( 'woocommerce' );

        // Enabling product gallery features - zoom, swipe, lightbox
        // https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
        if( get_option( 'use_wc_product_gallery', true ) ) {
            add_theme_support('wc-product-gallery-zoom');
            add_theme_support('wc-product-gallery-lightbox');
            add_theme_support('wc-product-gallery-slider');
        }

		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public function define_hooks() {
		$this->loader
            // @see https://gregrickaby.com/2013/05/remove-woocommerce-styles-and-scripts/
			->add_filter( 'woocommerce_enqueue_styles', 'remove_woocommerce_style' )
            ->add_action( 'wp_enqueue_scripts', 'manage_woocommerce_scripts', 99 );

        $this->loader
            ->add_filter( 'woocommerce_page_title', 'shop_page_title' )
            ->add_filter( 'woocommerce_product_tabs', 'remove_product_tabs', 98);

		$this->loader
			->add_action( 'woocommerce_register_form', 'registration_confirm_password_add_field' )
			->add_filter( 'woocommerce_registration_errors', 'registration_confirm_password_validation', 10, 3 )
			->add_action( 'woocommerce_checkout_init', 'checkout_confirm_password_add_field', 10, 1 )
			->add_action( 'woocommerce_after_checkout_validation', 'checkout_confirm_password_validation', 10, 2 );

		/**
		 * Content wrapper before and after
		 * Will be needed if you not using woocommerce.php on your theme
		 * @see  before_content()
		 * @see  after_content()
		 */
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		$this->loader
			->add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 5 )
			->add_action( 'woocommerce_before_main_content', 'before_content', 10 )
			->add_action( 'woocommerce_after_main_content', 'after_content', 10 )

			->add_filter( 'woocommerce_breadcrumb_defaults', 'woocommerce_breadcrumb_args' )
			->add_action( 'template_redirect', 'remove_sidebar' );

		/**
		 * Filters
		 * @see  product_thumbnail_columns()
		 * @see  products_per_page()
		 * @see  shop_loop_number_of_columns()
		 */
		$this->loader
			->add_filter( 'woocommerce_product_thumbnails_columns', 'product_thumbnail_columns' )
			->add_filter( 'loop_shop_per_page', 'products_per_page' )
			->add_filter( 'loop_shop_columns', 'shop_loop_number_of_columns' )
			->add_filter( 'woocommerce_output_related_products_args', 'related_products_args' )
            ->add_filter( 'woocommerce_cross_sells_total', 'cross_sells_limit' )
            ->add_filter( 'woocommerce_cross_sells_columns', 'cross_sells_columns' );

		$this->loader
			->add_action( 'widgets_init', 'register_widgets_area' )
			->add_filter( 'woocommerce_form_field_args', 'bootstrap_form_field_args', 10, 3 )
			->add_filter( 'woocommerce_form_field_checkbox', 'bootstrap_form_field_checkbox', 10, 4 )
			->add_filter( 'woocommerce_form_field_radio', 'bootstrap_form_field_radio', 10, 4 );

		$this->loader
			->add_action( 'post_class', 'content_product_class' )
			->add_action( 'product_cat_class', 'content_product_class' )
			->add_action( 'storms_wc_after_item_loop', 'storms_wc_after_item_loop' );

		$this->loader
			->add_action( 'init', 'prevent_wp_login' )
			->add_action( 'template_redirect', 'force_login_registration_page_on_checkout' )
			->add_filter( 'woocommerce_login_redirect', 'user_redirect_on_login_registration', 10, 2 )
			->add_filter( 'woocommerce_registration_redirect', 'user_redirect_on_login_registration', 10, 2 )
			->add_action( 'template_redirect', 'bypass_logout_confirmation' );
	}

	//<editor-fold desc="Styles and definitions">

    /**
     * Cleanup wp_head(), to remove unnecessary and unsafe wp meta tags
     */
    public function head_cleanup() {
        //remove generator meta tag
        remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
    }

	/**
	 * Remove all native WooCommerce styles
	 */
	public function remove_woocommerce_style( $enqueue_styles )
    {
        if( get_option( 'use_wc_styles', false ) ) {
            return $enqueue_styles;
        } else {
            return array();
        }
	}

    /**
     * Optimize WooCommerce Scripts
     * Remove WooCommerce Generator tag, styles, and scripts from non WooCommerce pages.
     */
    public function manage_woocommerce_scripts() {

		// Check if we should apply this modifications
		if( ! get_option( 'manage_woocommerce_scripts', false ) ) {
			return false;
		}

        // @TODO wc-cart-fragments e outros podem ser necessários na home! Verificar!

        // Dequeue scripts and styles
        if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			wp_dequeue_script( 'js-cookie' );
			wp_dequeue_script( 'jquery-blockui' );
			wp_dequeue_script( 'jquery-cookie' );           // deprecated.
			wp_dequeue_script( 'jquery-payment' );
			wp_dequeue_script( 'select2' );
			wp_dequeue_script( 'wc-address-i18n' );
			wp_dequeue_script( 'wc-add-payment-method' );
			wp_dequeue_script( 'wc-cart' );
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'wc-checkout' );
			wp_dequeue_script( 'wc-country-select' );
			wp_dequeue_script( 'wc-credit-card-form' );
			wp_dequeue_script( 'wc-add-to-cart' );
			wp_dequeue_script( 'wc-add-to-cart-variation' );
			wp_dequeue_script( 'wc-geolocation' );
			wp_dequeue_script( 'wc-lost-password' );
			wp_dequeue_script( 'wc-password-strength-meter' );
			wp_dequeue_script( 'wc-single-product' );
			wp_dequeue_script( 'woocommerce' );

            wp_dequeue_script( 'flexslider' );
            wp_dequeue_script( 'photoswipe' );
            wp_dequeue_script( 'photoswipe-ui-default' );
            wp_dequeue_script( 'prettyPhoto' );             // deprecated.
            wp_dequeue_script( 'prettyPhoto-init' );        // deprecated.
			wp_dequeue_script( 'zoom' );
        } else {
            // If the theme is NOT using WC product gallery, we don't load the scripts
            if( ! get_option( 'use_wc_product_gallery', true ) ) {
                wp_dequeue_script( 'flexslider' );
                wp_dequeue_script( 'photoswipe' );
                wp_dequeue_script( 'photoswipe-ui-default' );
                wp_dequeue_script( 'prettyPhoto' );             // deprecated.
                wp_dequeue_script( 'prettyPhoto-init' );        // deprecated.
                wp_dequeue_script( 'zoom' );
            }
        }

        // https://github.com/woocommerce/woocommerce/issues/15762

//        'photoswipe' // assets/css/photoswipe/photoswipe.css
//        'photoswipe-default-skin' // assets/css/photoswipe/default-skin/default-skin.css // 'photoswipe'
//        'select2' // assets/css/select2.css
//        'woocommerce_prettyPhoto_css' // deprecated. // assets/css/prettyPhoto.css
//
//        'flexslider' // assets/js/flexslider/jquery.flexslider.js // 'jquery'
//        'js-cookie' // assets/js/js-cookie/js.cookie.js
//        'jquery-blockui' // assets/js/jquery-blockui/jquery.blockUI.js // 'jquery'
//        'jquery-cookie' // deprecated. // assets/js/jquery-cookie/jquery.cookie.js // 'jquery'
//        'jquery-payment' // assets/js/jquery-payment/jquery.payment.js // 'jquery'
//        'photoswipe' // assets/js/photoswipe/photoswipe.js
//        'photoswipe-ui-default' // assets/js/photoswipe/photoswipe-ui-default.js // 'photoswipe'
//        'prettyPhoto' // deprecated. // assets/js/prettyPhoto/jquery.prettyPhoto.js // 'jquery'
//        'prettyPhoto-init' // deprecated. // assets/js/prettyPhoto/jquery.prettyPhoto.init.js // 'jquery', 'prettyPhoto'
//        'select2' // assets/js/select2/select2.full.js // 'jquery'
//        'wc-address-i18n' // assets/js/frontend/address-i18n.js // 'jquery'
//        'wc-add-payment-method' // assets/js/frontend/add-payment-method.js // 'jquery', 'woocommerce'
//        'wc-cart' // assets/js/frontend/cart.js // 'jquery', 'wc-country-select', 'wc-address-i18n'
//        'wc-cart-fragments' // assets/js/frontend/cart-fragments.js' // 'jquery', 'js-cookie'
//        'wc-checkout' // assets/js/frontend/checkout.js' // 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n'
//        'wc-country-select' // assets/js/frontend/country-select.js' // 'jquery'
//        'wc-credit-card-form' // assets/js/frontend/credit-card-form.js // 'jquery', 'jquery-payment'
//        'wc-add-to-cart' // assets/js/frontend/add-to-cart.js // 'jquery'
//        'wc-add-to-cart-variation' // assets/js/frontend/add-to-cart-variation.js // 'jquery', 'wp-util'
//        'wc-geolocation' // assets/js/frontend/geolocation.js // 'jquery'
//        'wc-lost-password' // assets/js/frontend/lost-password.js // 'jquery', 'woocommerce'
//        'wc-password-strength-meter' // assets/js/frontend/password-strength-meter.js // 'jquery', 'password-strength-meter'
//        'wc-single-product' // assets/js/frontend/single-product.js // 'jquery'
//        'woocommerce' // assets/js/frontend/woocommerce.js // 'jquery', 'jquery-blockui', 'js-cookie'
//        'zoom' // assets/js/zoom/jquery.zoom.js // 'jquery'
    }

    /**
     * Change the name of shop page
     */
    public function shop_page_title( $page_title ) {

        return get_option( 'shop_page_title', $page_title );
    }

    /**
     * Disable default WooCommerce tabs on product single page
     */
    function remove_product_tabs( $tabs ) {

        if( get_option( 'remove_tab_description', false ) ) {
            unset( $tabs['description'] );
        }

        if( get_option( 'remove_tab_reviews', false ) ) {
            unset( $tabs['reviews'] );
        }

        if( get_option( 'remove_tab_additional_information', false ) ) {
            unset( $tabs['additional_information'] );
        }

        return $tabs;
    }

	//</editor-fold>

	//<editor-fold desc="Login redirects">

	/**
	 * Don't allow any user to access the default WP login page
	 * We want them to use or theme custom login page
	 */
	public function prevent_wp_login() {
		// WP tracks the current page - global the variable to access it
		global $pagenow;

		if( get_option( 'prevent_wp_login', 'yes' ) ) {
			// Check if a $_GET['action'] is set, and if so, load it into $action variable
			$action = (isset($_GET['action'])) ? $_GET['action'] : '';
			// Check if we're on the login page, and ensure the action is not 'logout'
			if ($pagenow == 'wp-login.php' &&
				(!$action || ($action && !in_array($action, array('logout', 'lostpassword', 'rp', 'resetpass'))))
			) {

				// Load the 'myaccount' page url
				$page = wc_get_page_permalink('myaccount');

				// Redirect to the selected page
				wp_redirect($page);
				// Stop execution to prevent the page loading for any reason
				exit();
			}
		}
	}

	/**
	 * Redirect not logged in users to my-account, for registration/login
	 * @see https://stackoverflow.com/a/39357627/1003020
	 * @see https://wordpress.stackexchange.com/a/109097/54025
	 */
	public function force_login_registration_page_on_checkout() {
		// Case 1: Non logged user on checkout page
		if ( !is_user_logged_in() && is_checkout() ) {
			$login_page = esc_url( add_query_arg( 'return_to', 'checkout', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) );
			wp_redirect( $login_page );
		}
	}

	/**
	 * Redirect users to custom URL based on their role after login
	 * @see https://stackoverflow.com/a/29342329/1003020
	 *
	 * @param string $redirect
	 * @param object $user
	 * @return string
	 */
	public function user_redirect_on_login_registration( $redirect, $user ) {

		$checkout = get_permalink( wc_get_page_id( 'checkout' ) );

		if( isset( $_GET['return_to'] ) && 'checkout' === $_GET['return_to'] ) {
			// If the user came from checkout page, we send him back
			$redirect = $checkout;
		} else {
			// Redirect any other role to the previous visited page or, if not available, to the home
			$redirect = wp_get_referer() ? wp_get_referer() : home_url();
		}

		return $redirect;
	}

	/**
	 * Logout without confirmation
	 * Wordpress redirect logout to a 'confirm logout page' and this code avoids that
	 */
	public function bypass_logout_confirmation() {
		global $wp;

		if ( is_user_logged_in() && isset( $wp->query_vars['customer-logout'] ) ) {
			wp_logout();
			wp_redirect( home_url() );
		}
	}

	//</editor-fold>

	//<editor-fold desc="Check password on registration">

	/**
	 * Add a confirm password fields match on the registration page
	 * @see https://axlmulat.com/woocommerce/woocommerce-how-to-add-confirm-password-in-registration-and-checkout-page/
	 */
	public function registration_confirm_password_add_field() {
		?>
		<p class="form-row form-row-wide">
			<label for="reg_password2"><?php _e( 'Confirmar senha', 'storms' ); ?> <span class="required">*</span></label>
			<input type="password" class="input-text" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * Validate password match on the registration page
	 */
	public function registration_confirm_password_validation($reg_errors, $sanitized_user_login, $user_email) {
		global $woocommerce;

		extract( $_POST );

		if ( strcmp( $password, $password2 ) !== 0 ) {
			return new \WP_Error( 'registration-error', __( 'Senhas não são iguais', 'storms' ) );
		}
		return $reg_errors;
	}

	/**
	 * Add a confirm password field to the checkout page
	 * @see https://axlmulat.com/woocommerce/woocommerce-how-to-add-confirm-password-in-registration-and-checkout-page/
	 */
	public function checkout_confirm_password_add_field( $checkout ) {
		if ( get_option( 'woocommerce_registration_generate_password' ) == 'no' ) {

			$fields = $checkout->get_checkout_fields();

			$fields['account']['account_confirm_password'] = array(
				'type'              => 'password',
				'label'             => __( 'Confirmar senha', 'storms' ),
				'required'          => true,
				'placeholder'       => _x( 'Confirmar senha', 'placeholder', 'storms' )
			);

			$checkout->__set( 'checkout_fields', $fields );
		}
	}

	/**
	 * Validate confirm password field match to the checkout page
	 */
	public function checkout_confirm_password_validation( $posted ) {
		$checkout = WC()->checkout;
		if ( ! is_user_logged_in() && ( $checkout->must_create_account || ! empty( $posted['createaccount'] ) ) ) {
			if ( strcmp( $posted['account_password'], $posted['account_confirm_password'] ) !== 0 ) {
				wc_add_notice( __( 'Senhas não são iguais', 'storms' ), 'error' );
			}
		}
	}

	//</editor-fold>

	//<editor-fold desc="Bootstrap on form fields">

	/**
	 * WooCommerce - Modify each individual input type $args defaults
	 * To include Bootstrap classes
	 * Source: http://stackoverflow.com/a/36724593/1003020
	 *
	 * @param $args
	 * @param $key
	 * @param null $value
	 * @return mixed
	 */
	public function bootstrap_form_field_args( $args, $key, $value = null ) {
		/*
        $defaults = array(
            'type'              => 'text',
            'label'             => '',
            'description'       => '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            'id'                => $key,
            'class'             => array(),
            'label_class'       => array(),
            'input_class'       => array(),
            'return'            => false,
            'options'           => array(),
            'custom_attributes' => array(),
            'validate'          => array(),
            'default'           => '',
        );
        */

		// Start field type switch case
		switch ( $args['type'] ) {

			case "select" :  /* Targets all select input type elements, except the country and state select input types */
				$args['class'][] = 'form-group'; // Add a class to the field's html element wrapper - woocommerce input types (fields) are often wrapped within a <p></p> tag
				$args['input_class'] = array('form-control'); // Add a class to the form input itself
				//$args['custom_attributes']['data-plugin'] = 'select2';
				$args['label_class'] = array('col-form-label');
				$args['custom_attributes'] = array( 'data-plugin' => 'select2', 'data-allow-clear' => 'true', 'aria-hidden' => 'true',  ); // Add custom data attributes to the form input itself
				break;

			case 'country' : /* By default WooCommerce will populate a select with the country names - $args defined for this specific input type targets only the country select element */
				$args['class'][] = 'form-group single-country';
				$args['label_class'] = array('col-form-label');
				break;

			case "state" : /* By default WooCommerce will populate a select with state names - $args defined for this specific input type targets only the country select element */
				$args['class'][] = 'form-group'; // Add class to the field's html element wrapper
				//$args['input_class'] = array('form-control'); // add class to the form input itself
				//$args['custom_attributes']['data-plugin'] = 'select2';
				$args['label_class'] = array('col-form-label');
				$args['custom_attributes'] = array( 'data-plugin' => 'select2', 'data-allow-clear' => 'true', 'aria-hidden' => 'true',  );
				break;


			case "password" :
			case "text" :
			case "email" :
			case "tel" :
			case "number" :
				$args['class'][] = 'form-group';
				//$args['input_class'][] = 'form-control'; // will return an array of classes, the same as bellow
				$args['input_class'] = array('form-control');
				$args['label_class'] = array('col-form-label');
				break;

			case 'textarea' :
				$args['input_class'] = array('form-control');
				$args['label_class'] = array('col-form-label');
				break;

			case 'checkbox' :
				break;

			case 'radio' :
				break;

			default :
				$args['class'][] = 'form-group';
				$args['input_class'] = array('form-control');
				$args['label_class'] = array('col-form-label');
				break;
		}

		return $args;
	}

	/**
	 * Build WooCommerce checkbox with Bootstrap layout
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 * @return string
	 */
	public function bootstrap_form_field_checkbox( $field, $key, $args, $value ) {

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'storms'  ) . '">*</abbr>';
		} else {
			$required = '';
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$field = '';

		$field .= '<div class="checkbox' . esc_attr( implode( ' ', $args['class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . '>';
		$field .= '	<label>';
		$field .= '	<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" '.checked( $value, 1, false ) .'> '. $args['label'] . $required;
		$field .= '	</label>';
		$field .= '</div>';

		return $field;
	}

	/**
	 * Build WooCommerce radio with Bootstrap layout
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 * @return string
	 */
	public function bootstrap_form_field_radio( $field, $key, $args, $value ) {

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'storms'  ) . '">*</abbr>';
		} else {
			$required = '';
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$field = '';

		if ( ! empty( $args['options'] ) ) {
			$field .= '<div class="radio' . esc_attr( implode( ' ', $args['class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . '>';
			foreach ( $args['options'] as $option_key => $option_text ) {
				$field .= '	<label class="radio-inline">';
				$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />' . $option_text;
				$field .= '	</label>';
			}
			$field .= '</div>';
		}

		return $field;
	}

	//</editor-fold>

	//<editor-fold desc="Layout definitions">

	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 */
	public function before_content() {

        echo '<div class="row">';
		echo '<main id="content" class="main '. Template::main_layout() . '" role="main">';
	}

	/**
	 * After Content
	 * Closes the wrapping divs
	 */
	public function after_content() {

		echo '</main>';

		if( is_product() ) {
			get_sidebar('product');
		} else if( is_shop() || is_product_category() || is_product_tag() ) {
			get_sidebar('shop');
		}

        echo '</div>';
	}

	/**
	 * Define if the sidebar should be shown or not
	 * We remove the action every time, because um decide to show or not, on the layout code
	 */
	public function remove_sidebar() {
		if( is_woocommerce() ) {
			remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar');
		}
	}

	/**
	 * Customization for bootstrap breadcrumbs
	 */
	public function woocommerce_breadcrumb() {
        if( 'no' !== get_option( 'add_wc_breadcrumb_before_main_content', 'yes' ) ) {
            echo '<div class="row">';
            echo '<div class="col-12">';
            woocommerce_breadcrumb();
            echo '</div>';
            echo '</div>';
        }
	}

	/**
	 * Customization for bootstrap breadcrumbs
	 */
	public function woocommerce_breadcrumb_args( $args = array() ) {
		if ( get_option( 'customize_woo_breadcrumb' , true ) ) {
			return array(
				'delimiter' => '',
				'wrap_before' => '<ol class="breadcrumb woocommerce-breadcrumb" ' . (is_single() ? 'itemprop="breadcrumb"' : '') . '>',
				'wrap_after' => '</ol>',
				'before' => '<li class="breadcrumb-item">',
				'after' => '</li>',
				'home' => _x('Home', 'breadcrumb', 'storms'),
			);
		}
		return $args;
	}

	/**
	 * Product gallery thumnail columns
	 * @return integer number of columns
	 */
	public function product_thumbnail_columns() {
		return intval( apply_filters( 'storms_product_thumbnail_columns', 4 ) );
	}

	/**
	 * Products per page
	 * @return integer number of products
	 */
	public function products_per_page() {
		$numberColumns = $this->shop_loop_number_of_columns();

		if( $numberColumns == 3 ) {
			$default = 9;
		}
		elseif( $numberColumns > 2 ) {
			$default = 12;
		}

		return get_option( 'products_per_page', $default );
	}

	/**
	 * Default loop columns on product archives
	 * @return integer products per row
	 */
	public function shop_loop_number_of_columns() {
		global $woocommerce_loop;

		$columns = get_option( 'shop_loop_number_of_columns', 4 ); // Default is 4 products per row

		// If the number of columns is already setted (like, on a shortcode's parameter), we preserve it here
		if( isset( $woocommerce_loop['columns'] ) ) {
			$columns = $woocommerce_loop['columns'];
		}

        return $columns;
	}

	/**
	 * Register widget area on shop pages - create and sidebar-shop.php template to used
	 */
	public function register_widgets_area() {
		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = get_option('widget_title_tag', 'h3');

		register_sidebar( array(
			'name'          => __( 'Shop Sidebar', 'storms' ),
			'id'            => 'shop-sidebar',
			'description'   => __( 'Add widgets here to appear in your shop sidebar.', 'storms' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
			'after_title'   => '</' . $widget_title_tag . '>',
		) );

		register_sidebar( array(
			'name'          => __( 'Product Sidebar', 'storms' ),
			'id'            => 'product-sidebar',
			'description'   => __( 'Add widgets here to appear in your product sidebar.', 'storms' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
			'after_title'   => '</' . $widget_title_tag . '>',
		) );
	}

	/**
	 * Change number of related products on product page
	 * @see https://docs.woocommerce.com/document/change-number-of-related-products-output/
	 */
    public function related_products_args( $args ) {
		$args['posts_per_page'] = 3; // 3 related products
		$args['columns'] = apply_filters( 'woocommerce_related_products_columns', 3 ); // Default: arranged in 3 columns
		return $args;
	}

    /**
     * Change the number of products to be shown on cross-sells loop
     * @param $limit
     * @return int
     */
    public function cross_sells_limit( $limit ) {
        return 3; // @TODO Make this customizable
    }

    /**
     * Change the number of columns to be shown on cross-sells loop
     * @param $columns
     * @return int
     */
    public function cross_sells_columns( $columns ) {
        return 3; // @TODO Make this customizable
    }

	/**
	 * Get the classes for each product on the shop list, accordingly to the columns number
	 * @param array $classes Array of classes of the current post
	 * @return array Array of classes of the current post modified
	 */
	public function content_product_class( $classes ) {
		global $woocommerce_loop;

		// Verificamos se este eh um loop de products
		$is_products = false;
		if( isset( $woocommerce_loop['name'] ) &&
			$woocommerce_loop['name'] == 'products' ) {
			$is_products = true;
			$classes[] = $woocommerce_loop['name'];
		}

        // Verificamos se este eh um loop de related products
        $is_related = false;
        if( isset( $woocommerce_loop['name'] ) &&
            $woocommerce_loop['name'] == 'related' ) {
            $is_related = true;
            $classes[] = $woocommerce_loop['name'];
        }

        // Verificamos se este eh um loop de cross-sells
        $is_cross_sells = false;
        if( isset( $woocommerce_loop['name'] ) &&
            $woocommerce_loop['name'] == 'cross-sells' ) {
            $is_cross_sells = true;
            $classes[] = $woocommerce_loop['name'];
        }

        // Verificamos se este eh um loop de recent_products
		$recent_products = false;
        if( isset( $woocommerce_loop['name'] ) &&
			$woocommerce_loop['name'] == 'recent_products' ) {
			$recent_products = true;
			$classes[] = $woocommerce_loop['name'];
		}

		// Returns true when on the product archive page (shop)
		if( is_shop() || is_product_category() || is_product_tag() || $is_related || $is_cross_sells || $is_products || $recent_products ) {

			// How many columns we want to show on shop loop?
			$columns = $this->shop_loop_number_of_columns();

			// We show different number of columns if is a related products loop
			if( $is_related ) {
                $columns = apply_filters( 'woocommerce_related_products_columns', 3 );
            }

			switch ( $columns ) {
				case 6:
					$classes[] = 'col-6 col-sm-3 col-md-2';
					break;
				case 4:
					$classes[] = 'col-12 col-sm-6 col-md-3';
					break;
				case 3:
					$classes[] = 'col-12 col-sm-4 col-md-4';
					break;
				case 31:
					$classes[] = 'col-12 col-sm-6 col-md-4';
					break;
				case 2:
					$classes[] = 'col-12 col-sm-6 col-md-6';
					break;
				default:
					$classes[] = 'col-12 col-sm-12 col-md-12';
			}

		}

		return $classes;
	}

	/**
	 * @TODO Trabalho nao finalizado!
	 * Generate the necessary clearfixes, breaks, and rows for an responsive shop loop
	 * @link http://www.webdesign101.net/add-bootstrap-rows-woocommerce-loop/
	 * @param int $woocommerce_loop Index of the current item in the shop loop
	 */
	public function storms_wc_after_item_loop( $woocommerce_loop ) {
        global $woocommerce_loop;

        // Verificamos se este eh um produto no loop de related products
        $is_related = false;
        if( isset( $woocommerce_loop['name'] ) &&
            $woocommerce_loop['name'] == 'related' ) {
            $is_related = true;
        }

		// How many columns we want to show on shop loop?
		$columns = $this->shop_loop_number_of_columns();

        // We show different number of columns if is a related products loop
        if( $is_related ) {
            $columns = apply_filters( 'woocommerce_related_products_columns', 2 );
        }

		/*
		switch ( $woocommerce_loop ) {
			case 6:
				if(0 == ($woocommerce_loop % 6)) {
					echo '<div class="clearfix visible-md visible-lg"></div>';
				}
				if(0 == ($woocommerce_loop % 4)) {
					echo '<div class="clearfix visible-sm"></div>';
				}
				if(0 == ($woocommerce_loop % 2)) {
					echo '<div class="clearfix visible-xs"></div>';
				}
				break;
			case 4:
				if(0 == ($woocommerce_loop % 4)) {
					echo '<div class="clearfix visible-md visible-lg"></div>';
				}
				if(0 == ($woocommerce_loop % 2)) {
					echo '<div class="clearfix visible-sm"></div>';
				}
				break;
			case 3:
				if(0 == ($woocommerce_loop % 3)) {
					echo '<div class="clearfix visible-md visible-lg"></div>';
				}
				break;
			case 31:
				if(0 == ($woocommerce_loop % 3)) {
					echo '<div class="clearfix visible-md visible-lg"></div>';
				}
				if(0 == ($woocommerce_loop % 2)) {
					echo '<div class="clearfix visible-sm"></div>';
				}
				break;
			case 2:
				if(0 == ($woocommerce_loop % 2)) {
					echo '<div class="clearfix invisible-xs"></div>';
				}
				break;
			}
		*/

		if( $woocommerce_loop % $columns === 0 ) {
			echo '</div><div class="row">';
		}

	}

	//</editor-fold>
}
