<?php
/**
 * Plugin Name: Archive Lightbox Gallery
 * Plugin URI: https://developer.developer.developer
 * Description: Transform archive pages into visual galleries using post featured images with WordPress core lightbox.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://developer.developer.developer
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: archive-lightbox-gallery
 * Requires at least: 6.3
 * Requires PHP: 7.4
 *
 * @package ArchiveLightboxGallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'ALG_VERSION', '1.0.0' );
define( 'ALG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ALG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include required files.
require_once ALG_PLUGIN_DIR . 'includes/class-alg-settings.php';
require_once ALG_PLUGIN_DIR . 'includes/class-alg-archive-detector.php';
require_once ALG_PLUGIN_DIR . 'includes/class-alg-gallery-renderer.php';
require_once ALG_PLUGIN_DIR . 'includes/class-alg-assets.php';

/**
 * Initialize plugin components.
 *
 * @return void
 */
function alg_init() {
	// Initialize settings (admin only).
	if ( is_admin() ) {
		new ALG_Settings();
	}

	// Initialize frontend components.
	new ALG_Archive_Detector();
	new ALG_Assets();
}
add_action( 'plugins_loaded', 'alg_init' );

/**
 * Get plugin settings with defaults.
 *
 * @return array Plugin settings.
 */
function alg_get_settings() {
	$defaults = array(
		'enabled_taxonomies' => array( 'category', 'post_tag' ),
		'content_mode'       => 'excerpt',
		'columns'            => 2,
	);

	$settings = get_option( 'alg_settings', array() );

	return wp_parse_args( $settings, $defaults );
}
