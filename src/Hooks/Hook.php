<?php
/**
 * Hook for the Coffee plugin.
 *
 * @package Coffee
 * @version 1.0.0
 */

namespace App\Hooks;

use App\Controllers\Token;

/**
 * Hook Class.
 */
class Hook {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'rest_url_prefix', array( $this, 'changeRestUrlPrefix' ) );
		add_filter( 'rest_pre_dispatch', array( $this, 'restPreDispatch' ), 10, 3 );
	}

	/**
	 * Change the URL prefix for the REST API.
	 *
	 * @return string
	 */
	public function changeRestUrlPrefix() {
		return 'api';
	}

	/**
	 * Filter rest_pre_dispatch.
	 *
	 * @param mixed            $result  Anything.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 *
	 * @return void|WP_Error
	 */
	public function restPreDispatch( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {

		list($token) = sscanf( $request->get_header( 'authorization' ), 'Bearer %s' );

		if ( ! empty( $token ) ) {
			$auth = Token::validate( $token );

			if ( is_wp_error( $auth ) ) {
				return new \WP_Error(
					$auth->get_error_code(),
					$auth->get_error_message(),
					array( 'status' => 401 )
				);
			}

			wp_set_current_user( $auth->data->user->id );
		}
	}
}
