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
	StormsFramework\Storms\Front\Layout;

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

			// @TODO Verificar a necessidade deste codigo
			//->add_action( 'after_switch_theme', 'woocommerce_image_dimensions', 1 );

        $this->loader
            ->add_filter( 'woocommerce_page_title', 'shop_page_title' )
            ->add_action( 'woocommerce_add_to_cart',  'add_to_cart_checkout_redirect', 11 )
            ->add_filter( 'woocommerce_product_tabs', 'remove_product_tabs', 98);

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

        // @TODO Verificar a necessidade deste codigo
		//add_shortcode( 'storms_featured_products', array( $this, 'featured_products' ) );
	}

	//<editor-fold desc="Styles and definitions">

    /**
     * Cleanup wp_head(), to remove unnecessary and unsafe wp meta tags
     * @TODO Check if this is necessary
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
     * Redirecionar cliente para a pagina de checkout, ao adicionar um produto no carrinho
     */
    function add_to_cart_checkout_redirect() {
        if( get_option( 'redirect_to_checkout_on_click_buy', false ) ) {
            wp_safe_redirect(get_permalink(get_option('woocommerce_checkout_page_id')));
            die();
        }
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
				$args['label_class'] = array('control-label');
				$args['custom_attributes'] = array( 'data-plugin' => 'select2', 'data-allow-clear' => 'true', 'aria-hidden' => 'true',  ); // Add custom data attributes to the form input itself
				break;

			case 'country' : /* By default WooCommerce will populate a select with the country names - $args defined for this specific input type targets only the country select element */
				$args['class'][] = 'form-group single-country';
				$args['label_class'] = array('control-label');
				break;

			case "state" : /* By default WooCommerce will populate a select with state names - $args defined for this specific input type targets only the country select element */
				$args['class'][] = 'form-group'; // Add class to the field's html element wrapper
				//$args['input_class'] = array('form-control'); // add class to the form input itself
				//$args['custom_attributes']['data-plugin'] = 'select2';
				$args['label_class'] = array('control-label');
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
				$args['label_class'] = array('control-label');
				break;

			case 'textarea' :
				$args['input_class'] = array('form-control');
				$args['label_class'] = array('control-label');
				break;

			case 'checkbox' :
				break;

			case 'radio' :
				break;

			default :
				$args['class'][] = 'form-group';
				$args['input_class'] = array('form-control');
				$args['label_class'] = array('control-label');
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

	/**
	 * Define image sizes
	 */
	public function woocommerce_image_dimensions() {

		global $pagenow;

		if ( ! isset( $_GET['activated'] ) || $pagenow != 'themes.php' ) {
			return;
		}

		$catalog = array(
			'width'  => '236', // px
			'height' => '236', // px
			'crop'	 => 1      // true
		);

		$single = array(
			'width'  => '527', // px
			'height' => '527', // px
			'crop'	 => 1      // true
		);

		$thumbnail = array(
			'width'  => '153', // px
			'height' => '153', // px
			'crop'	 => 1      // false
		);

		// Image sizes
		update_option( 'shop_catalog_image_size', $catalog ); // Product category thumbs
		update_option( 'shop_single_image_size', $single ); // Single product image
		update_option( 'shop_thumbnail_image_size', $thumbnail ); // Image gallery thumbs
	}

	//</editor-fold>

	//<editor-fold desc="Layout definitions">

	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 */
	public function before_content() {

		if( is_product() ) {
			$layout = get_option( 'product_layout', '2c-r' );
		} else if( is_shop() || is_product_category() || is_product_tag() ) {
			$layout = get_option( 'shop_layout', '2c-r' );
		}

		// Check if layout is a valid value - if it is not, then we default to '2c-r'
		if( ! in_array( $layout, [ '1c', '2c-r', '2c-l' ] ) ) {
			$layout = '2c-r';
		}

        echo '<div class="row">';
		echo '<main id="content" class="main '. Layout::main_layout( $layout ) . '" role="main">';
	}

	/**
	 * After Content
	 * Closes the wrapping divs
	 */
	public function after_content() {

		echo '</main>';

		if( is_product() ) {
			$layout = get_option( 'product_layout', '2c-r' );
		} else if( is_shop() || is_product_category() || is_product_tag() ) {
			$layout = get_option( 'shop_layout', '2c-r' );
		}

		// Check if layout is a valid value - if it is not, then we default to '2c-r'
		if( ! in_array( $layout, [ '1c', '2c-r', '2c-l' ] ) ) {
			$layout = '2c-r';
		}

		if( $layout != '1c' ) {

			// Define $storms_wc_page_layout as global, to be visible on sidebar-shop and sidebar-product
			global $storms_wc_page_layout;
			$storms_wc_page_layout = $layout;

			if( is_product() ) {
				get_sidebar('product');
			} else if( is_shop() || is_product_category() || is_product_tag() ) {
				get_sidebar('shop');
			}
		}

        echo '</div>';
	}

	/**
	 * Define if the sidebar should be shown or not
	 */
	public function remove_sidebar() {

		if( is_woocommerce() ) {

			// We remove the action every time, because um decide to show or not, on the layout code
			remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar');

		}

	}

	/**
	 * Customization for bootstrap breadcrumbs
	 */
	public function woocommerce_breadcrumb(){
        if( get_option( 'add_wc_breadcrumb_before_main_content', true ) ) {
            echo '<div class="row">';
            echo '<div class="col-xs-12">';
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
				'before' => '<li>',
				'after' => '</li>',
				'home' => _x('Home', 'breadcrumb', 'storms'),
			);
		} else {
			return $args;
		}
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

	//</editor-fold>

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
	 * Override the woocommerce's featured_products shortcode, to change the html output
	 */
	public function featured_products( $atts ) {
		//if ( is_woocommerce_activated() ) {
			global $woocommerce_loop;

			extract( shortcode_atts( array(
				'per_page' 	=> '12',
				'columns' 	=> '4',
				'orderby' 	=> 'date',
				'order' 	=> 'desc'
			), $atts ) );

			$args = array(
				'post_type'				=> 'product',
				'post_status' 			=> 'publish',
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' 		=> $per_page,
				'orderby' 				=> $orderby,
				'order' 				=> $order,
				'meta_query'			=> array(
					array(
						'key' 		=> '_visibility',
						'value' 	=> array('catalog', 'visible'),
						'compare'	=> 'IN'
					),
					array(
						'key' 		=> '_featured',
						'value' 	=> 'yes'
					)
				)
			);

			ob_start();

			$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

			$woocommerce_loop['columns'] = $columns;

			if ( $products->have_posts() ) :

				woocommerce_product_loop_start();

				while ( $products->have_posts() ) : $products->the_post();

					wc_get_template_part( 'content', 'product' );

				endwhile; // end of the loop.

				woocommerce_product_loop_end();

			endif;

			wp_reset_postdata();

			return '<div class="woocommerce col-md-12 columns-' . $columns . '">' . ob_get_clean() . '</div>';
		//}
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

        // Verificamos se este eh um produto no loop de related products
        $is_related = false;
        if( isset( $woocommerce_loop['name'] ) &&
            $woocommerce_loop['name'] == 'related' ) {
            $is_related = true;
            $classes[] = $woocommerce_loop['name'];
        }

        // Verificamos se este eh um produto no loop de cross sells
        $is_cross_sells = false;
        if( isset( $woocommerce_loop['name'] ) &&
            $woocommerce_loop['name'] == 'cross-sells' ) {
            $is_cross_sells = true;
            $classes[] = $woocommerce_loop['name'];
        }

		// Returns true when on the product archive page (shop)
		if( is_shop() || is_product_category() || is_product_tag() || $is_related || $is_cross_sells ) {

			// How many columns we want to show on shop loop?
			$columns = $this->shop_loop_number_of_columns();

			// We show different number of columns if is a related products loop
			if( $is_related ) {
                $columns = apply_filters( 'woocommerce_related_products_columns', 3 );
            }

			switch ( $columns ) {
				case 6:
					$classes[] = 'col-xs-6 col-sm-3 col-md-2';
					break;
				case 4:
					$classes[] = 'col-xs-12 col-sm-6 col-md-3';
					break;
				case 3:
					$classes[] = 'col-xs-12 col-sm-12 col-md-4';
					break;
				case 31:
					$classes[] = 'col-xs-12 col-sm-6 col-md-4';
					break;
				case 2:
					$classes[] = 'col-xs-12 col-sm-6 col-md-6';
					break;
				default:
					$classes[] = 'col-xs-12 col-sm-12 col-md-12';
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
}
