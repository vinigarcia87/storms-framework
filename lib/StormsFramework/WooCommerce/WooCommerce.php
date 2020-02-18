<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2019, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   4.0.0
 *
 * WooCommerce class
 * @package StormsFramework
 *
 * Add WooCommerce support
 * @see  _documentation/WooCommerce_Class.md
 */

namespace StormsFramework\WooCommerce;

use StormsFramework\Base,
	StormsFramework\Template;
use StormsFramework\Helper;

class WooCommerce extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public function define_hooks() {

		$this->loader
			->add_action( 'init', 'support_woocommerce_gallery' );

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
			->add_action( 'woocommerce_before_account_navigation', 'open_row_myaccount_dashboard' )
			->add_action( 'woocommerce_account_dashboard', 'close_row_myaccount_dashboard' )

			->add_action( 'woocommerce_before_shop_loop', 'woocommerce_before_shop_loop_open', 1 )
			->add_action( 'woocommerce_before_shop_loop', 'woocommerce_before_shop_loop_close', 99 )
			->add_action( 'woocommerce_after_shop_loop', 'woocommerce_after_shop_loop_open', 1 )
			->add_action( 'woocommerce_after_shop_loop', 'woocommerce_after_shop_loop_close', 99 )

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
            ->add_filter( 'woocommerce_cross_sells_columns', 'cross_sells_columns' )
			->add_action( 'widgets_init', 'register_widgets_area' );

		$this->loader
			->add_filter( 'woocommerce_form_field_args', 'bootstrap_form_field_args', 10, 3 )
			->add_filter( 'woocommerce_form_field_checkbox', 'bootstrap_form_field_checkbox', 10, 4 )
			->add_filter( 'woocommerce_form_field_radio', 'bootstrap_form_field_radio', 10, 4 )
			->add_filter( 'woocommerce_form_field_country', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_state', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_textarea', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_checkbox', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_password', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_text', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_email', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_tel', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_number', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_select', 'clean_checkout_fields_class_attribute_values', 20, 4 )
			->add_filter( 'woocommerce_form_field_radio', 'clean_checkout_fields_class_attribute_values', 20, 4 );

		$this->loader
			->add_action( 'post_class', 'content_product_class' )
			->add_action( 'product_cat_class', 'content_product_class' )
			//->add_action( 'storms_wc_after_item_loop', 'storms_wc_after_item_loop' )
		;

		$this->loader
			->add_action( 'init', 'prevent_wp_login' )
			->add_action( 'template_redirect', 'force_login_registration_page_on_checkout' )
			->add_filter( 'woocommerce_login_redirect', 'user_redirect_on_login_registration', 10, 2 )
			->add_filter( 'woocommerce_registration_redirect', 'user_redirect_on_login_registration', 10, 2 )
			->add_filter( 'body_class', 'set_intern_login_body_class' )
			->add_action( 'template_redirect', 'bypass_logout_confirmation' );

	}

	//<editor-fold desc="Styles and definitions">

	/**
	 * Declare WooCommerce support
	 * Enable WooCommerce gallery features
	 */
	public function support_woocommerce_gallery() {

		// Enabling product gallery features - zoom, swipe, lightbox
		// https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
		if( 'yes' == get_option( 'use_wc_product_gallery', 'yes' ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}
	}

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
        if( 'yes' === get_option( 'use_wc_styles', 'no' ) ) {
            return $enqueue_styles;
        } else {
            return array();
        }
	}

    /**
     * Optimize WooCommerce Scripts
     * Remove WooCommerce Generator tag, styles, and scripts from non WooCommerce pages.
	 * @see https://crunchify.com/how-to-stop-loading-woocommerce-js-javascript-and-css-files-on-all-wordpress-postspages/
     */
    public function manage_woocommerce_scripts() {

		// Check if we should apply this modifications
		if( 'no' === get_option( 'manage_woocommerce_scripts', 'yes' ) ) {
			return false;
		}

		// Remove CSS and/or JS for Select2 used by WooCommerce, see https://gist.github.com/Willem-Siebe/c6d798ccba249d5bf080.
		if( 'no' === get_option( 'manage_woocommerce_selectWoo_scripts', 'no' ) ) {
			wp_dequeue_style('selectWoo');
			wp_deregister_style('selectWoo');

			wp_dequeue_script('selectWoo');
			wp_deregister_script('selectWoo');
		}

        // Dequeue scripts and styles
		if ( ! is_front_page() && ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) ) {

			// Dequeue WooCommerce styles
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-smallscreen' );

			// Dequeue WooCommerce scripts
			wp_dequeue_script('wc-cart-fragments');
			wp_dequeue_script('woocommerce');
			wp_dequeue_script('wc-add-to-cart');

			wp_deregister_script( 'js-cookie' );
			wp_dequeue_script( 'js-cookie' );

        }

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

        if( 'yes' == get_option( 'remove_tab_description', 'no' ) ) {
            unset( $tabs['description'] );
        }

        if( 'yes' == get_option( 'remove_tab_reviews', 'no' ) ) {
            unset( $tabs['reviews'] );
        }

        if( 'yes' == get_option( 'remove_tab_additional_information', 'no' ) ) {
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

		if( 'yes' == get_option( 'prevent_wp_login', 'yes' ) ) {

			// Check if a $_GET['action'] is set, and if so, load it into $action variable
			$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';
			// Check if we're on the login page, and ensure the action is not 'logout'
			if( $pagenow == 'wp-login.php' &&
				( !$action || ( $action && !in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass' ) ) ) )
			) {

				// Load the 'myaccount' page url
				$login_page = wc_get_page_permalink( 'myaccount' );

				// Check to see if is a wp-admin login dialog
				$is_intern_login = isset( $_GET['interim-login'] ) && 1 == $_GET['interim-login'];
				if( $is_intern_login ) {
					$login_page = esc_url( add_query_arg( 'interim-login', '1', $login_page ) );
				}

				// Redirect to the selected page
				wp_redirect( $login_page );
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
		if( !is_user_logged_in() && is_checkout() && 'yes' == get_option( 'force_login_registration_page_on_checkout', 'yes' ) ) {
			$myaccount = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			$login_page = esc_url( add_query_arg( 'return_to', 'checkout', $myaccount ) );
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

		// In case of internal login dialog, we want to redirect back to the login page to allow default wp behaviour
		$is_intern_login = isset( $_GET['interim-login'] ) && 1 == $_GET['interim-login'];
		if( $is_intern_login ) {
			$myaccount = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			$login_page = esc_url( add_query_arg( 'interim-login', '1', $myaccount ) );
			return $login_page;
		}

		$checkout = wc_get_checkout_url();

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
	 * When is a internal login dialog, set a body class
	 * so we can customize the login page
	 *
	 * @param $classes
	 * @return array
	 */
	function set_intern_login_body_class( $classes ) {

		if( is_account_page() && ( isset( $_GET['interim-login'] ) && 1 == $_GET['interim-login'] ) ) {

			$classes[] = 'interim-login';

			// When login was successfully on internal login dialog, this class will make wp close the dialog
			if( is_user_logged_in() ) {
				$classes[] = 'interim-login-success';
			}
		}
		return $classes;
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
		<p class="form-row-wide form-group">
			<label for="reg_password2"><?php _e( 'Confirmar senha', 'storms' ); ?> <span class="required">*</span></label>
			<input type="password" class="input-text form-control" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
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

		$field  = '';
		$field .= '<div class="form-check ' . esc_attr( implode( ' ', $args['class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . '>';
		$field .= '	<input type="' . esc_attr( $args['type'] ) . '" class="form-check-input ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" '.checked( $value, 1, false ) .'/> ';
		$field .= '	<label for="' . esc_attr( $args['id'] ) . '">'. $args['label'] . $required . '</label>';
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

	/**
	 * Remove the class "form-row" from all checkout fields
	 * It conflicts with Bootstrap 4
	 *
	 * @param $field
	 * @param $key
	 * @param $args
	 * @param $value
	 * @return mixed
	 */
	public function clean_checkout_fields_class_attribute_values( $field, $key, $args, $value ) {
		if( is_checkout() ) {
			// remove "form-row"
			$field = str_replace( array( '<p class="form-row ' ), array( '<p class="' ), $field );
		}
		return $field;
	}

	/**
	 * Remove the class "form-row" from all checkout fields
	 * It conflicts with Bootstrap 4
	 *
	 * @param $fields
	 * @return mixed
	 */
	function custom_checkout_fields_class_attribute_value( $fields ) {
		foreach( $fields as $fields_group_key => $group_fields_values ) {
			foreach( $group_fields_values as $field_key => $field ) {
				// Remove other classes (or set yours)
				$fields[$fields_group_key][$field_key]['class'] = array();
			}
		}
		return $fields;
	}

	//</editor-fold>

	//<editor-fold desc="Layout definitions">

	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 */
	public function before_content() {

        echo '<div class="st-grid-row row">';
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
	 * Incluindo uma row em volta do dashboard do my account - abertura
	 */
	public function open_row_myaccount_dashboard() {
		echo '<div class="st-grid-row row myaccount-dashboard">';
	}

	/**
	 * Incluindo uma row em volta do dashboard do my account - fechamento
	 */
	public function close_row_myaccount_dashboard() {
		echo '</div>';
	}

	/**
	 * Opening row and col-12 before result count and catolog ordering in woocommerce_before_shop_loop
	 * and before woocommerce_pagination in woocommerce_after_shop_loop
	 */
	public function woocommerce_before_shop_loop_open() {
		echo '<div class="st-grid-row row woocommerce-loop-header"><div class="col-12">';
	}

	/**
	 * Closing row and col-12 after result count and catolog ordering in woocommerce_before_shop_loop
	 * and after woocommerce_pagination in woocommerce_after_shop_loop
	 */
	public function woocommerce_before_shop_loop_close() {
		echo '</div></div>';
	}

	/**
	 * Opening row and col-12 before result count and catolog ordering in woocommerce_before_shop_loop
	 * and before woocommerce_pagination in woocommerce_after_shop_loop
	 */
	public function woocommerce_after_shop_loop_open() {
		echo '<div class="st-grid-row row woocommerce-loop-footer"><div class="col-12">';
	}

	/**
	 * Closing row and col-12 after result count and catolog ordering in woocommerce_before_shop_loop
	 * and after woocommerce_pagination in woocommerce_after_shop_loop
	 */
	public function woocommerce_after_shop_loop_close() {
		echo '</div></div>';
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
        if( 'yes' == get_option( 'add_wc_breadcrumb_before_main_content', 'yes' ) ) {
            echo '<div class="st-grid-row row">';
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

		if( ! isset( $woocommerce_loop['name'] ) ) {
			return $classes;
		}

		$is_products = false;
		$is_related = false;
		$is_cross_sells = false;
		$recent_products = false;
		switch ( $woocommerce_loop['name'] ) {
			// Verificamos se este eh um loop de products
			case '':
			case 'products':
				$is_products = true;
				break;

			// Verificamos se este eh um loop de related products
			case 'related':
				$is_related = true;
				break;

			// Verificamos se este eh um loop de cross-sells
			case 'cross-sells':
				$is_cross_sells = true;
				break;

			// Verificamos se este eh um loop de recent_products
			case 'recent_products':
				$recent_products = true;
				break;

			default:
				Helper::debug( 'content_product_class function found not listed wc loop name: ' . $woocommerce_loop['name'] );

		}
		$classes[] = $woocommerce_loop['name'];

		// Returns true when on a products list
		if( $is_products || $is_related || $is_cross_sells ||$recent_products ) {

			// How many columns we want to show on shop loop?
			$columns = $this->shop_loop_number_of_columns();

			// We show different number of columns if is a related products loop
			if( $is_related ) {
                $columns = apply_filters( 'woocommerce_related_products_columns', 3 );
            }

			switch ( $columns ) {
				case 6:
					$classes[] = 'st-product-col-1 col-6 col-sm-3 col-md-2';
					break;
				case 4:
					$classes[] = 'st-product-col-2 col-12 col-sm-6 col-md-3';
					break;
				case 3:
					$classes[] = 'st-product-col-3 col-12 col-sm-4 col-md-4';
					break;
				case 31:
					$classes[] = 'st-product-col-4 col-12 col-sm-6 col-md-4';
					break;
				case 2:
					$classes[] = 'st-product-col-5 col-12 col-sm-6 col-md-6';
					break;
				default:
					$classes[] = 'st-product-col-6 col-12 col-sm-12 col-md-12';
			}

		}

		return $classes;
	}

	/**
	 * Generate the necessary clearfixes, breaks, and rows for an responsive shop loop
	 * TODO Trabalho nao finalizado!
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
			echo '</div><div class="st-grid-row row">';
		}

	}

	//</editor-fold>
}
