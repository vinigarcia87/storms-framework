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
 * Template class
 * @package StormsFramework
 *
 * Adding support for custom HTML templates
 * @see  _documentation/Template_Class.md
 */

namespace StormsFramework;

class Template extends Base\Runner
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public function define_hooks() {

		$this->loader
			/* Set up the custom post layouts. */
			->add_action( 'admin_init', 'admin_setup' )
			/* Register metadata with WordPress. */
			->add_action( 'init', 'register_meta' )
			/* Add post type support for theme layouts. */
			->add_action( 'init', 'add_post_type_support', 5 )
			/* Add layout option in Customize. */
			->add_action( 'customize_register', 'customize_register' )
			->add_action( 'customize_register', 'theme_layout_customize_refresh', 11 )
			/* Filters the theme layout mod. */
			->add_filter( 'theme_mod_theme_layout', 'filter_layout', 5 )
			/* Filters the body_class hook to add a custom class. */
			->add_filter( 'body_class', 'add_layout_to_body_class' )
			/* Filters the tiny_mce_body_class hook to add a custom class. */
			//->add_filter( 'tiny_mce_before_init', 'add_layout_to_tinymce_body_class' )
			/* Uses the selected template to decide if the sidebar must be shown or not */
			->add_filter( 'is_active_sidebar', 'disable_sidebars', 10, 2 );

	}

	//<editor-fold desc="Layout getters and setters">

	/**
	 * Gets all the available layouts for the theme.
	 *
	 * @since  0.5.0
	 * @access public
	 * @return array  Either theme-supported layouts or the default layouts.
	 */
	private function get_layouts() {
		// Set up the default layout
		$default = array(
			/* Translators: Default theme layout option. */
			'default' => array(
				'title' => _x( 'Default', 'theme layout', 'storms' )
			)
		);

		// Get theme-supported layouts
		$layouts = get_theme_support( 'theme-layouts' );

		// Assign the strings passed in by the theme author
		if ( isset( $layouts[0] ) ) {
			$layouts[0] = array_merge( $default, $layouts[0] );
		}

		return isset( $layouts[0] ) ? $layouts[0] : array();
	}

	/**
	 * Get all layout's slugs
	 *
	 * @return array
	 */
	private function get_layouts_keys() {
		$layouts = $this->get_layouts();
		return array_keys( $layouts );
	}

	/**
	 * Get a specific layout's title.
	 *
	 * @since  0.2.0
	 * @param  string $layout
	 * @return string
	 */
	private function get_layout_title( $layout_name ) {

		/* Get an array of post layout strings. */
		$layouts = $this->get_layouts();

		/* Return the layout's string if it exists. Else, return the layout slug. */
		return ( ( isset( $layouts[ $layout_name ]['title'] ) ) ? $layouts[ $layout_name ]['title'] : $layout_name );
	}

	/**
	 * Get the post layout based on the given post ID.
	 *
	 * @since  0.2.0
	 * @param  int    $post_id The ID of the post to get the layout for.
	 * @return string $layout The name of the post's layout.
	 */
	private function get_post_layout( $post_id ) {

		/* Get the post layout. */
		$layout = get_post_meta( $post_id, $this->get_meta_key(), true );

		/* Return the layout if one is found.  Otherwise, return 'default'. */
		return ( !empty( $layout ) ? $layout : 'default' );
	}

	/**
	 * Update/set the post layout based on the given post ID and layout.
	 *
	 * @since  0.2.0
	 * @access public
	 * @param  int    $post_id The ID of the post to set the layout for.
	 * @param  string $layout  The name of the layout to set.
	 * @return bool            True on successful update, false on failure.
	 */
	private function set_post_layout( $post_id, $layout ) {
		return update_post_meta( $post_id, $this->get_meta_key(), $layout );
	}

	/**
	 * Deletes a post layout.
	 *
	 * @since  0.4.0
	 * @access public
	 * @param  int    $post_id The ID of the post to delete the layout for.
	 * @return bool            True on successful delete, false on failure.
	 */
	private function delete_post_layout( $post_id ) {
		return delete_post_meta( $post_id, $this->get_meta_key() );
	}

	/**
	 * Checks if a specific post's layout matches that of the given layout.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  string $layout  The name of the layout to check if the post has.
	 * @param  int    $post_id The ID of the post to check the layout for.
	 * @return bool            Whether the given layout matches the post's layout.
	 */
	private function has_post_layout( $layout, $post_id = '' ) {

		/* If no post ID is given, use WP's get_the_ID() to get it and assume we're in the post loop. */
		if ( empty( $post_id ) )
			$post_id = get_the_ID();

		/* Return true/false based on whether the layout matches. */
		return ( $layout == $this->get_post_layout( $post_id ) ? true : false );
	}

	/**
	 * Get the layout for a user/author archive page based on a specific user ID.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  int    $user_id The ID of the user to get the layout for.
	 * @return string          The layout if one exists, 'default' if one doesn't.
	 */
	private function get_user_layout( $user_id ) {

		/* Get the user layout. */
		$layout = get_user_meta( $user_id, $this->get_meta_key(), true );

		/* Return the layout if one is found.  Otherwise, return 'default'. */
		return ( !empty( $layout ) ? $layout : 'default' );
	}

	/**
	 * Update/set the layout for a user/author archive paged based on the user ID.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  int    $user_id The ID of the user to set the layout for.
	 * @param  string $layout  The name of the layout to set.
	 * @return bool            True on successful update, false on failure.
	 */
	private function set_user_layout( $user_id, $layout ) {
		return update_user_meta( $user_id, $this->get_meta_key(), $layout );
	}

	/**
	 * Deletes a user layout.
	 *
	 * @since  0.4.0
	 * @access public
	 * @param  int    $user_id The ID of the user to delete the layout for.
	 * @return bool            True on successful delete, false on failure.
	 */
	private function delete_user_layout( $user_id ) {
		return delete_user_meta( $user_id, $this->get_meta_key() );
	}

	/**
	 * Checks if a specific user's layout matches that of the given layout.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  string $layout  The name of the layout to check if the user has.
	 * @param  int    $user_id The ID of the user to check the layout for.
	 * @return bool            Whether the given layout matches the user's layout.
	 */
	private function has_user_layout( $layout, $user_id = '' ) {

		/* If no user ID is given, assume we're viewing an author archive page and get the user ID. */
		if ( empty( $user_id ) )
			$user_id = get_query_var( 'author' );

		/* Return true/false based on whether the layout matches. */
		return ( $layout == $this->get_user_layout( $user_id ) ? true : false );
	}

	/**
	 * Gets the layout for the current post based off the 'Layout' custom field key if viewing a singular post
	 * entry.  All other pages are given a default layout of 'layout-default'.
	 *
	 * @since  0.2.0
	 * @access public
	 * @return string The layout for the given page.
	 */
	private function get_layout() {

		// Get the available theme layouts.
		$layouts = $this->get_layouts_keys();

		// Get the theme layout arguments.
		$args = $this->get_args();

		// Set the layout to an empty string.
		$layout = get_theme_mod( 'theme_layout', $args['default'] );

		// Make sure the given layout is in the array of available post layouts for the theme.
		if ( empty( $layout ) || !in_array( $layout, $layouts ) || 'default' == $layout ) {
			$layout = $args['default'];
		}

		if( 'default' === $layout ) {
			$layout = $this->get_layout_for_default();
		}

		return esc_attr( apply_filters( 'theme_layout_get_layout', $layout ) );
	}

	/**
	 * Get the default layout defined for the theme
	 * @return string
	 */
	private function get_layout_for_default() {
		return Template::get_theme_layout();
	}

	/**
	 * Get the selected layout for a page
	 * @return string
	 */
	private static function get_theme_layout() {

		$layout = get_theme_mod( 'theme_layout', 'default' );

		// Check if layout is a valid value - if it is not, then we default to '2c-r'
		if( ! in_array( $layout, [ '1c', '2c-r', '2c-l' ] ) ) {
			$layout = 'default';
		}

		if( 'default' === $layout ) {
			if( is_product() ) {
				$layout = get_option( 'product_layout', '1c' );

			}elseif( is_shop() || is_product_category() || is_product_tag() ) {
				$layout = get_option( 'shop_layout', '2c-l' );

			} elseif( is_404() ) {
				$layout = get_option( 'page_layout', '1c' );

			} elseif( is_page() ) {
				$layout = get_option( 'page_layout', '1c' );

			} elseif( is_single() ) {
				$layout = get_option( 'single_layout', '1c' );

			} else {
				$layout = is_rtl() ? '2c-r' : '2c-l';

			}
		}

		return esc_attr( apply_filters( 'theme_layout_get_layout', $layout ) );
	}

	/**
	 * Wrapper function for returning the metadata key used for objects that can use layouts
	 *
	 * @since  0.3.0
	 * @access public
	 * @return string The meta key used for theme layouts.
	 */
	private function get_meta_key() {
		return apply_filters( 'theme_layouts_meta_key', 'Layout' );
	}

	/**
	 * Returns an array of arguments for setting up the theme layouts script.  The defaults are merged
	 * with the theme-supported arguments.
	 *
	 * @since  0.5.0
	 * @access public
	 * @return array  Arguments for the theme layouts script.
	 */
	private function get_args() {

		$defaults = array(
			'customize' => true,
			'post_meta' => true,
			'default'   => 'default'
		);

		$layouts = get_theme_support( 'theme-layouts' );

		$args = isset( $layouts[1] ) ? $layouts[1] : array();

		return apply_filters( 'theme_layouts_args', wp_parse_args( $args, $defaults ) );
	}

	//</editor-fold>

	//<editor-fold desc="Template engine">

	/**
	 * Registers the theme layouts meta key ('Layout') for specific object types and provides a function to
	 * sanitize the metadata on update.
	 *
	 * @since  0.4.0
	 * @access public
	 * @return void
	 */
	public function register_meta() {
		register_meta( 'post', $this->get_meta_key(), array( $this, 'sanitize_meta' ) );
		register_meta( 'user', $this->get_meta_key(), array( $this, 'sanitize_meta' ) );
	}

	/**
	 * Callback function for sanitizing meta when add_metadata() or update_metadata() is called by WordPress.
	 * If a developer wants to set up a custom method for sanitizing the data, they should use the
	 * "sanitize_{$meta_type}_meta_{$meta_key}" filter hook to do so.
	 *
	 * @since  0.4.0
	 * @access public
	 * @param  mixed  $meta_value The value of the data to sanitize.
	 * @param  string $meta_key   The meta key name.
	 * @param  string $meta_type  The type of metadata (post, comment, user, etc.)
	 * @return mixed  $meta_value
	 */
	public function sanitize_meta( $meta_value, $meta_key, $meta_type ) {
		return sanitize_html_class( $meta_value );
	}

	/**
	 * Adds post type support to all 'public' post types.  This allows themes to remove support for the
	 * 'theme-layouts' feature with remove_post_type_support().
	 *
	 * @since  0.4.0
	 * @access public
	 * @return void
	 */
	public function add_post_type_support() {

		/* Gets available public post types. */
		$post_types = get_post_types( array( 'public' => true ) );

		/* For each available post type, create a meta box on its edit page if it supports '$prefix-post-settings'. */
		foreach ( $post_types as $type ) {
			add_post_type_support( $type, 'theme-layouts' );
		}
	}

	/**
	 * Post layouts admin setup.  Registers the post layouts meta box for the post editing screen.
	 * Adds the metadata save function to the 'save_post' hook.
	 *
	 * @since  0.2.0
	 * @access public
	 * @return void
	 */
	public function admin_setup() {

		// Get the extension arguments
		$args = $this->get_args();

		/* Return if the theme doesn't support the post meta box. */
		if ( false === $args['post_meta'] ) {
			return;
		}

		/* Load the post meta boxes on the new post and edit post screens. */
		add_action( 'load-post.php',     array( $this, 'load_meta_boxes' ) );
		add_action( 'load-post-new.php', array( $this, 'load_meta_boxes' ) );

		/* If the attachment post type supports 'theme-layouts', add form fields for it. */
		if ( post_type_supports( 'attachment', 'theme-layouts' ) ) {

			/* Adds a theme layout <select> element to the attachment edit form. */
			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );

			/* Saves the theme layout for attachments. */
			add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 10, 2 );
		}
	}

	/**
	 * Adds a select drop-down element to the attachment edit form for selecting the attachment layout.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  array  $fields Array of fields for the edit attachment form.
	 * @param  object $post   The attachment post object.
	 * @return array  $fields
	 */
	public function attachment_fields_to_edit( $fields, $post ) {

		/* Get theme-supported theme layouts. */
		$post_layouts = $this->get_layouts();

		/* Get the current post's layout. */
		$post_layout = $this->get_post_layout( $post->ID );

		/* Loop through each theme-supported layout, adding it to the select element. */
		$select = '';
		foreach ( $post_layouts as $key => $layout ) {
			$select .= '<option id="post-layout-' . esc_attr( $key ) . '" value="' . esc_attr( $key ) . '" ' . selected( $post_layout, $key, false ) . '>' . esc_html( $this->get_layout_title( $layout['title'] ) ) . '</option>';
		}

		/* Set the HTML for the post layout select drop-down. */
		$select = '<select name="attachments[' . $post->ID . '][theme-layouts-post-layout]" id="attachments[' . $post->ID . '][theme-layouts-post-layout]">' . $select . '</select>';

		/* Add the attachment layout field to the $fields array. */
		$fields['theme-layouts-post-layout'] = array(
			'label'         => __( 'Layout', 'storms' ),
			'input'         => 'html',
			'html'          => $select,
			'show_in_edit'  => false,
			'show_in_modal' => true
		);

		/* Return the $fields array back to WordPress. */
		return $fields;
	}

	/**
	 * Saves the attachment layout for the attachment edit form.
	 *
	 * @since  0.3.0
	 * @access public
	 * @param  array  $post   The attachment post array (not the post object!).
	 * @param  array  $fields Array of fields for the edit attachment form.
	 * @return array  $post
	 */
	public function attachment_fields_to_save( $post, $fields ) {

		/* If the theme layouts field was submitted. */
		if ( isset( $fields['theme-layouts-post-layout'] ) ) {

			/* Get the meta key. */
			$meta_key = $this->get_meta_key();

			/* Get the previous post layout. */
			$meta_value = $this->get_post_layout( $post['ID'] );

			/* Get the submitted post layout. */
			$new_meta_value = $fields['theme-layouts-post-layout'];

			/* If there is no new meta value but an old value exists, delete it. */
			if ( current_user_can( 'delete_post_meta', $post['ID'], $meta_key ) && '' == $new_meta_value && $meta_value ) {
				$this->delete_post_layout( $post['ID'] );

				/* If a new meta value was added and there was no previous value, add it. */
			} elseif ( current_user_can( 'add_post_meta', $post['ID'], $meta_key ) && $new_meta_value && '' == $meta_value ) {
				$this->set_post_layout( $post['ID'], $new_meta_value );

				/* If the old layout doesn't match the new layout, update the post layout meta. */
			} elseif ( current_user_can( 'edit_post_meta', $post['ID'], $meta_key ) && $meta_value !== $new_meta_value ) {
				$this->set_post_layout( $post['ID'], $new_meta_value );
			}
		}

		/* Return the attachment post array. */
		return $post;
	}

	/**
	 * Registers custom sections, settings, and controls for the $wp_customize instance.
	 *
	 * @since     0.1.0
	 * @author    Justin Tadlock <justin@justintadlock.com>
	 * @author    Sami Keijonen  <sami.keijonen@foxnet.fi>
	 * @copyright Copyright (c) 2012
	 * @link      http://themehybrid.com/support/topic/add-theme-layout-in-theme-customize
	 * @access    public
	 * @param     object $wp_customize
	 */
	public function customize_register( $wp_customize ) {

		/* Get supported theme layouts. */
		$layouts = $this->get_layouts();
		$args = $this->get_args();

		if ( true === $args['customize'] ) {

			/* Add the layout section. */
			$wp_customize->add_section(
				'layout',
				array(
					'title'      => esc_html__( 'Layout', 'storms' ),
					'priority'   => 30,
					'capability' => 'edit_theme_options'
				)
			);

			/* Add the 'layout' setting. */
			$wp_customize->add_setting(
				'theme_layout',
				array(
					'default'           => get_theme_mod( 'theme_layout', $args['default'] ),
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_html_class',
					'transport'         => 'postMessage'
				)
			);

			/* Set up an array for the layout choices and add in the 'default' layout. */
			$layout_choices = array();

			/* Only add 'default' if it's the actual default layout. */
			if ( 'default' == $args['default'] ) {
				$layout_choices['default'] = $this->get_layout_title( 'default' );
			}

			/* Loop through each of the layouts and add it to the choices array with proper key/value pairs. */
			foreach ( $layouts as $layout )
				$layout_choices[$layout] = $this->get_layout_title( $layout['title'] );

			/* Add the layout control. */
			$wp_customize->add_control(
				'theme-layout-control',
				array(
					'label'    => esc_html__( 'Global Layout', 'storms' ),
					'section'  => 'layout',
					'settings' => 'theme_layout',
					'type'     => 'radio',
					'choices'  => $layout_choices
				)
			);

			/* If viewing the customize preview screen, add a script to show a live preview. */
			if ( $wp_customize->is_preview() && !is_admin() ) {
				add_action( 'wp_footer', array( $this, 'customize_preview_script' ), 21 );
			}
		}
	}

	/**
	 * Tell WP Customizer to refresh when layout is changed
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function theme_layout_customize_refresh( $wp_customize ) {
		/** @var \WP_Customize_Manager $wp_customize */
		$wp_customize->get_setting( 'theme_layout' )->transport = 'refresh';
	}

	/**
	 * Filters the 'theme_mods_theme_layout' hook to alter the layout based on post and user metadata.
	 * Theme authors should also use this hook to filter the layout if need be.
	 *
	 * @since  0.5.0
	 * @access public
	 * @param  string  $theme_layout
	 * @return string
	 */
	public function filter_layout( $theme_layout ) {

		/* If viewing a singular post, get the post layout. */
		if ( is_singular() ) {
			$layout = $this->get_post_layout( get_queried_object_id() );
		}
		/* If viewing an author archive, get the user layout. */
		elseif ( is_author() ) {
			$layout = $this->get_user_layout( get_queried_object_id() );
		}

		/* If a layout was found, set it. */
		if ( !empty( $layout ) && 'default' !== $layout ) {
			$theme_layout = $layout;
		}
		/* Else, if no layout option has yet been saved, return the theme default. */
		elseif ( empty( $theme_layout ) ) {
			$args = $this->get_args();
			$theme_layout = $args['default'];
		}

		return $theme_layout;
	}

	/**
	 * Hooks into the 'add_meta_boxes' hook to add the theme layouts meta box and the 'save_post' hook
	 * to save the metadata.
	 *
	 * @since  0.4.0
	 * @access public
	 * @return void
	 */
	public function load_meta_boxes() {

		/* Add the layout meta box on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		/* Saves the post format on the post editing page. */
		add_action( 'save_post',       array( $this, 'save_post' ), 10, 2 );
		add_action( 'add_attachment',  array( $this, 'save_post' )        );
		add_action( 'edit_attachment', array( $this, 'save_post' )        );
	}

	/**
	 * Adds the theme layouts meta box if the post type supports 'theme-layouts' and the current user has
	 * permission to edit post meta.
	 *
	 * @since  0.4.0
	 * @access public
	 * @param  string $post_type The post type of the current post being edited.
	 * @param  object $post      The current post object.
	 * @return void
	 */
	public function add_meta_boxes( $post_type, $post ) {

		/* Add the meta box if the post type supports 'post-stylesheets'. */
		if ( ( post_type_supports( $post_type, 'theme-layouts' ) ) && ( current_user_can( 'edit_post_meta', $post->ID ) || current_user_can( 'add_post_meta', $post->ID ) || current_user_can( 'delete_post_meta', $post->ID ) ) ) {
			add_meta_box( 'theme-layouts-post-meta-box', __( 'Layout', 'storms' ), array( $this, 'post_meta_box'), $post_type, 'side', 'default' );
		}
	}

	/**
	 * Displays a meta box of radio selectors on the post editing screen, which allows theme users to select
	 * the layout they wish to use for the specific post.
	 *
	 * @since  0.2.0
	 * @access public
	 * @param  object $post The post object currently being edited.
	 * @param  array  $box  Specific information about the meta box being loaded.
	 * @return void
	 */
	public function post_meta_box( $post, $box ) {

		/* Get theme-supported theme layouts. */
		$post_layouts = $this->get_layouts();

		/* Get the current post's layout. */
		$post_layout = $this->get_post_layout( $post->ID ); ?>

		<div class="post-layout">

		<?php wp_nonce_field( basename( __FILE__ ), 'theme-layouts-nonce' ); ?>

		<div class="post-layout-wrap">
			<ul>
				<?php foreach ( $post_layouts as $key => $layout ) { ?>
					<li><input type="radio" name="post-layout" id="post-layout-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $post_layout, $key ); ?> /> <label for="post-layout-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $this->get_layout_title( $layout['title'] ) ); ?></label></li>
				<?php } ?>
			</ul>
		</div>
		</div><?php
	}

	/**
	 * Saves the post layout metadata if on the post editing screen in the admin.
	 *
	 * @since  0.2.0
	 * @access public
	 * @param  int      $post_id The ID of the current post being saved.
	 * @param  object   $post    The post object currently being saved.
	 * @return void|int
	 */
	public function save_post( $post_id, $post = '' ) {

		/* Fix for attachment save issue in WordPress 3.5. @link http://core.trac.wordpress.org/ticket/21963 */
		if ( !is_object( $post ) )
			$post = get_post();

		/* Verify the nonce for the post formats meta box. */
		if ( !isset( $_POST['theme-layouts-nonce'] ) || !wp_verify_nonce( $_POST['theme-layouts-nonce'], basename( __FILE__ ) ) )
			return $post_id;

		/* Get the meta key. */
		$meta_key = $this->get_meta_key();

		/* Get the previous post layout. */
		$meta_value = $this->get_post_layout( $post_id );

		/* Get the submitted post layout. */
		$new_meta_value = $_POST['post-layout'];

		/* If there is no new meta value but an old value exists, delete it. */
		if ( current_user_can( 'delete_post_meta', $post_id, $meta_key ) && '' == $new_meta_value && $meta_value )
			$this->delete_post_layout( $post_id );

		/* If a new meta value was added and there was no previous value, add it. */
		elseif ( current_user_can( 'add_post_meta', $post_id, $meta_key ) && $new_meta_value && '' == $meta_value )
			$this->set_post_layout( $post_id, $new_meta_value );

		/* If the old layout doesn't match the new layout, update the post layout meta. */
		elseif ( current_user_can( 'edit_post_meta', $post_id, $meta_key ) && $meta_value !== $new_meta_value )
			$this->set_post_layout( $post_id, $new_meta_value );
	}

	/**
	 * JavaScript for handling the live preview editing of the theme layout in the theme customizer.  The
	 * script uses regex to remove all potential "layout-xyz" classes and replaces it with the user-selected
	 * layout.
	 *
	 * @since     0.1.0
	 * @access    public
	 * @author    Justin Tadlock <justin@justintadlock.com>
	 * @author    Sami Keijonen  <sami.keijonen@foxnet.fi>
	 * @copyright Copyright (c) 2012
	 * @link      http://themehybrid.com/support/topic/add-theme-layout-in-theme-customize
	 * @return    void
	 */
	public function customize_preview_script() { ?>

		<script type="text/javascript">
			wp.customize(
				'theme_layout',
				function( value ) {
					value.bind(
						function( to ) {
							var classes = jQuery( 'body' ).attr( 'class' ).replace( /layout-[a-zA-Z0-9_-]*/g, '' );
							jQuery( 'body' ).attr( 'class', classes ).addClass( 'layout-' + to );
						}
					);
				}
			);
		</script>
		<?php
	}

	//</editor-fold>

	//<editor-fold desc="Template functions">

	/**
	 * Adds the post layout class to the WordPress body class in the form of "layout-$layout".  This allows
	 * theme developers to design their theme layouts based on the layout class.  If designing a theme with
	 * this extension, the theme should make sure to handle all possible layout classes.
	 *
	 * @since  0.2.0
	 * @access public
	 * @param  array $classes
	 * @return array $classes
	 */
	public function add_layout_to_body_class( $classes ) {
		/* Adds the layout to array of body classes. */
		$classes[] = sanitize_html_class( $this->get_layout() );

		return $classes;
	}

	/**
	 * Add layout classes to tinymce body class
	 *
	 * @param $init_array
	 * @return mixed
	 */
	public function add_layout_to_tinymce_body_class( $init_array ) {

		$init_array['body_class'] = 'layout-' . sanitize_html_class( $this->get_layout() );

		return $init_array;
	}

	/**
	 * Filters whether a dynamic sidebar is considered "active"
	 * Set sidebars to "inactive" if the layout is defined to not have any sidebar
	 *
	 * @param bool       $is_active_sidebar Whether or not the sidebar should be considered "active".
	 *                                      In other words, whether the sidebar contains any widgets.
	 * @param int|string $index             Index, name, or ID of the dynamic sidebar.
	 * @return bool
	 */
	public function disable_sidebars( $is_active_sidebar, $index ) {

		$layout = $this->get_layout();

		if( '1c' === $layout ) {
			$layouts = $this->get_layouts();
			$hide_sidebars = $layouts[$layout]['hide-sidebars'];

			if( in_array( $index, $hide_sidebars ) ) {
				return false;
			}
		}
		return $is_active_sidebar;
	}

	/**
	 * Main layout
	 * @return string Classes name
	 */
	public static function main_layout() {

		$layout = Template::get_theme_layout();

		switch( $layout ) {
			// 2 columns - main content on left
			case '2c-l':
				return get_option('main_2c_l_size', 'col-md-9') . ' order-1 main-layout-left';
				break;
			// 2 columns - main content on right
			case '2c-r':
				return get_option( 'main_2c_r_size', 'col-md-9' ) . ' order-2 main-layout-right';
				break;
			// 1 column
			case '1c':
				return get_option('main_1c_size', 'col-md-12') . ' main-layout-full';
				break;
		}
	}

	/**
	 * Sidebar layout
	 * @return string Classes name
	 */
	public static function sidebar_layout() {

		$layout = Template::get_theme_layout();

		switch( $layout ) {
			// 2 columns - main content on left, sidebar on right
			case '2c-l':
				return get_option( 'sidebar_2c_l_size', 'col-md-3' ) . '  order-2 sidebar-layout-right';
				break;
			// 2 columns - main content on right, sidebar on left
			case '2c-r':
				return get_option( 'sidebar_2c_r_size', 'col-md-3' ) . '  order-1 sidebar-layout-left';
				break;
		}
	}

	//</editor-fold>

	/**
	 * Classes header container size
	 * @return string Classes name
	 */
	public static function header_container() {
		return get_option( 'storms_header_container_class', 'st-grid-container container' );
	}

	/**
	 * Classes wrap container size
	 * @return string Classes name
	 */
	public static function wrap_container() {
		return get_option( 'storms_wrap_container_class', 'st-grid-container container' );
	}

	/**
	 * Classes footer container size
	 * @return string Classes name
	 */
	public static function footer_container() {
		return get_option( 'storms_footer_container_class', 'st-grid-container container' );
	}
}
