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

		// Run only at admin panel
		if( is_admin() ) {
			// Admin modifications
			$this->loader
				->add_action( 'init', 'set_editor_style' )
				->add_filter( 'admin_title', 'change_admin_title', 10, 2 )
				->add_action( 'admin_head', 'set_admin_page_title' )
				->add_action( 'admin_menu', 'remove_links_from_menu' )
				->add_action( '_admin_menu', 'remove_appearance_editor' )
				->add_filter( 'image_send_to_editor', 'add_srcset_and_sizes_to_tinymce_img_markup', 10, 9 );

			$this->loader
				->add_filter( 'heartbeat_settings', 'optimize_heartbeat_settings' )
				->add_action( 'init', 'disable_heartbeat_unless_post_edit_screen', 1 )
				->add_action( 'init', 'stop_heartbeat', 1 );

			$this->loader
				->add_action( 'admin_head-profile.php', 'remove_admin_color_scheme_picker' )
				->add_action( 'admin_head-user-edit.php', 'remove_admin_color_scheme_picker' );

			$this->disable_wp_update_for_non_admin();

			// Dashboard modifications
			$this->loader
				->add_action( 'wp_dashboard_setup', 'remove_dashboard_widgets' )
				->add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		}

		if( is_admin_bar_showing() ) {
			// Admin bar modifications
			$this->loader
				->add_action( 'admin_bar_menu', 'toolbar_bootstrap_media_breakpoints_alert', 9900 )
				->add_action( 'admin_bar_menu', 'toolbar_system_environment_alert', 9999 )
				->add_action( 'wp_before_admin_bar_render', 'remove_adminbar_itens' );
		}

		// Login modifications
		$this->loader
			->add_filter( 'login_redirect', 'login_redirect', 10, 3 )
			->add_filter( 'login_errors', 'login_error_msg' );

		// Disable XML-RPC
		// @see https://kinsta.com/pt/blog/xmlrpc-php/
		add_filter( 'xmlrpc_enabled', '__return_null' );
		$this->loader
			->add_filter( 'bloginfo_url', 'remove_pingback_url', 10, 2 );
    }

	//<editor-fold desc="Admin modifications">

	/**
	 * Tell the TinyMCE editor to use a custom stylesheet
	 * This theme styles the visual editor to resemble the theme style
	 * The framework does not include a editor-style! The theme must create his own
	 */
	public function set_editor_style() {
		if( Helper::get_option( 'storms_set_editor_style', 'yes' ) ) {
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
	 * Limit Heartbeat API in WordPress
	 * @see https://wordpress.stackexchange.com/a/222021/54025
	 *
	 * @param $settings
	 * @return mixed
	 */
	public function optimize_heartbeat_settings( $settings ) {
		$settings['autostart'] = false;
		$settings['interval'] = 60;
		return $settings;
	}

	/**
	 * Limit on what pages Heartbeat API will work in WordPress
	 */
	public function disable_heartbeat_unless_post_edit_screen() {
		global $pagenow;

		if( 'yes' === Helper::get_option( 'storms_disable_heartbeat_unless_post_edit_screen', 'yes' ) ) {

			// We need on post and post-new pages
			$pages_that_need_heartbeat = array( 'post.php', 'post-new.php' );

			// If we are on admin.php?page=wc-admin or admin.php?page=wc-reports we also gonna need heartbeat
			if ( isset( $_GET['page'] ) && $pagenow == 'admin.php' && ( $_GET['page'] == 'wc-admin' || $_GET['page'] == 'wc-reports' ) ) {
				$pages_that_need_heartbeat[] = 'admin.php';
			}

			// If we are on edit.php?post_type=shop_order or edit.php?post_type=shop_coupon we also gonna need heartbeat
			if ( isset( $_GET['post_type'] ) && $pagenow == 'edit.php' && ( $_GET['post_type'] == 'shop_order' || $_GET['post_type'] == 'shop_coupon' ) ) {
				$pages_that_need_heartbeat[] = 'edit.php';
			}

			if ( ! in_array( $pagenow, $pages_that_need_heartbeat ) && isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'admin.php?page=wc-admin' ) === false ) {

				wp_deregister_script( 'heartbeat' );

			}

		}
	}

	/**
	 * Disable Heartbeat API in WordPress
	 */
	public function stop_heartbeat() {

		if( 'yes' === Helper::get_option( 'storms_stop_heartbeat', 'no' ) ) {
			wp_deregister_script('heartbeat');
		}
	}

	/**
	 * Change the image markup to include srcset and sizes
	 * when using tinyMCE editor
	 *
	 * @param $html
	 * @param $id
	 * @param $alt
	 * @param $title
	 * @param $align
	 * @param $url
	 * @param $size
	 * @return string
	 * @throws \Exception
	 */
	public function add_srcset_and_sizes_to_tinymce_img_markup( $html, $id, $caption, $title, $align, $url, $size, $alt, $rel ) {
		//$metadata = wp_get_attachment_metadata( $id );
		//$url = wp_get_attachment_url( $id );
		return wp_filter_content_tags( $html );
	}

	/**
	 * Remove sensitive menu itens from Worpress admin menu - for any admin user that is not the "super user"
	 * TODO Does not remove anything right now, we should select some items to remove
	 * Source: http://code.tutsplus.com/tutorials/customizing-your-wordpress-admin--wp-24941
	 * Souce: http://sethstevenson.net/customize-the-wordpress-admin-menu-based-on-user-roles/
	 */
	public function remove_links_from_menu() {
		global $userdata;

		$restricted_users_email = Helper::get_option( 'storms_restricted_users_email', '/@storms.com.br$/' );

		if( empty( $restricted_users_email ) ) {
			return;
		}

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
     * @param \WP_Admin_Bar $wp_admin_bar
     */
    function toolbar_system_environment_alert( $wp_admin_bar ) {
		global $wp_admin_bar;

        switch( wp_get_environment_type() ) {
			case 'production':
                $env_class = 'production';
                $env = strtoupper( __( 'production', 'storms' ) );
                break;
			case 'staging':
			case 'testing':
                $env_class = 'testing';
                $env = strtoupper( __( 'testing', 'storms' ) );
                break;
			case 'local':
			case 'development':
                $env_class = 'development';
                $env = strtoupper( __( 'development', 'storms' ) );
                break;
        }

        $args = array(
            'id'    => 'system_environment',
            'title' => $env,
            'href'  => '#',
            'meta'  => array(
            	'class' => 'system-environment ' . $env_class,
				//'html'  => $media
			)
        );

        $wp_admin_bar->add_node( $args );
    }

	/**
	 * Add an alert to admin bar to show what media breakpoint is currently being displayed
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
    function toolbar_bootstrap_media_breakpoints_alert( $wp_admin_bar ) {
		global $wp_admin_bar;

		$media = '';
		$media .= '<div id="detect-breakpoints">';
		$media .= '		<div class="d-block d-sm-none">XS</div>';
		$media .= '		<div class="d-none d-sm-block d-md-none">SM</div>';
		$media .= '		<div class="d-none d-md-block d-lg-none">MD</div>';
		$media .= '		<div class="d-none d-lg-block d-xl-none">LG</div>';
		$media .= '		<div class="d-none d-xl-block  d-xxl-none">XL</div>';
		$media .= '		<div class="d-none d-xxl-block">XXL</div>';
		$media .= '</div>';

		$args = array(
			'id'    => 'bootstrap_media_breakpoints',
			'title' => $media,
			'meta'  => array(
				'class' => 'bootstrap-media-breakpoints',
				//'html'  => $media
			)
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
        //$wp_admin_bar->remove_menu('customize');
		$wp_admin_bar->remove_menu('new-content');
        //$wp_admin_bar->remove_menu('edit');

		$this->wp_admin_bar_customize_menu( $wp_admin_bar );

        $wp_admin_bar->remove_menu('_options'); 						// Raymond Theme - JWSThemes
		$wp_admin_bar->remove_menu('backwpup'); 						// BackWPup
        $wp_admin_bar->remove_menu('wpseo-menu'); 						// Yoast SEO
        $wp_admin_bar->remove_menu('revslider'); 						// Slider Revolution
        $wp_admin_bar->remove_menu('vc_inline-admin-bar-link'); 		// Visual Composer
		$wp_admin_bar->remove_menu('itsec_admin_bar_menu'); 			// iThemes Security
        $wp_admin_bar->remove_menu('autoptimize'); 						// Autoptimize
        $wp_admin_bar->remove_menu('wphb'); 							// Hummingbird
		$wp_admin_bar->remove_menu('monsterinsights_frontend_button'); 	// Monster Insights

		// wp-admin-bar-top-secondary
		$wp_admin_bar->remove_menu('search');
		//$wp_admin_bar->remove_menu('my-account');
	}

	/**
	 * Adds the "Customize" link to the Toolbar.
	 * But on site-name menu context
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @global \WP_Customize_Manager $wp_customize
	 */
	protected function wp_admin_bar_customize_menu( $wp_admin_bar ) {
		global $wp_customize;

		// Don't show for users who can't access the customizer or when in the admin.
		if ( ! current_user_can( 'customize' ) || is_admin() ) {
			return;
		}

		// Don't show if the user cannot edit a given customize_changeset post currently being previewed.
		if ( is_customize_preview() && $wp_customize->changeset_post_id() && ! current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $wp_customize->changeset_post_id() ) ) {
			return;
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( is_customize_preview() && $wp_customize->changeset_uuid() ) {
			$current_url = remove_query_arg( 'customize_changeset_uuid', $current_url );
		}

		$customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );
		if ( is_customize_preview() ) {
			$customize_url = add_query_arg( array( 'changeset_uuid' => $wp_customize->changeset_uuid() ), $customize_url );
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'customize',
				'title' => __( 'Customize' ),
				'parent' => 'site-name',
				'href'  => $customize_url,
				'meta'  => array(
					'class' => 'hide-if-no-customize',
				),
			)
		);
	}

	//</editor-fold>

	//<editor-fold desc="Dashboard modifications">

	/**
	 * Remove default dashboard widgets
	 * Those are not useful for the average user
	 */
	public function remove_dashboard_widgets() {
		global $wp_meta_boxes;

		// Show all registred meta boxes
		//Helper::debug( $wp_meta_boxes );

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

			// Yoast SEO widget
			'wpseo-dashboard-overview' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
			'wpseo-wincher-dashboard-overview' => array(
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
			'quadlayers-dashboard-overview' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
			'wp-dashboard-widget-news' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
			'easy_wp_smtp_reports_widget_lite' => array(
				'page'    => 'dashboard',
				'context' => 'normal'
			),
		);

		$remove_defaults_widgets = apply_filters( 'storms_remove_dashboard_widgets', $remove_defaults_widgets );

		foreach( $remove_defaults_widgets as $widget_id => $options ) {
			remove_meta_box( $widget_id, $options['page'], $options['context'] );
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

			$restricted_users_email = Helper::get_option( 'storms_restricted_users_email', '/@storms.com.br$/' );

			if( empty( $restricted_users_email ) ) {
				return;
			}

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

		$lostpassword_url = Helper::is_woocommerce_activated() ? wc_lostpassword_url() : wp_lostpassword_url();
		$lost_password_link = '<a href="' . esc_url( $lostpassword_url ) . '">' . __( 'Lost your password?' ) .'</a>';

		return __( 'The credentials you provided are invalid', 'storms' ) . '. ' . $lost_password_link;
	}

	//</editor-fold>

	/**
	 * Removes the pingback header
	 *
	 * @param string $output
	 * @param string $show
	 * @return string
	 */
	function remove_pingback_url( $output, $show ) {

		if ( $show == 'pingback_url' ) {
			$output = '';
		}

		return $output;
	}
}
