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
		add_action( 'admin_menu', [ $this, 'register_menu' ], 9 );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_assets' ] );
		$this->register_webp_image_converter();
	}

	/**
	 * Register WebP Image Converter.
	 *
	 * @return void
	 */
	public function register_webp_image_converter(): void {
		try {
			$converter = Inc\WebPImageConverter::get_instance();
			$converter->run();
		} catch ( Exception $e ) {
			wp_die( 'Error: Registering Plugin - ' . $e->getMessage() );
		}
	}

	/**
	 * Register Menu.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		try {
			$menu = Plugin\Menu::get_instance();
			$menu->init();
		} catch ( Exception $e ) {
			wp_die( 'Error: Registering Menu - ' . $e->getMessage() );
		}
	}

	/**
	 * Register Assets.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		try {
			$assets = Plugin\Assets::get_instance();
			$assets->init();
		} catch ( Exception $e ) {
			wp_die( 'Error: Registering Assets - ' . $e->getMessage() );
		}
	}

	/**
	 * Log Messages.
	 *
	 * @param  string $message Message to be logged.
	 * @return void
	 */
	private function log( $message ): void {
		$logger = new Logger( 'info' );
		$logger->pushHandler( new StreamHandler( plugin_dir_path( __FILE__ ) . '../error.log', Logger::DEBUG ) );
		$logger->info( $message );
	}
}
