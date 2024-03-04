<?php
/**
 * Auth route for the Coffee plugin.
 *
 * @package Coffee
 * @version 1.0.0
 */

namespace App\Routes;

/**
 * Class Auth
 */
class Auth extends Route {

	/**
	 * The resource.
	 *
	 * @var string
	 */
	private static $resource = 'auth';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_rest_route(
			self::$namespace . '/v1',
			self::$resource . '/login',
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => function ( \WP_REST_Request $request ) {
					return ( new \App\Controllers\Auth() )->login( $request );
				},
				'args'     => array(
					'username' => array(
						'required'          => true,
						'description'       => 'The user username.',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'password' => array(
						'required'          => true,
						'description'       => 'The user password.',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}
}
