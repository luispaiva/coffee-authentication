<?php
/**
 * Plugin Name: Coffee - Authentication
 * Author:      Luis Paiva
 * Author URI:  https://luispaiva.com.br
 * Text Domain: coffee
 * Version:     0.0.1
 * Domain Path: /languages
 *
 * @package Coffee
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

if ( class_exists( App\Setup\Config::class ) ) {
	register_activation_hook( __FILE__, array( App\Setup\Config::class, 'activation' ) );
	register_deactivation_hook( __FILE__, array( App\Setup\Config::class, 'deactivation' ) );
	register_uninstall_hook( __FILE__, array( App\Setup\Config::class, 'uninstall' ) );
}

if ( class_exists( App\Init::class ) ) {
	add_action( 'plugins_loaded', array( App\Init::class, 'init' ) );
}
