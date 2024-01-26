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
 * Storms Framework Loader file
 * Include this file in your functions.php to start the framework
 */

require __DIR__ . '/vendor/autoload.php';

use \StormsFramework\Bootstrap,
	\StormsFramework\WooCommerce;

function storms_load_extensions() {
	// Load Storms Framework's configurations
	\StormsFramework\Configuration::set_defines();

	$support_backend = current_theme_supports( 'style-backend' );
	$support_brand_customization = current_theme_supports( 'brand-customization' );
	$support_frontend = current_theme_supports( 'style-frontend' );
	$support_theme_layouts = current_theme_supports( 'theme-layouts' );
	$support_bootstrap = current_theme_supports( 'use-bootstrap' );
	$support_woocomerce = current_theme_supports( 'use-woocommerce' );

	if ( $support_backend ) {
		(\StormsFramework\BackEnd::get_instance())->run();

		if( $support_brand_customization ) {
			(\StormsFramework\BrandCustomization::get_instance())->run();
		}
	}
	if ( $support_frontend ) {
		(\StormsFramework\FrontEnd::get_instance())->run();

		if ( $support_theme_layouts ) {
			(\StormsFramework\Template::get_instance())->run();

			// Enable theme layouts
			add_theme_support('theme-layouts',
				array(
					'1c'   => array(
						'title'         => __('1 Column', 'storms'),
						'thumbnail'     => '',
						'hide-sidebars' => array( 'main-sidebar', 'shop-sidebar' )
					),
					'2c-l' => array(
						'title'         => __('2 Columns: Content / Sidebar', 'storms'),
						'thumbnail'     => '',
					),
					'2c-r' => array(
						'title'         => __('2 Columns: Sidebar / Content', 'storms'),
						'thumbnail'     => '',
					),
				),
				array(
					'default' => 'default',
					'post_meta' => true,
					'customizer' => true
				)
			);
		}

		if ( $support_bootstrap ) {
			(Bootstrap\Bootstrap::get_instance())->run();
		}
	}
	if ( $support_woocomerce ) {
		if ( \StormsFramework\Helper::is_woocommerce_activated() ) {

			// Declare WooCommerce support
			add_theme_support( 'woocommerce' );

			(WooCommerce\WooCommerce::get_instance())->run();

			// Registering WooCommerce Mini Cart Widget
			add_action( 'widgets_init', array( new WooCommerce\Storms_WC_Cart_Mini(), 'register_widget' ) );

			// Registering WooCommerce User Menu Widget
			add_action( 'widgets_init', array( new WooCommerce\Storms_WC_User_Menu(), 'register_widget' ) );
		}
	}

	(\StormsFramework\StormsApi::get_instance())->run();
}
add_action( 'after_setup_theme', 'storms_load_extensions', 14 );

/**
 * Make theme available for translation
 * Translations can be filed in the /languages/ directory
 */
function load_storms_frameworktextdomain() {
	load_theme_textdomain( 'storms', plugin_dir_path( STORMS_FRAMEWORK_PATH ) . 'languages' );
}
add_action( 'init', 'load_storms_frameworktextdomain' );
