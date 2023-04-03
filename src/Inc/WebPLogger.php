<?php
/**
 * WebP Logger.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter\Inc;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * WebpLogger Class.
 */
class WebPLogger {
	/**
	 * Plugin instance.
	 *
	 * @var \WebPLogger
	 */
	private static $instance;

	/**
	 * Return plugin instance.
	 *
	 * @return \WebPLogger
	 */
	public static function get_instance(): WebPLogger {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Log Messages.
	 *
	 * @param  string $message Message to be logged.
	 * @return void
	 */
	public static function log( $message ): void {
		$logger = new Logger( 'info' );
		$logger->pushHandler( new StreamHandler( plugin_dir_path( __FILE__ ) . '../../error.log', Logger::DEBUG ) );
		$logger->info( $message );
	}
}
