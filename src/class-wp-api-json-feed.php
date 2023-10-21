<?php
/**
 * WP_API_JSON_Feed class
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <hello@felix-arntz.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
	 * @var WP_API_JSON_Feed|null
	 */
	private static $instance = null;

	/**
	 * JSON feed URLs instance.
	 *
	 * @since 1.1.0
	 * @var WP_API_JSON_Feed_URLs
	 */
	private $urls;

	/**
	 * Returns the main instance.
	 *
	 * @since 1.0.0
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
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->urls = new WP_API_JSON_Feed_URLs();
	}

	/**
	 * Adds the necessary hooks for the plugin to work.
	 *
	 * @since 1.0.0
	 */
	public function add_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_head', array( $this, 'render_current_feed_link_tags' ) );

		add_filter( 'register_post_type_args', array( $this, 'filter_post_type_args' ), 10, 2 );
	}

	/**
	 * Registers REST API routes for each post type that supports a JSON feed.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		require_once __DIR__ . '/class-wp-api-json-feed-rest-controller.php';

		$post_types = array_filter(
			get_post_types( array(), 'objects' ),
			array( $this, 'supports_json_feed' )
		);

		foreach ( $post_types as $post_type ) {
			$controller = new WP_API_JSON_Feed_REST_Controller( $this->urls, $post_type );
			$controller->register_routes();
		}
	}

	/**
	 * Renders link tags for the current JSON feeds to display in the <head>.
	 *
	 * This will always render a link to the posts feed, unless its JSON feed support was disabled.
	 * Additionally, if the current post type is not 'post', it will render a link to the JSON feed of that post type
	 * if it is supported.
	 *
	 * @since 1.1.0
	 */
	public function render_current_feed_link_tags() {
		$post_type = $this->get_current_post_type();

		// If the current is not of the 'post' post type, still render the posts JSON feed link in addition.
		if ( 'post' !== $post_type ) {
			$this->render_feed_link_tag( 'post' );
		}

		$this->render_feed_link_tag( $post_type );
	}

	/**
	 * Returns the current post type slug, or 'post' as a fallback.
	 *
	 * @since 1.1.0
	 *
	 * @return string Current post type slug based on the current WP_Query, or 'post' if none could be determined.
	 */
	public function get_current_post_type() {
		$post_type = get_post_type();
		if ( ! $post_type ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof WP_Post_Type ) {
				$post_type = $queried_object->name;
			} elseif ( $queried_object instanceof WP_Post ) {
				$post_type = $queried_object->post_type;
			} else {
				$post_type = 'post';
			}
		}

		return $post_type;
	}

	/**
	 * Renders a link tag for a JSON feed to display in the <head>.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Optional. Post type to render the feed link for. Default 'post'.
	 */
	public function render_feed_link_tag( $post_type = 'post' ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object || ! $this->supports_json_feed( $post_type_object ) ) {
			return;
		}

		/* translators: %s: post type plural label */
		$feed_title = sprintf( _x( '%s JSON Feed', 'feed link tag title', 'wp-api-json-feed' ), $post_type_object->labels->name );

		$feed_url = $this->urls->get_feed_url_for_post_type( $post_type_object );

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
	 * @return array Array of modified post type arguments.
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
	 * Checks whether the given post type supports showing a JSON feed.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 * @return bool True if the post type supports a JSON feed, false otherwise.
	 */
	private function supports_json_feed( WP_Post_Type $post_type ) {
		return isset( $post_type->show_json_feed ) && $post_type->show_json_feed;
	}
}
