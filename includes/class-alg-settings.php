<?php
/**
 * Settings Class
 *
 * Handles plugin settings page and options.
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ALG_Settings
 *
 * Manages the plugin settings page.
 */
class ALG_Settings {

	/**
	 * Option name for settings.
	 *
	 * @var string
	 */
	private $option_name = 'alg_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Archive Lightbox Gallery', 'archive-lightbox-gallery' ),
			__( 'Archive Gallery', 'archive-lightbox-gallery' ),
			'manage_options',
			'archive-lightbox-gallery',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'alg_settings_group',
			$this->option_name,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		// Taxonomies section.
		add_settings_section(
			'alg_taxonomies_section',
			__( 'Enabled Taxonomies', 'archive-lightbox-gallery' ),
			array( $this, 'render_taxonomies_section' ),
			'archive-lightbox-gallery'
		);

		add_settings_field(
			'enabled_taxonomies',
			__( 'Archive Types', 'archive-lightbox-gallery' ),
			array( $this, 'render_taxonomies_field' ),
			'archive-lightbox-gallery',
			'alg_taxonomies_section'
		);

		// Display section.
		add_settings_section(
			'alg_display_section',
			__( 'Display Options', 'archive-lightbox-gallery' ),
			array( $this, 'render_display_section' ),
			'archive-lightbox-gallery'
		);

		add_settings_field(
			'content_mode',
			__( 'Content Display', 'archive-lightbox-gallery' ),
			array( $this, 'render_content_mode_field' ),
			'archive-lightbox-gallery',
			'alg_display_section'
		);

		add_settings_field(
			'columns',
			__( 'Grid Columns', 'archive-lightbox-gallery' ),
			array( $this, 'render_columns_field' ),
			'archive-lightbox-gallery',
			'alg_display_section'
		);
	}

	/**
	 * Sanitize settings on save.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize enabled taxonomies.
		if ( isset( $input['enabled_taxonomies'] ) && is_array( $input['enabled_taxonomies'] ) ) {
			$sanitized['enabled_taxonomies'] = array_map( 'sanitize_key', $input['enabled_taxonomies'] );
		} else {
			$sanitized['enabled_taxonomies'] = array();
		}

		// Sanitize content mode.
		$valid_modes = array( 'excerpt', 'full' );
		if ( isset( $input['content_mode'] ) && in_array( $input['content_mode'], $valid_modes, true ) ) {
			$sanitized['content_mode'] = $input['content_mode'];
		} else {
			$sanitized['content_mode'] = 'excerpt';
		}

		// Sanitize columns.
		if ( isset( $input['columns'] ) ) {
			$columns = absint( $input['columns'] );
			$sanitized['columns'] = max( 1, min( 6, $columns ) );
		} else {
			$sanitized['columns'] = 2;
		}

		return $sanitized;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'alg_settings_group' );
				do_settings_sections( 'archive-lightbox-gallery' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render taxonomies section description.
	 *
	 * @return void
	 */
	public function render_taxonomies_section() {
		echo '<p>' . esc_html__( 'Select which archive types should display as a gallery.', 'archive-lightbox-gallery' ) . '</p>';
	}

	/**
	 * Render display section description.
	 *
	 * @return void
	 */
	public function render_display_section() {
		echo '<p>' . esc_html__( 'Configure how the gallery appears.', 'archive-lightbox-gallery' ) . '</p>';
	}

	/**
	 * Render taxonomies checkbox field.
	 *
	 * @return void
	 */
	public function render_taxonomies_field() {
		$settings = alg_get_settings();
		$enabled  = isset( $settings['enabled_taxonomies'] ) ? $settings['enabled_taxonomies'] : array();

		// Get public taxonomies.
		$taxonomies = get_taxonomies(
			array(
				'public'  => true,
				'show_ui' => true,
			),
			'objects'
		);

		foreach ( $taxonomies as $taxonomy ) {
			// Skip non-archive taxonomies.
			if ( ! $taxonomy->has_archive && 'category' !== $taxonomy->name && 'post_tag' !== $taxonomy->name ) {
				continue;
			}

			$checked = in_array( $taxonomy->name, $enabled, true );
			?>
			<label style="display: block; margin-bottom: 8px;">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->option_name ); ?>[enabled_taxonomies][]"
					value="<?php echo esc_attr( $taxonomy->name ); ?>"
					<?php checked( $checked ); ?>
				/>
				<?php echo esc_html( $taxonomy->label ); ?>
			</label>
			<?php
		}
	}

	/**
	 * Render content mode radio field.
	 *
	 * @return void
	 */
	public function render_content_mode_field() {
		$settings     = alg_get_settings();
		$content_mode = isset( $settings['content_mode'] ) ? $settings['content_mode'] : 'excerpt';
		?>
		<label style="display: block; margin-bottom: 8px;">
			<input
				type="radio"
				name="<?php echo esc_attr( $this->option_name ); ?>[content_mode]"
				value="excerpt"
				<?php checked( 'excerpt', $content_mode ); ?>
			/>
			<?php esc_html_e( 'Excerpt only', 'archive-lightbox-gallery' ); ?>
		</label>
		<label style="display: block; margin-bottom: 8px;">
			<input
				type="radio"
				name="<?php echo esc_attr( $this->option_name ); ?>[content_mode]"
				value="full"
				<?php checked( 'full', $content_mode ); ?>
			/>
			<?php esc_html_e( 'Full content', 'archive-lightbox-gallery' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Choose what content to display in the lightbox below the image.', 'archive-lightbox-gallery' ); ?>
		</p>
		<?php
	}

	/**
	 * Render columns number field.
	 *
	 * @return void
	 */
	public function render_columns_field() {
		$settings = alg_get_settings();
		$columns  = isset( $settings['columns'] ) ? $settings['columns'] : 2;
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $this->option_name ); ?>[columns]"
			value="<?php echo esc_attr( $columns ); ?>"
			min="1"
			max="6"
			step="1"
			style="width: 60px;"
		/>
		<p class="description">
			<?php esc_html_e( 'Number of columns in the gallery grid (1-6).', 'archive-lightbox-gallery' ); ?>
		</p>
		<?php
	}
}
