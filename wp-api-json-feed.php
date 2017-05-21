<?php
/*
Plugin Name: WP-API JSON Feed
Plugin URI:  https://wordpress.org/plugins/wp-api-json-feed/
Description: Implements a JSON feed following the version 1 spec by means of a REST API endpoint.
Version:     1.0.0
Author:      Felix Arntz
Author URI:  https://leaves-and-love.net
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: wp-api-json-feed
Tags:        json feed, feed, rest api
*/
/**
 * Plugin bootstrap functions.
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checks if the minimum WordPress version is installed, and if so, initializes the plugin.
 *
 * @since 1.0.0
 *
 * @codeCoverageIgnore
 */
function wp_api_json_feed_load() {
	load_plugin_textdomain( 'wp-api-json-feed' );

	if ( version_compare( get_bloginfo( 'version' ), '4.7.0', '<' ) ) {
		add_action( 'admin_notices', 'wp_api_json_feed_show_version_error_notice', 10, 0 );
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'src/class-wp-api-json-feed.php';

	WP_API_JSON_Feed::instance();
}
add_action( 'plugins_loaded', 'wp_api_json_feed_load', 10, 0 );

/**
 * Renders an admin notice that the minimum WordPress version requirement is not met.
 *
 * @since 1.0.0
 *
 * @codeCoverageIgnore
 */
function wp_api_json_feed_show_version_error_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php printf( __( 'The WP-API JSON Feed plugin requires at least WordPress version 4.7, but you are running version %s.', 'wp-api-json-feed' ), get_bloginfo( 'version' ) ); ?>
		</p>
	</div>
	<?php
}
