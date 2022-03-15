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
	 * Image ID.
	 *
	 * @var integer
	 */
	private int $id;

	/**
	 * Image source (absolute path).
	 *
	 * @var string
	 */
	private string $abs_source = '';

	/**
	 * Image destination (absolute path).
	 *
	 * @var string
	 */
	private string $abs_destination = '';

	/**
	 * Image source (relative path).
	 *
	 * @var string
	 */
	private string $rel_source = '';

	/**
	 * Image destination (relative path).
	 *
	 * @var string
	 */
	private string $rel_destination = '';

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
		add_action( 'add_attachment', [ $this, 'filter_add_attachment' ] );
		add_filter( 'wp_get_attachment_image_src', [ $this, 'filter_wp_get_attachment_image_src' ], 10, 4 );
		add_filter( 'post_thumbnail_html', [ $this, 'filter_post_thumbnail_html' ], 10, 5 );
		add_filter( 'render_block', [ $this, 'filter_wp_image_block' ], 10, 2 );
	}

	/**
	 * Generate WebP on add_attachment.
	 *
	 * @param  int $attachment_id Image ID.
	 * @return void
	 */
	public function filter_add_attachment( $attachment_id ): void {
		// Get Image ID.
		$this->id = $attachment_id;

		// Get image source.
		$this->rel_source = wp_get_attachment_url( $this->id );

		// Ensure this is image, then go ahead.
		if ( ! $this->is_image_attachment() ) {
			return;
		}

		// Generate WebP for main image.
		$this->convert_to_webp();
	}

	/**
	 * Generate WebP on wp_get_attachment_image_src.
	 *
	 * @param  array|false  $image         Array of Image data.
	 * @param  int          $attachment_id Image attachment ID.
	 * @param  string|int[] $size          Image size (width & height).
	 * @param  bool         $icon          Whether the image should be treated as an icon.
	 * @return array|false
	 */
	public function filter_wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		// Get image source.
		$this->rel_source = isset ( $image[0] ) ? (string) $image[0] : (string) $image;

		// Generate WebP.
		$this->convert_to_webp();

		// Return WebP Image.
		if ( file_exists( $this->abs_destination ) ) {
			$image[0] = $this->rel_destination;
		}

		// Safely return Image.
		return $image;
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
		$DOM = new DOMDocument();
		$DOM->loadHTML( $html, LIBXML_NOERROR );

		// Get image source.
		$image_DOM        = $DOM->getElementsByTagName( 'img' )->item( 0 );
		$this->rel_source = $image_DOM->getAttribute( 'src' );

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
		if ( $block['blockName'] === 'core/image' ) {
			// Get DOM.
			$DOM = new DOMDocument();
			$DOM->loadHTML( $block_content, LIBXML_NOERROR );

			// Get source.
			$image_DOM        = $DOM->getElementsByTagName( 'img' )->item( 0 );
			$this->rel_source = $image_DOM->getAttribute( 'src' );

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
					'quality'     => apply_filters( 'wic_quality', 85 ),
					'max-quality' => apply_filters( 'wic_max_quality', 100 ),
					'converter'   => apply_filters( 'wic_converter', 'imagick' ),
				]
			);
		}
	}

	/**
	 * Get Image source (absolute path).
	 *
	 * @return string
	 */
	private function get_image_source(): string {
		// Get relative path.
		$img_uploads_dir       = wp_upload_dir();
		$this->relative_source = wp_get_attachment_url( $this->id );

		// Get image source.
		return str_replace( $img_uploads_dir['baseurl'], $img_uploads_dir['basedir'], $this->relative_source );
	}

	/**
	 * Get Image destination (absolute path).
	 *
	 * @return string
	 */
	private function get_image_destination(): string {
		// Get file extension.
		$image_extension            = '.' . pathinfo( $this->source, PATHINFO_EXTENSION );
		$this->relative_destination = str_replace( $image_extension, '.webp', $this->relative_source );

		// Get image destination.
		return str_replace( $image_extension, '.webp', $this->source );
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
	 * @param  string $message Message to be loged.
	 * @return void
	 */
	private function log( $message ): void {
		$logger = new Logger( 'info' );
		$logger->pushHandler( new StreamHandler( __DIR__ . '/log_file.log', Logger::DEBUG ) );
		$logger->info( $message );
	}
}
