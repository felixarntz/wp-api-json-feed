<?php
/**
 * WP_API_JSON_Feed class
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class WP_API_JSON_Feed {

	/**
	 * The main instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var WP_API_JSON_Feed|null
	 */
	private static $instance = null;

	/**
	 * Returns the main instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return WP_API_JSON_Feed The main class instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->add_hooks();
		}

		return self::$instance;
	}

	/**
	 * Registers REST API routes for each post type that supports a JSON feed.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_rest_routes() {
		require_once dirname( __FILE__ ) . '/class-wp-api-json-feed-rest-controller.php';

		foreach ( get_post_types( array( 'show_json_feed' => true ), 'objects' ) as $post_type ) {
			$controller = new WP_API_JSON_Feed_REST_Controller( $post_type );
			$controller->register_routes();
		}
	}

	/**
	 * Renders a link tag for a JSON feed to display in the <head>.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $post_type Optional. Post type to render the feed link for. Default 'post'.
	 */
	public function render_feed_link_tag( $post_type = 'post' ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object ) {
			return;
		}

		if ( ! $post_type_object->show_json_feed ) {
			return;
		}

		/* translators: %s: post type plural label */
		$feed_title = sprintf( _x( '%s JSON Feed', 'feed link tag title', 'wp-api-json-feed' ), $post_type_object->labels->name );

		$feed_url = rest_url( sprintf( 'feed/v1/%s', ( ! empty( $post_type_object->json_feed_base ) ? $post_type_object->json_feed_base : $post_type_object->name ) ) );

		printf( '<link rel="alternate" type="application/json" title="%1$s" href="%2$s" />', esc_attr( $feed_title ), esc_url( $feed_url ) );
		echo "\n";
	}

	/**
	 * Adds additional post type arguments used by the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args      Array of arguments for registering a post type.
	 * @param string $post_type Post type key.
	 * @param array Array of modified post type arguments.
	 */
	public function filter_post_type_args( $args, $post_type ) {
		if ( ! isset( $args['show_json_feed'] ) ) {
			$args['show_json_feed'] = 'post' === $post_type ? true : false;
		}

		if ( ! isset( $args['json_feed_base'] ) ) {
			$args['json_feed_base'] = 'post' === $post_type ? 'posts' : '';
		}

		return $args;
	}

	/**
	 * Adds the necessary hooks for the plugin to work.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @codeCoverageIgnore
	 */
	private function add_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10, 0 );
		add_action( 'wp_head', array( $this, 'render_feed_link_tag' ), 10, 0 );

		add_filter( 'register_post_type_args', array( $this, 'filter_post_type_args' ), 10, 2 );
	}
}
