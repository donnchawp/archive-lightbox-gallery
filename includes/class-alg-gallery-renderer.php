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
	 * Posts without featured images.
	 *
	 * @var array
	 */
	private $posts_without_images;

	/**
	 * Constructor.
	 *
	 * @param array $posts               Posts with featured images.
	 * @param array $posts_without_images Posts without featured images.
	 */
	public function __construct( $posts, $posts_without_images = array() ) {
		$this->settings             = alg_get_settings();
		$this->posts                = $posts;
		$this->posts_without_images = $posts_without_images;
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
		return isset( $this->settings['columns'] ) ? (int) $this->settings['columns'] : 2;
	}

	/**
	 * Get the gallery HTML.
	 *
	 * @return string Gallery HTML.
	 */
	public function get_gallery_html() {
		if ( ! $this->has_posts() ) {
			return '<p class="alg-no-posts">' . esc_html__( 'No posts with featured images found.', 'archive-lightbox-gallery' ) . '</p>';
		}

		$output  = '<div class="alg-gallery-wrapper">';
		$output .= '<figure class="wp-block-gallery has-nested-images columns-' . esc_attr( $this->get_columns() ) . ' is-cropped alg-gallery">';
		$output .= '<ul class="blocks-gallery-grid">';

		$index = 0;
		foreach ( $this->posts as $post ) {
			$output .= $this->get_image_item_html( $post, $index );
			$index++;
		}

		$output .= '</ul>';
		$output .= '</figure>';
		$output .= '</div>';

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

	/**
	 * Get HTML for posts without featured images.
	 *
	 * @return string Posts without images HTML.
	 */
	public function get_posts_without_images_html() {
		if ( empty( $this->posts_without_images ) ) {
			return '';
		}

		$output  = '<div class="alg-posts-without-images">';
		$output .= '<h2 class="alg-posts-without-images-title">' . esc_html__( 'More Posts', 'archive-lightbox-gallery' ) . '</h2>';
		$output .= '<ul class="alg-posts-without-images-list">';

		foreach ( $this->posts_without_images as $post ) {
			$output .= $this->get_post_list_item_html( $post );
		}

		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get HTML for a single post list item (without featured image).
	 *
	 * @param WP_Post $post Post object.
	 * @return string Post list item HTML.
	 */
	private function get_post_list_item_html( $post ) {
		$title     = get_the_title( $post->ID );
		$permalink = get_permalink( $post->ID );
		$excerpt   = has_excerpt( $post->ID )
			? get_the_excerpt( $post->ID )
			: wp_trim_words( $post->post_content, 25, '&hellip;' );

		$output  = '<li class="alg-post-list-item">';
		$output .= '<a href="' . esc_url( $permalink ) . '" class="alg-post-list-link">';
		$output .= '<span class="alg-post-list-title">' . esc_html( $title ) . '</span>';
		$output .= '</a>';
		if ( $excerpt ) {
			$output .= '<p class="alg-post-list-excerpt">' . esc_html( $excerpt ) . '</p>';
		}
		$output .= '</li>';

		return $output;
	}

	/**
	 * Get lightbox overlay HTML.
	 *
	 * @return string Lightbox overlay HTML.
	 */
	public function get_lightbox_overlay_html() {
		ob_start();
		?>
		<!-- Lightbox Overlay -->
		<div class="alg-lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Image lightbox', 'archive-lightbox-gallery' ); ?>" hidden>
			<div class="alg-lightbox-overlay"></div>
			<button class="alg-lightbox-close" type="button" aria-label="<?php esc_attr_e( 'Close lightbox', 'archive-lightbox-gallery' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path>
				</svg>
			</button>
			<button class="alg-lightbox-prev" type="button" aria-label="<?php esc_attr_e( 'Previous image', 'archive-lightbox-gallery' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"></path>
				</svg>
			</button>
			<button class="alg-lightbox-next" type="button" aria-label="<?php esc_attr_e( 'Next image', 'archive-lightbox-gallery' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"></path>
				</svg>
			</button>
			<div class="alg-lightbox-content">
				<figure class="alg-lightbox-figure">
					<img class="alg-lightbox-image" src="" alt="" />
				</figure>
				<div class="alg-lightbox-info" aria-live="polite">
					<!-- Post content will be inserted here -->
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
