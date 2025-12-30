<?php
/**
 * Gallery Template
 *
 * Displays the archive gallery using theme's header and footer.
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the renderer from the global.
global $alg_gallery_renderer;

get_header();
?>

<main id="main" class="site-main alg-gallery-main">
	<div class="alg-gallery-wrapper">
		<?php if ( function_exists( 'the_archive_title' ) ) : ?>
		<header class="alg-gallery-header">
			<?php
			the_archive_title( '<h1 class="alg-gallery-title">', '</h1>' );
			the_archive_description( '<div class="alg-gallery-description">', '</div>' );
			?>
		</header>
		<?php endif; ?>

		<?php
		if ( $alg_gallery_renderer && $alg_gallery_renderer->has_posts() ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $alg_gallery_renderer->get_gallery_html();
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $alg_gallery_renderer->get_content_containers_html();
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $alg_gallery_renderer->get_posts_without_images_html();

			// Theme pagination.
			the_posts_pagination();
		} else {
			echo '<p class="alg-no-posts">' . esc_html__( 'No posts found.', 'archive-lightbox-gallery' ) . '</p>';
		}
		?>
	</div>
</main>

<?php
// Output lightbox.
if ( $alg_gallery_renderer ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $alg_gallery_renderer->get_lightbox_overlay_html();
}

get_footer();
