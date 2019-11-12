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
	$support_layout = current_theme_supports( 'style-layout' );
	$support_theme_layouts = current_theme_supports( 'theme-layouts' );
	$support_bootstrap = current_theme_supports( 'use-bootstrap' );
	$support_woocomerce = current_theme_supports( 'use-woocommerce' );

	if ( $support_backend ) {
		(new \StormsFramework\BackEnd())->run();

		if( $support_brand_customization ) {
			(new \StormsFramework\BrandCustomization())->run();
		}
	}
	if ( $support_frontend ) {
		(new \StormsFramework\FrontEnd())->run();

		if( $support_layout ) {
			(new \StormsFramework\Layout())->run();

			if ( $support_theme_layouts ) {
				(new \StormsFramework\Template())->run();

				// Enable theme layouts
				add_theme_support('theme-layouts',
					array(
						'1c'   => __('1 Column', 'storms'),
						'2c-l' => __('2 Columns: Content / Sidebar', 'storms'),
						'2c-r' => __('2 Columns: Sidebar / Content', 'storms')
					),
					array(
						'default' => 'default',
						'customizer' => true
					)
				);
			}

			if ( $support_bootstrap ) {
				(new Bootstrap\Bootstrap)->run();
			}
		}
	}
	if ( $support_woocomerce ) {
		if (WooCommerce\Functions::is_woocommerce_activated()) {
			(new WooCommerce\WooCommerce)->run();
		}
	}
}
add_action( 'after_setup_theme', 'storms_load_extensions', 14 );
