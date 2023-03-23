<?php
/**
 * Menu Service.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter\Plugin;

use WebPImageConverter\Plugin\Settings;

/**
 * Menu Class.
 */
class Menu {
	/**
	 * Menu Instance.
	 *
	 * @var \Menu
	 */
	private static $instance;

	/**
	 * Get instance of Class (Singleton).
	 *
	 * @return \Menu
	 */
	public static function get_instance(): object {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set up Menu.
	 *
	 * @return void
	 */
	public function init(): void {
		// Parent Menu.
		add_menu_page(
			__( Settings::NAME, Settings::DOMAIN ),
			__( Settings::NAME, Settings::DOMAIN ),
			Settings::ROLE,
			Settings::SLUG,
			false,
			'dashicons-images-alt2',
			99
		);

		// Dashboard Sub Menu.
		add_submenu_page(
			Settings::SLUG,
			__( Settings::NAME, Settings::DOMAIN ),
			__( 'Dashboard', Settings::DOMAIN ),
			Settings::ROLE,
			Settings::SLUG,
			[ $this, 'register_menu_page' ]
		);
	}

	/**
	 * Register Menu page.
	 *
	 * @return void
	 */
	public function register_menu_page(): void {
		echo $this->get_plugin_page();
	}

	public function get_plugin_page(): string {
		return sprintf(
			'<div style="background: #fff; height: 400px; display: flex; flex-wrap: wrap; gap: 0; font-family: Lato, Arial;">
				<div style="width: 200px; height: 100vh; background: #f0f0f1;">
					<h1>Hello World</h1>
				</div>
				<div style="width: 400px;">
					<h1>Hello World</h1>
				</div>
			</div>'
		);
	}
}
