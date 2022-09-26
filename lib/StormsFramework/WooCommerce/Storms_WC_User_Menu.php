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
 * WC_User_Menu
 * This code creates a user menu as a shortcode or widget
 */

namespace StormsFramework\WooCommerce;

use StormsFramework\Widget\Storms_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Storms_WC_User_Menu extends Storms_Widget
{
	public function register_widget() {
		register_widget( '\StormsFramework\WooCommerce\Storms_WC_User_Menu' );
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'Widget_WC_User_Menu storms_wc_user_menu storms-wc-user-menu';
		$this->widget_description = __( 'Shows a WooCommerce User Menu', 'storms' );
		$this->widget_id          = 'Storms_WC_User_Menu';
		$this->widget_name        = __( 'Storms WC User Menu', 'storms' );

		$this->settings = array(
//			'show_products_list' => array(
//				'type'  => 'select',
//				'std'   => 'yes',
//				'label' => __( 'Show products list - will appear as a dropdown', 'storms' ),
//				'options' => array(
//					'yes'   => __( 'yes', 'storms' ),
//					'no'  => __( 'no', 'storms' )
//				)
//			),
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
		wp_enqueue_script('storms-wc-user-menu-script',
			\StormsFramework\Helper::get_asset_url( '/js/storms-wc-user-menu' . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . '.js' ),
			array(), STORMS_FRAMEWORK_VERSION, true );
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

		$atts = [
			'wrap_widget'			=> ( ! empty( $instance['wrap_widget'] ) ) ? esc_attr( $instance['wrap_widget'] ) : 'yes',
			'extra_classes' 		=> esc_attr( $instance['extra_classes'] ?? '' ),
		];

		$current_user = wp_get_current_user();

		if ( ! ( $current_user instanceof \WP_User ) ) {
			return;
		}

		$user_logged_in = 0 !== $current_user->ID;
		$display_name = apply_filters( 'storms_wc_user_menu_not_logged_in_display_name', __( 'Login | Cadastro', 'storms' ) );

		if( $user_logged_in ) {
			$display_name = esc_html( $current_user->display_name );
			$display_name = 'Olá, ' . $display_name;
		}

		$html  = '';
		$html .= '<div class="storms-user-menu-content ' . $atts['extra_classes'] . '">';
		$html .= '    <a class="user-menu-link ' . ( $user_logged_in ? 'storms-usuario-logado' : 'storms-usuario-deslogado' ) . '" href="' . wc_get_account_endpoint_url( 'dashboard' ) . '" title="' . __( 'Acesse sua conta', 'storms' ) . '" aria-haspopup="true" aria-expanded="false">';
		$html .= '        <i class="st-ic-user-circle" aria-hidden="true"></i> ';
		$html .= '        <span class="user-menu-text">' . $display_name . '</span> ';
		$html .= '    </a>';

		$html .= '    <div class="user_menu_dropdown">';
		if( $user_logged_in ) {
			$html .= '    <ul class="storms-menu-usuario-logado-container">';
			$html .= '    <li><a href="' . esc_url( wc_get_page_permalink('myaccount') ) . '">Minha conta</a></li>';
			$html .= '    <li><a href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) ) . '">Meus pedidos</a></li>';

			// Required plugin: YITH Wishlist
			if( defined( 'YITH_WCWL' ) ) {
				$html .= '    <li><a href="' . esc_url(YITH_WCWL()->get_wishlist_url()) . '">Meus favoritos <span class="wishlist-counter">' . esc_html(yith_wcwl_count_all_products()) . '</span></a></li>';
			}

			$html .= '    <li><a href="' . esc_url( wc_logout_url() ) . '">Sair</a></li>';
			$html .= '    </ul>';
		} else {
			$html .= '    <div class="storms-menu-usuario-deslogado-container">';
			$html .= '    	<p><a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="btn btn-primary">' . __( 'Entrar', 'storms' ) . '</a></p>';

			if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
				$html .= '    	<p>' . __( 'Ainda não possui cadastro?', 'storms' ) . ' <a href="' . esc_url(wc_get_page_permalink('myaccount')) . '">' . __( 'Cadastre-se', 'storms' ) . '</a></p>';
			}

			$html .= '    </div>';
		}
		$html .= '    </div>';

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
