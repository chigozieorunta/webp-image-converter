<?php
/**
 * Assets Service.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter\Plugin;

use WebPImageConverter\Plugin\Settings;

/**
 * Assets Class.
 */
class Assets {
	/**
	 * Assets Instance.
	 *
	 * @var \Assets
	 */
	private static $instance;

	/**
	 * Get instance of Class (Singleton).
	 *
	 * @return \Assets
	 */
	public static function get_instance(): object {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register Styles and Scripts.
	 *
	 * @return void
	 */
	public function init() {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Register Admin Styles and Scripts.
	 *
	 * @return void
	 */
	public function admin_init() {
		$this->admin_styles();
		$this->admin_scripts();
	}

	/**
	 * Plugin Styles.
	 *
	 * @return void
	 */
	public function register_styles() {
		wp_enqueue_style(
			Settings::SLUG,
			plugin_dir_url( __DIR__ ) . '../assets/css/dist/webp-image-converter.css'
		);
	}

	/**
	 * Plugin Scripts.
	 *
	 * @return void
	 */
	public function register_scripts() {
		wp_enqueue_script(
			Settings::SLUG,
			plugin_dir_url( __DIR__ ) . '../assets/js/dist/webp-image-converter.js',
			array( 'jquery' )
		);
	}

	/**
	 * Admin Styles.
	 *
	 * @return void
	 */
	public function admin_styles() {
		wp_enqueue_style(
			Settings::SLUG,
			plugin_dir_url( __DIR__ ) . '../assets/css/dist/webp-image-converter-admin.css'
		);
	}

	/**
	 * Admin Scripts.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			Settings::SLUG,
			plugin_dir_url( __DIR__ ) . '../assets/js/dist/webp-image-converter-admin.js',
			array( 'jquery' )
		);
	}
}
