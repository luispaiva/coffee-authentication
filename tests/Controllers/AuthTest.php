<?php
/**
 * Test case for Auth Controller.
 *
 * @package Coffee
 */

declare(strict_types=1);

use App\Controllers\Auth;
use PHPUnit\Framework\TestCase;

/**
 * Class AuthTest
 */
final class AuthTest extends TestCase {

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private static $userId;

	/**
	 * Username.
	 *
	 * @var string
	 */
	private static $username;

	/**
	 * Password.
	 *
	 * @var string
	 */
	private static $password;

	/**
	 * Email.
	 *
	 * @var string
	 */
	private static $email;

	/**
	 * Refresh token.
	 *
	 * @var string
	 */
	private static $refreshToken;

	/**
	 * Set up before auth.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		$uuid           = wp_generate_uuid4();
		self::$username = $uuid;
		self::$password = $uuid;
		self::$email    = $uuid . '@test.com';
		self::$userId   = wp_create_user( self::$username, self::$password, self::$email );
	}

	/**
	 * Tear down after auth.
	 *
	 * @return void
	 */
	public static function tearDownAfterClass(): void {
		// Load the necessary WordPress files.
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// Load the pluggable functions.
		require_once ABSPATH . 'wp-includes/pluggable.php';
		\wp_delete_user( self::$userId );
	}

	/**
	 * Test login.
	 *
	 * @return void
	 */
	public function testFailLogin(): void {
		$auth    = new Auth();
		$request = new \WP_REST_Request();

		$request->set_param( 'username', self::$username );
		$request->set_param( 'password', 'wrong_password' );

		$response = $auth->login( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'authentication_failed', $response->get_error_code() );
		$this->assertEquals( 'Usuário ou senhas inválidos!', $response->get_error_message() );
		$this->assertTrue( $response->has_errors(), 'true' );
	}

	/**
	 * Test success login.
	 *
	 * @return void
	 */
	public function testSuccessLogin(): void {
		$auth    = new Auth();
		$request = new \WP_REST_Request();

		$request->set_param( 'username', self::$username );
		$request->set_param( 'password', self::$password );

		$response = $auth->login( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertJson( wp_json_encode( $response->data ) );

		$this->assertArrayHasKey( 'token', $response->data );
		$this->assertArrayHasKey( 'refreshToken', $response->data );
		$this->assertArrayHasKey( 'id', $response->data );
		$this->assertArrayHasKey( 'name', $response->data );
		$this->assertArrayHasKey( 'email', $response->data );

		self::$refreshToken = $response->data['refreshToken'];
		$this->assertTrue( (int) $response->data['id'] === self::$userId );
	}

	/**
	 * Test not found refresh token.
	 *
	 * @return void
	 */
	public function testNotFoundRefreshToken(): void {
		$auth    = new Auth();
		$request = new \WP_REST_Request();

		$request->set_param( 'refresh_token', null );

		$response = $auth->refresh( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'required_params', $response->get_error_code() );
		$this->assertEquals( 'O refresh token é obrigatório!', $response->get_error_message() );
		$this->assertTrue( $response->has_errors(), 'true' );
	}

	/**
	 * Test invalid refresh token.
	 *
	 * @return void
	 */
	public function testInvalidRefreshToken(): void {
		$auth    = new Auth();
		$request = new \WP_REST_Request();

		$request->set_param( 'refresh_token', 'invalid_refresh_token' );

		$response = $auth->refresh( $request );

		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'invalid_refresh_token', $response->get_error_code() );
		$this->assertEquals( 'Refresh token inválido!', $response->get_error_message() );
		$this->assertTrue( $response->has_errors(), 'true' );
	}

	/**
	 * Test success refresh token.
	 *
	 * @depends testSuccessLogin
	 * @return void
	 */
	public function testSuccessRefreshToken(): void {
		$auth    = new Auth();
		$request = new \WP_REST_Request();

		$request->set_param( 'refresh_token', self::$refreshToken );

		$response = $auth->refresh( $request );

		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertJson( wp_json_encode( $response->data ) );
		$this->assertArrayHasKey( 'token', $response->data );
		$this->assertArrayHasKey( 'refreshToken', $response->data );
		$this->assertArrayHasKey( 'id', $response->data );
		$this->assertArrayHasKey( 'name', $response->data );
		$this->assertArrayHasKey( 'email', $response->data );
		$this->assertTrue( (int) $response->data['id'] === self::$userId );
	}
}
