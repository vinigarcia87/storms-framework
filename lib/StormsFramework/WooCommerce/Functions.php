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
 * StormsFramework\WooCommerce\Functions class
 * set WooCommerce helper functions
 */

namespace StormsFramework\WooCommerce;

class Functions
{
	/**
	 * Query WooCommerce activation
	 * @return boolean
	 */
	public static function is_woocommerce_activated() {
		return class_exists( 'woocommerce' ) ? true : false;
	}

	/**
	 * Display Product Search
	 * @uses  is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	public static function storms_product_search() {
		if ( Functions::is_woocommerce_activated() ) { ?>
			<div class="site-search">
				<?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>
			</div>
			<?php
		}
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
	 * Get the classes for each product on the shop list, accordingly to the columns number
	 * Used on woocommerce custom templates in this theme
	 */
	/*
	public static function get_product_list_classes( $columns ) {
		switch ( $columns ) {
			case 6:
				$classes = 'col-6 col-sm-3 col-md-2';
				break;
			case 4:
				$classes = 'col-12 col-sm-6 col-md-3';
				break;
			case 3:
				$classes = 'col-12 col-sm-12 col-md-4';
				break;
			case 31:
				$classes = 'col-12 col-sm-6 col-md-4';
				break;
			case 2:
				$classes = 'col-12 col-sm-6 col-md-6';
				break;
			default:
				$classes = 'col-12 col-sm-12 col-md-12';
		}

		return $classes;
	}
	*/

	/**
	 * Generate the necessary clearfixes and breaks for an responsive shop loop
	 * Used on woocommerce custom templates in this theme
	 */
	/*
	public static function storms_woo_responsive_shop_loop( $woocommerce_loop ) {
		switch ($woocommerce_loop['columns']) {
			case 6:
				if (0 == ($woocommerce_loop['loop'] % 6)) { ?>
					<div class="clearfix visible-md visible-lg"></div>
				<?php }
				if (0 == ($woocommerce_loop['loop'] % 4)) { ?>
					<div class="clearfix visible-sm"></div>
				<?php }
				if (0 == ($woocommerce_loop['loop'] % 2)) { ?>
					<div class="clearfix visible-xs"></div>
				<?php }
				break;
			case 4:
				if (0 == ($woocommerce_loop['loop'] % 4)) { ?>
					<div class="clearfix visible-md visible-lg"></div>
				<?php }
				if (0 == ($woocommerce_loop['loop'] % 2)) { ?>
					<div class="clearfix visible-sm"></div>
				<?php }
				break;
			case 3:
				if (0 == ($woocommerce_loop['loop'] % 3)) { ?>
					<div class="clearfix visible-md visible-lg"></div>
				<?php }
				break;
			case 31:
				if (0 == ($woocommerce_loop['loop'] % 3)) { ?>
					<div class="clearfix visible-md visible-lg"></div>
				<?php }
				if (0 == ($woocommerce_loop['loop'] % 2)) { ?>
					<div class="clearfix visible-sm"></div>
				<?php }
				break;
			case 2:
				if (0 == ($woocommerce_loop['loop'] % 2)) { ?>
					<div class="clearfix invisible-xs"></div>
				<?php }
				break;
		}
	}
	*/

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
	 * @uses  is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	public static function storms_header_cart() {
		if ( Functions::is_woocommerce_activated() ) { ?>
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
}
