<?php
/**
 * Gallery Renderer Class
 *
 * Generates the gallery HTML output.
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ALG_Gallery_Renderer
 *
 * Handles gallery rendering and HTML generation.
 */
class ALG_Gallery_Renderer {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Posts with featured images.
	 *
	 * @var array
	 */
	private $posts;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = alg_get_settings();
	}

	/**
	 * Render the gallery page.
	 *
	 * @return void
	 */
	public function render() {
		global $wp_query;

		// Filter posts to only those with featured images.
		$this->posts = array_filter(
			$wp_query->posts,
			function( $post ) {
				return has_post_thumbnail( $post->ID );
			}
		);

		// Load the template.
		include ALG_PLUGIN_DIR . 'templates/gallery-template.php';
	}

	/**
	 * Get the archive title.
	 *
	 * @return string Archive title.
	 */
	public function get_archive_title() {
		$queried_object = get_queried_object();

		if ( $queried_object && isset( $queried_object->name ) ) {
			return $queried_object->name;
		}

		return __( 'Gallery', 'archive-lightbox-gallery' );
	}

	/**
	 * Check if there are posts to display.
	 *
	 * @return bool True if posts exist, false otherwise.
	 */
	public function has_posts() {
		return ! empty( $this->posts );
	}

	/**
	 * Get posts with featured images.
	 *
	 * @return array Posts array.
	 */
	public function get_posts() {
		return $this->posts;
	}

	/**
	 * Get number of columns.
	 *
	 * @return int Column count.
	 */
	public function get_columns() {
		return isset( $this->settings['columns'] ) ? (int) $this->settings['columns'] : 3;
	}

	/**
	 * Get the gallery HTML.
	 *
	 * @return string Gallery HTML.
	 */
	public function get_gallery_html() {
		if ( ! $this->has_posts() ) {
			return '<p>' . esc_html__( 'No posts with featured images found.', 'archive-lightbox-gallery' ) . '</p>';
		}

		$output  = '<figure class="wp-block-gallery has-nested-images columns-' . esc_attr( $this->get_columns() ) . ' is-cropped alg-gallery">';
		$output .= '<ul class="blocks-gallery-grid">';

		$index = 0;
		foreach ( $this->posts as $post ) {
			$output .= $this->get_image_item_html( $post, $index );
			$index++;
		}

		$output .= '</ul>';
		$output .= '</figure>';

		return $output;
	}

	/**
	 * Get HTML for a single gallery image item.
	 *
	 * @param WP_Post $post  Post object.
	 * @param int     $index Image index.
	 * @return string Image item HTML.
	 */
	private function get_image_item_html( $post, $index ) {
		$thumbnail_id  = get_post_thumbnail_id( $post->ID );
		$thumbnail_url = get_the_post_thumbnail_url( $post->ID, 'medium_large' );
		$full_url      = get_the_post_thumbnail_url( $post->ID, 'full' );
		$alt_text      = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

		if ( empty( $alt_text ) ) {
			$alt_text = get_the_title( $post->ID );
		}

		$output  = '<li class="blocks-gallery-item">';
		$output .= '<figure class="wp-block-image size-medium_large alg-lightbox-trigger"';
		$output .= ' data-alg-index="' . esc_attr( $index ) . '"';
		$output .= ' data-alg-full-src="' . esc_url( $full_url ) . '"';
		$output .= ' role="button"';
		$output .= ' tabindex="0"';
		/* translators: %s is the post title */
		$output .= ' aria-label="' . esc_attr( sprintf( __( 'Open %s in lightbox', 'archive-lightbox-gallery' ), $alt_text ) ) . '">';
		$output .= '<img';
		$output .= ' src="' . esc_url( $thumbnail_url ) . '"';
		$output .= ' alt="' . esc_attr( $alt_text ) . '"';
		$output .= ' loading="lazy"';
		$output .= ' />';
		$output .= '</figure>';
		$output .= '</li>';

		return $output;
	}

	/**
	 * Get hidden content containers for lightbox.
	 *
	 * @return string Hidden content HTML.
	 */
	public function get_content_containers_html() {
		if ( ! $this->has_posts() ) {
			return '';
		}

		$content_mode = isset( $this->settings['content_mode'] ) ? $this->settings['content_mode'] : 'excerpt';
		$output       = '<div class="alg-post-content-containers" style="display:none;">';

		$index = 0;
		foreach ( $this->posts as $post ) {
			$output .= $this->get_content_container_html( $post, $index, $content_mode );
			$index++;
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Get HTML for a single post content container.
	 *
	 * @param WP_Post $post         Post object.
	 * @param int     $index        Post index.
	 * @param string  $content_mode Content mode (excerpt or full).
	 * @return string Content container HTML.
	 */
	private function get_content_container_html( $post, $index, $content_mode ) {
		$title = get_the_title( $post->ID );

		if ( 'full' === $content_mode ) {
			$content = apply_filters( 'the_content', $post->post_content );
		} else {
			$content = has_excerpt( $post->ID )
				? apply_filters( 'the_excerpt', $post->post_excerpt )
				: wp_trim_words( $post->post_content, 55, '...' );
		}

		$permalink = get_permalink( $post->ID );

		$output  = '<div class="alg-post-content" data-alg-index="' . esc_attr( $index ) . '">';
		$output .= '<h2 class="alg-post-title">';
		$output .= '<a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a>';
		$output .= '</h2>';
		$output .= '<div class="alg-post-body">' . wp_kses_post( $content ) . '</div>';
		$output .= '</div>';

		return $output;
	}
}
