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
 * StormsFramework\FrontEnd class
 * Customization of the Wordpress FrontEnd
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
			->add_filter( 'the_generator', 'remove_the_generator' )

			// Theme customizations
			->add_action( 'init', 'setup_customize_support' )

            // Post image sizes
            ->add_action( 'init', 'post_image_sizes' )
            // Gallery image sizes
            ->add_action( 'init', 'gallery_image_sizes' );

		// Links and tags cleanup
		remove_action( 'wp_head', 'rel_canonical' );
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
			->add_filter( 'tiny_mce_before_init', 'add_layout_to_tinymce_body_class' )
			->add_filter( 'embed_oembed_html', 'embed_wrap' );

        //$this->loader
        //			->add_action( 'template_redirect', 'nice_search_redirect' )
        //			->add_filter( 'nav_menu_css_class', 'remove_nav_classes', 100, 1 )
        //			->add_filter( 'nav_menu_item_id', 'remove_nav_classes', 100, 1 )
        //			->add_filter( 'page_css_class', 'remove_nav_classes', 100, 1 );

        // @TODO Verificar se este codigo deve permanecer - Avaliar codigo em functions.php
		//$this->disable_auto_paragraphs();

		$this->remove_emojis();
		$this->loader
			->add_filter( 'wp_head', 'remove_wp_widget_recent_comments_style', 1 )
			->add_filter( 'wp_head', 'remove_recent_comments_style', 1 )
			->add_filter( 'use_default_gallery_style', 'remove_default_gallery_style' )
			->add_action( 'wp_head', 'add_website_meta_tags' );

        $this->loader
            ->add_filter( 'the_category', 'add_category_slug', 99, 1 )
            ->add_filter( 'the_title', 'menu_title_markup', 10, 2 );

        $this->loader
            ->add_action( 'init', 'overwrite_gallery_shortcode' )
            ->add_filter( 'image_size_names_choose', 'image_sizes_choose' );

        // Storms Popular Posts Shortcode
        $this->loader
            ->add_action( 'wp_head', 'track_posts' )
            ->add_filter( 'manage_posts_columns', 'add_views_column_title' )
            ->add_action( 'manage_posts_custom_column', 'add_views_column_content', 10, 2 );
        add_shortcode( 'storms_popular_posts', array( $this, 'popular_posts' ) );
    }

	//<editor-fold desc="FrontEnd optimizations">

	/**
	 * Setup the theme support
	 */
	public function setup_features() {
		/**
		 * Make theme available for translation
		 * Translations can be filed in the /languages/ directory
		 */
		load_theme_textdomain( 'storms', plugin_dir_path( STORMS_FRAMEWORK_PATH ) . 'languages' );

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
		set_post_thumbnail_size( get_option( 'post_thumb_width' , 825 ), get_option( 'post_thumb_height' , 510 ), get_option( 'post_thumb_crop' , true ) );

		/**
		 * Support HTML5
		 * Switch default core markup for search form, comment form, and comments to output valid HTML5
		 * See: http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
		 */
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

		/**
		 * Enable support for Post Formats.
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'
		) );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Enable support for wide alignment class for Gutenberg blocks
		add_theme_support( 'align-wide' );
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

	//</editor-fold>

	//<editor-fold desc="Theme customizations">

	/**
	 * Set up customization options for the theme
	 */
	public function setup_customize_support() {
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
     * Set the post image sizes
     */
    public function post_image_sizes() {
        // Gallery Image Sizes
        add_image_size( 'storms-post-main', get_option( 'post_main_width', 650 ), get_option( 'post_main_height', 420 ), get_option( 'post_main_crop', true ) );
        add_image_size( 'storms-post-thumb', get_option( 'post_thumb_width', 240 ), get_option( 'post_thumb_height', 240 ), get_option( 'post_thumb_crop', true ) );
    }

    /**
     * Set the wp gallery image sizes
     */
	public function gallery_image_sizes() {
        // Gallery Image Sizes
        add_image_size( 'storms-gallery-main', get_option( 'gallery_main_width', 400 ), get_option( 'gallery_main_height', 250 ), get_option( 'gallery_main_crop', true ) );
        add_image_size( 'storms-gallery-thumb', get_option( 'gallery_thumb_width', 200 ), get_option( 'gallery_thumb_height', 125 ), get_option( 'gallery_thumb_crop', true ) );
    }

	//</editor-fold>

	//<editor-fold desc="Styles, links and tags cleanup">

	/**
	 * Redirects ?s=query searches to /search/query, and converts %20 to +
     * @TODO Does not work properly - fails when 's' is empty and ignore other search attributes
	 * @link http://txfx.net/wordpress-plugins/nice-search/
	 */
	public function nice_search_redirect() {
		global $wp_rewrite;

		if ( !isset( $wp_rewrite ) || !is_object( $wp_rewrite ) || !$wp_rewrite->using_permalinks() )
			return;

		$search_base = $wp_rewrite->search_base;
		if ( is_search() && !is_admin() && ! empty( $_GET['s'] ) ) {
			wp_redirect( home_url( "/{$search_base}/" . urlencode( get_query_var( 's' ) ) ) );
			exit();
		}
	}

	/**
	 * Change the separator between title tag parts
	 */
	public function title_separator() {
		return get_option( 'title_separator', '|' );
	}

	/**
	 * Remove self-closing tag and change ''s to "'s on rel_canonical()
	 */
	public function rel_canonical() {
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
		preg_match_all("!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches);
		// Only display media if it is meaningful
		$media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';
		return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
	}

	/**
	 * Clean up output of <script> tags
	 */
	public function clean_script_tag( $input ) {
		$input = str_replace( "type='text/javascript' ", '', $input );
		return $input;
	}

	/**
	 * Remove unnecessary self-closing tags
	 */
	public function remove_self_closing_tags( $input ) {
		return str_replace( ' />', '>', $input );
	}

	/**
	 * Remove version query string from all styles and scripts
     * @see https://www.virendrachandak.com/techtalk/how-to-remove-wordpress-version-parameter-from-js-and-css-files/
     * @see https://kellenmace.com/force-css-changes-to-go-live-immediately/
	 */
	public function remove_script_version( $src ) {
	    if( ! $src ) {
	        return false;
        }

        // Remove the default version parameter from resources
	    $src = esc_url( remove_query_arg( 'ver', $src ) );

	    if( get_option( 'timestamp_assets', true ) ) {
            $ver = filemtime( get_stylesheet_directory() . '/functions.php' );
            $src = esc_url( add_query_arg( [ 'ver' => $ver ], $src ) );
        }

        return $src;
	}

	/**
	 * Clean up language_attributes() used in <html> tag - Remove dir="ltr"
	 */
	public function language_attributes() {
		$attributes = [];

		if ( is_rtl() )
			$attributes[] = 'dir="rtl"';

		$lang = get_bloginfo( 'language' );

		if ($lang)
			$attributes[] = "lang=\"$lang\"";

		$output = implode( ' ', $attributes );
		$output = apply_filters( 'storms_language_attributes', $output );

		return $output;
	}

	/**
	 * Filters to remove IDs and classes from menu items
	 * @TODO This code is removing anything! even when user adds custom classes do the menu item
	 * @link http://stackoverflow.com/questions/5222140/remove-li-class-id-for-menu-items-and-pages-list
	 */
	function remove_nav_classes( $var ) {
		return is_array( $var ) ? array() : '';
	}

	/**
	 * Cleanup body classes
	 * Add layout classes
	 */
	public function body_class( $classes ) {
		// Add post/page slug if not present
		if ( is_single() || is_page() && !is_front_page() ) {
			if ( !in_array( basename( get_permalink()), $classes ) ) {
				$classes[] = basename( get_permalink() );
			}
		}

		$pattern = "/page-template|page-id-|postid-|single-format-standard|author-/";
		$classes = preg_grep($pattern, $classes, PREG_GREP_INVERT);

		return $classes;
	}

	/**
	 * Add layout classes to tinymce body class
	 */
	public function add_layout_to_tinymce_body_class( $init_array ) {

		$init_array['body_class'] = 'layout-' . get_theme_mod( 'theme_layout' );

		return $init_array;
	}

	/**
	 * Wrap embedded media as suggested by Readability
	 * @link https://gist.github.com/965956
	 * @link http://www.readability.com/publishers/guidelines#publisher
	 */
	public function embed_wrap( $cache ) {
		return '<div class="entry-content-asset">' . $cache . '</div>';
	}

	/**
	 * Disable automatic paragraph tags
	 */
	public function disable_auto_paragraphs() {
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_excerpt', 'wpautop' );
	}

	/**
	 * Remove Emoji's from Wordpress
	 */
	public function remove_emojis() {
		if( get_option( 'remove_emoji', true ) ) {
			// Emoji's
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

			$this->loader->add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
		}
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 */
	public function disable_emojis_tinymce( $plugins ) {
		return ( is_array( $plugins ) ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	}

	/**
	 * Remove injected CSS for recent comments widget
	 */
	public function remove_wp_widget_recent_comments_style() {
		if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) )
			remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
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
	 * Remove injected CSS from gallery
	 */
	public function remove_default_gallery_style() {
		return false; // __return_false
	}

	/**
	 * Add meta tags with description and keywords for the website
	 * Not add to admin pages
	 * Meta tags added:
	 * 	- description meta tag
	 * 	- keywords meta tag
	 */
	public function add_website_meta_tags() {
		if (!is_admin()) {
			$meta_tags = '';
			if ( get_option( 'meta_description' , '' ) != '' ) {
				$meta_description = get_option( 'meta_description' , '' );
				$meta_tags .= '<meta name="description" content="' . $meta_description . '">';
			}
			if ( get_option( 'meta_keywords' , '' ) != '' ) {
				$meta_keywords = get_option( 'meta_keywords' , '' );
				$meta_tags .= '<meta name="keywords" content="' . $meta_keywords . '" />';
			}
		}

		echo $meta_tags;
	}

	//</editor-fold>

    // Adiciona o slug da categoria em the_category()
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

    // Permite usar #BR# nos itens do menu para quebrar linhas
    // @see http://wordpress.stackexchange.com/a/105900/54025
    public function menu_title_markup( $title, $id ) {
        if ( is_nav_menu_item ( $id ) ){
            $title = str_ireplace( "#BR#", "<br/>", $title );
        }
        return $title;
    }

    // Making custom sizes choosable from the Media Gallery
    // @see https://code.tutsplus.com/tutorials/using-custom-image-sizes-in-your-theme-and-resizing-existing-images--wp-24815
    public function image_sizes_choose( $sizes ) {
        $custom_sizes = array(
            'storms-post-main'  => 'Storms Post Main Image (' . get_option( 'post_main_width', 400 ) . 'x' . get_option( 'post_main_height', 250 ) . ')',
            'storms-post-thumb'  => 'Storms Post Thumb Image (' . get_option( 'post_thumb_width', 400 ) . 'x' . get_option( 'post_thumb_height', 250 ) . ')',
            'storms-gallery-main'  => 'Storms WP Gallery Main Image (' . get_option( 'gallery_main_width', 400 ) . 'x' . get_option( 'gallery_main_height', 250 ) . ')',
            'storms-gallery-thumb' => 'Storms WP Gallery Thumb Image (' . get_option( 'gallery_thumb_width', 200 ) . 'x' . get_option( 'gallery_thumb_height', 125 ) . ')',
        );
        return array_merge( $sizes, $custom_sizes );
    }

    //<editor-fold desc="Storms Post Gallery Shortcode">

    /**
     * Custom Post Gallery
     * Builds the Gallery shortcode output, using Cycle2 jQuery plugin
     *
     * This implements the functionality of the Gallery Shortcode for displaying
     * WordPress images on a post.
     *
     * @since 2.5.0
     *
     * @staticvar int $instance
     *
     * @param array $attr {
     *     Attributes of the gallery shortcode.
     *
     *     @type string       $order      Order of the images in the gallery. Default 'ASC'. Accepts 'ASC', 'DESC'.
     *     @type string       $orderby    The field to use when ordering the images. Default 'menu_order ID'.
     *                                    Accepts any valid SQL ORDERBY statement.
     *     @type int          $id         Post ID.
     *     @type string       $itemtag    HTML tag to use for each image in the gallery.
     *                                    Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
     *     @type string       $icontag    HTML tag to use for each image's icon.
     *                                    Default 'dt', or 'div' when the theme registers HTML5 gallery support.
     *     @type string       $captiontag HTML tag to use for each image's caption.
     *                                    Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
     *     @type int          $columns    Number of columns of images to display. Default 3.
     *     @type string|array $size       Size of the images to display. Accepts any valid image size, or an array of width
     *                                    and height values in pixels (in that order). Default 'thumbnail'.
     *     @type string       $ids        A comma-separated list of IDs of attachments to display. Default empty.
     *     @type string       $include    A comma-separated list of IDs of attachments to include. Default empty.
     *     @type string       $exclude    A comma-separated list of IDs of attachments to exclude. Default empty.
     *     @type string       $link       What to link each image to. Default empty (links to the attachment page).
     *                                    Accepts 'file', 'none'.
     * }
     * @return string HTML content to display gallery.
     */
    public function post_gallery( $attr ) {
        $post = get_post();
        static $instance = 0;
        $instance++;
        if ( ! empty( $attr['ids'] ) ) {
            // 'ids' is explicitly ordered, unless you specify otherwise.
            if ( empty( $attr['orderby'] ) ) {
                $attr['orderby'] = 'post__in';
            }
            $attr['include'] = $attr['ids'];
        }

        /**
         * Filters the default gallery shortcode output.
         *
         * If the filtered output isn't empty, it will be used instead of generating
         * the default gallery template.
         *
         * @since 2.5.0
         * @since 4.2.0 The `$instance` parameter was added.
         *
         * @see gallery_shortcode()
         *
         * @param string $output   The gallery output. Default empty.
         * @param array  $attr     Attributes of the gallery shortcode.
         * @param int    $instance Unique numeric ID of this gallery shortcode instance.
         */
        $output = apply_filters( 'post_gallery', '', $attr, $instance );
        if ( $output != '' ) {
            return $output;
        }
        $html5 = current_theme_supports( 'html5', 'gallery' );
        $atts = shortcode_atts( array(
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post ? $post->ID : 0,
            'itemtag'    => $html5 ? 'figure'     : 'dl',
            'icontag'    => $html5 ? 'div'        : 'dt',
            'captiontag' => $html5 ? 'figcaption' : 'dd',
            'columns'    => 3,
            'size'       => 'thumbnail',
            'include'    => '',
            'exclude'    => '',
            'link'       => ''
        ), $attr, 'gallery' );
        $id = intval( $atts['id'] );
        if ( ! empty( $atts['include'] ) ) {
            $_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
            $attachments = array();
            foreach ( $_attachments as $key => $val ) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif ( ! empty( $atts['exclude'] ) ) {
            $attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
        } else {
            $attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
        }
        if ( empty( $attachments ) ) {
            return '';
        }
        if ( is_feed() ) {
            $output = "\n";
            foreach ( $attachments as $att_id => $attachment ) {
                $output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
            }
            return $output;
        }
        $image_main_slide = '';
        $image_thumb_slide = '';
        foreach ( $attachments as $att_id => $attachment ) {
            $image_main = wp_get_attachment_image( $att_id, get_option( 'gallery_main_img_size', 'storms-gallery-main' ), false );
            $image_thumb = wp_get_attachment_image( $att_id, get_option( 'gallery_thumb_img_size', 'storms-gallery-thumb' ), false );
            $image_main_slide .= "<figure>";
            $image_main_slide .= $image_main;
            if ( trim($attachment->post_excerpt) ) {
                $image_main_slide .= "<figcaption>" . wptexturize( $attachment->post_excerpt ) . "</figcaption>";
            }
            $image_main_slide .= "</figure>";
            $image_thumb_slide .= "<figure>";
            $image_thumb_slide .= $image_thumb;
            $image_thumb_slide .= "</figure>";
        }
        /* ============================================================ */
        $output_css  = '';
        $output_css .= '    <style> ';
        $output_css .= '        .storms-gallery { ';
        $output_css .= '            margin: 35px 0; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .cycle-prev, ';
        $output_css .= '        .storms-gallery .cycle-next { ';
        $output_css .= '            font-size: 5em; ';
        $output_css .= '            position: absolute; ';
        $output_css .= '            bottom: 0; ';
        $output_css .= '            color: #52accc; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .cycle-prev { ';
        $output_css .= '            float: left; ';
        $output_css .= '            left: -35px; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .cycle-next { ';
        $output_css .= '            float: right; ';
        $output_css .= '            right: -35px; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .slide-midia .cycle-1 figure { ';
        $output_css .= '            width:100%; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .slide-thumb { ';
        $output_css .= '            margin-top: 10px; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .slide-midia .cycle-1 .cycle-slide, ';
        $output_css .= '        .storms-gallery .slide-thumb .cycle-2 .cycle-slide { ';
        $output_css .= '            padding: 6px; ';
        $output_css .= '        } ';
        $output_css .= '        .storms-gallery .slide-midia .cycle-1 img, ';
        $output_css .= '        .storms-gallery .slide-thumb .cycle-2 img { ';
        $output_css .= '            width: 100%; ';
        $output_css .= '            height: auto; ';
        $output_css .= '        } ';
        $output_css .= '        .cycle-2 .cycle-slide-active img { ';
        $output_css .= '            -webkit-filter: drop-shadow(1px 1px 5px #52accc); ';
        $output_css .= '            filter: drop-shadow(1px 1px 5px #52accc); ';
        $output_css .= '        } ';
        $output_css .= '    </style> ';
        /* ============================================================ */
        $output_js  = '';
        $output_js .= '			<script> ';
        $output_js .= '				jQuery(document).ready(function($) { ';
        $output_js .= '					var slideshows = $(".cycle-slideshow", ".gallery-'. $id . '").on("cycle-next cycle-prev", function(e, opts) { ';
        $output_js .= '						slideshows.not(this).cycle("goto", opts.currSlide); ';
        $output_js .= '					}); ';
        $output_js .= '					$("#cycle-'. $id . '-2 .cycle-slide").on( "click", function() { ';
        $output_js .= '						var index = $("#cycle-'. $id . '-2").data("cycle.API").getSlideIndex(this); ';
        $output_js .= '						slideshows.cycle("goto", index); ';
        $output_js .= '					}); ';
        $output_js .= '				}); ';
        $output_js .= '			</script> ';
        /* ============================================================ */
        $output  = '';
        $output .=      $output_css;
        $output .= '	<div class="row storms-gallery gallery-'. $id . '"> ';
        $output .= '		<div class="col-lg-12"> ';
        $output .= '			<div id="slideshow-'. $id . '-1" class="slide-midia"> ';
        $output .= '				<div id="cycle-'. $id . '-1" class="cycle-slideshow cycle-1" data-cycle-slides="> figure" data-cycle-timeout="0"> ';
        $output .=                      $image_main_slide;
        $output .= '				</div> ';
        $output .= '			</div> ';
        $output .= '			<div id="slideshow-'. $id . '-2" class="slide-thumb"> ';
        $output .= '				<div id="cycle-'. $id . '-2" class="cycle-slideshow cycle-2" data-cycle-slides="> figure" data-cycle-timeout="0" data-cycle-prev="#slideshow-'. $id . '-2 #cycle-'. $id . '-prev" ';
        $output .= '					 data-cycle-next="#slideshow-'. $id . '-2 #cycle-'. $id . '-next" data-cycle-fx="carousel" data-cycle-carousel-visible="5" data-cycle-carousel-fluid=true> ';
        $output .=                      $image_thumb_slide;
        $output .= '				</div> ';
        $output .= '				<a href="#" id="cycle-'. $id . '-prev" class="cycle-prev"><i class="fa fa-angle-left"></i></a> ';
        $output .= '				<a href="#" id="cycle-'. $id . '-next" class="cycle-next"><i class="fa fa-angle-right"></i></a> ';
        $output .= '			</div> ';
        $output .= '		</div> ';
        $output .= '	</div> ';
        $output .=      $output_js;

        // If Cycle2 jQuery plugin is loaded, we enqueue it - If not, the user should enqueue it himself
        if( get_option( 'load_cycle2', true ) ) {
            wp_enqueue_script( 'cycle2' );
            wp_enqueue_script( 'cycle2-carousel' );
        }

        return $output;
    }

    public function overwrite_gallery_shortcode() {
        remove_shortcode( 'gallery' );
        add_shortcode( 'gallery', array( $this, 'post_gallery' ) );
    }

    //</editor-fold>

    //<editor-fold desc="Storms Popular Posts Shortcode">

    /**
     * Popular posts tracker
     * Add the post counter every time a user read it
     * @see https://digwp.com/2016/03/diy-popular-posts/
     */
    public function track_posts( $post_id ) {
        if( !is_single() ) {
            return;
        }

        if( empty( $post_id ) ) {
            global $post;
            $post_id = $post->ID;
        }

        $count_key = 'post_popular_views';
        $count = get_post_meta( $post_id, $count_key, true );
        if ($count == '') {
            $count = 0;
            delete_post_meta( $post_id, $count_key );
            add_post_meta( $post_id, $count_key, '0' );
        } else {
            $count++;
            update_post_meta( $post_id, $count_key, $count );
        }
    }

    /**
     * Add popular post counter on posts list - Column title
     * @see https://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
     * To keep the count accurate, we have to get rid of prefetching - remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
     */
    public function add_views_column_title( $defaults ) {
        $defaults['post_popular_views'] = __( 'Views', 'storms' );
        return $defaults;
    }

    /**
     * Add popular post counter on posts list - Column content
     * @see https://code.tutsplus.com/articles/add-a-custom-column-in-posts-and-custom-post-types-admin-screen--wp-24934
     * To keep the count accurate, we have to get rid of prefetching - remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
     */
    public function add_views_column_content( $column_name, $post_ID ) {
        if ($column_name == 'post_popular_views') {
            echo get_post_meta( $post_ID, 'post_popular_views', true );
        }
    }

    /**
     * Shortcode to show most popular posts
     * @TODO Carregar um template, para que o usuario possa customizar
     */
    public function popular_posts( $args ) {
        $args = shortcode_atts( array(
            'posts_per_page' => 7,
        ), $args );
        $popular = new WP_Query(array(
            'posts_per_page' => $args['posts_per_page'],
            'meta_key' => 'post_popular_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ));
        if ( $popular->have_posts() ) {
            ?>
            <?php
            if(isset($args['title'])) {
                echo '<h3>' . $args['title'] . '</h3>';
            }
            while ($popular->have_posts()) :
                $popular->the_post();
                ?>

                <article class="popular-post">
                    <div class="row">
                        <div class="col-sm-3" style="padding-right: 0;">
                            <a href="<?php the_permalink(); ?>">
                                <figure class="img-blog-over">
                                    <?php the_post_thumbnail( 'storms-post-thumb', array( 'class' => 'img-fluid blog-img' ) ); ?>
                                </figure>
                            </a>
                        </div>
                        <div class="col-sm-9 content-box">
                            <header class="clearfix">
                                <ul class="list-unstyled list-inline">
                                    <li class="category">
                                        <?php
                                        $category_html = '';
                                        $categories = get_the_category();
                                        if ( ! empty( $categories ) ) :
                                            $category = $categories[0]; ?>
                                            <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" rel="category" class="<?php echo $category->slug; ?>"><?php echo $category->name; ?></a>
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <time class="data x-small"><?php the_time('j | M'); ?></time>
                                    </li>
                                </ul>
                            </header>

                            <div class="popular-post-content">
                                <a class="post-title" href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <?php
            endwhile;
            wp_reset_postdata();
        }
    }

    //</editor-fold>

}
