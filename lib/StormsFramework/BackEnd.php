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
 * BackEnd class
 * @package StormsFramework
 *
 * Customization of the Wordpress Admin Area
 * @see  _documentation/BackEnd_Class.md
 */

namespace StormsFramework;

use StormsFramework\Base,
	StormsFramework\Widget\Dashboard;

class BackEnd extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
    }

	public function define_hooks() {

		// Run only if user is logged in
		if( is_user_logged_in() ) {
			// Admin modifications
			$this->loader
				->add_action( 'init', 'set_editor_style' )
				->add_filter( 'admin_title', 'change_admin_title', 10, 2 )
				->add_action( 'admin_head', 'set_admin_page_title' )
				->add_action( 'admin_menu', 'remove_links_from_menu' )
				->add_action( '_admin_menu', 'remove_appearance_editor' );

			$this->remove_admin_color_scheme_picker();
			$this->disable_wp_update_for_non_admin();

			// Admin bar modifications
			$this->loader
                ->add_action( 'admin_bar_menu', 'toolbar_system_environment_alert', 9999 )
				->add_action( 'wp_before_admin_bar_render', 'remove_adminbar_itens' );

			// Dashboard modifications
			$this->loader
				->add_action( 'wp_dashboard_setup', 'remove_dashboard_widgets' )
				->add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		}

		// Login modifications
		$this->loader
			->add_filter( 'login_redirect', 'login_redirect', 10, 3 )
			->add_filter( 'login_errors', 'login_error_msg' );
    }

	//<editor-fold desc="Admin modifications">

	/**
	 * Tell the TinyMCE editor to use a custom stylesheet
	 * This theme styles the visual editor to resemble the theme style
	 * The framework does not include a editor-style! The theme must create his own
	 */
	public function set_editor_style() {
		if( get_option( 'set_editor_style', 'yes' ) ) {
			$styles = array(
				Helper::get_asset_url('/css/editor-style.min.css'),
			);
			add_editor_style($styles);
		}
	}

	/**
	 * Change the <title> tag in admin area
	 * The title will look like: 'Title of the page | My website'
	 * TODO Add a filter for this!
	 * Source: http://wordpress.stackexchange.com/questions/17025/change-page-title-in-admin-area
	 */
	public function change_admin_title( $admin_title, $title ) {
		return $title . ' | ' . get_bloginfo( 'name' );
	}

	/**
	 * Change 'admin pages' title text to the website name and website description - if defined
	 * For example, "Dashboard" becomes "My Site | Another Wordpress blog"
	 * TODO Add a filter for this!
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
	 * For safety reasons, we don't need that
	 */
	public function remove_appearance_editor() {
		remove_action( 'admin_menu', '_add_themes_utility_last', 101 );
	}

	/**
	 * Remove sensitive menu itens from Worpress admin menu - for any admin user that is not the "super user"
	 * TODO Does not remove anything right now, we should select some items to remove
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
	 * Why anyone whould need this?
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

	//</editor-fold>

	//<editor-fold desc="Admin bar modifications">

    /**
     * Add an alert to admin bar to make clear what environment the user is conected to
	 * It uses SF_ENV constant to check the current environment
     * @param $wp_admin_bar
     */
    function toolbar_system_environment_alert( $wp_admin_bar ) {
		/** @var \WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

        switch( SF_ENV ) {
            case 'PRD':
                $env_class = 'production';
                $env = strtoupper( __( 'production', 'storms' ) );
                break;
            case 'TST':
                $env_class = 'testing';
                $env = strtoupper( __( 'testing', 'storms' ) );
                break;
            case 'DEV':
                $env_class = 'development';
                $env = strtoupper( __( 'development', 'storms' ) );
                break;
            default:
                $env_class = strtolower( SF_ENV );
                $env = '<strong>' . SF_ENV . '</strong>';
        }

        $args = array(
            'id'    => 'system_environment',
            'title' => $env,
            'href'  => '#',
            'meta'  => array( 'class' => 'system-environment ' . $env_class )
        );

        $wp_admin_bar->add_node( $args );
    }

	/**
	 * Remove menu items from admin bar
	 * They are unnecessary for most users, and you may need to add your own links
	 * TODO Add some options or filters to allow the theme to exclude some links from the list
	 */
	public function remove_adminbar_itens() {
		/** @var \WP_Admin_Bar $wp_admin_bar */
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

        $wp_admin_bar->remove_menu('_options'); 				// Raymond Theme - JWSThemes
		$wp_admin_bar->remove_menu('backwpup'); 				// BackWPup
        $wp_admin_bar->remove_menu('wpseo-menu'); 				// Yoast SEO
        $wp_admin_bar->remove_menu('revslider'); 				// Slider Revolution
        $wp_admin_bar->remove_menu('vc_inline-admin-bar-link'); // Visual Composer
		$wp_admin_bar->remove_menu('itsec_admin_bar_menu'); 	// iThemes Security
        $wp_admin_bar->remove_menu('autoptimize'); 				// Autoptimize
        $wp_admin_bar->remove_menu('wphb'); 					// Hummingbird

		// wp-admin-bar-top-secondary
		$wp_admin_bar->remove_menu('search');
		//$wp_admin_bar->remove_menu('my-account');
	}

	//</editor-fold>

	//<editor-fold desc="Dashboard modifications">

	/**
	 * Remove default dashboard widgets
	 * Those are not useful for the average user
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

			// Menu Icons plugin dashboard widget
			'themeisle' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),

			// YITH widgets
			'yith_dashboard_products_news' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
			'yith_dashboard_blog_news' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),

			// Other widgets
			'wpgenie_dashboard_products_news' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
		);

		// ual_dashboard_widget -> show only if admin

		foreach ( $remove_defaults_widgets as $widget_id => $options ) {
			remove_meta_box($widget_id, $options['page'], $options['context']);
		}
	}

	/**
	 * Add custom Storms dashboard widgets
	 * Only visible to admin "super user"
	 * SystemErrors Dashboard Widget: List errors shown on debug.log file
	 */
	public function add_dashboard_widgets() {
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
	 * Generic login error message
	 * For security reasons, it's not wise to tell hackers if they guess wrong the username or the password
	 * Source: https://ausweb.com.au/tutorials/2014/12/01/securing-wordpress-16-wordpress-security-tips-tricks/
	 */
	public function login_error_msg( $msg ) {
		return __( 'The credentials you provided are invalid', 'storms' );
	}

	//</editor-fold>

}
