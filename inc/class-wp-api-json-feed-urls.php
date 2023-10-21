<?php
/**
 * WP_API_JSON_Feed_URL class
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <hello@felix-arntz.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class to manage JSON feed URLs.
 *
 * @since 1.1.0
 */
class WP_API_JSON_Feed_URLs {

	/**
	 * Gets the JSON feed URL for a given post type's feed.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 * @return string Post type JSON feed URL.
	 */
	public function get_feed_url_for_post_type( WP_Post_Type $post_type ) {
		return $this->get_feed_url(
			$this->get_url_namespace(),
			$this->get_url_base_for_post_type( $post_type )
		);
	}

	/**
	 * Gets the URL namespace.
	 *
	 * @since 1.1.0
	 *
	 * @return string The REST URL namespace for all JSON feeds.
	 */
	public function get_url_namespace() {
		return 'feed/v1';
	}

	/**
	 * Gets the URL base for a given post type's feed.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 * @return string Post type JSON feed URL base.
	 */
	public function get_url_base_for_post_type( WP_Post_Type $post_type ) {
		if ( ! empty( $post_type->json_feed_base ) ) {
			return $post_type->json_feed_base;
		}

		return $post_type->name;
	}

	/**
	 * Gets the JSON feed URL for a given URL namespace and base.
	 *
	 * @since 1.1.0
	 *
	 * @param string $rest_namespace URL namespace.
	 * @param string $rest_base      URL base.
	 * @return string JSON feed URL for the given URL namespace and base.
	 */
	public function get_feed_url( $rest_namespace, $rest_base ) {
		return rest_url(
			sprintf(
				'%1$s/%2$s',
				trim( $rest_namespace, '/' ),
				trim( $rest_base, '/' )
			)
		);
	}
}
