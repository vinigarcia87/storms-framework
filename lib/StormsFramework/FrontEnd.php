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
 * FrontEnd class
 * @package StormsFramework
 *
 * Customization of the Wordpress FrontEnd
 * @see  _documentation/FrontEnd_Class.md
 */

namespace StormsFramework;

use StormsFramework\Base;

class FrontEnd extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
    }

	public function define_hooks() {

		// FrontEnd optimizations
		$this->loader
			->add_action( 'init', 'setup_features' )
			->add_action( 'init', 'head_cleanup' )
			->add_filter( 'the_generator', 'remove_the_generator' );

		remove_action( 'wp_head', 'rel_canonical' );

		$this->remove_oembed();

		$this->loader->add_action( 'send_headers', 'http_header_security', 10, 0 );

		// Links and tags cleanup
		$this->loader
			->add_filter( 'document_title_separator', 'title_separator' )
			->add_action( 'wp_head', 'rel_canonical' )
			->add_filter( 'wp_list_categories', 'modify_category_rel' )
			->add_filter( 'the_category', 'modify_category_rel' )
			->add_filter( 'wp_tag_cloud', 'modify_tag_rel' )
			->add_filter( 'the_tags', 'modify_tag_rel' )
			->add_filter( 'style_loader_tag', 'clean_style_tag' )
			->add_filter( 'script_loader_tag', 'clean_script_tag' )
			->add_filter( 'get_avatar', 'remove_self_closing_tags' )
			->add_filter( 'comment_id_fields', 'remove_self_closing_tags' )
			->add_filter( 'post_thumbnail_html', 'remove_self_closing_tags' )
			->add_filter( 'script_loader_src', 'remove_script_version', 15, 1 )
			->add_filter( 'style_loader_src', 'remove_script_version', 15, 1 )
			->add_filter( 'language_attributes', 'language_attributes' )
			->add_filter( 'body_class', 'body_class' )
			->add_filter( 'embed_oembed_html', 'oembed_wrap', 10, 4 )
			->add_filter( 'embed_googlevideo', 'oembed_wrap', 10, 2 )

			->add_filter( 'wp_is_mobile', 'wp_is_mobile_exclude_tablets' );

		$this->remove_emojis();

		$this->loader
			->add_filter( 'wp_head', 'remove_wp_widget_recent_comments_style', 1 )
			->add_filter( 'wp_head', 'remove_recent_comments_style', 1 )
			->add_filter( 'use_default_gallery_style', 'remove_default_gallery_style' );

        $this->loader
            ->add_filter( 'the_category', 'add_category_slug', 99, 1 )
            ->add_filter( 'the_title', 'menu_title_markup', 10, 2 );

		$this->loader
			->add_filter( 'the_content', 'content_add_rel_noopener', 10 );
			//->add_filter( 'the_content', 'content_remove_wrapping_p', 10 );

		$this->loader
			->add_action( 'init', 'register_menus' )
			->add_action( 'widgets_init', 'register_widgets_area_header', 1 )
			->add_action( 'widgets_init', 'register_widgets_area_main', 10 )
			->add_action( 'widgets_init', 'register_widgets_area_footer_1', 15 )
			->add_action( 'widgets_init', 'register_widgets_area_footer_2', 20 );

		$this->loader
			->add_filter( 'wp_nav_menu_items', 'add_widget_area_on_menu_item', 10, 2 );
    }

	//<editor-fold desc="FrontEnd optimizations">

	/**
	 * Setup the theme support
	 * Content width
	 * WP management of document title
	 * Excerpt on pages
	 * Post Thumbnails on posts and pages - Add custom post thumbnail sizes
	 * HTML5 markup for search-form, comment-form, comment-list, gallery, caption
	 * Post Formats: aside, image, video, quote, link, gallery, status, audio, chat
	 * RSS feed links to HTML <head>
	 * Wide alignment class for Gutenberg blocks
	 * Custom Header
	 * Custom Background
	 */
	public function setup_features() {

		/**
		 * $content_width is a global variable used by WordPress for max image upload sizes and media embeds (in pixels).
		 * Example: If the content area is 640px wide, set $content_width = 620; so images and videos will not overflow.
		 * Default: 1140px is the default Bootstrap container width.
		 * Source: https://codex.wordpress.org/Content_Width
		 */
		global $content_width;
		if ( !isset( $content_width ) ) {
			$content_width = Helper::get_option( 'storms_content_width', 1140 );
		}

		/**
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/**
		 * Support The Excerpt on pages
		 */
		add_post_type_support( 'page', 'excerpt' );

		/**
		 * Enable support for Post Thumbnails on posts and pages
		 * See: http://codex.wordpress.org/Post_Thumbnails
		 * See: http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
		 * See: http://codex.wordpress.org/Function_Reference/add_image_size
		 * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( Helper::get_option( 'storms_post_thumb_width' , 825 ), Helper::get_option( 'storms_post_thumb_height' , 510 ), 'yes' === Helper::get_option( 'storms_post_thumb_crop' , 'yes' ) );

		/**
		 * Support HTML5
		 * Switch default core markup for search form, comment form, and comments to output valid HTML5
		 * See: http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
		 */
		add_theme_support( 'html5', array( 'script', 'style', 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

		/**
		 * Enable support for Post Formats.
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat' ) );

		// Adds RSS feed links to HTML <head>
		add_theme_support( 'automatic-feed-links' );

		// Enable support for wide alignment class for Gutenberg blocks
		add_theme_support( 'align-wide' );

		// Support Custom Header
		$default = array(
			'width'         => 0,
			'height'        => 0,
			'flex-height'   => false,
			'flex-width'    => false,
			'header-text'   => false,
			'default-image' => '',
			'uploads'       => true,
		);
		add_theme_support( 'custom-header', $default );

		// Support Custom Background
		$defaults = array(
			'default-color' => 'f0f0f0',
			'default-image' => '',
		);
		add_theme_support( 'custom-background', $defaults );
	}

	/**
	 * Cleanup wp_head(), to remove unnecessary and unsafe wp meta tags
	 */
	public function head_cleanup() {
		// Category feeds
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		// Post and comment feeds
		remove_action( 'wp_head', 'feed_links', 2 );

		// EditURI link
		remove_action( 'wp_head', 'rsd_link' );

		// Windows live writer
		remove_action( 'wp_head', 'wlwmanifest_link' );

		// Index link
		remove_action( 'wp_head', 'index_rel_link' );

		// Previous link
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );

		// Start link
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );

		// Links for adjacent posts
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

		// WP version
		remove_action( 'wp_head', 'wp_generator' );

		// WC generator tag
		remove_action( 'wp_head', 'wc_generator_tag' );

		// Inject rel=shortlink into head if a shortlink is defined for the current page
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );

		// Originally from http://wpengineer.com/1438/wordpress-header/
		add_action( 'wp_head', 'ob_start', 1, 0 );
		add_action( 'wp_head', function () {
			$pattern = '/.*' . preg_quote(esc_url(get_feed_link('comments_' . get_default_feed())), '/') . '.*[\r\n]+/';
			echo preg_replace($pattern, '', ob_get_clean());
		}, 3, 0 );
	}

	/**
	 * Remove WP version meta tag
	 */
	public function remove_the_generator() {
		return false; // __return_false
	}

	/**
	 * Remove Wordpress oembed
	 * @see https://www.isitwp.com/remove-everything-oembed/
	 */
	public function remove_oembed() {

		//Remove the REST API endpoint.
		remove_action('rest_api_init', 'wp_oembed_register_route');

		// Turn off oEmbed auto discovery.
		add_filter( 'embed_oembed_discover', '__return_false' );

		//Don't filter oEmbed results.
		remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

		//Remove oEmbed discovery links.
		remove_action('wp_head', 'wp_oembed_add_discovery_links');

		//Remove oEmbed JavaScript from the front-end and back-end.
		remove_action('wp_head', 'wp_oembed_add_host_js');
	}

	//</editor-fold>

	/**
	 * Harden and improve WordPress security
	 * @see https://digital.com/wordpress-hosting/security/
	 * @see https://scotthelme.co.uk/hardening-your-http-response-headers/
	 */
	public function http_header_security() {

		/**
		 * Content Security Policy (CSP)
		 * CSP helps mitigate XSS attacks by whitelisting the allowed sources of content such as scripts, styles, and images.
		 * A content security policy can prevent the browser from loading malicious assets.
		 * Unfortunately there isn’t an one size fit all approach to CSP’s. Before you create your CSP you need to evaluate the
		 * resources you’re actually loading. Once you think you have a handle on how resources are loading you can set up a
		 * policy based on those requirements.
		 * @see https://scotthelme.co.uk/content-security-policy-an-introduction/
		 * @see https://blog.sucuri.net/2018/04/content-security-policy.html
		 */
		//header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:");

		/**
		 * Set X-Frame-Options Header
		 * The X-Frame-Options HTTP response header can be used to indicate whether or not a browser
		 * should be allowed to render a page in a <frame>, <iframe>, <embed> or <object>. Sites can
		 * use this to avoid click-jacking attacks, by ensuring that their content is not embedded into other sites
		 * @see https://stackoverflow.com/a/44573750/1003020
		 */
		send_frame_options_header();

		/**
		 * X-XSS-Protection and X-Content-Type-Options
		 * The X-XSS-Protection helps mitigate Cross-site scripting (XSS) attacks and X-Content-Type-Options header instructs
		 * IE not to sniff mime types, preventing attacks related to mime-sniffing.
		 */
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');

		/**
		 * HTTP Strict Transport Security (HSTS)
		 * HSTS is a way for the server to instruct the browser that the browser should only communicate with the server
		 * over HTTPS.
		 */
		header('Strict-Transport-Security:max-age=31536000; includeSubdomains; preload');

		/**
		 * Referrer Policy
		 * Allows a site to control how much information the browser includes with navigations away from a document and
		 * should be set by all sites.
		 * @see https://scotthelme.co.uk/a-new-security-header-referrer-policy/
		 */
		header('Referrer-Policy: no-referrer-when-downgrade');

		/**
		 * Permissions Policy
		 * Allows a site to control which features and APIs can be used in the browser
		 */
		//header('permissions-policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

		/**
		 * Implement Cookie with HTTPOnly and Secure flag in WordPress
		 * This instructs the browser to trust the cookie only by the server and that cookie is accessible over secure SSL channels.
		 * @see https://geekflare.com/wordpress-x-frame-options-httponly-cookie/
		 */
		@ini_set('session.cookie_httponly', true);
		@ini_set('session.cookie_secure', true);
		@ini_set('session.use_only_cookies', true);
		@ini_set( 'session.cookie_samesite', 'None' );

	}

	//<editor-fold desc="Styles, links and tags cleanup">

	/**
	 * Change the separator between title tag parts
	 */
	public function title_separator() {
		return Helper::get_option( 'storms_title_separator', '|' );
	}

	/**
	 * Remove self-closing tag and change ''s to "'s on rel_canonical()
	 */
	public function rel_canonical() {
		/** @var \WP_Query $wp_the_query */
		global $wp_the_query;

		if( !is_singular() )
			return;

		if (!$id = $wp_the_query->get_queried_object_id())
			return;

		$link = get_permalink($id);
		echo "\t<link rel=\"canonical\" href=\"$link\">\n";
	}

	/**
	 * Add rel="nofollow" and remove rel="category"
	 */
	public function modify_category_rel( $text ) {
		$search = array( 'rel="category"', 'rel="category tag"' );
		$text = str_replace( $search, 'rel="nofollow"', $text );

		return $text;
	}

	/**
	 * Add rel="nofollow" and remove rel="tag"
	 */
	public function modify_tag_rel( $taglink ) {
		return str_replace( 'rel="tag">', 'rel="nofollow">', $taglink );
	}

	/**
	 * Clean up output of stylesheet <link> tags
	 */
	public function clean_style_tag( $input ) {
		if( ! is_admin() ) {
			preg_match_all( "!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches );
			// Only display media if it is meaningful
			$media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';
			return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
		}
		return $input;
	}

	/**
	 * Clean up output of <script> tags
	 */
	public function clean_script_tag( $input ) {
		if( !is_admin() ) {
			$input = str_replace( "type='text/javascript' ", '', $input );
			return str_replace( "'", '"', $input );
		}
		return $input;
	}

	/**
	 * Remove unnecessary self-closing tags
	 */
	public function remove_self_closing_tags( $input ) {
		if( !is_admin() ) {
			return str_replace( ' />', '>', $input );
		}
		return $input;
	}

	/**
	 * Clean up language_attributes() used in <html> tag - Remove dir="ltr"
	 */
	public function language_attributes() {
		$attributes = [];

		if ( is_rtl() ) {
			$attributes[] = 'dir="rtl"';
		}

		$lang = get_bloginfo( 'language' );

		if ($lang) {
			$attributes[] = "lang=\"$lang\"";
		}

		$output = implode( ' ', $attributes );
		$output = apply_filters( 'storms_language_attributes', $output );

		return $output;
	}

	/**
	 * Remove version query string from all styles and scripts
	 * Can add a custom timestamp based on the modification time of the functions.php
	 * That can help to force browsers to purge cache for styles and scripts anytime we upload a new theme version
     * @see https://www.virendrachandak.com/techtalk/how-to-remove-wordpress-version-parameter-from-js-and-css-files/
     * @see https://kellenmace.com/force-css-changes-to-go-live-immediately/
	 */
	public function remove_script_version( $src ) {
	    if( ! $src ) {
	        return false;
        }

        // Remove the default version parameter from resources
	    $src = remove_query_arg( 'ver', $src );

		// Apply a versioning number based on modification time of functions.php
	    if( Helper::get_option( 'storms_timestamp_assets', 'yes' ) ) {
            $ver = filemtime( get_stylesheet_directory() . '/functions.php' );
            $src = add_query_arg( [ 'ver' => $ver ], $src );
        }

        return $src;
	}

	/**
	 * Cleanup body classes
	 * Add post/page slug if not present
	 * Remove classes that show page/post id
	 */
	public function body_class( $classes ) {
		// Add post/page slug if not present
		if ( is_single() || is_page() && !is_front_page() ) {
			if ( !in_array( basename( get_permalink()), $classes ) ) {
				$classes[] = basename( get_permalink() );
			}
		}

		// Remove classes that show page/post id
		$pattern = "/page-template|page-id-|postid-|single-format-standard|author-/";
		$classes = preg_grep($pattern, $classes, PREG_GREP_INVERT);

		return $classes;
	}

	/**
	 * Enclose embedded media in a div.
	 * Wrapping all flash embeds in a div allows for easier styling with CSS media queries.
	 * @link https://gist.github.com/965956
	 */
	public function oembed_wrap( $cache, $url, $attr = '', $post_ID = '' ) {
		$classes = apply_filters( 'oembed_wrap_classes', array( 'embed-wrap' ) );
		return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $cache . '</div>';
	}

	/**
	 * Change wp_is_mobile() function to exclude tablets
	 *
	 * @param $is_mobile
	 * @return bool
	 */
	public function wp_is_mobile_exclude_tablets( $is_mobile ) {

		if( $is_mobile && Helper::is_tablet() ) {
			return false;
		}
		return $is_mobile;
	}

	/**
	 * Remove Emoji's from Wordpress
	 * @see https://kinsta.com/pt/base-de-conhecimento/desativar-os-emojis-no-wordpress/
	 */
	public function remove_emojis() {
		if( Helper::get_option( 'storms_remove_emoji', 'yes' ) ) {
			// Emoji's
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

			$this->loader->add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 )
						 ->add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
		}
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array $urls URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 * @return array Difference betwen the two arrays.
	 */
	function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			// This filter is documented in wp-includes/formatting.php
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}
		return $urls;
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 */
	public function disable_emojis_tinymce( $plugins ) {
		return ( is_array( $plugins ) ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

	/**
	 * Remove injected CSS for recent comments widget
	 *
	 * @param array $plugins
	 * @return array Difference betwen the two arrays
	 */
	public function remove_wp_widget_recent_comments_style() {
		if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
			remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
		}
	}

	/**
	 * Remove injected CSS from recent comments widget
	 */
	public function remove_recent_comments_style() {
		global $wp_widget_factory;

		if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) )
			remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
	}

	/**
	 * Remove injected CSS from photo gallery
	 */
	public function remove_default_gallery_style() {
		return false; // __return_false
	}

	/**
	 * Add the category slug to the_category()
	 *
	 * @param $html
	 * @return mixed
	 */
    public function add_category_slug( $html ) {
        if ( ! is_admin() ) {
            if ($html != '') {
                $a = new \SimpleXMLElement($html);
                $category = strtolower(basename($a['href']));
                $replacement = '$1 class="' . $category . '">$3';
                $html = preg_replace('#(.*)(>)(.*<\/a>)#Uis', $replacement, $html);
            }
        }
        return $html;
    }

	/**
	 * Allow us to use #BR# on menu items, to add line-break
	 * WP remove all html from menu items names, so we force <br> by using #BR#
	 * @see http://wordpress.stackexchange.com/a/105900/54025
	 *
	 * @param $title
	 * @param $id
	 * @return mixed
	 */
    public function menu_title_markup( $title, $id ) {
        if ( is_nav_menu_item ( $id ) ){
            $title = str_ireplace( "#BR#", "<br/>", $title );
        }
        return $title;
    }

	//</editor-fold>

	//<editor-fold desc="Parsing a post content">

	/**
	 * Adding rel=noopener
	 * Fixing security issue regarding links opening in a new tab.
	 * @see https://css-tricks.com/leverage-wordpress-functions-reduce-html-posts/#article-header-id-14
	 * @see https://mathiasbynens.github.io/rel-noopener/
	 */
	public function content_add_rel_noopener( $content ) {
		if( $content !== '' ) {
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
			$document = new \DOMDocument();
			libxml_use_internal_errors( true );
			$document->loadHTML( utf8_decode( $content ) );

			/** @var /DOMNodeList $nodes */
			$nodes = $document->getElementsByTagName( 'a' );
			foreach( $nodes as $node ) {
				$href = $node->getAttribute( 'href' );
				// We add rel="noopener noreferrer" only on links to external websites
				if( strpos( $href, get_site_url() ) === false ) {
					$node->setAttribute( 'rel', 'noopener noreferrer' );
				}
			}

			$html = $document->saveHTML();
			return $html;
		}
		return $content;
	}

	/**
	 * Removing wrapping paragraphs
	 * Remove the wrapping paragraph from images and other elements, such as picture, video, audio, and iframe.
	 * @TODO Test this before using! Not sure if needed or working
	 * @see https://css-tricks.com/leverage-wordpress-functions-reduce-html-posts/#article-header-id-13
	 * @see https://www.jitbit.com/alexblog/256-targetblank---the-most-underestimated-vulnerability-ever/
	 */
	public function content_remove_wrapping_p( $content ) {
		if( $content !== '' ) {
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
			$document = new \DOMDocument();
			libxml_use_internal_errors( true );
			$document->loadHTML( utf8_decode( $content ) );

			// Iterating a nodelist while manipulating it is not a good thing, because
			// the nodelist dynamically updates itself. Get all things that must be
			// unwrapped and put them in an array.
			$tagNames = array( 'img', 'picture', 'video', 'audio', 'iframe' );
			$mediaElements = array();
			foreach( $tagNames as $tagName ) {
				$nodes = $document->getElementsByTagName( $tagName );
				foreach ( $nodes as $node ) {
					$mediaElements[] = $node;
				}
			}

			foreach( $mediaElements as $element ) {
				// Get a reference to the parent paragraph that may have been added by
				// WordPress. It might be the direct parent node or the grandparent
				// (LOL) in case of links
				$paragraph = null;

				// Get a reference to the image itself or to the link containing the
				// image, so we can later remove the wrapping paragraph
				$theElement = null;

				if( $element->parentNode->nodeName == 'p' ) {
					$paragraph = $element->parentNode;
					$theElement = $element;
				} else if( $element->parentNode->nodeName == 'a' &&
					$element->parentNode->parentNode->nodeName == 'p' ) {
					$paragraph = $element->parentNode->parentNode;
					$theElement = $element->parentNode;
				}

				// Make sure the wrapping paragraph only contains this child
				if( $paragraph && $paragraph->textContent == '' ) {
					$paragraph->parentNode->replaceChild( $theElement, $paragraph );
				}
			}

			$html = $document->saveHTML();
			return $html;
		}
		return $content;
	}

	//</editor-fold>

	//<editor-fold desc="Widgets and Menus">

	/**
	 * Register wp_nav_menu() menus
	 * Main Menu
	 * @link http://codex.wordpress.org/Function_Reference/register_nav_menus
	 */
	public function register_menus() {
		if( Helper::get_option( 'storms_add_storms_menu', 'yes' ) ) {
			register_nav_menus(array(
				'main_menu' => __( 'Main Menu', 'storms' ),
			));
		}
	}

	/**
	 * Register widgets area
	 * Header Sidebar widget area
	 */
	public function register_widgets_area_header() {

		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = Helper::get_option( 'storms_widget_title_tag', 'h3' );

		// Header Sidebar
		if( Helper::get_option( 'storms_add_header_sidebar', 'yes' ) ) {

			register_sidebar(array(
				'name' => __( 'Header Sidebar', 'storms' ),
				'id' => 'header-sidebar',
				'description' => __( 'Add widgets here to appear in your header region.', 'storms' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

		if( \StormsFramework\Helper::get_option( 'storms_add_header_menu_item_sidebar', 'yes' ) ) {

			/**
			 * Register a new widget area on the menu as a menu item
			 * @source https://wordpress.org/support/topic/insert-a-plugin-or-a-widget-in-the-top-menu/
			 */
			register_sidebar(array(
				'name' => __( 'Header Menu Item Sidebar', 'storms' ),
				'id' => 'header-menu-item-sidebar',
				'description' => __('Add widgets here to appear in your menu item region.', 'storms'),
				'before_widget' => '<li id="%1$s" class="widget %2$s menu-item nav-item dropdown has-megamenu">',
				'after_widget' => '</li>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

		// Header Menu Right Sidebar
		if( Helper::get_option( 'storms_add_header_menu_right_sidebar', 'yes' ) ) {

			register_sidebar(array(
				'name' => __( 'Header Menu Right Sidebar', 'storms' ),
				'id' => 'header-menu-sidebar-right',
				'description' => __( 'Add widgets here to appear in your header region.', 'storms' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

		// Header Bottom Sidebar
		if( Helper::get_option( 'storms_add_header_bottom_sidebar', 'yes' ) ) {

			register_sidebar(array(
				'name' => __( 'Header Bottom Sidebar', 'storms' ),
				'id' => 'header-bottom-sidebar',
				'description' => __( 'Add widgets here to appear in your header region.', 'storms' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

	}

	/**
	 * Add a sidebar on menu to act as a menu item
	 * Allows to choose which menus will have the sidebar - defaults to 'all' menus
	 *
	 * @param $items
	 * @param $args
	 * @return string
	 */
	public function add_widget_area_on_menu_item( $items, $args ) {
		if( \StormsFramework\Helper::get_option( 'storms_add_header_menu_item_sidebar', 'yes' ) ) {
			$menu_slug = ( $args && $args->menu ) ? $args->menu->slug : '';
			$menu_list = \StormsFramework\Helper::get_option( 'storms_header_menu_item_sidebar_menu_slug_list', 'all' );

			if( empty( $menu_slug ) || 'all' === $menu_list || in_array( $menu_slug, explode( ',', $menu_list ) ) ) {
				$menu_widget_area = \StormsFramework\Helper::get_dynamic_sidebar( 'header-menu-item-sidebar' );
				return $menu_widget_area . $items;
			}

		}
		return $items;
	}

	/**
	 * Register widgets area
	 * Main Sidebar widget area
	 */
	public function register_widgets_area_main() {

		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = Helper::get_option( 'storms_widget_title_tag', 'h3' );

		// Main Sidebar
		if( Helper::get_option( 'storms_add_main_sidebar', 'yes' ) ) {

			register_sidebar(array(
				'name' => __( 'Main Sidebar', 'storms' ),
				'id' => 'main-sidebar',
				'description' => __( 'Add widgets here to appear in your sidebar.', 'storms' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widgettitle widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			));

		}

	}

	/**
	 * Register widgets area
	 * Footer 1 Sidebar widget area
	 */
	public function register_widgets_area_footer_1() {

		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = Helper::get_option( 'storms_widget_title_tag', 'h3' );

		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Top', 'storms'),
				'id' => 'footer-1-sidebar-top',
				'description' => __('Add widgets here to appear in your footer 1 top side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Left', 'storms'),
				'id' => 'footer-1-sidebar-left',
				'description' => __('Add widgets here to appear in your footer 1 left side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Middle 1', 'storms'),
				'id' => 'footer-1-sidebar-middle-1',
				'description' => __('Add widgets here to appear in your footer 1 middle 1 side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Middle 2', 'storms'),
				'id' => 'footer-1-sidebar-middle-2',
				'description' => __('Add widgets here to appear in your footer 1 middle 2 side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Right', 'storms'),
				'id' => 'footer-1-sidebar-right',
				'description' => __('Add widgets here to appear in your footer 1 right side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
		register_sidebar(array(
				'name' => __('Footer 1 Sidebar Bottom', 'storms'),
				'id' => 'footer-1-sidebar-bottom',
				'description' => __('Add widgets here to appear in your footer 1 bottom side.', 'storms'),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<' . $widget_title_tag . ' class="widget-title">',
				'after_title' => '</' . $widget_title_tag . '>',
			)
		);
	}

	/**
	 * Register widgets area
	 * Footer 2 Sidebar widget area
	 */
	public function register_widgets_area_footer_2() {

		// Define what title tag will be use on widgets - h1, h2, h3, ...
		$widget_title_tag = Helper::get_option( 'storms_widget_title_tag', 'h3' );

		// Footer Sidebars
		if( Helper::get_option( 'storms_add_footer_sidebar', 'yes' ) ) {

			$numFooterSidebars = Helper::get_option( 'storms_number_of_footer_sidebars', 4 );
			for ($i = 1; $i <= intval($numFooterSidebars); $i++) {
				register_sidebar(array(
						'name' => sprintf( __( 'Footer Sidebar %d', 'storms' ), $i ),
						'id' => sprintf( 'footer-sidebar-%d', $i ),
						'description' => sprintf( __( 'Add widgets here to appear in your footer region %d.', 'storms' ), $i ),
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

}
