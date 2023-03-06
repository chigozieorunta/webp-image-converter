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
		add_filter( 'post_thumbnail_html', [ $this, 'convert_to_webp' ], 10, 5 );
	}

	/**
	 * Convert to WebP.
	 *
	 * @param string       $html The post thumbnail HTML.
	 * @param int          $post_id The post ID.
	 * @param int          $thumbnail_id The post thumbnail ID, or 0 if there isn't one.
	 * @param string|int[] $size Requested image size.
	 * @param string|array $attr Query string or array of attributes.
	 * @return void
	 */
	public function convert_to_webp( $html, $post_id, $thumbnail_id, $size, $attr ) {
		// Get image paths.
		$this->id          = $thumbnail_id;
		$this->source      = $this->get_image_source();
		$this->destination = $this->get_image_destination();

		// If image is empty.
		if ( ! file_exists( $this->source ) ) {
			return $html;
		}

		// Convert to WebP.
		if ( ! file_exists( $this->destination ) ) {
			WebPConvert::convert(
				$this->source,
				$this->destination,
				[
					'quality'     => 85,
					'max-quality' => 100,
					'converter'   => 'imagick',
				]
			);
		}

		return str_replace( $this->relative_source, $this->relative_destination, $html );
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
}
