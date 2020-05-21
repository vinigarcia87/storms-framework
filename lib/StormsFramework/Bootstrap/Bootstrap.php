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

        // Add CSS class to images on posts and pages
        if( Helper::get_option( 'storms_add_extra_classes_to_img', true ) ) {
            $this->loader
                ->add_filter( 'the_content', 'responsive_images', 10 )
                ->add_filter( 'post_thumbnail_html', 'responsive_images', 10 )
                ->add_filter( 'image_send_to_editor', 'responsive_images', 10 )
				->add_action( 'woocommerce_single_product_image_thumbnail_html', 'responsive_images', 10, 2 )
				->add_filter( 'wp_get_attachment_image_attributes','add_class_post_thumbnail', 10 );
        }

        $this->loader
			->add_filter( 'the_password_form', 'password_form' );

		$this->loader
			->add_filter( 'get_calendar', 'calendar_widget' )
			->add_filter( 'comment_reply_link', 'add_bootstrap_btn_class', 10 )
			->add_filter( 'edit_comment_link', 'add_bootstrap_btn_class', 10 )
			->add_filter( 'get_avatar', 'avatar_img_circle_class', 10, 1 )
			->add_filter( 'cleaner_gallery_image', 'cleaner_gallery_anchor_class', 99, 4 )
			->add_filter( 'comment_form_defaults', 'bootstrap_comment_form_args', 15 )
			->add_filter( 'wp_list_categories', 'bootstrap_count_badges' );

		add_action( 'after_setup_theme', 'register_navwalker' );
	}

	/**
	 * Wordpress Bootstrap 4 responsive images
	 * Add img-fluid class to images
	 * Source: https://gist.github.com/mkdizajn/7352469
	 * @see https://stackoverflow.com/a/20499803/1003020
	 *
	 * @param $content
	 * @return string
	 */
	public function responsive_images( $content ) {
		if($content !== '') {
			$new_classes = apply_filters( 'add_classes_to_images', array( 'img-fluid' ) ); // Array of classes

			$content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$document = new \DOMDocument();
			libxml_use_internal_errors(true);
			$document->loadHTML(utf8_decode($content));

			$imgs = $document->getElementsByTagName('img');
			foreach ($imgs as $img) {
				$existing_class = $img->getAttribute('class');
				$img_classes = array_unique(array_merge($new_classes, explode(' ', $existing_class)));

				$img->setAttribute('class', implode(' ', $img_classes));
			}

			$html = $document->saveHTML();

			// remove dimensions from images
			// @WARNING If we remove the width/height properties from images, the WooCommerce PhotoSwipe will not work!
			// @see https://github.com/woocommerce/woocommerce/issues/15376
			//$html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );

			return $html;
		}
	}

	/**
	 * Wordpress Bootstrap 4 responsive images
	 * Add img-fluid class to images
	 *
	 * @param $attr
	 * @return mixed
	 */
	function add_class_post_thumbnail( $attr ) {
		$new_classes = apply_filters( 'add_classes_to_images', array( 'img-fluid' ) ); // Array of classes

		foreach( $new_classes as $class ) {
			$attr['class'] .= ' ' . $class;
		}
		return $attr;
	}

	/**
	 * Bootstrap password form for posts
	 * @see https://wordpress.stackexchange.com/a/71299/54025
	 */
	public function password_form( $form ) {

		// We have a cookie, but it doesn’t match the password.
		$cookie_name = 'wp-postpass_' . COOKIEHASH;
		if ( isset ( $_COOKIE[ $cookie_name ] ) ) : ?>
			<div class="website-notices-wrapper">
				<div class="website-message" role="alert">
				<?php esc_html_e( 'Sorry, your password is wrong.', 'storms' ) ?></div>
			</div>
		<?php
			// Forcing the cookie to expire with the session
			unset( $_COOKIE[ $cookie_name ] );
			setcookie($cookie_name, '', 0, COOKIEPATH);
		endif;
		?>

		<p><?php _e( 'This post is password protected. To view it please enter your password below: or add custom message', 'storms' ); ?></p>
		<form class="form-inline my-2 my-lg-0 post-password-form" action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post">
			<div class="form-group">
				<label for="password" class="col-form-label mr-2"><?php _e( 'Password', 'storms' ); ?></label>
				<input name="post_password" id="password" type="password" class="form-control mr-2" required placeholder="<?php _e( 'Password', 'storms' ); ?>"/>
			</div>
			<input type="submit" name="Submit" class="btn btn-secondary" value="<?php esc_attr_e( "Submit" ); ?>" />
		</form>
		<?php
		return '';
	}

	/**
	 * Modify the calendar widget styling to work better for bootstrap styling
	 */
	public function calendar_widget( $html ) {
		if( ! $html ) {
			return;
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

	/**
	 * Parse the reply link HTML to adjust the output to meet bootstrap HTML/CSS structure
	 * Add btn btn-link classes to buttons
	 */
	public function add_bootstrap_btn_class( $html ) {
		if ( ! $html ) {
			return;
		}

		$dom = new \DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		$x = new \DOMXPath($dom);

		foreach($x->query("//a") as $node) {
			$classes = $node->getAttribute( "class" );
			$classes .= ' btn btn-link';
			$node->setAttribute( "class" , $classes );
		}

		$newHtml = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());

		return $newHtml;
	}

	/**
	 * Parse the avatar HTML to adjust the output to meet bootstrap HTML/CSS structure
	 * Add rounded-circle class to avatar images
	 */
	public function avatar_img_circle_class( $html ) {

		if( ! $html ) {
			return;
		}

		$dom = new \DOMDocument();

		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );

		$x = new \DOMXPath( $dom );

		foreach( $x->query( "//img" ) as $node ) {
			$classes = $node->getAttribute( "class" );
			$classes .= ' rounded-circle';
			$node->setAttribute( "class" , $classes );
		}

		$newHtml = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML() );

		return $newHtml;
	}

	/**
	 * Parse the cleaner gallery HTML to adjust the output to meet bootstrap HTML/CSS structure
	 * Add thumbanail class to gallery image
	 */
	public function cleaner_gallery_anchor_class( $html, $attachment_id, $attr, $cleaner_gallery_instance ) {

		if( ! $html ) {
			return;
		}

		$dom = new \DOMDocument();

		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );

		$x = new \DOMXPath( $dom );

		foreach( $x->query( "//a" ) as $node ) {
			$node->setAttribute( "class", "thumbnail" );
		}

		$newHtml = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML() );

		return $newHtml;
	}

	/**
	 * Modify comment form to work better with bootstrap styles
	 */
	public function bootstrap_comment_form_args( $args ) {

		$post_id = get_the_ID();
		$commenter = wp_get_current_commenter();
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		$html5    = 'html5';

		$fields   =  array(
			'author' => '<div class="form-group comment-form-author"> ' . //'<label for="author">' . __( 'Nome', 'storms' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				'<input id="author" class="form-control" name="author" type="text" placeholder="Nome" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div>',
			'email'  => '<div class="form-group comment-form-email"> '. //<label for="email">' . __( 'Email', 'storms' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				'<input id="email" class="form-control" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' placeholder="E-mail" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div>',
		);

		$args = array(
			'fields'               => $fields,
			'comment_field'        => '<div class="form-group comment-form-comment"><textarea id="comment" class="form-control" name="comment" cols="45" rows="8" aria-required="true" placeholder="Comentário"></textarea></div>',
			'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'Você precisa estar <a href="%s">logado</a> para postar um comentário.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logado como <a href="%1$s">%2$s</a>. <a href="%3$s" title="Sair desta conta">Sair?</a>' ), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
			'comment_notes_before' => '',
			'comment_notes_after'  => '',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'name_submit'          => 'submit',
			'title_reply'          => __( 'Deixe seu comentário', 'storms' ),
			'title_reply_to'       => __( 'Responder a %s', 'storms' ),
			'cancel_reply_link'    => __( 'Cancelar comentário', 'storms' ),
			'label_submit'         => __( 'Enviar', 'storms' ),
			'format'               => 'html5',
			'class_submit'         => 'submit',
			'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s btn btn-secondary" value="%4$s" />',
			'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
		);

		return apply_filters( 'storms_wc_product_review_comment_form_args', $args );

	}

	/**
	 * Use bootstrap badge styling for category counts e.g. in the category widget
	 */
	public function bootstrap_count_badges($links) {
		//woocommerce already has a span with a count class
		if ( strpos( $links ,'<span class="count">' ) !== false) {

			$links = str_replace('<span class="count">', '<span class="badge">', $links);

			$links = str_replace( array('(',')') , '', $links);

		} else {

			$links = str_replace('</a> (', '</a> <span class="badge">', $links);

			$links = str_replace(')', '</span>', $links);

		}

		return $links;

	}

}
