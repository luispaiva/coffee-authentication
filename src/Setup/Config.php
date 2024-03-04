<?php
/**
 * Initial setup for the WP Backend Challenge plugin.
 *
 * @package WP_Backend_Challenge
 * @author  Luis Paiva <contato@luispaiva.com.br>
 *
 * @version 1.0.0
 */

namespace App\Setup;

/**
 * Class Config.
 */
class Config {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		define( 'COFFEE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		define( 'COFFEE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'COFFEE_PLUGIN_VERSION', '1.0.0' );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain( 'coffee', false, COFFEE_PLUGIN_PATH . '/languages' );
	}

	/**
	 * Activation the plugin.
	 *
	 * @return void
	 */
	public static function activation(): void {
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public static function deactivation(): void {
		flush_rewrite_rules();
	}

	/**
	 * Uninstall the plugin.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			die;
		}
	}
}
