<?php
/**
 * Main Plugin.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter;

use DOMDocument;
use Monolog\Logger;
use WebPConvert\WebPConvert;
use Monolog\Handler\StreamHandler;

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
	 * Generate WebP on post_thumbnail_html.
	 *
	 * @param  string       $html         The post thumbnail HTML.
	 * @param  int          $post_id      The post ID.
	 * @param  int          $thumbnail_id The post thumbnail ID, or 0 if there isn't one.
	 * @param  string|int[] $size         Requested image size.
	 * @param  string|array $attr         Query string or array of attributes.
	 * @return string
	 */
	public function filter_post_thumbnail_html( $html, $post_id, $thumbnail_id, $size, $attr ): string {
		// Get DOM.
		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_NOERROR );

		// Get image source.
		$image_dom        = $dom->getElementsByTagName( 'img' )->item( 0 );
		$this->rel_source = $image_dom->getAttribute( 'src' );

		// Generate WebP.
		$this->convert_to_webp();

		// Return WebP Image.
		if ( file_exists( $this->abs_destination ) ) {
			return str_replace( $this->rel_source, $this->rel_destination, $html );
		}

		// Safely return default.
		return $html;
	}

	/**
	 * Generate WebP on WP image_block.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block Block array properties.
	 * @return string
	 */
	public function filter_wp_image_block( $block_content, $block ): string {
		if ( 'core/image' === $block['blockName'] ) {
			// Get DOM.
			$dom = new DOMDocument();
			$dom->loadHTML( $block_content, LIBXML_NOERROR );

			// Get source.
			$image_dom        = $dom->getElementsByTagName( 'img' )->item( 0 );
			$this->rel_source = $image_dom->getAttribute( 'src' );

			// Generate WebP.
			$this->convert_to_webp();

			// Return WebP Image.
			if ( file_exists( $this->abs_destination ) ) {
				return str_replace( $this->rel_source, $this->rel_destination, $block_content );
			}
		}

		// Safely return Block content.
		return $block_content;
	}

	/**
	 * Convert to WebP.
	 *
	 * @return void
	 */
	private function convert_to_webp(): void {
		// Set image sources.
		$this->set_image_source();

		// Set image destinations.
		$this->set_image_destination();

		// If image is empty.
		if ( ! file_exists( $this->abs_source ) ) {
			return;
		}

		// Convert to WebP.
		if ( ! file_exists( $this->abs_destination ) ) {
			WebPConvert::convert(
				$this->abs_source,
				$this->abs_destination,
				[
					'quality'     => (int) apply_filters( 'wic_quality', 85 ),
					'max-quality' => (int) apply_filters( 'wic_max_quality', 100 ),
					'converter'   => (string) apply_filters( 'wic_converter', 'imagick' ),
				]
			);
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
