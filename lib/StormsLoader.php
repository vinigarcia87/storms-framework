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
 * Storms Framework Loader file
 * Include this file in your functions.php to start the framework
 */

require __DIR__ . '/vendor/autoload.php';

use StormsFramework\Storms,
	StormsFramework\Vendor,
	StormsFramework\Storms\WooCommerce;

function storms_load_extensions() {
	// Load Storms Framework's configurations
	Storms\Configuration::set_defines();

	if ( current_theme_supports( 'style-backend' ) ) {
		(new Storms\BackEnd())->run();
	}
	if ( current_theme_supports( 'style-frontend' ) ) {
		(new Storms\FrontEnd())->run();

		if( current_theme_supports( 'style-layout' ) ) {
			(new Storms\Layout)->run();

			if ( current_theme_supports( 'theme-layouts' ) ) {
				(new Storms\Template())->run();

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

			/**
		     * Enable support for wide alignment class for Gutenberg blocks
		     */
			add_theme_support( 'align-wide' );

			if ( current_theme_supports( 'use-bootstrap' ) ) {
				(new Storms\Bootstrap\Bootstrap)->run();
			}
		}
	}
	if ( current_theme_supports( 'use-woocommerce' ) ) {
		if (WooCommerce\Functions::is_woocommerce_activated()) {
			(new Storms\WooCommerce\WooCommerce)->run();
		}
	}
}
add_action( 'after_setup_theme', 'storms_load_extensions', 14 );
