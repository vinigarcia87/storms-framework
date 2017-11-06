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
 * StormsFramework\Storms\Bootstrap\CommentWalker class
 * A custom WordPress comment walker class to implement the Bootstrap 3 Media object in wordpress comment list.
 *
 * @package     WP Bootstrap Comment Walker
 * @version     1.0.0
 * @author      Edi Amin <to.ediamin@gmail.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link        https://github.com/ediamin/wp-bootstrap-comment-walker
 */

namespace StormsFramework\Storms\Bootstrap;

class CommentWalker extends \Walker_Comment
{

	/**
	 * Output a comment in the HTML5 format.
	 *
	 * @access protected
	 * @since 1.0.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param object $comment Comment to display.
	 * @param int    $depth   Depth of comment.
	 * @param array  $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		?>		
		<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $this->has_children ? 'parent media' : 'media' ); ?>>
			<div class="comment-block">
				<?php if ( 0 != $args['avatar_size'] ): ?>
				<div class="media-left">
					<a href="<?php echo get_comment_author_url(); ?>" class="media-object">
						<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
					</a>
				</div>
				<?php endif; ?>

				<div class="media-body" id="div-comment-<?php comment_ID(); ?>">

					<div class="comment-header">
						<?php printf( '<span class="media-heading">%s</span>', get_comment_author_link() ); ?>

						comentou no dia
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php printf( _x( '%1$s', '1: date' ), get_comment_date() ); ?>
						</time>

                        <?php do_action( 'woocommerce_review_before_comment_meta', $comment ); ?>
					</div><!-- .comment-header -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation label label-info"><?php _e( 'Your comment is awaiting moderation.' ); ?></p>
					<?php endif; ?>

					<div class="comment-content">
						<?php comment_text(); ?>
					</div><!-- .comment-content -->

					<ul class="list-inline">
						<?php
                            $args['allow_reply'] = $args['allow_reply'] ?? true;
                            if( $args['allow_reply'] ) {
                                comment_reply_link(array_merge($args, array(
                                    'add_below' => 'div-comment',
                                    'depth' => $depth,
                                    'max_depth' => $args['max_depth'],
                                    'before' => '<li class="reply-link">',
                                    'after' => '</li>',
                                    'reply_text' => '<i class="fa fa-reply" aria-hidden="true"></i> ' . __('Reply'),
                                )));
                            }
							edit_comment_link( __( 'Edit' ), '<li class="edit-link">', '</li>' );
                        ?>
					</ul>

				</div>
			</div>
		<?php
	}

}
