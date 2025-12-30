<?php
/**
 * Archive Detector Class
 *
 * Detects when to activate the gallery on archive pages.
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ALG_Archive_Detector
 *
 * Handles detection of archive contexts and triggers gallery rendering.
 */
class ALG_Archive_Detector {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_render_gallery' ), 10 );
	}

	/**
	 * Check if gallery should be rendered and render it.
	 *
	 * @return void
	 */
	public function maybe_render_gallery() {
		if ( ! $this->should_activate() ) {
			return;
		}

		// Set gallery active for asset loading.
		ALG_Assets::set_gallery_active( true );

		$renderer = new ALG_Gallery_Renderer();
		$renderer->render();
		exit;
	}

	/**
	 * Determine if the gallery should activate on the current page.
	 *
	 * @return bool True if gallery should activate, false otherwise.
	 */
	public function should_activate() {
		// Don't activate in admin or REST requests.
		if ( is_admin() || defined( 'REST_REQUEST' ) ) {
			return false;
		}

		// Must be an archive page.
		if ( ! $this->is_supported_archive() ) {
			return false;
		}

		// Check if this taxonomy is enabled in settings.
		$settings  = alg_get_settings();
		$taxonomy  = $this->get_current_taxonomy();
		$enabled   = isset( $settings['enabled_taxonomies'] ) ? $settings['enabled_taxonomies'] : array();

		if ( ! in_array( $taxonomy, $enabled, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current page is a supported archive type.
	 *
	 * @return bool True if supported archive, false otherwise.
	 */
	private function is_supported_archive() {
		return is_category() || is_tag() || is_tax();
	}

	/**
	 * Get the current taxonomy slug.
	 *
	 * @return string Taxonomy slug or empty string.
	 */
	private function get_current_taxonomy() {
		if ( is_category() ) {
			return 'category';
		}

		if ( is_tag() ) {
			return 'post_tag';
		}

		if ( is_tax() ) {
			$queried_object = get_queried_object();
			if ( $queried_object && isset( $queried_object->taxonomy ) ) {
				return $queried_object->taxonomy;
			}
		}

		return '';
	}
}
