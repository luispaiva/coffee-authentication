<?php
/**
 * Class initial setup for the Coffee plugin.
 *
 * @package Coffee
 */

namespace App;

/**
 * Class Init
 */
class Init {

	/**
	 * Initializes the plugin.
	 *
	 * @return void
	 */
	public static function init() {
		new Hooks\Hook();
		new Routes\Route();
	}
}
