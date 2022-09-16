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
 * StormsFramework\Widget\Dashboard\BrandInfo class
 * Dashboard Widget that shows your brand information
 */

namespace StormsFramework\Widget\Dashboard;

use StormsFramework\Base;
use StormsFramework\Helper;

class BrandInfo extends Base\Manager
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
    }

	/**
	 * Load the widget dashboard
	 */
	public function load_widget() {
		wp_add_dashboard_widget(
			'brandinfo-dashboard-widget',
			'Storms Websolutions',
			array( $this, 'brand_description' ),
			'dashboard', 'high'
		);
	}

	public function brand_description() {
		$brand_name = 'Storms Websolutions';
		$brand_email = 'storms@storms.com.br';
		$brand_src = Helper::get_asset_url( '/img/storms/logo/brandinfo-logo.png' );
		$brand_manual = Helper::get_option( 'storms_system_manual', plugins_url( 'wp-manual/EasyWPGuide_WP54.pdf', STORMS_FRAMEWORK_PATH ) ); // @TODO Set default system manual link

		$wp_version = get_bloginfo('version');

		$wc_version = '-';
		if ( defined( 'WC_VERSION' ) ) {
			$wc_version = WC_VERSION;
		}

		$env = '';
        switch( wp_get_environment_type() ) {
			case 'production':
                $env = '<strong style="color: #92000f;">' . strtoupper( __( 'production', 'storms' ) ) . '</strong>';
                break;
			case 'staging':
			case 'testing':
                $env = '<strong>' . strtoupper( __( 'testing', 'storms' ) ) . '</strong>';
                break;
			case 'local':
			case 'development':
                $env = '<strong>' . strtoupper( __( 'development', 'storms' ) ) . '</strong>';
                break;
        }
        $env .= ( (defined( 'WP_DEBUG' ) && WP_DEBUG) ? '<i> - DEBUG está habilitado.</i>' : '' );

        // Last modified date of child theme functions.php
		date_default_timezone_set( 'America/Sao_Paulo' );
		$ver_date = date( 'Y-m-d H:i:s', filemtime( get_stylesheet_directory() . '/functions.php' ) );

		$brand_extra_info = '<p>' . __( 'System version', 'storms' ) . ': ' . STORMS_SYSTEM_VERSION . ' ' .
							'<small>' . '@ ' . $ver_date . '</small><br>' .
			                __( 'System environment', 'storms' ) . ': ' . $env . '<br><br>' .
							__( 'PHP version', 'storms' ) . ': ' . phpversion() . '<br>' .
							__( 'Wordpress version', 'storms' ) . ': ' . $wp_version . '<br>' .
							__( 'WooCommerce version', 'storms' ) . ': ' . $wc_version . '</p>';

		$content = '<div style="display: table-cell;vertical-align: middle;padding-right: 10px;">' .
				   '	<img style="height: 70px;" alt="' . $brand_name . '" src="' . $brand_src . '">' .
				   '</div>' .
				   '<ul style="display: table-cell;">' .
				   '	<li>' . __( 'This website was developed by', 'storms' ) . ' ' . $brand_name . '</li>' .
				   '	<li>' . __( 'Need help? Contact us at', 'storms' ) . ' <a href="' . $brand_email . '">' . $brand_email . '</a></li>' .
				   '	<li>' . __( 'Have you read our system manual?', 'storms' ) . ' <a href="' . $brand_manual . '" target="_blank">' . __( 'Click here!', 'storms' ) . '</a></li>' .
				   '</ul>' .
				   '<hr style="border-color: #fefefe -moz-use-text-color #fafafa;"/>' .

				   '<div class="brand-extra-content">' . $brand_extra_info . '</div>';

		// Let the theme add custom info to this widget
		$theme_custom_info = apply_filters( 'storms_brandinfo_theme_custom_info', [] );

		if( ! empty( $theme_custom_info ) ) {
			$content .= '<hr style="border-color: #fefefe;"/>';
			$content .= '<div class="brand-extra-content" style="margin: 1em 0;">';
			$content .= '<ul style="display: table-cell;">';
			foreach( $theme_custom_info as $info ) {
				$content .= '<li>' . $info . '</li>';
			}
			$content .= '</div>';
		}

		echo $content;
	}
}
