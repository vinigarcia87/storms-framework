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
 * Bootstrap class
 * @package StormsFramework
 *
 * Add Bootstrap support
 * @see  _documentation/Bootstrap_Class.md
 */

namespace StormsFramework\Bootstrap;

use StormsFramework\Base;
use StormsFramework\Helper;

class Bootstrap extends Base\Runner
{
	public function __construct() {
		parent::__construct(__CLASS__, STORMS_FRAMEWORK_VERSION, $this);
	}

	public function define_hooks() {

        $this->loader
			->add_filter( 'the_password_form', 'password_form', 10, 2 )
			->add_filter( 'get_calendar', 'calendar_widget' )

			// Comment Form customization - wp-includes/comment-template.php
			->add_filter( 'comment_form_fields', 'bootstrap_comment_form_fields' );

	}

	/**
	 * Filters the HTML output for the protected post password form.
	 *
	 * If modifying the password field, please note that the core database schema
	 * limits the password field to 20 characters regardless of the value of the
	 * size attribute in the form input.
	 *
	 * @param string  $output The password form HTML output.
	 * @param WP_Post $post   Post object.
	 * @return string
	 */
	public function password_form( $output, $post ) {
		$post   = get_post( $post );
		$label  = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
		$output = '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" class="post-password-form" method="post">
		<p>' . __( 'This content is password protected. To view it please enter your password below:' ) . '</p>
		<p><label class="form-label" for="' . $label . '">' . __( 'Password:' ) . '</label> <input class="form-control" name="post_password" id="' . $label . '" type="password" size="20" /> <input class="btn btn-secondary" type="submit" name="Submit" value="' . esc_attr_x( 'Enter', 'post password form' ) . '" /></p></form>
		';

		return $output;
	}

	/**
	 * Modify the calendar widget styling to work better for bootstrap styling
	 */
	public function calendar_widget( $html ) {
		if( ! $html ) {
			return $html;
		}

		$dom = new \DOMDocument();

		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );

		$x = new \DOMXPath( $dom );

		foreach( $x->query( "//table" ) as $node ) {
			$node->setAttribute( 'class', 'table table-sm table-striped' );
		}

		$newHtml = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML() );

		return $newHtml;

	}

	//<editor-fold desc="Comment Form">

	/**
	 * Filters the comment form default arguments.
	 * Use {@see 'comment_form_default_fields'} to filter the comment fields.
	 *
	 * @param array $defaults The default comment form arguments.
	 * @return mixed|void
	 */
	public function bootstrap_comment_form_fields( $comment_fields ) {

		$html5 = 'html5' === (  current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml' );

		// Identify required fields visually.
		$required_indicator = ' <span class="required" aria-hidden="true">*</span>';
		$required_attribute = ( $html5 ? ' required' : ' required="required"' );

		$commenter     = wp_get_current_commenter();
		$user          = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';

		$html5 = 'html5' === (  current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml' );

		$req   = get_option( 'require_name_email' );

		// Define attributes in HTML5 or XHTML syntax.
		$required_attribute = ( $html5 ? ' required' : ' required="required"' );
		$checked_attribute  = ( $html5 ? ' checked' : ' checked="checked"' );

		// Identify required fields visually.
		$required_indicator = ' <span class="required" aria-hidden="true">*</span>';

		$comment_fields['author'] =
			sprintf(
				'<p class="comment-form-author">%s %s</p>',
				sprintf(
					'<label for="author" class="form-label">%s%s</label>',
					__( 'Name' ),
					( $req ? $required_indicator : '' )
				),
				sprintf(
					'<input id="author" name="author" type="text" value="%s" size="30" maxlength="245"%s  class="form-control" />',
					esc_attr( $commenter['comment_author'] ),
					( $req ? $required_attribute : '' )
				)
			);
		$comment_fields['email'] =
			sprintf(
				'<p class="comment-form-email">%s %s</p>',
				sprintf(
					'<label for="email" class="form-label">%s%s</label>',
					__( 'Email' ),
					( $req ? $required_indicator : '' )
				),
				sprintf(
					'<input id="email" name="email" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes"%s  class="form-control" />',
					( $html5 ? 'type="email"' : 'type="text"' ),
					esc_attr( $commenter['comment_author_email'] ),
					( $req ? $required_attribute : '' )
				)
			);

		// Nobody needs this...
		unset( $comment_fields['url'] );

		if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
			$consent = empty( $commenter['comment_author_email'] ) ? '' : $checked_attribute;

			$comment_fields['cookies'] = sprintf(
				'<p class="comment-form-cookies-consent form-check">%s %s</p>',
				sprintf(
					'<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" class="form-check-input" type="checkbox" value="yes"%s />',
					$consent
				),
				sprintf(
					'<label for="wp-comment-cookies-consent" class="form-check-label">%s</label>',
					__( 'Save my name, email, and website in this browser for the next time I comment.' )
				)
			);
		}

		$comment_fields['comment'] =
			sprintf(
				'<p class="comment-form-comment">%s %s</p>',
				sprintf(
					'<label for="comment" class="form-label">%s%s</label>',
					_x( 'Comment', 'noun' ),
					$required_indicator
				),
				'<textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525"' . $required_attribute . ' class="form-control"></textarea>'
			);

		return $comment_fields;
	}

	//</editor-fold>

}
