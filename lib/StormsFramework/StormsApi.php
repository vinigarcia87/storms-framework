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
 * StormsApi class
 * @package StormsFramework
 *
 * Customization of the Wordpress StormsApi
 * @see  _documentation/StormsApi_Class.md
 */

namespace StormsFramework;

class StormsApi extends Base\Runner
{
	private $apiEndpoint;

	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public function define_hooks() {

		$this->apiEndpoint = apply_filters( 'storms_api_endpoint', 'storms-api' );

		$this->loader
			->add_action( 'init', 'add_endpoint', 0 )
			->add_filter( 'query_vars', 'add_query_vars', 0 )
			->add_action( 'parse_request', 'handle_api_requests', 0 );

	}

	//<editor-fold desc="Layout getters and setters">

	/**
	 * Add new endpoints.
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( $this->apiEndpoint, EP_ALL );
	}

	/**
	 * Add new query vars.
	 *
	 * @param array $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->apiEndpoint;
		return $vars;
	}

	/**
	 *
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET[$this->apiEndpoint] ) ) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars[$this->apiEndpoint] = sanitize_key( wp_unslash( $_GET[$this->apiEndpoint] ) ); // WPCS: input var okay, CSRF ok.
		}

		// wc-api endpoint requests.
		if ( ! empty( $wp->query_vars[$this->apiEndpoint] ) ) {

			// Buffer, we won't want any output here.
			//ob_start();

			// No cache headers.
			\StormsFramework\Helper::nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars[$this->apiEndpoint] ) );

			// Trigger generic action before request hook.
			do_action( 'storms_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'storms_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'storms_api_' . $api_request );

			// Done, clear buffer and exit.
			//ob_end_clean();
			//die( '-1' );
		}
	}

	/**
	 * Return the Storms API URL for a given request.
	 * storms_api_request_url( 'Endpoint' )
	 *
	 * @param string    $request Requested endpoint.
	 * @param bool|null $ssl     If should use SSL, null if should auto detect. Default: null.
	 * @return string
	 */
	public static function api_request_url( $request, $ssl = null ) {
		$apiEndpoint = apply_filters( 'storms_api_endpoint', 'storms-api' );

		if ( is_null( $ssl ) ) {
			$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
		} elseif ( $ssl ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		if ( strstr( get_option( 'permalink_structure' ), '/index.php/' ) ) {
			$api_request_url = trailingslashit( home_url( '/index.php/' . $apiEndpoint . '/' . $request, $scheme ) );
		} elseif ( get_option( 'permalink_structure' ) ) {
			$api_request_url = trailingslashit( home_url( '/' . $apiEndpoint . '/' . $request, $scheme ) );
		} else {
			$api_request_url = add_query_arg( $apiEndpoint, $request, trailingslashit( home_url( '', $scheme ) ) );
		}

		return esc_url_raw( apply_filters( 'storms_api_request_url', $api_request_url, $request, $ssl ) );
	}

	//</editor-fold>

}
