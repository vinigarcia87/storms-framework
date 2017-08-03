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
 * StormsFramework\Storms\Front\FrontStyle class
 * Front end styling control class
 */

namespace StormsFramework\Storms\Front;

use StormsFramework\Base,
	StormsFramework\Storms;

class Layout extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public function define_hooks() {

		/**
		 * $content_width is a global variable used by WordPress for max image upload sizes and media embeds (in pixels).
		 * Example: If the content area is 640px wide, set $content_width = 620; so images and videos will not overflow.
		 * Default: 1140px is the default Bootstrap container width.
		 * Source: https://codex.wordpress.org/Content_Width
		 */
		global $content_width;
		if (!isset($content_width)) {
			$content_width = get_option( 'content_width', 1140 );
		}

		$this->loader
			->add_filter( 'stylesheet_uri', 'stylesheet_uri', 10, 2 )
			->add_action( 'wp_enqueue_scripts', 'frontend_styles', 10 )
			->add_action( 'wp_enqueue_scripts', 'frontend_scripts' )

			->add_action( 'wp_head', 'jquery_local_fallback', 2 );
			if( !is_admin() ) {
				$this->loader
					->add_filter('script_loader_src', 'jquery_local_fallback', 10, 2);
			}

		$this->loader
			->add_action( 'init', 'register_menus' )
			->add_action( 'widgets_init', 'register_widgets_area' )

			->add_filter( 'sidebars_widgets', 'disable_sidebars' );

	}

	//<editor-fold desc="Scripts and Styles">

	/**
	 * Custom stylesheet URI
	 */
	public function stylesheet_uri( $stylesheet, $stylesheet_dir ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return Storms\Helper::get_asset_url( '/css/style.css' );
		} else {
			return Storms\Helper::get_asset_url( '/css/style.min.css' );
		}
	}

	/**
	 * Register and load all stylesheets that will be used on front end
	 */
	public function frontend_styles() {

        // We remove some know plugin's styles, so you can add them only on the pages you need
        wp_deregister_style('contact-form-7');
        wp_deregister_style('newsletter-subscription');
        add_filter( 'newsletter_enqueue_style', '__return_false' );

		// Default Theme Style
		wp_enqueue_style( 'main-style-theme', get_stylesheet_uri(), array(), STORMS_FRAMEWORK_VERSION, 'all' );

		// Custom Storms Style
		if ( get_option( 'load_stormscss', false ) ) {
			// Custom storms styles
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_register_style('storms', Storms\Helper::get_asset_url('/css/storms.css'), false, STORMS_FRAMEWORK_VERSION);
			} else {
				wp_register_style('storms', Storms\Helper::get_asset_url('/css/storms.min.css'), false, STORMS_FRAMEWORK_VERSION);
			}
			wp_enqueue_style('storms');
		}
	}

	/**
	 * Output the local fallback immediately after jQuery's <script>
	 * @link http://wordpress.stackexchange.com/a/12450
	 */
	public function jquery_local_fallback( $src, $handle = null ) {
		static $run_next = false;

		if ( $run_next && get_option( 'load_external_jquery', false ) ) {
			// Defaults to match the version loaded via CDN
			$local_jquery = Storms\Helper::get_asset_url( '/js/libs/jquery/1.10.2/jquery.min.js' );
			echo '<script>window.jQuery || document.write(\'<script src="' . $local_jquery .'"><\/script>\')</script>' . "\n";


			$run_next = false;
		}

		if ( $handle === 'jquery' ) {
			$run_next = true;
		}

		return $src;
	}

	/**
	 * Register and load all front end scripts
	 */
	public function frontend_scripts() {

        // We remove some know plugin's scripts, so you can add them only on the pages you need
        wp_deregister_script( 'jquery-form' );
        wp_deregister_script('contact-form-7');
        wp_deregister_script( 'newsletter-subscription' );
        wp_deregister_script( 'wp-embed' ); // https://codex.wordpress.org/Embeds

		// http://jquery.com/
		wp_deregister_script('jquery'); // Remove o jquery padrao do wordpress
		if( get_option( 'load_jquery', true ) ) {

			// Decide se carrega jquery externo ou interno
			if( get_option( 'load_external_jquery', false ) && !is_admin() ) {
				wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', false, '1.10.2', false);
			} else {
				wp_register_script('jquery', Storms\Helper::get_asset_url( '/js/libs/jquery/1.10.2/jquery.min.js' ), false, '1.10.2', false);
			}
			wp_enqueue_script('jquery');
		}

        // Main theme scripts
        if ( get_option( 'show_theme_scripts', true ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                wp_register_script('main-script-theme', Storms\Helper::get_asset_url('/js/scripts.js'), array('jquery'), STORMS_FRAMEWORK_VERSION, false);
            } else {
                wp_register_script('main-script-theme', Storms\Helper::get_asset_url('/js/scripts.min.js'), array('jquery'), STORMS_FRAMEWORK_VERSION, false);
            }
            //wp_enqueue_script('main-script-theme'); // Attention! You have to enqueue on the pages you need!
        }

        // Cycle 2 jQuery slideshow plugin
        if ( get_option( 'load_cycle2', true ) ) {
            wp_register_script('cycle2', Storms\Helper::get_asset_url('/js/jquery.cycle2.min.js'), array('jquery'), STORMS_FRAMEWORK_VERSION, false);
            //wp_enqueue_script('cycle2'); // Attention! You have to enqueue on the pages you need!

            wp_enqueue_script('cycle2-carousel', Storms\Helper::get_asset_url('/js/cycle2/plugin/jquery.cycle2.carousel.min.js'), array('cycle2'), '1.0.0', true);
            //wp_enqueue_script('cycle2-carousel'); // Attention! You have to enqueue on the pages you need!
        }

        // Storms JS
        if ( get_option( 'load_stormsjs', false ) ) {
            wp_register_script('storms-js', Storms\Helper::get_asset_url('/js/storms.js'), array('jquery'), STORMS_FRAMEWORK_VERSION, false);
            //wp_enqueue_script('storms-js'); // Attention! You have to enqueue on the pages you need!
        }

        // http://fitvidsjs.com/
        if ( get_option( 'load_fitvids', false ) ) {

            wp_register_script( 'fitvids', Storms\Helper::get_asset_url('/js/libs/fitvids/1.1.0/jquery.fitvids.js'), array('jquery'), '1.1.0', false );
            //wp_enqueue_script('fitvids'); // Attention! You have to enqueue on the pages you need!
        }

		// http://modernizr.com/
		if ( get_option( 'show_modernizr', false ) ) {
			wp_register_script('modernizr', Storms\Helper::get_asset_url('/js/libs/modernizr/2.8.3/modernizr.custom.js'), array('jquery'), '2.8.3', false);
			wp_enqueue_script('modernizr');
		}

		// HTML5 Shivs
		if ( get_option( 'show_html5_js_shivs', false ) ) {

			// https://github.com/afarkas/html5shiv
			wp_register_script( 'html5shiv', Storms\Helper::get_asset_url('/js/libs/html5shiv/3.7.2/html5shiv.js'), false, '3.7.2' );
			wp_enqueue_script('html5shiv');
			wp_script_add_data('html5shiv', 'conditional', 'lt IE 9');

			// https://github.com/paulirish/matchMedia.js
			wp_register_script( 'matchmedia', Storms\Helper::get_asset_url('/js/libs/matchmedia/matchMedia.js'), false, '1.0.0' );
			wp_enqueue_script('matchmedia');
			wp_script_add_data('matchmedia', 'conditional', 'lt IE 9');

			// https://github.com/scottjehl/Respond
			wp_register_script( 'respond', Storms\Helper::get_asset_url('/js/libs/respond/1.4.2/respond.min.js'), false, '1.4.2' );
			wp_enqueue_script('respond');
			wp_script_add_data('respond', 'conditional', 'lt IE 9');
		}

		// Load Thread comments WordPress script
		if ( is_singular() && comments_open() && get_option('thread_comments') ) {
			wp_enqueue_script('comment-reply');
		}
	}

	/**
	 * Add the HTML linter for Bootstrap projects - Bootlint
	 * Only if is on debug mode - WP_DEBUG is true
	 */
	public function add_bootlint() {
		if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && get_option( 'show_bootlint', false ) ) {
			?>
			<!-- Bootlint - Source: https://github.com/twbs/bootlint -->
			<script src="https://maxcdn.bootstrapcdn.com/bootlint/latest/bootlint.min.js"></script>
			<script>
				$(function () {
					AvaliarBootstrap = function() {
						if( typeof (bootlint) === 'undefined' ) {
							var s=document.createElement('script');
							s.src='<?php echo esc_url( Storms\Helper::get_asset_url( '/js/libs/bootlint/0.14.2/bootlint.js' ) ); ?>';
							s.onload=function(){
								bootlint.showLintReportForCurrentDocument([], { problemFree: false });
							};
							document.body.appendChild(s);
						} else {
							bootlint.showLintReportForCurrentDocument( [], { problemFree: false } );
						}
					};
					AvaliarBootstrap();
				});
			</script>
			<?php
		}
	}

	//</editor-fold>

	//<editor-fold desc="Widgets and Menus">

	/**
	 * Register wp_nav_menu() menus
	 * - Main Menu
	 * @link http://codex.wordpress.org/Function_Reference/register_nav_menus
	 */
	public function register_menus() {
		if ( get_option( 'add_storms_menu', true ) ) {

			register_nav_menus(array(
				'main_menu' => __('Main Menu', 'storms'),
			));

		}
	}

	/**
	 * Register widgets area
	 * - Header Sidebar widget area
	 * - Main Sidebar widget area
	 * - Footer Sidebar widget area
	 */
	public function register_widgets_area() {

		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = get_option('widget_title_tag', 'h3');

		// Header Sidebar
		if (get_option('add_header_sidebar', true)) {

			register_sidebar(array(
				'name' => __('Header Sidebar', 'storms'),
				'id' => 'header-sidebar',
				'description' => __('Add widgets here to appear in your header region.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

		// Main Sidebar
		if (get_option('add_main_sidebar', true)) {

			register_sidebar(array(
				'name' => __('Main Sidebar', 'storms'),
				'id' => 'main-sidebar',
				'description' => __('Add widgets here to appear in your sidebar.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

		// Footer Sidebars
		if (get_option('add_footer_sidebar', true)) {

			$numFooterSidebars = get_option('number_of_footer_sidebars', 4);
			for ($i = 1; $i <= intval($numFooterSidebars); $i++) {
				register_sidebar(array(
						'name' => sprintf(__('Footer Sidebar %d', 'storms'), $i),
						'id' => sprintf('footer-sidebar-%d', $i),
						'description' => sprintf(__('Add widgets here to appear in your footer region %d.', 'storms'), $i),
						'before_widget' => '<aside id="%1$s" class="widget %2$s">',
						'after_widget' => '</aside>',
						'before_title' => '<' . $widget_title_tag . '>',
						'after_title' => '</' . $widget_title_tag . '>',
					)
				);
			}

		}
	}

	//</editor-fold>

	//<editor-fold desc="Template functions">

	/**
	 * Classes header container size
	 * @return string Classes name
	 */
	public static function header_container() {
		return get_option( 'storms_header_container_class', 'container' );
	}

	/**
	 * Classes wrap container size
	 * @return string Classes name
	 */
	public static function wrap_container() {
		return get_option( 'storms_wrap_container_class', 'container' );
	}

	/**
	 * Classes footer container size
	 * @return string Classes name
	 */
	public static function footer_container() {
		return get_option( 'storms_footer_container_class', 'container' );
	}

	/**
	 * Main layout
	 * @param  mixed  $layout You can force an specific layout, ignoring the page defined layout - default is null
	 * @return string Classes name
	 */
	public static function main_layout( $layout = null ) {

		// If there is no forced layout to use, read from the page/post configuration
		if( $layout === null ) {
			$layout = get_theme_mod( 'theme_layout' );
		} else {
			// Check if forced layout is a valid value - if it is not, then we default to 'default'
			if( ! in_array( $layout, [ 'default', '1c', '2c-r', '2c-l' ] ) ) {
				$layout = 'default';
			}
		}

		if( $layout == 'default' ) {
			$layout = is_page() ? '1c' : ( is_rtl() ? '2c-r' : '2c-l' );
		}

		switch( $layout ) {
			// 2 columns - main content on left
			case '2c-l':
				return get_option('main_2c_l_size', 'col-md-9') . ' main-layout-left';
				break;
			// 2 columns - main content on right
			case '2c-r':
				$sidebar = get_option( 'sidebar_2c_r_size', 'col-md-3' );
				$push_right = preg_replace( "/col-(.*)-(.*)/", "col-$1-push-$2", $sidebar );
				return $push_right . ' ' . get_option( 'main_2c_r_size', 'col-md-9' ) . ' main-layout-right';
				break;
			// 1 column
			case '1c':
				return get_option('main_1c_size', 'col-md-12') . ' main-layout-full';
				break;
		}
	}

	/**
	 * Sidebar layout
	 * @param  mixed  $layout You can force an specific layout, ignoring the page defined layout - default is null
	 * @return string Classes name
	 */
	public static function sidebar_layout( $layout = null ) {

		// If there is no forced layout to use, read from the page/post configuration
		if( $layout === null ) {
			$layout = get_theme_mod( 'theme_layout' );
		} else {
			// Check if forced layout is a valid value - if it is not, then we default to 'default'
			if( ! in_array( $layout, [ 'default', '1c', '2c-r', '2c-l' ] ) ) {
				$layout = 'default';
			}
		}
		
		if( $layout == 'default' ) {
			$layout = is_page() ? '1c' : is_rtl() ? '2c-r' : '2c-l';
		}

		switch( $layout ) {
			// 2 columns - main content on left, sidebar on right
			case '2c-l':
				return get_option( 'sidebar_2c_l_size', 'col-md-3' ) . ' sidebar-layout-right';
				break;
			// 2 columns - main content on right, sidebar on left
			case '2c-r':
				$main = get_option( 'main_2c_r_size', 'col-md-9' );
				$pull_left = preg_replace( "/col-(.*)-(.*)/", "col-$1-pull-$2", $main );
				return $pull_left . ' ' . get_option( 'sidebar_2c_r_size', 'col-md-3' ) . ' sidebar-layout-left';
				break;
		}
	}

	/**
	 * Remove sidebars if the layout is defined to not have
	 * any sidebar
	 */
	public function disable_sidebars( $sidebars_widgets ) {

		if ( current_theme_supports( 'theme-layouts' ) && ! is_admin() ) {

			$layout = get_theme_mod( 'theme_layout' );
			
			if( $layout == 'default' ) {
				$layout = is_page() ? '1c' : is_rtl() ? '2c-r' : '2c-l';
			}

			if ( '1c' == $layout ) {
				$sidebars_widgets['main-sidebar'] = false;
				$sidebars_widgets['shop-sidebar'] = false;
			}
		}

		return $sidebars_widgets;
	}

	//</editor-fold>
}
