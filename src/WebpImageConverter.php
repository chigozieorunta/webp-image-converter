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
	 * @var \WebpImageConverter
	 */
	private \WebpImageConverter $instance;

	/**
	 * Return plugin instance.
	 *
	 * @return \WebpImageConverter
	 */
	public static function get_instance(): \WebpImageConverter {
		if ( null === self::$instance ) {
			return new self();
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