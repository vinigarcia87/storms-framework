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
 * StormsFramework\Storms\BackEnd class
 * Customization of the Wordpress Admin Area
 */

namespace StormsFramework\Storms;

use StormsFramework\Base,
	StormsFramework\Storms,
	StormsFramework\Widget\Dashboard;

class BackEnd extends Base\Runner
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
				->add_action( 'init', 'set_editor_style' )
				->add_action( 'admin_enqueue_scripts', 'set_admin_scripts' )
				->add_filter( 'admin_title', 'change_admin_title', 10, 2 )
				->add_action( 'admin_head', 'set_admin_page_title' )
				->add_action( 'admin_menu', 'remove_links_from_menu' )
				->add_action( '_admin_menu', 'remove_appearance_editor' )
				->add_action( 'admin_menu', 'add_menu_user_card_developed_by', 999999999 )
				->add_filter( 'admin_footer_text', 'change_footer_text' );

			$this->remove_admin_color_scheme_picker();
			$this->disable_wp_update_for_non_admin();

			// Admin bar modifications
			$this->loader
				->add_action( 'wp_before_admin_bar_render', 'remove_adminbar_itens' )
				->add_action( 'wp_enqueue_scripts', 'adminbar_color_scheme' )
				->add_action( 'admin_bar_menu', 'add_adminbar_brand_link' );

			// Dashboard modifications
			$this->loader
				->add_action( 'wp_dashboard_setup', 'remove_dashboard_widgets' )
				->add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		}

		// Login modifications
		$this->loader
			//->add_action( 'login_init', 'remove_all_wp_login_style' )
			->add_action( 'login_enqueue_scripts', 'login_scripts' )
			->add_action( 'init', 'login_page_script' )
			->add_filter( 'login_headerurl', 'change_login_logo_url' )
			->add_filter( 'login_headertitle', 'change_login_logo_url_title' )
			->add_filter( 'login_redirect', 'login_redirect', 10, 3 )
			->add_filter( 'login_errors', 'login_error_msg' )
			->add_action( 'login_footer', 'change_footer_text' );
    }

	//<editor-fold desc="Admin modifications">

	/**
	 * Tell the TinyMCE editor to use a custom stylesheet
	 * This theme styles the visual editor to resemble the theme style
	 */
	public function set_editor_style() {
		$styles = array(
			//Storms\Helper::get_asset_url( '/css/COLOR_FILE.min.css' ),
			Storms\Helper::get_asset_url( '/css/editor-style.min.css' ),
		);
		add_editor_style( $styles );
	}

	/**
	 * Add custom admin scripts
	 * See: http://codex.wordpress.org/Creating_Admin_Themes
	 * Source: http://code.tutsplus.com/articles/customizing-the-wordpress-admin-adding-styling--wp-33530
	 */
	public function set_admin_scripts() {
		wp_register_style( 'storms-admin', Storms\Helper::get_asset_url( '/css/admin.min.css' ), array(), STORMS_FRAMEWORK_VERSION );
		wp_enqueue_style( 'storms-admin' );
	}

	/**
	 * Add meta tags with brand information to Wordpress pages
	 * Not add to admin pages
	 * Meta tags added:
	 * 	- author meta tag
	 * 	- copyright meta tag
	 */
	public function add_brand_meta_tags() {
		$brand_name = get_option( 'meta_autor', 'Storms Websolutions' );
		$copyright = get_option( 'meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' );

		$meta_tags = '';
		if ( !is_admin() ) {
			$meta_tags .= '<meta name="author" content="' . $brand_name . '" />';
			$meta_tags .= '<meta name="copyright" content="Copyright ' . wp_strip_all_tags( $copyright ) .'" />';
		}

		echo $meta_tags;
	}

	/**
	 * Add favicon in admin area
	 */
	public function set_default_favicon( $url, $size, $blog_id ) {
		if( $url == '' ) {
			return Storms\Helper::get_asset_url( '/img/storms/icons/storms_favicon.png' );
		}
		return $url;
	}

	/**
	 * Change the <title> in admin area
	 * Source: http://wordpress.stackexchange.com/questions/17025/change-page-title-in-admin-area
	 */
	public function change_admin_title( $admin_title, $title ) {
		return $title . ' | ' . get_bloginfo( 'name' );
	}

	/**
	 * Change 'admin pages' title text to the website name and website description - if defined
	 * For example, "Dashboard" becomes "My Site | Another Wordpress blog"
	 */
	public function set_admin_page_title() {
		global $title;
		if( $title == 'Painel' ) {
			$title = get_bloginfo( 'name' );

			$site_description = get_bloginfo( 'description', 'display' );
			if( $site_description )
				$title .= ' | ' . $site_description;
		} else {
			$title .= ' | ' . get_bloginfo( 'name' );
		}
	}

	/**
	 * Remove editor menu from appearance panel
	 */
	public function remove_appearance_editor() {
		remove_action( 'admin_menu', '_add_themes_utility_last', 101 );
	}

	/**
	 * Remove sensitive menu itens from Worpress admin menu - for any admin user that is not the "super user"
	 * Source: http://code.tutsplus.com/tutorials/customizing-your-wordpress-admin--wp-24941
	 * Souce: http://sethstevenson.net/customize-the-wordpress-admin-menu-based-on-user-roles/
	 */
	public function remove_links_from_menu() {
		global $userdata;

		$restricted_users_email = get_option( 'restricted_users_email', '/@storms.com.br$/' );

		// If logged in user don't match $restricted_users_email email, we disable sensitive menu itens
		if( !preg_match( $restricted_users_email, $userdata->user_email ) ) {
			//remove_menu_page( 'tools.php ');           // Remove 'Tools'
			//remove_menu_page( 'plugins.php' );         // Remove 'Plugins'
			//remove_menu_page( 'themes.php' );          // Remove 'Appearance'
			//remove_menu_page( 'options-general.php' ); // Remove 'Settings'
		}
	}

	/**
	 * Stop users from switching Admin Color Schemes
	 * Source: http://www.wpbeginner.com/wp-tutorials/how-to-set-default-admin-color-scheme-for-new-users-in-wordpress/
	 */
	public function remove_admin_color_scheme_picker() {
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
	}

	/**
	 * Disable the "please update now" message in WP dashboard for non admin users
	 */
	public function disable_wp_update_for_non_admin() {
		if ( !current_user_can( 'administrator' ) ) {
			remove_action( 'init', 'wp_version_check' );
			$this->loader->add_filter( 'pre_option_update_core', '_return_null' );
		}
	}

	/**
	 * Add "user card" and "developed by" card on admin menu
	 */
	public function add_menu_user_card_developed_by() {
		// Add the 'user card' at the top-level admin menu
		$user_card  = Storms\Helper::get_user_info_card();
		add_menu_page( 'User Online', $user_card, 'read', 'user-onine', '', '&nbsp;', 0 );

		// Add the 'developed by' at the top-level admin menu
		$developed_by  = Storms\Helper::get_developed_by();
		add_menu_page( 'Developed By', $developed_by, 'read', 'developed-by', '', '&nbsp;', 999999999 );
	}

	/**
	 * Change dashboard and login footer text
	 */
	public function change_footer_text() {
		$brand_name = get_option( 'meta_autor', 'Storms Websolutions' );
		$copyright = get_option( 'meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' );

		echo '<p id="footer">' . $copyright . ' </p>';
	}

	//</editor-fold>

	//<editor-fold desc="Admin bar modifications">

	/**
	 * Remove menu items from admin bar
	 */
	public function remove_adminbar_itens() {
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_menu('about');
		$wp_admin_bar->remove_menu('wporg');
		$wp_admin_bar->remove_menu('documentation');
		$wp_admin_bar->remove_menu('support-forums');
		$wp_admin_bar->remove_menu('feedback');
		$wp_admin_bar->remove_menu('view-site');
		$wp_admin_bar->remove_menu('updates');
		$wp_admin_bar->remove_menu('my-sites');
		$wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('customize');
		$wp_admin_bar->remove_menu('new-content');
        //$wp_admin_bar->remove_menu('edit');

        $wp_admin_bar->remove_menu('_options'); // Raymond Theme - JWSThemes
		$wp_admin_bar->remove_menu('backwpup'); // BackWPup
        $wp_admin_bar->remove_menu('wpseo-menu'); // Yoast SEO
        $wp_admin_bar->remove_menu('revslider'); // Slider Revolution
        $wp_admin_bar->remove_menu('vc_inline-admin-bar-link'); // Visual Composer
		$wp_admin_bar->remove_menu('itsec_admin_bar_menu'); // iThemes Security

		// wp-admin-bar-top-secondary
		$wp_admin_bar->remove_menu('search');
		//$wp_admin_bar->remove_menu('my-account');
	}

	/**
	 * Color schemes do not apply to admin bar at front end, even if user is logged in - This is the way to force it
	 * Source: http://wordpress.stackexchange.com/questions/126469/how-to-change-admin-bar-color-scheme-in-mp6-wp-3-8-front-end
	 */
	public function adminbar_color_scheme() {
		if ( is_user_logged_in() && is_admin_bar_showing() )
			wp_enqueue_style( 'color-adminbar', Storms\Helper::get_asset_url( '/css/adminbar.min.css' ), array( 'admin-bar' ) );
	}

	/**
	 * Add brand icon to admin bar
	 */
	public function add_adminbar_brand_link() {

		$title = 'Storms Websolutions';
		$link = esc_url( 'http://www.storms.com.br/' );
		$src = Storms\Helper::get_asset_url( '/img/storms/logo/cloud_storms.png' );
		$img = '<img src="' . esc_url( $src ) . '" style="height: 100%;" title="' . $title . '" />';

		global $wp_admin_bar;
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
	 * Remove default dashboard widgets
	 */
	public function remove_dashboard_widgets() {
		//Remove WordPress Welcome Panel
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		// Page - Where can we find this widget ( dashboard / post / attachment / ... )
		// Context - In which area is the widget located ( normal / advanced / side )
		$remove_defaults_widgets = array(
			// Today widget
			'dashboard_right_now' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
			// Draft widget
			'dashboard_quick_press' => array(
				'page'    => 'dashboard',
				'context' => 'side'
			),
			// Wordpress news
			'dashboard_primary' => array(
				'page'    => 'dashboard',
				'context' => 'side'
			),
			// Recent website activity
			'dashboard_activity' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
		);

		foreach ( $remove_defaults_widgets as $widget_id => $options )
			remove_meta_box( $widget_id, $options['page'], $options['context'] );
	}

	/**
	 * Add custom dashboard widgets
	 */
	public function add_dashboard_widgets() {
		// Storms Description Widget
		if ( class_exists( 'StormsFramework\\Widget\\Dashboard\\BrandInfo' ) ) {
			( new Dashboard\BrandInfo() )->load_widget();
		}

		// Storms System Errors Widget
		if ( class_exists( 'StormsFramework\\Widget\\Dashboard\\SystemErrors' ) ) {
			global $userdata;

			$restricted_users_email = get_option( 'restricted_users_email', '/@storms.com.br$/' );

			// If logged in user's email match $restricted_users_email, we show the errors log
			if( preg_match( $restricted_users_email, $userdata->user_email ) ) {
				( new Dashboard\SystemErrors() )->load_widget();
			}
		}
	}

	//</editor-fold>

	//<editor-fold desc="Login modifications">

	/**
	 * This code will leave you with a completely unstyled wp-login.php page
	 * Re-register the login style after deregistering it to prevent the unwanted request that results in a 404
	 * Hook on 'login_init' : add_action( 'login_init', 'remove_all_wp_login_style' )
	 * Source: http://wordpress.stackexchange.com/a/168491/54025
	 */
	public function remove_all_wp_login_style() {
		wp_deregister_style( 'login' );

		// Custom login styles
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_register_style('login', Storms\Helper::get_asset_url('/css/login.css'), STORMS_FRAMEWORK_VERSION);
		} else {
			wp_register_style('login', Storms\Helper::get_asset_url('/css/login.min.css'), STORMS_FRAMEWORK_VERSION);
		}
	}

	/**
	 * Add scripts and styles to login page
	 */
	public function login_scripts() {
		// http://jquery.com/
		wp_enqueue_script( 'jquery' );

		// Custom login styles
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_register_style('login-style', Storms\Helper::get_asset_url('/css/login.css'), STORMS_FRAMEWORK_VERSION);
		} else {
			wp_register_style('login-style', Storms\Helper::get_asset_url('/css/login.min.css'), STORMS_FRAMEWORK_VERSION);
		}
		wp_enqueue_style( 'login-style' );

		// Custom login scripts
		//wp_register_script( 'login-script', Storms\Helper::get_asset_url( '/js/login.js' ), null, STORMS_FRAMEWORK_VERSION );
		//wp_enqueue_script( 'login-script' );
	}

	/**
	 * Scripts that change some aspects of the login page
	 * document title, always check "remember me", "back to blog" text and "password recovery" text
	 */
	public function login_page_script() {
		add_filter( 'login_footer', function() {
			$doc_title = get_bloginfo( 'name' ) . ' ' . get_option( 'title_separator', '|' ) . ' ' .  __( 'Login', 'storms' );
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
	 * Change the url of the logo
	 */
	public function change_login_logo_url() {
		return get_bloginfo( 'url' );
	}

	/**
	 * Change the title of the logo
	 */
	public function change_login_logo_url_title() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Redirect users to home on login, when they trying to access admin pages
	 * but let admin and editor users go to wherever they want to
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		global $user;

		if ( isset( $user->roles ) ) {
			if ( in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles ) ) {
				return $redirect_to;
			}else if ( strpos( $redirect_to, admin_url() ) !== false ) {
				return home_url();
			}
			return $redirect_to;
		}
		return home_url();
	}

	/**
	 * Generic login error message - for security reasons
	 * Source: https://ausweb.com.au/tutorials/2014/12/01/securing-wordpress-16-wordpress-security-tips-tricks/
	 */
	public function login_error_msg( $msg ) {
		return __( 'The credentials you provided are invalid', 'storms' );
	}

	//</editor-fold>
}
