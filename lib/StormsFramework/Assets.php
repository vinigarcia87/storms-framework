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
 * Assets class
 * @package StormsFramework
 *
 * Styles and scripts control class
 * @see  _documentation/Assets_Class.md
 */

namespace StormsFramework;

use StormsFramework\Base;

class Assets extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	private $jquery_version = '3.4.1';
	private $cycle2_version = '2.1.7';

	public function define_hooks() {

		$this->loader
			->add_filter( 'stylesheet_uri', 'stylesheet_uri', 10, 2 )
			->add_action( 'wp_enqueue_scripts', 'enqueue_main_style', 10 )
			->add_action( 'wp_enqueue_scripts', 'remove_unused_styles', 10 );

		$this->loader
			->add_action( 'wp_enqueue_scripts', 'jquery_scripts' )
			->add_action( 'wp_head', 'jquery_local_fallback', 2 );
			if( !is_admin() ) {
				$this->loader
					->add_filter('script_loader_src', 'jquery_local_fallback', 10, 2);
			}

		$this->loader
			->add_action( 'wp_enqueue_scripts', 'remove_unused_scripts' )
			->add_action( 'wp_enqueue_scripts', 'frontend_scripts' );

	}

	//<editor-fold desc="Scripts and Styles">

	/**
	 * Custom stylesheet URI
	 * get_stylesheet_uri() return assets/css/style.min.css
	 *
	 * @param $stylesheet
	 * @param $stylesheet_dir
	 * @return bool|string
	 */
	public function stylesheet_uri( $stylesheet, $stylesheet_dir ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return Helper::get_asset_url( '/css/style.css' );
		}
		return Helper::get_asset_url( '/css/style.min.css' );
	}

	/**
	 * Register and load main theme stylesheet
	 */
	public function enqueue_main_style() {
		// Default Theme Style
		wp_enqueue_style( 'main-style-theme', get_stylesheet_uri(), array(), STORMS_FRAMEWORK_VERSION, 'all' );
	}

	/**
	 * We remove some well-know plugin's styles, so you can add them manually only on the pages you need
	 * Styles that we remove are: contact-form-7, newsletter-subscription, newsletter_enqueue_style
	 */
	public function remove_unused_styles() {
		wp_deregister_style( 'contact-form-7' );
		wp_deregister_style( 'newsletter-subscription' );
		add_filter( 'newsletter_enqueue_style', '__return_false' );
	}

	/**
	 * Enqueue jQuery scripts
	 */
	public function jquery_scripts() {
		// http://jquery.com/
		wp_deregister_script( 'jquery' ); // Remove o jquery padrao do wordpress
		if( get_option( 'load_jquery', 'yes' ) ) {

			// Decide se carrega jquery externo ou interno
			if( !is_admin() && 'yes' == get_option( 'load_external_jquery', 'no' ) ) {
				wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/' . $this->jquery_version . '/jquery.min.js', false, $this->jquery_version, false);
			}
			wp_register_script('jquery', Helper::get_asset_url( '/js/jquery/' . $this->jquery_version . '/jquery.min.js' ), false, $this->jquery_version, false);

			wp_enqueue_script('jquery');
		}
	}

	/**
	 * Output the local fallback immediately after jQuery's <script>
	 * Only if external jquery is been used
	 * @link http://wordpress.stackexchange.com/a/12450
	 */
	public function jquery_local_fallback( $src, $handle = null ) {
		static $run_next = false;

		if( $run_next && 'yes' == get_option( 'load_external_jquery', 'no' ) ) {
			// Defaults to match the version loaded via CDN
			$local_jquery = Helper::get_asset_url( '/js/jquery/' . $this->jquery_version . '/jquery.min.js' );
			echo '<script>window.jQuery || document.write(\'<script src="' . $local_jquery .'"><\/script>\')</script>' . "\n";

			$run_next = false;
		}

		if( $handle === 'jquery' ) {
			$run_next = true;
		}

		return $src;
	}

	/**
	 * We remove some well-know plugin's scripts, so you can add them manually only on the pages you need
	 * Scripts that we remove are: jquery-form, contact-form-7, newsletter-subscription, wp-embed
	 */
	public function remove_unused_scripts() {
		// We remove some know plugin's scripts, so you can add them only on the pages you need
		wp_deregister_script( 'jquery-form' );
		wp_deregister_script('contact-form-7');
		wp_deregister_script( 'newsletter-subscription' );
		wp_deregister_script( 'wp-embed' ); // https://codex.wordpress.org/Embeds
	}

	/**
	 * Register main theme script
	 * Register cycle2 and cycle2-carousel script
	 * Adjust Thread comments WordPress script to load only on specific pages
	 * TODO Check if cycle2 is necessary
	 */
	public function frontend_scripts() {

        // Main theme scripts
        if ( 'yes' == get_option( 'show_theme_scripts', 'yes' ) ) {
			wp_register_script( 'main-script-theme', Helper::get_asset_url('/js/scripts' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min' ) . '.js'), array('jquery'), STORMS_FRAMEWORK_VERSION, true );
            //wp_enqueue_script( 'main-script-theme' ); // Attention! You have to enqueue on the pages you need!
        }

        // Cycle 2 jQuery slideshow plugin
		/*
        if ( get_option( 'load_cycle2', 'no' ) ) {
            wp_register_script( 'cycle2', Helper::get_asset_url('/js/jquery.cycle2.min.js'), array('jquery'), $this->cycle2_version, true );
            //wp_enqueue_script( 'cycle2' ); // Attention! You have to enqueue on the pages you need!

            wp_enqueue_script( 'cycle2-carousel', Helper::get_asset_url('/js/cycle2/plugin/jquery.cycle2.carousel.min.js'), array('cycle2'), $this->cycle2_version, true );
            //wp_enqueue_script( 'cycle2-carousel' ); // Attention! You have to enqueue on the pages you need!
        }
		*/

		// Load Thread comments WordPress script
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	//</editor-fold>

}
