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
        switch( SF_ENV ) {
            case 'PRD':
                $env = '<strong style="color: #92000f;">' . strtoupper( __( 'production', 'storms' ) ) . '</strong>';
                break;
            case 'TST':
                $env = '<strong>' . strtoupper( __( 'testing', 'storms' ) ) . '</strong>';
                break;
            case 'DEV':
                $env = '<strong>' . strtoupper( __( 'development', 'storms' ) ) . '</strong>';
                break;
            default:
                $env = '<strong>' . SF_ENV . '</strong>';
        }
        $env .= ( (defined( 'WP_DEBUG' ) && WP_DEBUG) ? '<i> - DEBUG está habilitado.</i>' : '' );

		$brand_extra_info = '<p>' . __( 'System version', 'storms' ) . ': ' . STORMS_SYSTEM_VERSION . ' ' .
							( '' == STORMS_SYSTEM_COMMIT ? '<br>' : '<small>Commit #' . STORMS_SYSTEM_COMMIT . '</small><br>' ) .
			                __( 'System environment', 'storms' ) . ': ' . $env . '<br><br>' .
							__( 'PHP version', 'storms' ) . ': ' . phpversion() . '<br>' .
							__( 'Wordpress version', 'storms' ) . ': ' . $wp_version . '<br>' .
							__( 'WooCommerce version', 'storms' ) . ': ' . $wc_version . '</p>';

		// Let the theme add custom info to this widget
		$theme_custom_info = apply_filters( 'storms_brandinfo_theme_custom_info', '' );

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

		if( ! empty( $theme_custom_info ) ) {
			$content .= '<hr style="border-color: #fefefe -moz-use-text-color #fafafa;"/>';
			$content .= '<div class="brand-extra-content" style="margin: 1em 0;">' . $theme_custom_info . '</div>';
		}

		echo $content;
	}
}
