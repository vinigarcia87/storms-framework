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
 * StormsFramework\Front\Bootstrap\Pagination class
 * Extends the Breadcrumb_Trail class to output twitter bootstrap compatible breadcrumbs
 */

namespace StormsFramework\Bootstrap;

/**
 * Wordpress Bootstrap 4.1 pagination (with custom WP_Query() and global $wp_query support)
 * Accepts a WP_Query instance to build pagination (for custom wp_query()),
 * or nothing to use the current global $wp_query (eg: taxonomy term page)
 * - Tested on WP 4.9.5
 * - Tested with Bootstrap 4.1
 * - Tested on Sage 9
 *
 * Gist link: https://gist.github.com/mtx-z/f95af6cc6fb562eb1a1540ca715ed928
 *
 * USAGE:
 *     <?php echo bootstrap_pagination(); ?> //uses global $wp_query
 * or with custom WP_Query():
 *     <?php
 *      $query = new \WP_Query($args);
 *       ... while(have_posts()), $query->posts stuff ...
 *       echo bootstrap_pagination($query);
 *     ?>
 *
 * @param WP_Query|null $wp_query
 * @param bool $echo
 * @return string
 */
class WP_Bootstrap_Pagination
{
	/**
	 * Retrieve paginated link for archive post pages.
	 *
	 * Technically, the function can be used to create paginated link list for any
	 * area. The 'base' argument is used to reference the url, which will be used to
	 * create the paginated links. The 'format' argument is then used for replacing
	 * the page number. It is however, most likely and by default, to be used on the
	 * archive post pages.
	 *
	 * The 'type' argument controls format of the returned value. The default is
	 * 'plain', which is just a string with the links separated by a newline
	 * character. The other possible values are either 'array' or 'list'. The
	 * 'array' value will return an array of the paginated link list to offer full
	 * control of display. The 'list' value will place all of the paginated links in
	 * an unordered HTML list.
	 *
	 * The 'total' argument is the total amount of pages and is an integer. The
	 * 'current' argument is the current page number and is also an integer.
	 *
	 * An example of the 'base' argument is "http://example.com/all_posts.php%_%"
	 * and the '%_%' is required. The '%_%' will be replaced by the contents of in
	 * the 'format' argument. An example for the 'format' argument is "?page=%#%"
	 * and the '%#%' is also required. The '%#%' will be replaced with the page
	 * number.
	 *
	 * You can include the previous and next links in the list by setting the
	 * 'prev_next' argument to true, which it is by default. You can set the
	 * previous text, by using the 'prev_text' argument. You can set the next text
	 * by setting the 'next_text' argument.
	 *
	 * If the 'show_all' argument is set to true, then it will show all of the pages
	 * instead of a short list of the pages near the current page. By default, the
	 * 'show_all' is set to false and controlled by the 'end_size' and 'mid_size'
	 * arguments. The 'end_size' argument is how many numbers on either the start
	 * and the end list edges, by default is 1. The 'mid_size' argument is how many
	 * numbers to either side of current page, but not including current page.
	 *
	 * It is possible to add query vars to the link by using the 'add_args' argument
	 * and see add_query_arg() for more information.
	 *
	 * The 'before_page_number' and 'after_page_number' arguments allow users to
	 * augment the links themselves. Typically this might be to add context to the
	 * numbered links so that screen reader users understand what the links are for.
	 * The text strings are added before and after the page number - within the
	 * anchor tag.
	 *
	 * @since 2.1.0
	 * @since 4.9.0 Added the `aria_current` argument.
	 *
	 * @global WP_Query   $wp_query
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of arguments for generating paginated links for archives.
	 *
	 *     @type string $base               Base of the paginated url. Default empty.
	 *     @type string $format             Format for the pagination structure. Default empty.
	 *     @type int    $total              The total amount of pages. Default is the value WP_Query's
	 *                                      `max_num_pages` or 1.
	 *     @type int    $current            The current page number. Default is 'paged' query var or 1.
	 *     @type string $aria_current       The value for the aria-current attribute. Possible values are 'page',
	 *                                      'step', 'location', 'date', 'time', 'true', 'false'. Default is 'page'.
	 *     @type bool   $show_all           Whether to show all pages. Default false.
	 *     @type int    $end_size           How many numbers on either the start and the end list edges.
	 *                                      Default 1.
	 *     @type int    $mid_size           How many numbers to either side of the current pages. Default 2.
	 *     @type bool   $prev_next          Whether to include the previous and next links in the list. Default true.
	 *     @type bool   $prev_text          The previous page text. Default '&laquo; Previous'.
	 *     @type bool   $next_text          The next page text. Default 'Next &raquo;'.
	 *     @type string $type               Controls format of the returned value. Possible values are 'plain',
	 *                                      'array' and 'list'. Default is 'plain'.
	 *     @type array  $add_args           An array of query args to add. Default false.
	 *     @type string $add_fragment       A string to append to each link. Default empty.
	 *     @type string $before_page_number A string to appear before the page number. Default empty.
	 *     @type string $after_page_number  A string to append after the page number. Default empty.
	 * }
	 * @return string|array|void String of page links or array of page links.
	 */
	public static function pagination($args = '', \WP_Query $wp_query = null, $echo = true) {

		if( null === $wp_query ) {
			global $wp_query;
		}

		$defaults = array(
			'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
			'format' => '?paged=%#%',
			'add_args' => false,
			'current' => max(1, get_query_var('paged')),
			'total' => $wp_query->max_num_pages,
			'prev_text' => __('« Prev'),
			'next_text' => __('Next »'),
			'type' => 'array',				// @TODO Erro quando 'type' => 'list'
			'end_size' => 3,
			'mid_size' => 1,

			'prev_next' => true,
			'show_all' => false,
			'add_fragment' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$pages = paginate_links( $args );

		if( is_array( $pages ) ) {
			//$paged = ( get_query_var( 'paged' ) == 0 ) ? 1 : get_query_var( 'paged' );
			$pagination = '<ul class="pagination">';
			foreach( $pages as $page ) {
				$pagination .= '<li class="page-item' . (strpos($page, 'current') !== false ? ' active' : '') . '"> ' . str_replace('page-numbers', 'page-link', $page) . '</li>';
			}
			$pagination .= '</ul>';
			if( $echo ) {
				echo $pagination;
			} else {
				return $pagination;
			}
		}
		return null;
	}

	/**
	 * Displays or retrieves pagination links for the comments on the current post.
	 *
	 * @see paginate_links()
	 * @since 2.7.0
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string|array $args Optional args. See paginate_links(). Default empty array.
	 * @return string|array|void Markup for comment page links or array of comment page links.
	 */
	public static function paginate_comments_links( $args = array() ) {
		global $wp_rewrite;

		if ( ! is_singular() ) {
			return;
		}

		$page = get_query_var( 'cpage' );
		if ( ! $page ) {
			$page = 1;
		}
		$max_page = get_comment_pages_count();
		$defaults = array(
			'base'         => add_query_arg( 'cpage', '%#%' ),
			'format'       => '',
			'total'        => $max_page,
			'current'      => $page,
			'echo'         => true,
			'type'         => 'plain',
			'add_fragment' => '#comments',
		);
		if ( $wp_rewrite->using_permalinks() ) {
			$defaults['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
		}

		$args       = wp_parse_args( $args, $defaults );
		$page_links = WP_Bootstrap_Pagination::pagination( $args );

		if ( $args['echo'] && 'array' !== $args['type'] ) {
			echo $page_links;
		} else {
			return $page_links;
		}
	}
}
