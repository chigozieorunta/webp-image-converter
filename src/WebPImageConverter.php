<?php
/**
 * Main Plugin.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter;

/**
 * WebpImageConverter Class.
 */
class WebPImageConverter {
	/**
	 * Plugin instance.
	 *
	 * @var \WebPImageConverter
	 */
	private static $instance;

	/**
	 * Return plugin instance.
	 *
	 * @return \WebPImageConverter
	 */
	public static function get_instance(): WebPImageConverter {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Run hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		// Run hooks.
	}
}
