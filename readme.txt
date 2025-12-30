=== Archive Lightbox Gallery ===
Contributors: developer
Tags: gallery, lightbox, archive, featured images, photography
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Transform WordPress archive pages into visual galleries with a lightbox, using post featured images.

== Description ==

Archive Lightbox Gallery automatically converts your category, tag, and taxonomy archive pages into beautiful image galleries. Each post's featured image becomes a gallery thumbnail that opens in a lightbox overlay, displaying the full-size image along with the post title and content.

Perfect for:

* Photo bloggers
* Visual storytellers
* Portfolio sites
* Any site using categories or tags as photo collections

= Features =

* **Automatic gallery display** - Archive pages instantly become image galleries
* **Built-in lightbox** - Click any image to view it full-size with post content
* **Keyboard navigation** - Use arrow keys to navigate, ESC to close
* **Responsive grid** - Adapts to any screen size
* **Configurable columns** - Choose 1-6 columns for your grid
* **Content options** - Display full post content or excerpts in the lightbox
* **Per-taxonomy control** - Enable or disable for specific taxonomies
* **Accessibility ready** - Full keyboard support and ARIA labels
* **Lightweight** - No jQuery dependency, minimal footprint

= How It Works =

1. Install and activate the plugin
2. Configure which taxonomies should display as galleries (Settings > Archive Gallery)
3. Visit any category or tag archive page
4. Your posts with featured images are displayed as a clickable gallery grid

== Installation ==

1. Upload the `archive-lightbox-gallery` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Archive Gallery to configure options
4. Visit a category or tag archive to see your gallery

== Frequently Asked Questions ==

= Which archive pages are supported? =

Categories, tags, and custom taxonomy archives are all supported. You can enable or disable the gallery for each taxonomy type in the settings.

= What happens to posts without featured images? =

Posts without featured images are simply not displayed in the gallery. Only posts with a featured image will appear.

= Can I customize the number of columns? =

Yes, you can set between 1 and 6 columns in Settings > Archive Gallery. The grid is also responsive and will automatically reduce columns on smaller screens.

= Does this work with my theme? =

The plugin renders its own template for archive pages, so it should work with any theme. The design is intentionally minimal to fit most sites.

= Can I show full post content or just excerpts? =

Yes, you can choose between displaying the full post content or just the excerpt in the lightbox. This is configurable in the settings.

= Is the lightbox accessible? =

Yes, the lightbox includes full keyboard navigation (arrow keys to navigate, ESC to close), proper focus management, and ARIA attributes for screen readers.

= Does this affect my single post pages? =

No, single posts and pages are not affected. The gallery only appears on archive pages (categories, tags, taxonomies).

== Screenshots ==

1. Gallery grid display on a category archive page
2. Lightbox view with image and post content
3. Settings page with taxonomy and display options

== Changelog ==

= 1.0.0 =
* Initial release
* Gallery grid display for archive pages
* Custom lightbox with navigation
* Settings page for taxonomy and display configuration
* Keyboard navigation support
* Responsive design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Archive Lightbox Gallery.
