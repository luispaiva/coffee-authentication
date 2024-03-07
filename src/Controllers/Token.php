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

		$issued_at  = time();
		$not_before = apply_filters( 'jwt_auth_not_before', $issued_at, $issued_at );
		$expire     = apply_filters( 'jwt_auth_token_expire', $issued_at + ( MINUTE_IN_SECONDS * 60 ) );
		$token      = array(
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => array(
				'user' => array(
					'id' => $user->data->ID,
				),
			),
		);

		$token        = JWT::encode( $token, self::$secret_key, 'HS256' );
		$refreshToken = wp_generate_uuid4();

		self::setRefreshToken( $user->data->ID, $refreshToken );

		$data = array(
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
			return new \WP_Error( 'rest_auth_invalid_token', $e->getMessage(), array( 'status' => 403 ) );
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
				'rest_auth_invalid_refresh_token',
				esc_html__( 'Refresh token inválido!', 'coffee' ),
				array( 'status' => 403 )
			);
		}

		return get_user_by( 'id', $userId );
	}

	/**
	 * Refresh token.
	 *
	 * @param int    $userId       User ID.
	 * @param string $refreshToken Refresh token.
	 *
	 * @return bool
	 */
	private static function setRefreshToken( $userId, $refreshToken ) {
		return update_user_meta( $userId, 'refresh_token', $refreshToken );
	}

	/**
	 * Get user ID by refresh token.
	 *
	 * @param string $refreshToken Refresh token.
	 *
	 * @return int
	 */
	private static function getUserIdByRefreshToken( $refreshToken ) {
		global $wpdb;

		$cache_key = 'refresh_token_' . $refreshToken;
		$userId    = wp_cache_get( $cache_key, 'refresh_token' );

		if ( false !== $userId ) {
			return (int) $userId;
		}

		$userId = $wpdb->get_var( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'refresh_token' AND meta_value = %s",
				$refreshToken
			)
		);

		wp_cache_set( $cache_key, $userId, 'refresh_token' );

		return (int) $userId;
	}
}
