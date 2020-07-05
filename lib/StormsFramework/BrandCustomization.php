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
 * BrandCustomization class
 * @package StormsFramework
 *
 * Customization of the Wordpress Admin Area to the look and fell of Storms Brand
 * @see _documentation/BrandCustomization_Class.md
 */

namespace StormsFramework;

use StormsFramework\Base,
	StormsFramework\Widget\Dashboard;

class BrandCustomization extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
    }

	public function define_hooks() {

		// Change default favicon to all the website
		$this->loader
			->add_filter( 'get_site_icon_url', 'set_default_favicon', 10, 3 )
			->add_action( 'wp_head', 'add_brand_meta_tags' );

		// Run only if user is logged in
		if( is_user_logged_in() ) {

			// Admin modifications
			$this->loader
				->add_action( 'admin_enqueue_scripts', 'set_admin_scripts' )
				->add_action( 'admin_menu', 'add_menu_user_card_developed_by', 999999999 )
				->add_filter( 'admin_footer_text', 'change_footer_text' );

			// Admin bar modifications
			$this->loader
				->add_action( 'wp_enqueue_scripts', 'adminbar_color_scheme' )
				->add_action( 'admin_bar_menu', 'add_adminbar_brand_link' );

			// Dashboard modifications
			$this->loader
				->add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		}

		// Login modifications
		$this->loader
			->add_action( 'login_enqueue_scripts', 'login_scripts' )
			->add_action( 'init', 'login_page_script' )
			->add_filter( 'login_headerurl', 'change_login_logo_url' )
			->add_filter( 'login_headertext', 'change_login_logo_url_title' )
			->add_action( 'login_footer', 'change_footer_text' );
    }

	//<editor-fold desc="Admin modifications">

	/**
	 * Add custom admin scripts
	 * See: http://codex.wordpress.org/Creating_Admin_Themes
	 * Source: http://code.tutsplus.com/articles/customizing-the-wordpress-admin-adding-styling--wp-33530
	 */
	public function set_admin_scripts() {
		wp_register_style( 'storms-admin', Helper::get_asset_url( '/css/admin.min.css' ), array(), STORMS_FRAMEWORK_VERSION );
		wp_enqueue_style( 'storms-admin' );
	}

	/**
	 * Add meta tags with brand information to the website header
	 * Meta tags added: author meta tag, copyright meta tag
	 */
	public function add_brand_meta_tags() {
		$brand_name = Helper::get_option( 'storms_meta_autor', 'Storms Websolutions' );
		$copyright = Helper::get_option( 'storms_meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' );

		$meta_tags = '';
		if ( ! is_admin() ) {
			$meta_tags .= '<meta name="author" content="' . $brand_name . '" />';
			$meta_tags .= '<meta name="copyright" content="Copyright ' . wp_strip_all_tags( $copyright ) .'" />';
		}
		echo $meta_tags;
	}

	/**
	 * Add favicon to the website
	 */
	public function set_default_favicon( $url, $size, $blog_id ) {
		if( $url == '' ) {
		    $icon = Helper::get_option( 'storms_website_favicon', '/img/storms/icons/storms_favicon.png' );
			return Helper::get_asset_url( $icon );
		}
		return $url;
	}

	/**
	 * Add "user card" and "developed by" card on admin menu
	 * TODO Add options for the user define what to show here
	 */
	public function add_menu_user_card_developed_by() {
		// Add the 'user card' at the top-level admin menu
		$user_card  = Helper::get_user_info_card();
		add_menu_page( 'User Online', $user_card, 'read', 'user-onine', '', '&nbsp;', 0 );

		// Add the 'developed by' at the top-level admin menu
		$developed_by  = Helper::get_developed_by();
		add_menu_page( 'Developed By', $developed_by, 'read', 'developed-by', '', '&nbsp;', 999999999 );
	}

	/**
	 * Change dashboard and login footer text
	 */
	public function change_footer_text() {
		$brand_name = Helper::get_option( 'storms_meta_autor', 'Storms Websolutions' );
		$copyright = Helper::get_option( 'storms_meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' );

		echo '<p id="footer">' . $copyright . ' </p>';
	}

	//</editor-fold>

	//<editor-fold desc="Admin bar modifications">

	/**
	 * Color schemes do not apply to admin bar at front end, even if user is logged in - This is the way to force it
	 * Source: http://wordpress.stackexchange.com/questions/126469/how-to-change-admin-bar-color-scheme-in-mp6-wp-3-8-front-end
	 */
	public function adminbar_color_scheme() {
		if ( is_user_logged_in() && is_admin_bar_showing() )
			wp_enqueue_style( 'color-adminbar', Helper::get_asset_url( '/css/adminbar.min.css' ), array( 'admin-bar' ) );
	}

	/**
	 * Add brand icon to admin bar
	 * TODO Add some options for customization
	 */
	public function add_adminbar_brand_link() {
		/** @var \WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		$title = 'Storms Websolutions';
		$link = esc_url( 'http://www.storms.com.br/' );
		$src = Helper::get_asset_url( '/img/storms/logo/cloud_storms.png' );
		$img = '<img src="' . esc_url( $src ) . '" style="height: 100%;" title="' . $title . '" />';

		$wp_admin_bar->add_menu(array(
			'id' => 'brand-home',
			'title' => $img,
			'href' => $link,
			'meta'  => array( 'target' => '_blank' )
		));
	}

	//</editor-fold>

	//<editor-fold desc="Dashboard modifications">

	/**
	 * Add custom dashboard widgets
	 */
	public function add_dashboard_widgets() {
		// Storms Description Widget
		if ( class_exists( 'StormsFramework\\Widget\\Dashboard\\BrandInfo' ) ) {
			( new Dashboard\BrandInfo() )->load_widget();
		}

	}

	//</editor-fold>

	//<editor-fold desc="Login modifications">

	/**
	 * Add scripts and styles to customize the login page
	 */
	public function login_scripts() {
		// http://jquery.com/
		wp_enqueue_script( 'jquery' );

		// Custom login styles
		wp_register_style('login-style', Helper::get_asset_url('/css/login' . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css'), STORMS_FRAMEWORK_VERSION);
		wp_enqueue_style( 'login-style' );
	}

	/**
	 * Scripts that change some aspects of the login page
	 * Document title, always check "remember me", "back to blog" text and "password recovery" text
	 */
	public function login_page_script() {
		add_filter( 'login_footer', function() {
			$doc_title = get_bloginfo( 'name' ) . ' ' . Helper::get_option( 'storms_title_separator', '|' ) . ' ' .  __( 'Login', 'storms' );
			$script = '
				<script>
					jQuery(document).ready(function( $ ) {
						// Modifica o texto de Voltar para Website
						$("#backtoblog a").html(\''. __( 'Home Page', 'storms' ) . '\');

						// Set "Remember Me" To Checked
						$("#rememberme").prop("checked", true);

						// Change the default title
						document.title = "' . $doc_title . '";

						// Change the default title separator from &rsaquo; (›) to |
						//document.title = document.title.replace(/›/g, "|");
						// Change the default title separator from &rsaquo; (‹) to |
						//document.title = document.title.replace(/‹/g, "|");
					});
				</script>';

			echo $script;
		} );
	}

	/**
	 * Change the url of the logo, to be the website URL
	 */
	public function change_login_logo_url() {
		return get_bloginfo( 'url' );
	}

	/**
	 * Change the title of the logo, to be the website name
	 */
	public function change_login_logo_url_title() {
		return esc_html__( get_bloginfo( 'name' ) );
	}

	//</editor-fold>

}
