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
 * Helper class
 * @package StormsFramework
 *
 * Helper functions for better use of the framework
 * @see  _documentation/Helper_Class.md
 */

namespace StormsFramework;

use StormsFramework\Base;

class Helper extends Base\Manager
{

	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );

		//add_action( 'edit_category', array( $this, 'category_transient_flusher' ) );
		//add_action( 'save_post', array( $this, 'category_transient_flusher' ) );
	}

	/**
	 * If the option we trying to get is not setted
	 * in the DB, we initialize it if the default option
	 *
	 * @param $option
	 * @param bool $default
	 * @return mixed|void
	 */
	public static function get_option( $option, $default = false ) {
		$value = get_option( $option );
		if( empty( $value ) ) {
			update_option( $option, $default );
		}
		return $value;
	}

	/**
	 * Get a dynamic sidebar as a string
	 *
	 * @param $sidebar_id
	 * @return false|string
	 */
	public static function get_dynamic_sidebar( $sidebar_id ) {
		ob_start();
		$out = '';
		if( dynamic_sidebar( $sidebar_id ) ) {
			$out = ob_get_contents();
		}
		ob_end_clean();
		return $out;
	}

	/**
	 * Debug variables
     * @reminder (new \Exception())->getTraceAsString() to show call stack
     * @see https://stackoverflow.com/a/7039409/1003020
	 */
	public static function debug( $variable, $title = '', $write_on_file = true ) {
		$date = (new \DateTime())->format('Y-m-d H:i:s');
		$title = ($title != '') ? '== ' . $title . ' ===================================' : '';
		$content = ( !is_scalar( $variable ) ) ? print_r( $variable, true ) : $variable;
		if( !$write_on_file ) {
			echo '<h3>' . $title . '</h3>';
			echo '<pre style="margin-left: 200px;">' . $content . '</pre>';
		} else {
			$fp = fopen( WP_CONTENT_DIR . '/storms-framework.log', 'a+' );
			$e = (new \Exception)->getTrace()[1];
			$file = isset( $e['file'] ) ? str_replace( str_replace( '/', '', ABSPATH ), '...', $e['file'] ) : 'N/A';
			$function = isset( $e['function'] ) ? $e['function'] : 'N/A';
			$line = isset( $e['line'] ) ? $e['line'] : 'N/A';
			$log_info = '[' . $date . ' - '. $file . ' at "' . $function . '" on line ' . $line . '] ' . PHP_EOL;
			fwrite( $fp, $log_info . "\t" . $title . PHP_EOL. "\t" . $content . PHP_EOL );
			fclose( $fp );
		}
	}

	/**
	 * Print a separator element on log files
	 * @param bool $write_on_file
	 */
	public static function debug_separator( $write_on_file = true ) {
		if( !$write_on_file ) {
			echo '<hr/>';
		} else {
			$fp = fopen( WP_CONTENT_DIR . '/storms-framework.log', 'a+' );
			fwrite( $fp, PHP_EOL . "=====================================================================" . PHP_EOL . PHP_EOL );
			fclose( $fp );
		}
	}

	/**
	 * Return an array of functions that have been called to get to the current point in code
	 * @param string $title
	 * @param null $ignore_class
	 * @param int $skip_frames
	 */
	public static function backtrace( $title = '', $ignore_class = null, $skip_frames = 0 ) {
		Helper::debug( wp_debug_backtrace_summary( $ignore_class, $skip_frames, false ), $title );
	}

	/**
	 * Get the current page URL
	 * @return string|void
	 */
	public static function get_current_page() {
		global $wp;
		return home_url( $wp->request );
	}

	/**
	 * Say if a child-theme is being used
	 */
	public static function theme_has_child() {
		return get_template_directory() !== get_stylesheet_directory();
	}

	/**
	 * Return the asset url with an fallback to the parent theme
	 */
	public static function get_asset_url( $file = null ) {

		// Stylesheet directory path for current theme
		if ( file_exists( get_stylesheet_directory() . '/assets' . $file ) ) {
			return esc_url( get_stylesheet_directory_uri() . '/assets' . $file );
			// Current theme directory
		} else if ( file_exists( get_template_directory() . '/assets' . $file ) ) {
			return esc_url( get_template_directory_uri() . '/assets' . $file );
			// Storms Framework plugin directory
		} else if( file_exists( plugin_dir_path( STORMS_FRAMEWORK_PATH ) . 'assets' . $file ) ) {
            return esc_url( plugin_dir_url( STORMS_FRAMEWORK_PATH ) . 'assets' . $file );
        } else {
            return false;
        }
	}

	/**
	 * Return the template url with an fallback to the parent theme
	 */
	public static function get_template_url( $file = null ) {
		if ( file_exists( get_stylesheet_directory() . $file ) ) {
			return esc_url( get_stylesheet_directory_uri() . $file );
		} else if ( file_exists( get_template_directory() . $file ) ) {
			return esc_url( get_template_directory_uri() . $file );
		} else if( file_exists( plugin_dir_path( STORMS_FRAMEWORK_PATH ) . $file ) ) {
			return esc_url( plugin_dir_url( STORMS_FRAMEWORK_PATH ) . $file );
		} else {
			return false;
		}
	}

	/**
	 * Return the template path with an fallback to the parent theme
	 */
	public static function get_template_path( $file = null ) {
		if ( file_exists( get_stylesheet_directory() . $file ) ) {
			return get_stylesheet_directory() . $file;
		} else if ( file_exists( get_template_directory() . $file ) ) {
			return get_template_directory() . $file;
		} else if( file_exists( plugin_dir_path( STORMS_FRAMEWORK_PATH ) . $file ) ) {
			return plugin_dir_path( STORMS_FRAMEWORK_PATH ) . $file;
		} else {
			return false;
		}
	}

	/**
	 * Query WooCommerce activation
	 * @return boolean
	 */
	public static function is_woocommerce_activated() {
		return class_exists( 'woocommerce' ) ? true : false;
	}

	/**
	 * Query any plugin activation
	 * @return boolean
	 */
	public static function is_plugin_activated( $plugin_name ) {
		return in_array( $plugin_name, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	/**
	 * Display search form.
	 *
	 * Will first attempt to locate the searchform.php file in either the child or
	 * the parent, then load it. If it doesn't exist, then the default search form
	 * will be displayed
	 *
	 * @type bool   $echo       Whether to echo or return the form. Default true.
	 * @return string
	 */
	public static function get_search_form( $echo = true ) {

		$searchform = get_search_form( array( 'echo' => false ) );
		if( ! $echo ) {
			return $searchform;
		}
		echo $searchform;
	}

	/**
	 * Display product search form.
	 *
	 * Will first attempt to locate the product-searchform.php file in either the child or.
	 * the parent, then load it. If it doesn't exist, then the default search form.
	 * will be displayed.
	 *
	 * @param bool $echo (default: true).
	 * @return string
	 */
	public static function get_product_search_form( $echo = true ) {

		$form = get_product_search_form( array( 'echo' => false ));

		if ( ! $echo ) {
			return $form;
		}

		echo $form; // WPCS: XSS ok.
	}

	/**
	 * Get a card with the avatar and the email of the logged in user
	 */
	public static function get_user_info_card( $user_id = '' ) {

		// Get current user, if no user id have been informed
		$user = ( $user_id == '' ) ? wp_get_current_user() : get_userdata( $user_id );

		$avatar_img = get_avatar( $user->ID, 120 );

		// Function get_wp_user_avatar is defined on WP Avatar plugin
		if( function_exists( 'get_wp_user_avatar' ) ) {
			$avatar_img = get_wp_user_avatar( $user->ID, 120 );
		}

		$maxsize = 20;
		$email_formatado = ( strlen( $user->user_email ) > $maxsize ? substr( $user->user_email, 0, $maxsize ) . '...' : $user->user_email );

		$user_card  = '
				<div class="user-card">
					<div class="user-avatar">
						' . $avatar_img . '
					</div>
					<div class="user-info">
						<h1>' . $user->display_name . '<br>
						<small title="' . $user->user_email . '">' . $email_formatado . '</small>
						</h1>
					</div>
				</div>
		';

		return $user_card;
	}

	/**
	 * Show an 'developed by' with the Storms logo
	 */
	public static function get_developed_by() {
		$html = '
			<div id="developed-by" class="developed-by">
				<img src="' . Helper::get_asset_url( '/img/storms/logo/bn_storms_white.png' ) . '">
				<small>v.' . STORMS_SYSTEM_VERSION . '</small>
			</div>
		';
		return $html;
	}

	//<editor-fold desc="Data creation functions">

	/**
	 * Create a new product attribute from a label name
	 * @source https://stackoverflow.com/a/51994543/1003020
	 *
	 * @param $label_name
	 * @param int $attribute_public
	 * @return \WP_Error
	 */
	public static function create_product_attribute( $label_name, $attribute_public = 0 ) {
		global $wpdb;

		$slug = sanitize_title( $label_name );

		if ( strlen( $slug ) >= 28 ) {
			return new \WP_Error( 'invalid_product_attribute_slug_too_long', sprintf( __( 'Name "%s" is too long (28 characters max). Shorten it, please.', 'woocommerce' ), $slug ), array( 'status' => 400 ) );
		} elseif ( wc_check_if_attribute_name_is_reserved( $slug ) ) {
			return new \WP_Error( 'invalid_product_attribute_slug_reserved_name', sprintf( __( 'Name "%s" is not allowed because it is a reserved term. Change it, please.', 'woocommerce' ), $slug ), array( 'status' => 400 ) );
		} elseif ( taxonomy_exists( wc_attribute_taxonomy_name( $label_name ) ) ) {
			return new \WP_Error( 'invalid_product_attribute_slug_already_exists', sprintf( __( 'Name "%s" is already in use. Change it, please.', 'woocommerce' ), $label_name ), array( 'status' => 400 ) );
		}

		$data = array(
			'attribute_label'   => $label_name,
			'attribute_name'    => $slug,
			'attribute_type'    => 'select',
			'attribute_orderby' => 'menu_order',
			'attribute_public'  => $attribute_public, // Enable archives ==> (0 or 1)
		);

		$results = $wpdb->insert( "{$wpdb->prefix}woocommerce_attribute_taxonomies", $data );

		if ( false === $results ) {
			return new \WP_Error( 'cannot_create_attribute', 'Error on creating attribute taxonomy', array( 'status' => 400 ) );
		}

		$id = $wpdb->insert_id;

		do_action('woocommerce_attribute_added', $id, $data);

		wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );

		delete_transient('wc_attribute_taxonomies');
	}

	/**
	 * Adiciona um usuario completo - com ID especifico, password sem encriptaçao, meta dados e roles
	 * Usado para importaçoes e criaçao de usuarios iniciais
	 *
	 * Exemplo de array de dados:
	 * 'data' 		=> [
	 * 		'ID'					=> 47,
	 * 		'user_login'           	=> 'laboral',
	 * 		'user_pass'            	=> '$P$BG.KiZwUd03dHB/pmYh/O3lGCep4NG/',
	 * 		'user_nicename'        	=> 'laboral',
	 * 		'user_email'           	=> 'laboral@prosoftin.com.br',
	 * 		'user_url'             	=> 'http://prosoftin.com.br/',
	 * 		'user_registered'		=> '2017-07-31 17:42:11',
	 * 		'user_activation_key'	=> '1501522931:$P$B3I.BFP6NeOwQ62H5AfrbeHMY3sCdJ/',
	 * 		'display_name'			=> 'Laboral Provensi',
	 * 	],
	 * 	'meta_data'	=> [
	 * 		'first_name'           	=> 'Laboral',
	 * 		'last_name'            	=> 'Provensi',
	 * 		'nickname'				=> 'laboral.provensi',
	 * 		'dexst1629_user_avatar'	=> '10116',
	 * 	],
	 * 	'roles'		=> [
	 * 		'shop_manager',
	 * 		'administrator',
	 * 	]
	 *
	 * @param $user_info
	 * @return bool|\WP_User
	 */
	public static function create_user( $user_info ) {
		global $wpdb;

		$user_data = $user_info['data'];
		if ( false !== get_user_by( 'id', $user_data['ID'] ) || username_exists( $user_data['user_login'] ) !== false || email_exists( $user_data['user_email'] ) !== false ) {
			return false;
		}

		$wpdb->insert( $wpdb->users, $user_data );
		$user_id = (int) $wpdb->insert_id;

		$user = new \WP_User( $user_id );

		$meta_data = $user_info['meta_data'];
		foreach( $meta_data as $key => $value ) {
			update_user_meta( $user_id, $key, $value );
		}

		$roles = $user_info['roles'];
		$user->remove_role( 'subscriber' );
		foreach( $roles as $role ) {
			$user->add_role( $role );
		}

		return $user;
	}

	//</editor-fold>

	/**
	 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
	 * placed under a 'children' member of their parent term.
	 * Source: http://wordpress.stackexchange.com/a/99516/54025
	 *
	 * @param array   $cats     taxonomy term objects to sort
	 * @param array   $into     result array to put them in
	 * @param integer $parentId the current parent ID to put them in
	 */
	public static function sort_terms_hierarchically( array &$cats, array &$into, $parentId = 0 ) {

		/**
		 * $cat = ( ... ) ? 'category' : 'product_cat';
		 * $product_categories = get_terms( $cat, $argss );
		 * $categoryHierarchy = array();
		 * Helper::sort_terms_hierarchicaly( $product_categories, $categoryHierarchy );
		 * $product_categories = $categoryHierarchy;
		 */

		foreach ($cats as $i => $cat) {
			if ($cat->parent == $parentId) {
				$into[$cat->term_id] = $cat;
				unset($cats[$i]);
			}
		}

		foreach ($into as $topCat) {
			$topCat->children = array();
			Helper::sort_terms_hierarchically( $cats, $topCat->children, $topCat->term_id );
		}
	}

	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @return array $sizes Data for all currently-registered image sizes.
	 * @uses   get_intermediate_image_sizes()
	 * @global $_wp_additional_image_sizes
	 */
	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = [];

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} else if ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = [
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				];
			}
		}

		return $sizes;
	}

	/**
	 * Send emails using WooCommerce email style
	 *
	 * @param $user_email
	 * @param $subject
	 * @param $email_heading
	 * @param $message
	 * @param $headers
	 * @return bool
	 */
	public static function send_wc_email( $user_email, $subject, $email_heading, $message, $headers ) {
		// Load the mailer class
		$mailer = WC()->mailer();

		// Create a new email
		$email = new \WC_Email();

		// Wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );

		return $mailer->send( $user_email, $subject, $message, $headers );
		//return wp_mail( $user_email, $subject, $message, $headers );
	}

	/**
	 * TODO MUST REVIEW THE FUNCTIONS BELOW!
	 * ======================================================================================= */

	/**
	 * Returns true if a blog has more than 1 category.
	 *
	 * @return bool
	 */
	public static function is_categorized_blog() {
	    $all_the_cool_cats = get_transient( 'storms_categories' );
	    if ( false === $all_the_cool_cats ) {
	        // Create an array of all the categories that are attached to posts.
	        $all_the_cool_cats = get_categories( array(
	            'fields'     => 'ids',
	            'hide_empty' => 1,
	            // We only need to know if there is more than one category.
	            'number'     => 2,
	        ) );

	        // Count the number of categories that are attached to the posts.
	        $all_the_cool_cats = count( $all_the_cool_cats );

	        set_transient( 'storms_categories', $all_the_cool_cats );
	    }

	    if ( $all_the_cool_cats > 1 || is_preview() ) {
	        // This blog has more than 1 category so is_categorized_blog should return true.
	        return true;
	    } else {
	        // This blog has only 1 category so is_categorized_blog should return false.
	        return false;
	    }
	}

	/**
	 * Flush out the transients used in is_categorized_blog.
	 */
	public function category_transient_flusher() {
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	        return;
	    }
	    // Like, beat it. Dig?
	    delete_transient( 'storms_categories' );
	}

	/**
	 * Get the product thumbnail, or the placeholder if not set
	 * Modification that insert bootstrap classes
	 */
	public static function storms_get_product_thumbnail( $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $post;

		$bootstrap_classes = ' rounded-circle img-thumbnail';

		if ( has_post_thumbnail() )
		{
			return get_the_post_thumbnail( $post->ID, $size . $bootstrap_classes );
		}
		elseif ( wc_placeholder_img_src() )
		{
			$dimensions = wc_get_image_size( $size );
			return apply_filters('woocommerce_placeholder_img', '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="' . esc_attr( $dimensions['width'] ) . '" class="woocommerce-placeholder wp-post-image ' . $bootstrap_classes . '" height="' . esc_attr( $dimensions['height'] ) . '" />' );
			return wc_placeholder_img( $size );
		}
	}

	/**
	 * Retrieve a post's terms as a list with specified format.
	 * Melhora a function get_the_term_list(), permitindo passar classes css para o link do term
	 */
	public static function storms_get_the_term_list( $id, $taxonomy, $before = '', $sep = '', $after = '', $classes = '' ) {
		$terms = get_the_terms( $id, $taxonomy );

		if ( is_wp_error( $terms ) )
			return $terms;

		if ( empty( $terms ) )
			return false;

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $taxonomy );
			if ( is_wp_error( $link ) )
				return $link;
			$term_links[] = '<a href="' . esc_url( $link ) . '" rel="tag" class="' . $classes . '">' . $term->name . '</a>';
		}

		/**
		 * Filter the term links for a given taxonomy.
		 *
		 * The dynamic portion of the filter name, $taxonomy, refers
		 * to the taxonomy slug.
		 *
		 * @since 2.5.0
		 *
		 * @param array $term_links An array of term links.
		 */
		$term_links = apply_filters( "term_links-$taxonomy", $term_links );

		return $before . join( $sep, $term_links ) . $after;
	}

	/**
	 * Override woocommerce_subcategory_thumbnail that
	 * remove the width and height of the thumbnails
	 * @param $category
	 */
	public static function woocommerce_subcategory_thumbnail( $category ) {
		$small_thumbnail_size = apply_filters( 'single_product_small_thumbnail_size', 'shop_catalog' );
		$dimensions           = wc_get_image_size( $small_thumbnail_size );
		$thumbnail_id         = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
			$image = $image[0];
		} else {
			$image = wc_placeholder_img_src();
		}

		if ( $image ) {
			// Prevent esc_url from breaking spaces in urls for image embeds
			// Ref: http://core.trac.wordpress.org/ticket/23605
			$image = str_replace( ' ', '%20', $image );

			// echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '" width="' . esc_attr( $dimensions['width'] ) . '" height="' . esc_attr( $dimensions['height'] ) . '" />';
			echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $category->name ) . '" class="img-fluid" width="100%" />';
		}
	}

	/**
	 * Redefine woocommerce_output_related_products
	 * @see https://gist.github.com/woogist/5975638
	 */
	public static function woocommerce_output_related_products() {
		$args = array(
			'posts_per_page' => 4,
			'columns'        => 4
		);
		woocommerce_related_products($args);
	}

	/**
	 * Output the add to cart button for variations.
	 * Modification on button classes to use bootstrap
	 * On WooCommerce 2.5, we gonna move this code to
	 * the new template single-product/add-to-cart/variation-add-to-cart-button.php
	 */
	public static function woocommerce_single_variation_add_to_cart_button() {
		global $product;
		?>
		<div class="variations_button">
			<?php woocommerce_quantity_input( array( 'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 ) ); ?>
			<button type="submit" class="single_add_to_cart_button btn btn-primary"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
			<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
			<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
			<input type="hidden" name="variation_id" class="variation_id" value="" />
		</div>
		<?php
	}

	/**
	 * Display Header Cart
	 *
	 * @return void
	 */
	public static function storms_header_cart() {
		if ( Helper::is_woocommerce_activated() ) { ?>
			<ul class="site-header-cart menu">
				<?php storms_cart_link(); ?>
				<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
			</ul>
			<?php
		}
	}

	/**
	 * Cart Link
	 * Displayed a link to the cart including the number of items present and the cart total
	 * @param  array $settings Settings
	 * @return array           Settings
	 */
	public static function storms_cart_link() {
		if ( is_cart() ) {
			$class = 'current-menu-item active';
		} else {
			$class = '';
		}
		?>
		<li class="<?php echo esc_attr( $class ); ?>">
			<a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php _e( 'View your shopping cart', 'storms' ); ?>">
				<?php echo wp_kses_data( WC()->cart->get_cart_total() ); ?> <span class="count"><?php echo wp_kses_data( sprintf( _n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'storms' ), WC()->cart->get_cart_contents_count() ) );?></span>
			</a>
		</li>
		<?php
	}

	//<editor-fold desc="Front end helper functions">

	/**
	 * Check if post/page is in a menu
	 * Source: http://wordpress.stackexchange.com/a/75608/54025
	 *
	 * @param $menu menu name, id, or slug
	 * @param $object_id int post object id of page
	 * @return bool true if object is in menu
	 */
	public static function is_in_menu( $menu = null, $object_id = null ) {

		// get menu object
		$menu_object = wp_get_nav_menu_items( esc_attr( $menu ) );

		// stop if there isn't a menu
		if( ! $menu_object )
			return false;

		// get the object_id field out of the menu object
		$menu_items = wp_list_pluck( $menu_object, 'object_id' );

		// use the current post if object_id is not specified
		if( !$object_id ) {
			global $post;
			$object_id = get_queried_object_id();
		}

		// test if the specified page is in the menu or not. return true or false.
		return in_array( (int) $object_id, $menu_items );
	}

	/**
	 * Returns the permalink for a page based on the incoming slug
	 * Source: http://wordpress.stackexchange.com/questions/4999/get-page-link-from-slug
	 */
	public static function get_permalink_by_slug( $slug ) {
		return esc_url( get_permalink( get_page_by_path( $slug ) ) );
	}

	/**
	 * Print HTML with meta information for the current post-date/time and author
	 * @return void
	 */
	public static function posted_on() {

		if ( is_sticky() && is_home() && ! is_paged() ) {
			echo '<span class="featured-post">' . __( 'Sticky', 'storms' ) . ' </span>';
		}

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        $time_string_updated = '';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
            $time_string_updated = '<time class="updated" datetime="%1$s">%2$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);

        $time_string_updated = sprintf( $time_string_updated,
            esc_attr( get_the_modified_date( 'c' ) ),
            esc_html( get_the_modified_date() )
        );

		$posted_on = sprintf(
			/* translators: %s: post creation date. */
			esc_html_x( 'Posted on %s', 'post creation date', 'storms' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		// @TODO Adicionar opções para nao exibir horario de publicacao e/ou ediçao do post
		if( $time_string_updated != '' ) {
            $posted_on .= ' - ';
            $posted_on .= sprintf(
            /* translators: %s: post update date. */
                esc_html_x('Last updated on %s', 'post update date', 'storms'),
                '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string_updated . '</a>'
            );
        }

		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'storms' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
	}

	/**
	 * Prints HTML with meta information for the categories, tags and comments
	 * @return void
	 */
	public static function entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'Used between list items, there is a space after the comma.', 'storms' ) );
			if ( $categories_list && Helper::is_categorized_blog() ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'storms' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'storms' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'storms' ) . '</span>', $tags_list ); // WPCS: XSS OK.
			}
		}

		if ( ( is_single() || is_page() ) && ( comments_open() || get_comments_number() ) && ! post_password_required() ) {

			comments_template();

		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="sr-only">%s</span>', 'storms' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			' <span class="edit-link">',
			' <i class="fa fa-pencil-square-o"></i></span>'
		);
	}

	/**
	 * Print HTML with meta information for the current post-date/time and author
	 * @return void
	 */
	public static function paging_nav() {
		$mid  = 2;     // Total of items that will show along with the current page.
		$end  = 1;     // Total of items displayed for the last few pages.
		$show = false; // Show all items.

		echo Helper::pagination( $mid, $end, false );
	}

	/**
	 * Pagination
	 *
	 * @global array $wp_query   Current WP Query.
	 * @global array $wp_rewrite URL rewrite rules.
	 *
	 * @param  int   $mid   Total of items that will show along with the current page.
	 * @param  int   $end   Total of items displayed for the last few pages.
	 * @param  bool  $show  Show all items.
	 * @param  mixed $query Custom query.
	 *
	 * @return string       Return the pagination.
	 */
	public static function pagination( $mid = 2, $end = 1, $show = false, $query = null ) {
		global $wp_query, $wp_rewrite;

		$total_pages = $wp_query->max_num_pages;

		if ( is_object( $query ) && null != $query ) {
			$total_pages = $query->max_num_pages;
		}

		if ( $total_pages > 1 ) {
			$url_base = $wp_rewrite->pagination_base;
			$big = 999999999;

			// Sets the paginate_links arguments.
			$arguments = apply_filters( 'storms_pagination_args', array(
					'base'      => esc_url_raw( str_replace( $big, '%#%', get_pagenum_link( $big, false ) ) ),
					'format'    => '',
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => $total_pages,
					'show_all'  => $show,
					'end_size'  => $end,
					'mid_size'  => $mid,
					'type'      => 'list',
					'prev_text' => __( '&laquo; Previous', 'storms' ),
					'next_text' => __( 'Next &raquo;', 'storms' ),
				)
			);

			$pagination = '<div class="pagination-wrap">' . paginate_links( $arguments ) . '</div>';

			// Prevents duplicate bars in the middle of the url.
			if ( $url_base ) {
				$pagination = str_replace( '//' . $url_base . '/', '/' . $url_base . '/', $pagination );
			}

			return $pagination;
		}
	}

	/**
	 * Pagination that show disabled links
	 * Exibe os links de prev e next desabilitados, ao inves de sumir com eles
	 * Fonte: http://wordpress.stackexchange.com/questions/52638/pagination-how-do-i-always-show-previous
	 *
	 * @global array $wp_query   Current WP Query.
	 * @global array $wp_rewrite URL rewrite rules.
	 *
	 * @param  int   $mid   Total of items that will show along with the current page.
	 * @param  int   $end   Total of items displayed for the last few pages.
	 * @param  bool  $show  Show all items.
	 * @param  mixed $query Custom query.
	 *
	 * @return string       Return the pagination.
	 */
	public static function pagination_show_disabled($mid_size = 2, $end_size = 1, $show_all = false) {
		global $wp_rewrite, $wp_query;
		$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
		// http://codex.wordpress.org/Function_Reference/paginate_links
		$pagination = array(
			'base'         => str_replace( 999999999, '%#%', get_pagenum_link( 999999999 ) ),
			'format'       => '',
			'current'      => max( 1, get_query_var( 'paged' ) ), // Get whichever is the max out of 1 and the current page count
			'total'        => $wp_query->max_num_pages, // Get total number of pages in current query
			'prev_next'	   => true,
			'prev_text'    => '&laquo;',
			'next_text'    => '&raquo;',
			'type'         => 'array',   // 'array' - An array of the paginated link list to offer full control of display
			'show_all'     => $show_all, // If set to True, then it will show all of the pages instead of a short list of the pages near the current page
			'end_size'     => $end_size, // How many numbers on either the start and the end list edges
			'mid_size'     => $mid_size, // How many numbers to either side of current page, but not including current page
		);
		if ( $wp_rewrite->using_permalinks() )
			$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
		if ( !empty( $wp_query->query_vars['s'] ) )
			$pagination['add_args'] = array( 's' => get_query_var( 's' ) );

		$pages = paginate_links( $pagination );

		if ($pages ==  null)
			$pages = array('<span class="page-numbers current">1</span>');

		// Button Next
		if ( $current == 1) {
			$anterior = '<li class="disabled"><a href="#">&laquo;</a></li>';
			array_unshift($pages, $anterior); // Prepend to array
		} else {
			$pages[0] = '<li>' . $pages[0] . '</li>';
		}

		// Button Previous
		if ( $current == $wp_query->max_num_pages ) {
			$proximo = '<li class="disabled"><a href="#">&raquo;</a></li>';
			$pages[] = $proximo; // Append to array
		} else {
			$last_key = count($pages) - 1;
			$pages[$last_key] = '<li>' . $pages[$last_key] . '</li>';
		}

		echo '<ul class="pagination">';
		foreach($pages as $key => $page)
			if($key != 0 && $key != count($pages))
				if($key != $current)
					// Print page links
					echo '<li>'.$page.'</li>';
				else
					// Print current page link
					echo '<li class="active"><span>' . $key . ' <span class="sr-only">'.__('Current Page', 'storms').'</span></span></li>';
			else
				// Print previous and next buttons
				echo $page;
		echo '</ul>';
	}

	/**
	 * Related Posts
	 *
	 * Usage:
	 * To show related by categories:
	 * Add in single.php <?php storms_related_posts(); ?>
	 * To show related by tags:
	 * Add in single.php <?php storms_related_posts( 'tag' ); ?>
	 *
	 * @global array $post         WP global post.
	 *
	 * @param  string $display      Set category or tag.
	 * @param  int    $qty          Number of posts to be displayed (default 5).
	 * @param  string $title        Set the widget title.
	 * @param  bool   $thumb        Enable or disable displaying images.
	 * @param  string $post_type    Post type.
	 *
	 * @return string              Related Posts.
	 */
	public static function related_posts( $display = 'category', $qty = 4, $title = '', $thumb = true, $post_type = 'post' ) {
		global $post;

		$show = false;
		$post_qty = (int) $qty;
		! empty( $title ) || $title = __( 'Related Posts', 'storms' );

		// Creates arguments for WP_Query.
		switch ( $display ) {
			case 'tag':
				$tags = wp_get_post_tags( $post->ID );

				if ( $tags ) {
					// Enables the display.
					$show = true;

					$tag_ids = array();
					foreach ( $tags as $individual_tag ) {
						$tag_ids[] = $individual_tag->term_id;
					}

					$args = array(
						'tag__in' => $tag_ids,
						'post__not_in' => array( $post->ID ),
						'posts_per_page' => $post_qty,
						'post_type' => $post_type,
						'ignore_sticky_posts' => 1
					);
				}
				break;

			default :
				$categories = get_the_category( $post->ID );

				if ( $categories ) {

					// Enables the display.
					$show = true;

					$category_ids = array();
					foreach ( $categories as $individual_category ) {
						$category_ids[] = $individual_category->term_id;
					}

					$args = array(
						'category__in' => $category_ids,
						'post__not_in' => array( $post->ID ),
						'showposts' => $post_qty,
						'post_type' => $post_type,
						'ignore_sticky_posts' => 1,
					);
				}
				break;
		}

		if ( $show ) {

			$related = new WP_Query( $args );
			if ( $related->have_posts() ) {

				$layout = '<div id="related-post">';
				$layout .= '<h3>' . esc_attr( $title ) . '</h3>';
				$layout .= ( $thumb ) ? '<div class="grid-row  row">' : '<ul>';

				while ( $related->have_posts() ) {
					$related->the_post();

					$layout .= ( $thumb ) ? '<div class="col-md-' . ceil( 12 / $qty ) . '">' : '<li>';

					if ( $thumb ) {
						// Filter to replace the image.
						$image = apply_filters( 'storms_related_posts_thumbnail', get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) );

						$layout .= '<span class="thumb">';
						$layout .= sprintf( '<a href="%s" title="%s" class="thumbnail">%s</a>', get_permalink(), get_the_title(), $image );
						$layout .= '</span>';
					}

					$layout .= '<span class="text">';
					$layout .= sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', get_permalink(), get_the_title() );
					$layout .= '</span>';

					$layout .= ( $thumb ) ? '</div>' : '</li>';
				}

				$layout .= ( $thumb ) ? '</div>' : '</ul>';
				$layout .= '</div>';

				echo $layout;
			}
			wp_reset_postdata();
		}
	}

	/**
	 * Custom excerpt for content or title
	 *
	 * Usage:
	 * Place: <?php echo storms_excerpt( 'excerpt', value ); ?>
	 *
	 * @param  string $type  Sets excerpt or title.
	 * @param  int    $limit Sets the length of excerpt.
	 *
	 * @return string       Return the excerpt.
	 */
	public static function excerpt( $type = 'excerpt', $limit = 40 ) {
		$limit = (int) $limit;

		// Set excerpt type.
		switch ( $type ) {
			case 'title':
				$excerpt = get_the_title();
				break;

			default :
				$excerpt = get_the_excerpt();
				break;
		}

		return wp_trim_words( $excerpt, $limit );
	}

	/**
	 * Breadcrumbs
	 *
	 * @param  string $homepage  Homepage name.
	 *
	 * @return string            HTML of breadcrumbs.
	 */
	public static function breadcrumbs( $homepage = '' ) {
		global $wp_query, $post, $author;
		! empty( $homepage ) || $homepage = __( 'Home', 'storms' );
		// Default html.
		$current_before = '<li class="active">';
		$current_after  = '</li>';
		if ( ! is_home() && ! is_front_page() || is_paged() ) {
			// First level.
			echo '<ol id="breadcrumbs" class="breadcrumb">';
			echo '<li><a href="' . home_url() . '" rel="nofollow">' . $homepage . '</a></li>';
			// Single post.
			if ( is_single() && ! is_attachment() ) {
				// Checks if is a custom post type.
				if ( 'post' != $post->post_type ) {
					// But if Woocommerce
					if ( 'product' === $post->post_type ) {
						$shop_page    = get_post( wc_get_page_id( 'shop' ) );
						echo '<li><a href="' . get_permalink( $shop_page ) . '">' . get_the_title( $shop_page ) . '</a></li>';
						// Gets Woocommerce post type taxonomies.
						$taxonomy = get_object_taxonomies( 'product' );
						$taxy = 'product_cat';
					} else {
						$post_type = get_post_type_object( $post->post_type );
						echo '<li><a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $post_type->label . '</a></li> ';
						// Gets post type taxonomies.
						$taxonomy = get_object_taxonomies( $post_type->name );
						$taxy = $taxonomy[0];
					}
					if ( $taxonomy ) {
						// Gets post terms.
						$terms = get_the_terms( $post->ID, $taxy );
						$term  = $terms ? array_shift( $terms ) : '';
						// Gets parent post terms.
						$parent_term = get_term( $term->parent, $taxy );
						if ( $term ) {
							if ( $term->parent ) {
								echo '<li><a href="' . get_term_link( $parent_term ) . '">' . $parent_term->name . '</a></li> ';
							}
							echo '<li><a href="' . get_term_link( $term ) . '">' . $term->name . '</a></li> ';
						}
					}
				} else {
					$category = get_the_category();
					$category = $category[0];
					// Gets parent post terms.
					$parent_cat = get_term( $category->parent, 'category' );
					if ( $category->parent ) {
						echo '<li><a href="' . get_term_link( $parent_cat ) . '">' . $parent_cat->name. '</a></li>';
					}
					echo '<li><a href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a></li>';
				}
				echo $current_before . get_the_title() . $current_after;
				// Single attachment.
			} elseif ( is_attachment() ) {
				$parent   = get_post( $post->post_parent );
				$category = get_the_category( $parent->ID );
				$category = $category[0];
				echo '<li><a href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a></li>';
				echo '<li><a href="' . get_permalink( $parent ) . '">' . $parent->post_title . '</a></li>';
				echo $current_before . get_the_title() . $current_after;
				// Page without parents.
			} elseif ( is_page() && ! $post->post_parent ) {
				echo $current_before . get_the_title() . $current_after;
				// Page with parents.
			} elseif ( is_page() && $post->post_parent ) {
				$parent_id   = $post->post_parent;
				$breadcrumbs = array();
				while ( $parent_id ) {
					$page = get_page( $parent_id );
					$breadcrumbs[] = '<li><a href="' . get_permalink( $page->ID ) . '">' . get_the_title( $page->ID ) . '</a></li>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse( $breadcrumbs );
				foreach ( $breadcrumbs as $crumb ) {
					echo $crumb . ' ';
				}
				echo $current_before . get_the_title() . $current_after;
				// Category archive.
			} elseif ( is_category() ) {
				$category_object  = $wp_query->get_queried_object();
				$category_id      = $category_object->term_id;
				$current_category = get_category( $category_id );
				$parent_category  = get_category( $current_category->parent );
				// Displays parent category.
				if ( 0 != $current_category->parent ) {
					echo '<li>' . get_category_parents( $parent_category, TRUE, ' ' ) . '</li>';
				}
				printf( __( '%sCategory: %s%s', 'storms' ), $current_before, single_cat_title( '', false ), $current_after );
				// Tags archive.
			} elseif ( is_tag() ) {
				printf( __( '%sTag: %s%s', 'storms' ), $current_before, single_tag_title( '', false ), $current_after );
				// Custom post type archive.
			} elseif ( is_post_type_archive() ) {
				// Check if Woocommerce Shop
				if ( is_shop() ) {
					$shop_page_id = wc_get_page_id( 'shop' );
					echo $current_before . get_the_title( $shop_page_id ) . $current_after;
				} else {
					echo $current_before . post_type_archive_title( '', false ) . $current_after;
				}
				// Search page.
			} elseif ( is_search() ) {
				printf( __( '%sSearch result for: &quot;%s&quot;%s', 'storms' ), $current_before, get_search_query(), $current_after );
				// Author archive.
			} elseif ( is_author() ) {
				$userdata = get_userdata( $author );
				echo $current_before . __( 'Posted by', 'storms' ) . ' ' . $userdata->display_name . $current_after;
				// Archives per days.
			} elseif ( is_day() ) {
				echo '<li><a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a></li>';
				echo '<li><a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '">' . get_the_time( 'F' ) . '</a></li>';
				echo $current_before . get_the_time( 'd' ) . $current_after;
				// Archives per month.
			} elseif ( is_month() ) {
				echo '<li><a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a></li>';
				echo $current_before . get_the_time( 'F' ) . $current_after;
				// Archives per year.
			} elseif ( is_year() ) {
				echo $current_before . get_the_time( 'Y' ) . $current_after;
				// Archive fallback for custom taxonomies.
			} elseif ( is_archive() ) {
				$current_object = $wp_query->get_queried_object();
				$taxonomy       = get_taxonomy( $current_object->taxonomy );
				$term_name      = $current_object->name;
				// Displays the post type that the taxonomy belongs.
				if ( ! empty( $taxonomy->object_type ) ) {
					// Get correct Woocommerce Post Type crumb
					if ( is_woocommerce() ) {
						$shop_page    = get_post( wc_get_page_id( 'shop' ) );
						echo '<li><a href="' . get_permalink( $shop_page ) . '">' . get_the_title( $shop_page ) . '</a></li>';
					} else {
						$_post_type = array_shift( $taxonomy->object_type );
						$post_type = get_post_type_object( $_post_type );
						echo '<li><a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $post_type->label . '</a></li> ';
					}
				}
				// Displays parent term.
				if ( 0 != $current_object->parent ) {
					$parent_term = get_term( $current_object->parent, $current_object->taxonomy );
					echo '<li><a href="' . get_term_link( $parent_term ) . '">' . $parent_term->name . '</a></li>';
				}
				echo $current_before . $taxonomy->label . ': ' . $term_name . $current_after;
				// 404 page.
			} elseif ( is_404() ) {
				echo $current_before . __( '404 Error', 'storms' ) . $current_after;
			}
			// Gets pagination.
			if ( get_query_var( 'paged' ) ) {
				if ( is_archive() ) {
					echo ' (' . sprintf( __( 'Page %s', 'storms' ), get_query_var( 'paged' ) ) . ')';
				} else {
					printf( __( 'Page %s', 'storms' ), get_query_var( 'paged' ) );
				}
			}
			echo '</ol>';
		}
	}

	/**
	 * Get only the url of user avatar, instead of the <img/>
	 * Source: http://wordpress.stackexchange.com/a/59604/54025
	 *
	 * @param  int     $get_avatar	Return of 'get_wp_user_avatar()'
	 *
	 * @return string
	 */
	public static function get_avatar_url($get_avatar) {
		preg_match('/src="(.*?)"/i', $get_avatar, $matches);
		return $matches[1];
	}

	/**
	 * Get a image URL
	 *
	 * @param  int     $id      Image ID.
	 * @param  int     $width   Image width.
	 * @param  int     $height  Image height.
	 * @param  boolean $crop    Image crop.
	 * @param  boolean $upscale Force the resize.
	 *
	 * @return string
	 */
	public static function get_image_url( $id, $width, $height, $crop = true, $upscale = false ) {
		$resizer    = Thumbnail_Resizer::get_instance();
		$origin_url = wp_get_attachment_url( $id );
		$url        = $resizer->process( $origin_url, $width, $height, $crop, $upscale );

		return $url;
	}

	/**
	 * Custom post thumbnail
	 *
	 * @param  int     $width   Width of the image.
	 * @param  int     $height  Height of the image.
	 * @param  string  $class   Class attribute of the image.
	 * @param  string  $alt     Alt attribute of the image.
	 * @param  boolean $crop    Image crop.
	 * @param  string  $class   Custom HTML classes.
	 * @param  boolean $upscale Force the resize.
	 *
	 * @return string         Return the post thumbnail.
	 */
	public static function post_thumbnail( $width, $height, $alt, $crop = true, $class = '', $upscale = false ) {
		$thumb = get_post_thumbnail_id();

		if ( $thumb ) {
			$image = Helper::get_image_url( $thumb, $width, $height, $crop, $upscale );
			$html  = '<img class="wp-image-thumb img-fluid ' . sanitize_html_class( $class ) . '" src="' . $image . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" alt="' . esc_attr( $alt ) . '" />';

			return apply_filters( 'storms_thumbnail_html', $html );
		}
	}

	/**
	 * Gets the excerpt of a specific post ID or object
	 * @param - $post - object/int - the ID or object of the post to get the excerpt of
	 * @param - $length - int - the length of the excerpt in words
	 * @param - $tags - string - the allowed HTML tags. These will not be stripped out
	 * @param - $extra - string - text to append to the end of the excerpt
	 */
	public static function get_excerpt_by_id( $post, $length = false ) {

		if ( is_numeric($post) ) {
			$post = get_post($post);
		} elseif( ! is_object($post) ) {
			return false;
		}

		if ( has_excerpt( $post->ID ) ) {
			return $the_excerpt = apply_filters( 'get_the_excerpt', $post->post_excerpt );
		} else {
			$the_excerpt = $post->post_content;
		}

		$the_excerpt = strip_shortcodes( $the_excerpt );
		$the_excerpt = apply_filters( 'the_content', $the_excerpt );
		$the_excerpt = str_replace(']]>', ']]&gt;', $the_excerpt);

		$excerpt_length = ( $length ) ? $length : apply_filters( 'excerpt_length', 55 );
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$the_excerpt = wp_trim_words( $the_excerpt, $excerpt_length, $excerpt_more );

		return apply_filters( 'wp_trim_excerpt', $the_excerpt );
	}

	//</editor-fold>
}
