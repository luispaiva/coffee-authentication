<?php
/**
 * Token Controller for the Coffee plugin.
 *
 * @package Coffee
 */

namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Token Controller Class.
 */
class Token {

	/**
	 * Secret key.
	 *
	 * @var string
	 */
	private static $secret_key = AUTH_KEY;

	/**
	 * Constructor.
	 *
	 * @param string|null $secret_key Secret key.
	 *
	 * @return void
	 */
	public function __construct( ?string $secret_key = null ) {
		self::$secret_key = $secret_key ?? self::$secret_key;
	}

	/**
	 * Generate token.
	 *
	 * @param \WP_User $user User.
	 *
	 * @return bool|array
	 */
	public static function generate( $user ) {

		if ( ! $user instanceof \WP_User ) {
			return false;
		}

		$token        = self::getToken( $user->data->ID );
		$refreshToken = self::getRefreshToken( $user->data->ID );
		$data         = array(
			'token'        => $token,
			'refreshToken' => $refreshToken,
			'id'           => $user->data->ID,
			'name'         => $user->data->display_name,
			'email'        => $user->data->user_email,
		);

		return apply_filters( 'jwt_auth_token_before_dispatch', $data, $user );
	}



	/**
	 * Validate token.
	 *
	 * @param string $token Token.
	 *
	 * @return object|WP_Error
	 */
	public static function validate( string $token ) {
		try {
			return JWT::decode( $token, new Key( self::$secret_key, 'HS256' ) );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'invalid_token', $e->getMessage(), array( 'status' => 403 ) );
		}
	}

	/**
	 * Validate refresh token.
	 *
	 * @param string $refreshToken Refresh token.
	 *
	 * @return \WP_User|WP_Error
	 */
	public static function validateRefreshToken( string $refreshToken ) {
		$userId = self::getUserIdByRefreshToken( $refreshToken );

		if ( ! $userId ) {
			return new \WP_Error(
				'invalid_refresh_token',
				esc_html__( 'Refresh token inválido!', 'coffee' ),
				array( 'status' => 403 )
			);
		}

		return get_user_by( 'id', $userId );
	}

	/**
	 * Get token.
	 *
	 * @param mixed $userId User ID.
	 *
	 * @return string
	 */
	private static function getToken( $userId ) {
		$issued_at    = time();
		$not_before   = apply_filters( 'jwt_auth_not_before', $issued_at, $issued_at );
		$expire       = apply_filters( 'jwt_auth_token_expire', $issued_at + MINUTE_IN_SECONDS * 60 );
		$refreshToken = array(
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $userId,
				),
			),
		);

		return JWT::encode( $refreshToken, self::$secret_key, 'HS256' );
	}

	/**
	 * Get refresh token.
	 *
	 * @param mixed $userId User ID.
	 *
	 * @return string
	 */
	private static function getRefreshToken( $userId ) {
		$issued_at    = time();
		$not_before   = apply_filters( 'jwt_auth_refresh_token_not_before', $issued_at, $issued_at );
		$expire       = apply_filters( 'jwt_auth_refresh_token_expire', $issued_at + DAY_IN_SECONDS * 60 );
		$refreshToken = array(
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $userId,
				),
			),
		);

		return JWT::encode( $refreshToken, self::$secret_key, 'HS256' );
	}

	/**
	 * Get user ID by refresh token.
	 *
	 * @param string $refreshToken Refresh token.
	 *
	 * @return bool|int
	 */
	private static function getUserIdByRefreshToken( $refreshToken ) {
		$refreshToken = self::validate( $refreshToken );

		if ( is_wp_error( $refreshToken ) ) {
			return false;
		}

		return (int) $refreshToken->data->user->id;
	}
}
