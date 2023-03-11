<?php
/**
 * Main Plugin.
 *
 * @package WebPImageConverter
 */

namespace WebPImageConverter;

use WebPConvert\WebPConvert;

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
	private string $source;

	/**
	 * Image destination (absolute path).
	 *
	 * @var string
	 */
	private string $destination;

	/**
	 * Image source (relative path).
	 *
	 * @var string
	 */
	private string $relative_source;

	/**
	 * Image destination (relative path).
	 *
	 * @var string
	 */
	private string $relative_destination;

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
		add_action( 'add_attachment', [ $this, 'generate_webp_on_add_attachment' ] );
		add_filter( 'post_thumbnail_html', [ $this, 'generate_webp_on_post_thumbnail_html' ], 10, 5 );
	}

	/**
	 * Generate WebP on add_attachment.
	 *
	 * @param int $image_id Image ID.
	 * @return void
	 */
	public function generate_webp_on_add_attachment( $image_id ): void {
		// Get Image ID.
		$this->id = $image_id;

		// Ensure this is image, then go ahead.
		$this->is_image_attachment();

		// Generate WebP.
		$this->convert_to_webp();
	}

	/**
	 * Generate WebP on post_thumbnail_html.
	 *
	 * @param string       $html The post thumbnail HTML.
	 * @param int          $post_id The post ID.
	 * @param int          $thumbnail_id The post thumbnail ID, or 0 if there isn't one.
	 * @param string|int[] $size Requested image size.
	 * @param string|array $attr Query string or array of attributes.
	 * @return string
	 */
	public function generate_webp_on_post_thumbnail_html( $html, $post_id, $thumbnail_id, $size, $attr ): string {
		// Get Image ID.
		$this->id = $thumbnail_id;

		// Generate WebP.
		$this->convert_to_webp();

		// Return WebP Image.
		if ( file_exists( $this->destination ) ) {
			return str_replace( $this->relative_source, $this->relative_destination, $html );
		}

		// Safely return default.
		return $html;
	}

	/**
	 * Convert to WebP.
	 *
	 * @return void
	 */
	public function convert_to_webp(): void {
		// Get image paths.
		$this->source      = $this->get_image_source();
		$this->destination = $this->get_image_destination();

		// If image is empty.
		if ( ! file_exists( $this->source ) ) {
			return;
		}

		// Convert to WebP.
		if ( ! file_exists( $this->destination ) ) {
			WebPConvert::convert(
				$this->source,
				$this->destination,
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
	public function get_image_source(): string {
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
	public function get_image_destination(): string {
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
	public function is_image_attachment(): bool {
		// Get the file path.
		$file_path = get_attached_file( $this->id );

		// Check if it's an image.
		$filetype = wp_check_filetype( $file_path );
		if ( strpos( $filetype['type'], 'image/' ) !== false ) {
			return true;
		}

		return false;
	}
}
