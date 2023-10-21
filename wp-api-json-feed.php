<?php
/**
 * Plugin bootstrap functions.
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <hello@felix-arntz.me>
 *
 * @wordpress-plugin
 * Plugin Name: WP-API JSON Feed
 * Plugin URI: https://wordpress.org/plugins/wp-api-json-feed/
 * Description: Implements JSON feeds following the official JSON feed specification by using the WordPress REST API.
 * Version: 1.1.0
 * Requires at least: 5.4
 * Requires PHP: 5.6
 * Author: Felix Arntz
 * Author URI: https://felix-arntz.me
 * License: GNU General Public License v3 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-api-json-feed
 * Tags: json feed, feed, rest api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checks if the minimum WordPress version is installed, and if so, initializes the plugin.
 *
 * @since 1.0.0
 */
function wp_api_json_feed_load() {
	if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
		add_action( 'admin_notices', 'wp_api_json_feed_show_version_error_notice', 10, 0 );
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-api-json-feed.php';
	require_once plugin_dir_path( __FILE__ ) . 'inc/class-wp-api-json-feed-urls.php';

	WP_API_JSON_Feed::instance();
}
add_action( 'plugins_loaded', 'wp_api_json_feed_load', 10, 0 );

/**
 * Renders an admin notice that the minimum WordPress version requirement is not met.
 *
 * @since 1.0.0
 */
function wp_api_json_feed_show_version_error_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WordPress version number */
				esc_html__( 'The WP-API JSON Feed plugin requires at least WordPress version 5.4, but you are running version %s.', 'wp-api-json-feed' ),
				esc_html( get_bloginfo( 'version' ) )
			);
			?>
		</p>
	</div>
	<?php
}
