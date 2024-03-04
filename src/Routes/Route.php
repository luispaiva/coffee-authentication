<?php
/**
 * Routes for the Coffee plugin.
 *
 * @package Coffee
 * @version 1.0.0
 */

namespace App\Routes;

/**
 * Class Route
 */
class Route {

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected static $namespace = 'coffee';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function registerRoutes(): void {
		Auth::register();
	}
}
