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
		$brand_manual = get_option( 'storms_system_manual', plugins_url( 'wp-manual/EasyWPGuide_WP4.8.pdf', STORMS_FRAMEWORK_PATH ) ); // @TODO Set default system manual link
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
                            '<small>Commit #' . STORMS_SYSTEM_COMMIT . '</small><br>' .
			                __( 'System environment', 'storms' ) . ': ' . $env . '</p>';

		$content = '<div style="display: table-cell;vertical-align: middle;padding-right: 10px;">' .
				   '	<img style="height: 70px;" alt="' . $brand_name . '" src="' . $brand_src . '">' .
				   '</div>' .
				   '	<ul style="display: table-cell;">' .
				   '	<li>' . __( 'This website was developed by', 'storms' ) . ' ' . $brand_name . '</li>' .
				   '	<li>' . __( 'Need help? Contact us at', 'storms' ) . ' <a href="' . $brand_email . '">' . $brand_email . '</a></li>' .
				   '	<li>' . __( 'Have you read our system manual?', 'storms' ) . ' <a href="' . $brand_manual . '" target="_blank">' . __( 'Click here!', 'storms' ) . '</a></li>' .
				   '</ul>' .
				   '<hr style="border-color: #fefefe -moz-use-text-color #fafafa;"/>' .

				   '<div class="brand-extra-content">' . $brand_extra_info . '</div>';

		echo $content;
	}
}
