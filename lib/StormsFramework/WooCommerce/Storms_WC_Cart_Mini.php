<?php
/**
 * Storms Websolutions (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2019, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   4.0.0
 *
 * WC_Cart_Mini
 * This code creates the shop cart as a shortcode or widget
 */

namespace StormsFramework\WooCommerce;

use StormsFramework\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Storms_WC_Cart_Mini extends \WC_Widget
{
	public function register_widget() {
		register_widget( '\StormsFramework\WooCommerce\Storms_WC_Cart_Mini' );

		add_filter( 'woocommerce_add_to_cart_fragments', array( '\StormsFramework\WooCommerce\Storms_WC_Cart_Mini', 'wc_cart_mini_ajax_fragments' ), 10 );
	}

	public static function wc_cart_mini_ajax_fragments( $fragments ) {
		if ( ! defined('WOOCOMMERCE_CART') ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		// Select the html element to be replaced with the updated cart
		ob_start();
		the_widget( '\StormsFramework\WooCommerce\Storms_WC_Cart_Mini', [ 'wrap_widget' => 'no' ] );
		$fragments['div.storms-cart-contents'] = Helper::minify_html( ob_get_clean() );

		ob_start();
		the_widget( '\StormsFramework\WooCommerce\Storms_WC_Cart_Mini', [  'wrap_widget' => 'no' , 'show_products_list' => 'no' ] );
		$fragments['div.storms-cart-contents-no-list'] = Helper::minify_html( ob_get_clean() );

		return $fragments;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'Widget_WC_Cart_Mini storms_wc_cart_mini storms-wc-mini-cart';
		$this->widget_description = __( 'Shows a WooCommerce Mini Cart', 'storms' );
		$this->widget_id          = 'Storms_WC_Cart_Mini';
		$this->widget_name        = __( 'Storms WC Cart Mini', 'storms' );

		$this->settings = array(
			'show_products_list' => array(
				'type'  => 'select',
				'std'   => 'yes',
				'label' => __( 'Show products list - will appear as a dropdown', 'storms' ),
				'options' => array(
					'yes'   => __( 'yes', 'storms' ),
					'no'  => __( 'no', 'storms' )
				)
			),
			'extra_classes' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Extra class', 'storms' )
			)
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		parent::__construct();
	}

	public function frontend_scripts() {
		wp_enqueue_script('storms-wc-cart-mini-script',
			\StormsFramework\Helper::get_asset_url( '/js/storms-wc-mini-cart' . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . '.js' ),
			array( 'jquery' ), STORMS_FRAMEWORK_VERSION, true );

		// Add WordPress data to a Javascript file
		wp_localize_script( 'storms-wc-cart-mini-script', 'storms_wc_mini_cart_vars', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'wc_ajax_url' => \WC_AJAX::get_endpoint( "%%endpoint%%" ),
			'debug_mode' => defined( 'WP_DEBUG' ) && WP_DEBUG,
		] );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $woocommerce;

		$atts = [
			'wrap_widget'			=> ( ! empty( $instance['wrap_widget'] ) ) ? esc_attr( $instance['wrap_widget'] ) : 'yes',
			'show_products_list'    => ( ! empty( $instance['show_products_list'] ) ) ? esc_attr( $instance['show_products_list'] ) : 'yes',
			'extra_classes' 		=> esc_attr( $instance['extra_classes'] ?? '' ),
		];

		// We don't show the mini cart when the customer is on checkout
		if( is_checkout() ) {
			return;
		}

		// Make sure cart is loaded!
		// @see https://wordpress.org/support/topic/activation-breaks-customise?replies=10#post-7908988
		if ( empty( $woocommerce->cart ) ) {
			$woocommerce->cart = new \WC_Cart();
		}

		// $woocommerce->cart->get_cart_total() is not a display function,
		// so we add tax if cart prices are set to display incl. tax
		// see https://github.com/woothemes/woocommerce/issues/6701
		if ( $woocommerce->cart->display_cart_ex_tax ) {
			$cart_contents_total = wc_price( $woocommerce->cart->cart_contents_total );
		} else {
			$cart_contents_total = wc_price( $woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total );
		}
		$cart_contents_total = apply_filters( 'woocommerce_cart_contents_total', $cart_contents_total );

		// Get total of items on cart
		$cart_contents_count = $woocommerce->cart->get_cart_contents_count();

		if( 'yes' === $atts['show_products_list'] ) {
			$atts['extra_classes'] = 'storms-cart-contents ' . $atts['extra_classes'];
		} else {
			$atts['extra_classes'] = 'storms-cart-contents-no-list ' . $atts['extra_classes'];
		}

		$html  = '';
		$html .= '<div class="' . $atts['extra_classes'] . '">';
		$html .= '    <a class="cart-link" href="' . wc_get_cart_url() . '" title="' . __( 'View your shopping cart', 'storms' ) . '" aria-haspopup="true" aria-expanded="false">';
		//$html .= '        <i class="fa fa-shopping-cart" aria-hidden="true"></i> ';
		$html .= '        <i class="fa st-ic-shop-cart" aria-hidden="true"></i> ';
		$html .= '        <span class="cart-contents-count">' . esc_html( $cart_contents_count ) . '</span> ';
		$html .= '        <span class="cart-contents-total">' . strip_tags( $cart_contents_total ) . '</span>';
		$html .= '    </a>';
		if( 'yes' === $atts['show_products_list'] ) {

			// @see https://generatewp.com/snippet/nkVvvJ6/
			ob_start();
			woocommerce_mini_cart();
			$products_list = ob_get_clean();

			$html .= '    <div class="shopping_cart_dropdown">';
			$html .= '        <div class="widget_shopping_cart_content">';
			$html .= 		      $products_list;
			$html .= '        </div>';
			$html .= '    </div>';
		}
		$html .= '</div>';

		if( 'yes' === $atts['wrap_widget'] ) {
			$this->widget_start($args, $instance);
			echo $html;
			$this->widget_end( $args );
		} else {
			echo $html;
		}
	}

}
