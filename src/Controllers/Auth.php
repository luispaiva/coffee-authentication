<?php
/**
 * Auth Controller for the Coffee plugin.
 *
 * @package Coffee
 */

namespace App\Controllers;

/**
 * Auth Controller Class.
 */
class Auth {

	/**
	 * Login.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function login( \WP_REST_Request $request ) {

		if ( empty( $request->get_param( 'username' ) ) || empty( $request->get_param( 'password' ) ) ) {
			return new \WP_Error(
				'required_params',
				esc_html__( 'Usuário e senha são obrigatórios!', 'coffee' ),
				array( 'status' => 401 )
			);
		}

		$user = wp_authenticate(
			$request->get_param( 'username' ),
			$request->get_param( 'password' )
		);

		if ( is_wp_error( $user ) ) {
			return new \WP_Error(
				'authentication_failed',
				esc_html__( 'Usuário ou senhas inválidos!', 'coffee' ),
				array( 'status' => 401 )
			);
		}

		return new \WP_REST_Response( Token::generate( $user ), 200 );
	}

	/**
	 * Refresh token.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function refresh( \WP_REST_Request $request ) {

		if ( empty( $request->get_param( 'refresh_token' ) ) ) {
			return new \WP_Error(
				'required_params',
				esc_html__( 'O refresh token é obrigatório!', 'coffee' ),
				array( 'status' => 401 )
			);
		}

		$refreshToken = $request->get_param( 'refresh_token' );
		$user         = Token::validateRefreshToken( $refreshToken );

		if ( is_wp_error( $user ) ) {
			return new \WP_Error(
				'invalid_refresh_token',
				esc_html__( 'Refresh token inválido!', 'coffee' ),
				array( 'status' => 401 )
			);
		}

		return new \WP_REST_Response( Token::generate( $user ), 200 );
	}
}
