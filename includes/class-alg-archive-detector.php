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
	 * Whether gallery mode is activated for this request.
	 *
	 * @var bool|null Null means not yet checked.
	 */
	private $is_activated = null;

	/**
	 * Whether the gallery has been rendered.
	 *
	 * @var bool
	 */
	private $gallery_rendered = false;

	/**
	 * Gallery renderer instance.
	 *
	 * @var ALG_Gallery_Renderer|null
	 */
	private $renderer = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Block theme filter - use pre_render_block to short-circuit.
		add_filter( 'pre_render_block', array( $this, 'pre_render_block_filter' ), 10, 3 );

		// Classic theme hooks registered after query is ready.
		add_action( 'template_redirect', array( $this, 'maybe_activate_gallery' ) );
	}

	/**
	 * Check if gallery should activate and set up hooks.
	 *
	 * @return void
	 */
	public function maybe_activate_gallery() {
		if ( ! $this->check_should_activate() ) {
			return;
		}

		$this->init_renderer();

		// Register hooks for classic themes.
		add_filter( 'the_content', array( $this, 'filter_post_content' ), 1 );
		add_filter( 'the_excerpt', array( $this, 'filter_post_content' ), 1 );
		add_filter( 'post_class', array( $this, 'filter_post_class' ), 10, 3 );
		add_action( 'wp_head', array( $this, 'output_inline_css' ), 100 );
		add_action( 'wp_footer', array( $this, 'output_lightbox' ), 5 );
	}

	/**
	 * Check and cache activation status.
	 *
	 * @return bool Whether gallery should activate.
	 */
	private function check_should_activate() {
		if ( null === $this->is_activated ) {
			$this->is_activated = $this->should_activate();
		}
		return $this->is_activated;
	}

	/**
	 * Initialize the renderer with posts.
	 *
	 * @return void
	 */
	private function init_renderer() {
		if ( null !== $this->renderer ) {
			return;
		}

		global $wp_query;

		if ( ! isset( $wp_query->posts ) || empty( $wp_query->posts ) ) {
			return;
		}

		// Filter posts to only those with featured images.
		$posts_with_images = array_filter(
			$wp_query->posts,
			function( $post ) {
				return has_post_thumbnail( $post->ID );
			}
		);

		// Set gallery active for asset loading.
		ALG_Assets::set_gallery_active( true );

		// Initialize renderer with filtered posts.
		$this->renderer = new ALG_Gallery_Renderer( $posts_with_images );
	}

	/**
	 * Pre-render block filter to replace post template with gallery.
	 *
	 * @param string|null $pre_render   The pre-rendered content. Null to use default.
	 * @param array       $parsed_block The parsed block data.
	 * @param WP_Block    $parent_block The parent block, if any.
	 * @return string|null The gallery HTML or null to use default rendering.
	 */
	public function pre_render_block_filter( $pre_render, $parsed_block, $parent_block = null ) {
		// If already pre-rendered by something else, don't interfere.
		if ( null !== $pre_render ) {
			return $pre_render;
		}

		// Only target the post-template block.
		if ( ! isset( $parsed_block['blockName'] ) || 'core/post-template' !== $parsed_block['blockName'] ) {
			return null;
		}

		// Check conditions inside the filter since it's registered early.
		if ( ! $this->check_should_activate() ) {
			return null;
		}

		// Only replace once.
		if ( $this->gallery_rendered ) {
			return null;
		}

		// Initialize renderer if not already done.
		$this->init_renderer();

		if ( null === $this->renderer ) {
			return null;
		}

		$this->gallery_rendered = true;

		// Add lightbox to footer.
		add_action( 'wp_footer', array( $this, 'output_lightbox' ), 5 );

		$output  = $this->renderer->get_gallery_html();
		$output .= $this->renderer->get_content_containers_html();

		return $output;
	}

	/**
	 * Replace post content with gallery on first post (classic themes).
	 *
	 * @param string $content The post content.
	 * @return string Modified content.
	 */
	public function filter_post_content( $content ) {
		if ( ! $this->is_activated || ! in_the_loop() ) {
			return $content;
		}

		// First post in loop gets the gallery.
		if ( ! $this->gallery_rendered ) {
			$this->gallery_rendered = true;

			$output  = $this->renderer->get_gallery_html();
			$output .= $this->renderer->get_content_containers_html();

			return $output;
		}

		// Subsequent posts return empty.
		return '';
	}

	/**
	 * Add CSS classes to posts for hiding (classic themes).
	 *
	 * @param string[] $classes Post classes.
	 * @param string[] $class   Additional classes.
	 * @param int      $post_id Post ID.
	 * @return string[] Modified classes.
	 */
	public function filter_post_class( $classes, $class, $post_id ) {
		if ( ! $this->is_activated || ! in_the_loop() ) {
			return $classes;
		}

		if ( ! $this->gallery_rendered ) {
			$classes[] = 'alg-gallery-container';
		} else {
			$classes[] = 'alg-hidden-post';
		}

		return $classes;
	}

	/**
	 * Output CSS for classic themes.
	 *
	 * @return void
	 */
	public function output_inline_css() {
		?>
		<style>
			.alg-hidden-post { display: none !important; }
			.alg-gallery-container .entry-title,
			.alg-gallery-container .entry-meta,
			.alg-gallery-container .post-meta,
			.alg-gallery-container .entry-footer,
			.alg-gallery-container .post-thumbnail,
			.alg-gallery-container > header:first-child { display: none !important; }
		</style>
		<?php
	}

	/**
	 * Output lightbox overlay in footer.
	 *
	 * @return void
	 */
	public function output_lightbox() {
		if ( ! $this->gallery_rendered ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderer->get_lightbox_overlay_html();
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
