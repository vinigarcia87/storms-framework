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
            ->add_filter( 'woocommerce_product_tabs', 'remove_product_tabs', 98);

		$this->loader
			//->add_action( 'woocommerce_checkout_init', 'checkout_confirm_password_add_field', 10, 1 )
			->add_action( 'woocommerce_register_form', 'registration_confirm_password_add_field' )
			->add_filter( 'woocommerce_registration_errors', 'registration_confirm_password_validation', 10, 3 )
			->add_action( 'woocommerce_after_checkout_validation', 'checkout_confirm_password_validation', 10, 2 );

		$this->loader
			// wp_list_comments parameters in single-product-reviews.php
			->add_filter( 'woocommerce_product_review_list_args', 'product_review_list_args' )
			// Comment Form customization - wp-includes/comment-template.php
			->add_filter( 'comment_form_fields', 'product_review_comment_form_fields', 15 );

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
			->add_action( 'init', 'remove_sidebar' );

		/**
		 * Filters
		 * @see  product_thumbnail_columns()
		 * @see  products_per_page()
		 * @see  shop_loop_number_of_columns()
		 */
		$this->loader
			->add_action( 'widgets_init', 'register_widgets_area' )

			->add_filter( 'woocommerce_product_thumbnails_columns', 'product_thumbnail_columns' )
			->add_filter( 'loop_shop_per_page', 'products_per_page' )
			->add_filter( 'loop_shop_columns', 'shop_loop_number_of_columns' )
			->add_filter( 'woocommerce_output_related_products_args', 'related_products_on_product_page_args' )
			->add_filter( 'woocommerce_upsell_display_args', 'upsell_on_product_page_args' )

            ->add_filter( 'woocommerce_cross_sells_total', 'cross_sells_limit' )
            ->add_filter( 'woocommerce_cross_sells_columns', 'cross_sells_columns' );

		$this->loader
			// @see plugins/woocommerce/includes/wc-template-functions.php - woocommerce_form_field
			->add_filter( 'woocommerce_form_field_args', 'bootstrap_form_field_args', 10, 3 )
			->add_filter( 'woocommerce_form_field_checkbox', 'bootstrap_form_field_checkbox', 10, 4 )
			->add_filter( 'woocommerce_form_field_radio', 'bootstrap_form_field_radio', 10, 4 );

		$this->loader
			->add_action( 'post_class', 'content_product_class' )
			->add_action( 'product_cat_class', 'content_product_class' )
			//->add_action( 'storms_wc_after_item_loop', 'storms_wc_after_item_loop' )
		;

		$this->loader
			->add_action( 'init', 'prevent_wp_login' )
			->add_action( 'template_redirect', 'force_login_registration_page_on_checkout' )
			->add_filter( 'woocommerce_login_redirect', 'user_redirect_on_login_registration', 10 )
			->add_filter( 'woocommerce_registration_redirect', 'user_redirect_on_login_registration', 10 )
			->add_filter( 'body_class', 'set_intern_login_body_class' )
			->add_action( 'template_redirect', 'bypass_logout_confirmation' )
			->add_filter( 'password_change_email', 'password_change_email', 10, 3 )
			->add_filter( 'email_change_email', 'password_change_email', 10, 3 )
			->add_action( 'woocommerce_init', 'force_non_logged_user_wc_session' );

		$this->loader
			->add_action( 'init', 'register_custom_order_status' )
			->add_filter('wc_order_statuses', 'register_custom_orders_status')
			->add_filter( 'woocommerce_admin_order_actions', 'add_custom_order_status_buttons_to_order_admin_actions', 10, 3 )
			->add_action( 'current_screen', 'add_style_for_custom_order_status_on_orders_admin_page' );

	}

	//<editor-fold desc="Styles and definitions">

	/**
	 * Declare WooCommerce support
	 * Enable WooCommerce gallery features
	 */
	public function support_woocommerce_gallery() {

		// Enabling product gallery features - zoom, swipe, lightbox
		// https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
		if( 'yes' == Helper::get_option( 'storms_use_wc_product_gallery', 'yes' ) ) {
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
        if( 'yes' === Helper::get_option( 'storms_use_wc_styles', 'no' ) ) {
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
		if( 'no' === Helper::get_option( 'storms_manage_woocommerce_scripts', 'yes' ) ) {
			return false;
		}

		// Remove CSS and/or JS for Select2 used by WooCommerce, see https://gist.github.com/Willem-Siebe/c6d798ccba249d5bf080.
		if( 'no' === Helper::get_option( 'storms_manage_woocommerce_selectWoo_scripts', 'no' ) ) {
			wp_dequeue_style('selectWoo');
			wp_deregister_style('selectWoo');

			wp_dequeue_script('selectWoo');
			wp_deregister_script('selectWoo');
		}

		// Dequeue WooCommerce styles
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-smallscreen' );

		// Dequeue scripts
		if( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			wp_dequeue_script( 'woocommerce' );
			wp_dequeue_script( 'js-cookie' );
		}

        /*
		if ( ! is_front_page() && ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			// Dequeue WooCommerce scripts
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'wc-add-to-cart' );
        }
        */
    }

    /**
     * Disable default WooCommerce tabs on product single page
     */
    function remove_product_tabs( $tabs ) {

        if( 'yes' == Helper::get_option( 'storms_remove_tab_description', 'no' ) ) {
            unset( $tabs['description'] );
        }

        if( 'yes' == Helper::get_option( 'storms_remove_tab_reviews', 'no' ) ) {
            unset( $tabs['reviews'] );
        }

        if( 'yes' == Helper::get_option( 'storms_remove_tab_additional_information', 'no' ) ) {
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

		if( 'yes' === Helper::get_option( 'storms_prevent_wp_login', 'yes' ) ) {

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
	 * when woocommerce has guest checkout disable
	 * @see https://stackoverflow.com/a/39357627/1003020
	 * @see https://wordpress.stackexchange.com/a/109097/54025
	 */
	public function force_login_registration_page_on_checkout() {
		// Case 1: Non logged user on checkout page
		if( !is_user_logged_in() && is_checkout() && 'no' === get_option( 'woocommerce_enable_guest_checkout' ) ) {

			$myaccount = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			$login_page = esc_url( add_query_arg( 'return_to', 'checkout', $myaccount ) );

			// Redirect to the selected page
			wp_redirect( $login_page );

			// Stop execution to prevent the page loading for any reason
			exit();
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
	public function user_redirect_on_login_registration( $redirect ) {

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
		<p class="form-row-wide">
			<label class="form-label" for="reg_password2"><?php _e( 'Confirmar senha', 'storms' ); ?> <span class="required">*</span></label>
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
	 *
	 * TODO This is triggering an error - Must change the implementation to:
	 * 		https://stackoverflow.com/questions/48168359/enable-confirmation-password-in-woocommerce-checkout-form
	 * 		Can't test right now 'cause storms theme is forcing register before checkout
	 *
	 * @param \WC_Checkout $checkout
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

	/**
	 * wp_list_comments parameters in single-product-reviews.php
	 *
	 * @return array
	 */
	public function product_review_list_args() {
		return array(
			'style'         => 'ul',
			'short_ping'    => true,
			'avatar_size'   => '64',
			'allow_reply'   => false,
			'walker'        => new \StormsFramework\Bootstrap\WP_Bootstrap_Commentwalker(),
		);
	}

	/**
	 * @param $comment_form
	 * @return mixed
	 */
	public function product_review_comment_form_fields( $comment_fields ) {

		if ( ! is_woocommerce() ) {
			return $comment_fields;
		}

		if ( wc_review_ratings_enabled() ) {
			$comment_fields['comment'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
					<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
					<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
					<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
					<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
					<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
					<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
				</select></div>';
		}

		$comment_fields['comment'] .= '<p class="comment-form-comment"><label for="comment" class="form-label">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

		return $comment_fields;
	}

	/**
	 * Edit Wordpress user's Password/Email Change email to look like WooCommerce email
	 * This email is sent when admin change/reset a user's password or email on admin panel
	 *
	 * @param $email_change_email
	 * @param $user
	 * @param $userdata
	 * @return mixed
	 */
	public function password_change_email( $email_change_email, $user, $userdata ) {
		$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		// Load the mailer class
		$mailer = WC()->mailer();

		// Create a new email
		$email = new \WC_Email();

		$subject = __( '[%s] Password Changed' );
		$email_change_email['subject'] = sprintf( $subject, $blog_name );

		$from_name   = ( '' !== get_site_option( 'site_name' ) ) ? esc_html( get_site_option( 'site_name' ) ) : 'WordPress';
		$admin_email = get_site_option( 'admin_email' );
		$email_change_email['headers'] = [];
		$email_change_email['headers'][] = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/html; charset="' . get_option( 'blog_charset' ) . "\"\n";

		// Wrap the content with the email template and then add styles
		$email_change_email['message'] = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( '', $email_change_email['message'] ) ) );

		return $email_change_email;
	}

	/**
	 * Initiate Woocommerce User session when it's not logged in
	 */
	public function force_non_logged_user_wc_session() {

		if( 'yes' === Helper::get_option( 'storms_force_non_logged_user_wc_session', 'no' ) ) {

			if (is_user_logged_in() || is_admin()) {
				return;
			}

			if (isset(WC()->session) && !WC()->session->has_session()) {
				WC()->session->set_customer_session_cookie(true);
			}

		}
	}

	//</editor-fold>

	//<editor-fold desc="Bootstrap on form fields">

	/**
	 * WooCommerce - Modify each individual input type $args defaults
	 * to include Bootstrap classes
	 * Source: http://stackoverflow.com/a/36724593/1003020
	 *
	 * @param $args
	 * @param $key
	 * @param null $value
	 * @return mixed
	 */
	public function bootstrap_form_field_args( $args, $key, $value = null ) {

		// Start field type switch case
		switch ( $args['type'] ) {
			case 'select' :
			case 'state':
				$args['input_class'][] = 'form-select';
				$args['label_class'][] = 'form-label';
				break;

			case 'country':
				$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

				if ( 1 === count( $countries ) ) {
					$args['class'][] = 'single-country';
					$args['label_class'][] = 'form-label';
				} else {
					$args['input_class'][] = 'form-select';
					$args['label_class'][] = 'form-label';
				}

			case 'checkbox' :
			case 'radio' :
				break;

			default :
				$args['input_class'][] = 'form-control';
				$args['label_class'][] = 'form-label';
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

		if ( is_null( $value ) ) {
			$value = $args['default'];
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
		$field .= '	<label class="form-check-label" for="' . esc_attr( $args['id'] ) . '">'. $args['label'] . $required . '</label>';
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
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling
		$custom_attributes = array();

		$external_div_class = '';
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				if( $attribute == 'external_div_class' ) {
					$external_div_class = esc_attr( $attribute_value );
				} else {
					$custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
				}
			}
		}

		$field = '';

		if ( ! empty( $args['options'] ) ) {
			$sort            = $args['priority'] ? $args['priority'] : '';
			$field_container = '<div class="form-row ' . $external_div_class . '" id="%1$s" data-priority="' . esc_attr( $sort ) . '">%2$s</div>';

			$label_id        = $args['id'];
			$label_id .= '_' . current( array_keys( $args['options'] ) );

			$name = esc_attr( $key );
			$custom_attr = implode( ' ', $custom_attributes );
			$input_class = esc_attr( implode( ' ', $args['input_class'] ) );

			// Default to form-check, you can use also form-check-inline
			$div_class = esc_attr( implode( ' ', $args['class'] ) );
			if( ! in_array( array( 'form-check', 'form-check-inline' ), $args['class'] ) ) {
				$div_class = 'form-check ' . $div_class;
			}

			foreach ( $args['options'] as $option_key => $option_text ) {
				$id = esc_attr( $args['id'] ) . '_' . esc_attr( $option_key );
				$checked = checked( $value, $option_key, false );

				$field .= '<div class="' . esc_attr( $div_class ) . '" ' . $custom_attr . '>';
				$field .= '<input class="form-check-input ' . esc_attr( $input_class ) . '" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $option_key ) . '" ' . $checked . '>';
				$field .= '<label class="form-label form-check-label" for="' . esc_attr( $id ) . '">' . esc_html( $option_text ) . '</label>';
				$field .= '</div>';
			}
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="form-label ' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field_html .= $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_id, $field_html );
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

        echo '<div class="st-grid-row row">';
		echo '<main id="content" class="main '. Template::main_layout() . '" role="main">';
	}

	/**
	 * After Content
	 * Closes the wrapping divs
	 */
	public function after_content() {

		echo '</main>';

		if( apply_filters( 'storms_show_product_sidebar', is_product() ) ) {
			get_sidebar('product');
		} else if( apply_filters( 'storms_show_shop_sidebar', is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
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
        if( 'yes' == Helper::get_option( 'storms_add_wc_breadcrumb_before_main_content', 'yes' ) ) {
        	?>
			<div class="<?php echo esc_attr( Helper::get_option( 'storms_woo_breadcrumb_container_class' , '' ) ); ?> st-container-breadcrumb">
				<div class="st-grid-row row">
					<div class="col-12">
						<?php woocommerce_breadcrumb(); ?>
					</div>
				</div>
			</div>
			<?php
        }
	}

	/**
	 * Customization for bootstrap breadcrumbs
	 */
	public function woocommerce_breadcrumb_args( $args = array() ) {
		if ( Helper::get_option( 'storms_customize_woo_breadcrumb' , true ) ) {
			return array(
				'delimiter' => '',
				'wrap_before' => '<ol class="breadcrumb woocommerce-breadcrumb" ' . ( is_single() ? 'itemprop="breadcrumb"' : '' ) . '>',
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

		return Helper::get_option( 'storms_products_per_page', $default );
	}

	/**
	 * Default loop columns on product archives
	 * @return integer products per row
	 */
	public function shop_loop_number_of_columns() {
		global $woocommerce_loop;

		$columns = Helper::get_option( 'storms_shop_loop_number_of_columns', 4 ); // Default is 4 products per row

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
		$widget_title_tag = Helper::get_option( 'storms_widget_title_tag', 'span' );

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
    public function related_products_on_product_page_args( $args ) {
		$args['posts_per_page'] = Helper::get_option( 'storms_related_products_limit', 3 ); // 3 related products
		$args['columns'] = apply_filters( 'woocommerce_related_products_columns', Helper::get_option( 'storms_related_products_columns', 3 ) ); // Default: arranged in 3 columns
		return $args;
	}

	/**
	 * Change number of upsell products on product page
	 */
	public function upsell_on_product_page_args( $args ) {
		$args['posts_per_page'] = Helper::get_option( 'storms_upsells_limit', 3 ); // 3 related products
		$args['columns'] = apply_filters( 'woocommerce_upsells_columns', Helper::get_option( 'storms_upsells_columns', 3 ) ); // Default: arranged in 3 columns
		return $args;
	}

    /**
     * Change the number of products to be shown on cross-sells loop
	 * Displayed on cart page
     * @param $limit
     * @return int
     */
    public function cross_sells_limit( $limit ) {
        return Helper::get_option( 'storms_cross_sells_limit', 3 );
    }

    /**
     * Change the number of columns to be shown on cross-sells loop
	 * Displayed on cart page
     * @param $columns
     * @return int
     */
    public function cross_sells_columns( $columns ) {
        return Helper::get_option( 'storms_cross_sells_columns', 3 );
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
		$is_up_sells = false;
		$recent_products = false;
		$featured_products = false;
		$sale_products = false;
		$product_categories = false;
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

			// Verificamos se este eh um loop de featured products
			case 'featured_products':
				$featured_products = true;
				break;

			// Verificamos se este eh um loop de cross-sells
			case 'cross-sells':
				$is_cross_sells = true;
				break;

			// Verificamos se este eh um loop de up-sells
			case 'up-sells':
				$is_up_sells = true;
				break;

			// Verificamos se este eh um loop de recent_products
			case 'recent_products':
				$recent_products = true;
				break;

			case 'sale_products':
				$sale_products = true;
				break;

			case 'product_categories':
				$product_categories = true;
				break;

			default:
				Helper::debug( 'content_product_class function found not listed wc loop name: ' . $woocommerce_loop['name'] );

		}
		$classes[] = $woocommerce_loop['name'];

		// Returns true when on a products list
		if( $is_products || $is_related || $featured_products || $is_cross_sells || $is_up_sells || $recent_products || $sale_products || $product_categories ) {

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

	//<editor-fold desc="Custom Order Status">

	/**
	 * Return a list of new custom order status to be added
	 * Status slug can only be a maximum of 20 characters - WooCommerce status are always prefixed with wc-
	 * @source: https://jilt.com/blog/woocommerce-custom-order-status-2/
	 *
	 * @return array
	 */
	private function get_custom_order_status() {
		return apply_filters( 'storms_wc_custom_order_status_list', [] );
	}

	/**
	 * Registering our custom status as a post status in WordPress
	 * @source: https://www.sellwithwp.com/woocommerce-custom-order-status-2/
	 */
	public function register_custom_order_status() {

		$custom_order_status_list = $this->get_custom_order_status();

		if( ! empty( $custom_order_status_list ) ) {
			foreach( $custom_order_status_list as $custom_status ) {
				$slug = $custom_status['slug'];
				$label = $custom_status['label'];

				register_post_status( $slug, array(
					'label' 					=> $label,
					'public' 					=> true,
					'exclude_from_search' 		=> false,
					'show_in_admin_all_list' 	=> true,
					'show_in_admin_status_list' => true,
					'label_count' 				=> _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>')
				) );
			}
		}
	}

	/**
	 * Add new status to list of WC Order statuses
	 * source: https://www.sellwithwp.com/woocommerce-custom-order-status-2/
	 *
	 * @param $order_statuses
	 * @return array
	 * @throws \Exception
	 */
	public function register_custom_orders_status( $order_statuses ) {

		$new_order_statuses = array();

		$custom_order_status_list = $this->get_custom_order_status();

		if( ! empty( $custom_order_status_list ) ) {

			foreach( $order_statuses as $key => $status ) {

				$new_order_statuses[$key] = $status;

				// Add new order status after processing
				if( 'wc-processing' === $key ) {
					foreach( $custom_order_status_list as $custom_status ) {
						$slug = $custom_status['slug'];
						$label = $custom_status['label'];

						$new_order_statuses[$slug] = $label;
					}
				}
			}
		}
		return $new_order_statuses;
	}

	/**
	 * Add Button Actions in Order list
	 *
	 * @param array $actions
	 * @param \WC_Order $the_order
	 * @return array
	 */
	public function add_custom_order_status_buttons_to_order_admin_actions( $actions, $the_order ) {

		$custom_order_status_list = $this->get_custom_order_status();

		if( ! empty( $custom_order_status_list ) ) {

			$key = 0;
			do {
				// Se for o primeiro item da nossa lista custom, faremos ele aparecer depois do 'processing'
				$action_anterior = $key > 0 ? str_replace( 'wc-', '', $custom_order_status_list[$key - 1]['slug'] ) : 'processing';
				if( $the_order->has_status( [ $action_anterior ] ) ) {

					// Removemos outros passos, para garantir que nosso custom status seja o unico valido
					$actions = [];

					// Se for o ultimo item da nossa lista custom, faremos que o proximo passo seja o 'completed'
					$action = '';
					if( $key < count( $custom_order_status_list ) ) {
						$slug = $custom_order_status_list[$key]['slug'];
						$label = $custom_order_status_list[$key]['label'];

						$action = str_replace( 'wc-', '', $slug );
					} else if( $key == count( $custom_order_status_list ) ) {
						$action = 'complete';
						$label = __( 'Complete', 'woocommerce' );
					}

					if( ! empty( $action ) ) {
						$actions[$action] = array(
							'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $action . '&order_id=' . $the_order->get_id() ), 'woocommerce-mark-order-status' ),
							'name'   => $label,
							'action' => $action,
						);
					}
				}

				$key++;
			} while( $key <= count( $custom_order_status_list ) );
		}
		return $actions;
	}

	/**
	 * Add a style for custom order status action buttons and status alert box on woocommerce orders page
	 */
	function add_style_for_custom_order_status_on_orders_admin_page() {
		$current_screen = get_current_screen();

		if( 'edit' == $current_screen->base && 'shop_order' == $current_screen->post_type ) {

			echo '<style>';
			echo apply_filters( 'storms_wc_custom_order_status_style', '' );
			echo '</style>';

		}
	}

	//</editor-fold>
}
