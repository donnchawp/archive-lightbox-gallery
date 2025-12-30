<?php
/**
 * Gallery Template
 *
 * Full page template for the archive gallery display.
 *
 * @package ArchiveLightboxGallery
 *
 * @var ALG_Gallery_Renderer $this The gallery renderer instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Manually trigger asset enqueue since we're before wp_head.
do_action( 'wp_enqueue_scripts' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'alg-gallery-page' ); ?>>
<?php wp_body_open(); ?>

<div class="alg-gallery-wrapper">
	<header class="alg-gallery-header">
		<h1 class="alg-gallery-title"><?php echo esc_html( $this->get_archive_title() ); ?></h1>
		<?php
		// Display archive description if available.
		$description = term_description();
		if ( $description ) :
			?>
			<div class="alg-gallery-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</header>

	<main class="alg-gallery-main">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in get_gallery_html().
		echo $this->get_gallery_html();
		?>
	</main>

	<?php
	// Hidden content containers for lightbox.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in get_content_containers_html().
	echo $this->get_content_containers_html();
	?>

	<?php if ( ! $this->has_posts() ) : ?>
		<p class="alg-no-posts">
			<?php esc_html_e( 'No posts with featured images found in this archive.', 'archive-lightbox-gallery' ); ?>
		</p>
	<?php endif; ?>

	<nav class="alg-gallery-pagination">
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => __( '&laquo; Previous', 'archive-lightbox-gallery' ),
				'next_text' => __( 'Next &raquo;', 'archive-lightbox-gallery' ),
			)
		);
		?>
	</nav>
</div>

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

<?php wp_footer(); ?>
</body>
</html>
