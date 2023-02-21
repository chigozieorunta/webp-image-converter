<?php
/**
 * Plugin Name: WebP Image Converter
 * Plugin URI:  https://github.com/chigozieorunta/webp-image-converter
 * Description: A simple WP plugin to help convert JPG/PNG images during page runtime to WebP formats.
 * Version:     1.0.0
 * Author:      Chigozie Orunta
 * Author URI:  https://chigozieorunta.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webpimageconverter
 * Domain Path: /languages
 *
 * @package WebPImageConverter
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Require autoload.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Run plugin.
( \WebPImageConverter\WebPImageConverter::get_instance )->run();