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

		// Convert to WebP.
		WebPConvert::convert(
			$this->source,
			$this->destination,
			[
				'quality'     => 85,
				'max-quality' => 100,
				'converter'   => 'imagick',
			]
		);

		return str_replace( $this->relative_source, $this->relative_destination, $html );
	}
}
