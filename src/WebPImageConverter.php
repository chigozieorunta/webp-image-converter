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
	 * Set Image sources.
	 *
	 * @return void
	 */
	private function set_image_source(): void {
		$img_uploads_dir  = wp_upload_dir();
		$this->abs_source = str_replace( $img_uploads_dir['baseurl'], $img_uploads_dir['basedir'], $this->rel_source );
	}

	/**
	 * Set Image destinations.
	 *
	 * @return void
	 */
	private function set_image_destination(): void {
		// Set image destinations.
		$image_extension       = '.' . pathinfo( $this->rel_source, PATHINFO_EXTENSION );
		$this->rel_destination = str_replace( $image_extension, '.webp', $this->rel_source );
		$this->abs_destination = str_replace( $image_extension, '.webp', $this->abs_source );
	}

	/**
	 * Check if attachment is image.
	 *
	 * @return boolean
	 */
	private function is_image_attachment(): bool {
		// Get the file path.
		$file_path = get_attached_file( $this->id );

		// Check if it's an image.
		$filetype = wp_check_filetype( $file_path );
		if ( strpos( $filetype['type'], 'image/' ) !== false ) {
			return true;
		}

		return false;
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
