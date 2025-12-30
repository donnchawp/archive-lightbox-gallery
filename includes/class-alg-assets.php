<?php
/**
 * Assets Class
 *
 * Handles script and style enqueueing.
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ALG_Assets
 *
 * Manages asset loading for the gallery.
 */
class ALG_Assets {

	/**
	 * Whether gallery is active on current request.
	 *
	 * @var bool
	 */
	private static $is_gallery_active = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
	}

	/**
	 * Set gallery active state.
	 *
	 * @param bool $active Whether gallery is active.
	 * @return void
	 */
	public static function set_gallery_active( $active ) {
		self::$is_gallery_active = (bool) $active;
	}

	/**
	 * Check if gallery is active.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public static function is_gallery_active() {
		return self::$is_gallery_active;
	}

	/**
	 * Enqueue gallery assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! self::$is_gallery_active ) {
			return;
		}

		$settings = alg_get_settings();
		$columns  = isset( $settings['columns'] ) ? (int) $settings['columns'] : 2;

		// Plugin CSS.
		wp_enqueue_style(
			'alg-gallery',
			ALG_PLUGIN_URL . 'assets/css/alg-gallery.css',
			array(),
			ALG_VERSION
		);

		// Add inline CSS for columns.
		$inline_css = ':root { --alg-columns: ' . absint( $columns ) . '; }';
		wp_add_inline_style( 'alg-gallery', $inline_css );

		// Plugin JS for custom lightbox.
		wp_enqueue_script(
			'alg-lightbox',
			ALG_PLUGIN_URL . 'assets/js/alg-content-swap.js',
			array(),
			ALG_VERSION,
			true
		);
	}
}
