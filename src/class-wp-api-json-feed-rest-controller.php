<?php
/**
 * WP_API_JSON_Feed_REST_Controller class
 *
 * @package WPAPIJSONFeed
 * @author Felix Arntz <hello@felix-arntz.me>
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to access post type feeds via the REST API.
 *
 * @since 1.0.0
 *
 * @see WP_REST_Controller
 */
class WP_API_JSON_Feed_REST_Controller extends WP_REST_Controller {

	/**
	 * Post type object.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WP_Post_Type
	 */
	protected $post_type;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;
		$this->namespace = 'feed/v1';
		$this->rest_base = ! empty( $this->post_type->json_feed_base ) ? $this->post_type->json_feed_base : $this->post_type->name;
	}

	/**
	 * Registers the route for the feed of the controller.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'page'                   => array(
						'description'        => __( 'Current page of the feed.', 'wp-api-json-feed' ),
						'type'               => 'integer',
						'default'            => 1,
						'sanitize_callback'  => 'absint',
						'validate_callback'  => 'rest_validate_request_arg',
						'minimum'            => 1,
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given request has access to read the feed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Retrieves the feed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$query_args = array(
			'posts_per_page' => get_option( 'posts_per_rss', 10 ),
			'post_type'      => $this->post_type->name,
			'post_status'    => 'publish',
		);

		if ( isset( $request['page'] ) ) {
			$query_args['paged'] = $request['page'];
		}

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$page = ! empty( $query_args['paged'] ) ? (int) $query_args['paged'] : 1;
		$total_posts = $posts_query->found_posts;

		if ( ! empty( $query_args['paged'] ) && $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error( 'rest_feed_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'wp-api-json-feed' ), array( 'status' => 400 ) );
		}

		$feed = array(
			'version'       => 'https://jsonfeed.org/version/1',
			'home_page_url' => get_post_type_archive_link( $this->post_type->name ),
			'feed_url'      => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			'items'         => $query_result,
		);

		if ( ! $feed['home_page_url'] ) {
			$feed['home_page_url'] = get_home_url();
		}

		if ( 'post' === $this->post_type->name ) {
			$feed_title = get_bloginfo( 'name' );
		} else {
			/* translators: 1: site title, 2: post type plural label */
			$feed_title = sprintf( _x( '%1$s: %2$s', 'feed title', 'wp-api-json-feed' ), get_bloginfo( 'name' ), $this->post_type->labels->name );
		}

		/**
		 * Filters the feed title for the JSON feed of a specific post type.
		 *
		 * The dynamic part of the filter `$this->post_type->name` refers to the post type slug for the feed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $feed_title Feed title.
		 */
		$feed_title = apply_filters( "wp_api_json_feed_title_{$this->post_type->name}", $feed_title );

		/**
		 * Filters the feed title for a JSON feed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $feed_title     Feed title.
		 * @param string $post_type_slug Post type slug for the feed.
		 */
		$feed_title = apply_filters( 'wp_api_json_feed_title', $feed_title, $this->post_type->name );

		// Make sure the feed title is never empty.
		if ( empty( $feed_title ) ) {
			$feed_title = get_bloginfo( 'name' );
		}

		if ( 'post' === $this->post_type->name ) {
			$feed_description = get_bloginfo( 'description' );
		} else {
			$feed_description = $this->post_type->description;
		}

		/**
		 * Filters the feed description for the JSON feed of a specific post type.
		 *
		 * The dynamic part of the filter `$this->post_type->name` refers to the post type slug for the feed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $feed_description Feed description.
		 */
		$feed_description = apply_filters( "wp_api_json_feed_description_{$this->post_type->name}", $feed_description );

		/**
		 * Filters the feed description for a JSON feed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $feed_description Feed description.
		 * @param string $post_type_slug   Post type slug for the feed.
		 */
		$feed_description = apply_filters( 'wp_api_json_feed_description', $feed_description, $this->post_type->name );

		$feed['title'] = $feed_title;
		if ( ! empty( $feed_description ) ) {
			$feed['description'] = $feed_description;
		}

		$show_feed_author = false;
		if ( 'post' === $this->post_type->name && ! is_multi_author() ) {
			$show_feed_author = true;
		}

		/**
		 * Filters whether to show the author for the entire feed.
		 *
		 * If enabled, the user with the admin email address will be displayed.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $show_feed_author Whether to show the feed author. Default is true on a single author blog,
		 *                                 false for a multi-author blog.
		 * @param string $post_type_slug   Post type slug for the feed.
		 */
		$show_feed_author = apply_filters( 'wp_api_json_feed_show_feed_author', $show_feed_author, $this->post_type->name );

		if ( $show_feed_author ) {
			$feed_user = get_user_by( 'email', get_option( 'admin_email' ) );
			if ( $feed_user && $feed_user->exists() ) {
				$feed['author'] = $feed_user;
			}
		}

		if ( has_site_icon() ) {
			$feed_icon = get_site_icon_url( 512 );
			if ( ! empty( $feed_icon ) ) {
				$feed['icon'] = $feed_icon;
			}

			$feed_favicon = get_site_icon_url( 64 );
			if ( ! empty( $feed_favicon ) ) {
				$feed['favicon'] = $feed_favicon;
			}
		}

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$feed['prev_url'] = add_query_arg( 'page', $prev_page, $feed['feed_url'] );
		}

		if ( $max_pages > $page ) {
			$next_page = $page + 1;

			$feed['next_url'] = add_query_arg( 'page', $next_page, $feed['feed_url'] );
		}

		/**
		 * Filters whether the feed should be displayed as expired.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $is_feed_expired Whether the feed has expired. Default false.
		 * @param string $post_type_slug  Post type slug for the feed.
		 */
		$is_feed_expired = apply_filters( 'wp_api_json_feed_is_expired', false, $this->post_type->name );

		if ( $is_feed_expired ) {
			$feed['expired'] = true;
		}

		$data = $this->prepare_item_for_response( $feed, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepares a feed output for response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array           $feed    Feed data.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $feed, $request ) {
		$schema = $this->get_item_schema();

		$data = array();

		foreach ( array_keys( $schema['properties'] ) as $property ) {
			if ( 'items' === $property ) {
				$data['items'] = array();
				continue;
			}

			if ( empty( $feed[ $property ] ) ) {
				continue;
			}

			if ( 'author' === $property ) {
				$data['author'] = $this->get_author_data( $feed['author'] );
				continue;
			}

			$data[ $property ] = $feed[ $property ];
		}

		if ( isset( $data['items'] ) && isset( $feed['items'] ) ) {
			foreach ( $feed['items'] as $post ) {
				$data['items'][] = $this->prepare_post_for_feed( $post, $schema['properties']['items']['items'], $request );
			}
		}

		/**
		 * Filters the prepared data for the JSON feed of a specific post type.
		 *
		 * The dynamic part of the filter `$this->post_type->name` refers to the post type slug for the feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $data    Prepared feed data.
		 * @param array           $feed    Raw feed data.
		 * @param array           $schema  Schema data for a feed.
		 * @param WP_REST_Request $request Request object.
		 */
		$data = apply_filters( "wp_api_json_feed_post_data_{$this->post_type->name}", $data, $feed, $schema, $request );

		/**
		 * Filters the prepared data for a JSON feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $data           Prepared feed data.
		 * @param array           $feed           Raw feed data.
		 * @param array           $schema         Schema data for a feed.
		 * @param WP_REST_Request $request        Request object.
		 * @param string          $post_type_slug Post type slug for the feed.
		 */
		$data = apply_filters( 'wp_api_json_feed_post_data', $data, $feed, $schema, $request, $this->post_type->name );


		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves the feed's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => "{$this->post_type->name}_feed",
			'type'       => 'object',
			'properties' => array(
				'version'       => array(
					'description' => __( 'URL of the version of the format the feed uses.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
					'arg_options' => array(
						'required' => true,
					),
				),
				'title'         => array(
					'description' => __( 'Name of the feed.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'arg_options' => array(
						'required' => true,
					),
				),
				'home_page_url' => array(
					'description' => __( 'URL of the resource that the feed describes.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
				),
				'feed_url'      => array(
					'description' => __( 'URL of the feed.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
				),
				'description'   => array(
					'description' => __( 'Provides more detail on what the feed is about.', 'wp-api-json-feed' ),
					'type'        => 'string',
				),
				'user_comment'  => array(
					'description' => __( 'Description of the purpose of the feed.', 'wp-api-json-feed' ),
					'type'        => 'string',
				),
				'next_url'      => array(
					'description' => __( 'URL of a feed that provides the next n items, where n is determined by the publisher.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
				),
				'icon'          => array(
					'description' => __( 'URL of an image for the feed suitable to be used in a timeline.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
				),
				'favicon'       => array(
					'description' => __( 'URL of an image for the feed suitable to be used in a source list.', 'wp-api-json-feed' ),
					'type'        => 'string',
					'format'      => 'uri',
				),
				'author'        => array(
					'description' => __( 'The feed author.', 'wp-api-json-feed' ),
					'type'        => 'object',
					'properties'  => $this->get_author_schema_properties(),
				),
				'expired'       => array(
					'description' => __( 'Whether or not the feed is finished.', 'wp-api-json-feed' ),
					'type'        => 'boolean',
				),
				'hubs'          => array(
					'description' => __( 'Endpoints that can be used to subscribe to real-time notifications from the publisher of this feed.', 'wp-api-json-feed' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => $this->get_hub_schema_properties(),
					),
				),
				'items'         => array(
					'description' => __( 'The items of the feed.', 'wp-api-json-feed' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => $this->get_item_schema_properties(),
					),
				),
			),
		);

		/**
		 * Filters the schema for the JSON feed of a specific post type.
		 *
		 * The dynamic part of the filter `$this->post_type->name` refers to the post type slug for the feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array $schema Feed schema.
		 */
		$schema = apply_filters( "wp_api_json_feed_schema_{$this->post_type->name}", $schema );

		/**
		 * Filters the schema for a JSON feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $schema         Feed schema.
		 * @param string $post_type_slug Post type slug for the feed.
		 */
		$schema = apply_filters( 'wp_api_json_feed_schema', $schema, $this->post_type->name );

		return $schema;
	}

	/**
	 * Retrieves the properties for a feed hub's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Hub schema property data.
	 */
	protected function get_hub_schema_properties() {
		return array(
			'type' => array(
				'description' => __( 'Endpoint type.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'url'  => array(
				'description' => __( 'Endpoint URL.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
		);
	}

	/**
	 * Retrieves the properties for a feed item's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Item schema property data.
	 */
	protected function get_item_schema_properties() {
		return array(
			'id'           => array(
				'description' => __( 'Unique identifier for that item for that feed over time. ', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'url'          => array(
				'description' => __( 'URL of the resource described by the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
			'external_url' => array(
				'description' => __( 'URL of a referenced page elsewhere.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
			'title'        => array(
				'description' => __( 'Plain text title of the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'content_html' => array(
				'description' => __( 'HTML content of the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'content_text' => array(
				'description' => __( 'Plain text content of the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'summary'      => array(
				'description' => __( 'A plain text sentence or two describing the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'image'        => array(
				'description' => __( 'URL of the main image for the item.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
			'banner_image'  => array(
				'description' => __( 'URL of an image to use as a banner.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
			'date_published' => array(
				'description' => __( 'The date in RFC 3339 format.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'date-time',
			),
			'date_modified'  => array(
				'description' => __( 'The modification date in RFC 3339 format.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'date-time',
			),
			'author'        => array(
				'description' => __( 'Author of the item.', 'wp-api-json-feed' ),
				'type'        => 'object',
				'properties'  => $this->get_author_schema_properties(),
			),
			'tags'           => array(
				'description' => __( 'Plain text values the item is tagged with.', 'wp-api-json-feed' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
				),
			),
			'attachments'    => array(
				'description' => __( 'Related resources for the item.', 'wp-api-json-feed' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => $this->get_attachment_schema_properties(),
				),
			),
		);
	}

	/**
	 * Retrieves the properties for a feed item attachment's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Item attachment schema property data.
	 */
	protected function get_attachment_schema_properties() {
		return array(
			'url'                 => array(
				'description' => __( 'The location of the attachment.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'mime_type'           => array(
				'description' => __( 'The MIME type of the attachment.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'title'               => array(
				'description' => __( 'Name for the attachment. ', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'size_in_bytes'       => array(
				'description' => __( 'Size of how large the file is.', 'wp-api-json-feed' ),
				'type'        => 'integer',
			),
			'duration_in_seconds' => array(
				'description' => __( 'Duration of how long the attachment takes to listen to or watch.', 'wp-api-json-feed' ),
				'type'        => 'integer',
			),
		);
	}

	/**
	 * Retrieves the properties for an author's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Author schema property data.
	 */
	protected function get_author_schema_properties() {
		return array(
			'name'   => array(
				'description' => __( 'Name of the author.', 'wp-api-json-feed' ),
				'type'        => 'string',
			),
			'url'    => array(
				'description' => __( 'Website URL of a site the author owns.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
			'avatar' => array(
				'description' => __( 'URL of the image avatar of the author.', 'wp-api-json-feed' ),
				'type'        => 'string',
				'format'      => 'uri',
			),
		);
	}

	/**
	 * Prepares a post output for a feed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Post         $post    Post object.
	 * @param array           $schema  Schema data for a post in a feed.
	 * @param WP_REST_Request $request Request object.
	 * @return array Post data for a feed.
	 */
	protected function prepare_post_for_feed( $post, $schema, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$post_data = array(
			'id' => get_the_guid(),
			/** This filter is documented in wp-includes/feed.php */
			'url' => apply_filters( 'the_permalink_rss', get_permalink() ),
			'title' => get_the_title_rss(),
		);

		if ( get_option( 'rss_use_excerpt' ) ) {
			/** This filter is documented in wp-includes/feed.php */
			$post_data['content_text'] = apply_filters( 'the_excerpt_rss', get_the_excerpt() );
		} else {
			$post_data['content_html'] = get_the_content_feed( 'json' );
		}

		if ( post_type_supports( $post->post_type, 'thumbnail' ) && has_post_thumbnail() ) {
			$post_data['image'] = get_the_post_thumbnail_url();
		}

		$post_data['date_published'] = get_the_date( 'c' );
		$post_data['date_modified']  = get_the_modified_date( 'c' );

		if ( post_type_supports( $post->post_type, 'author' ) && ! empty( $post->post_author ) ) {
			$post_author = get_userdata( $post->post_author );
			if ( $post_author && $post_author->exists() ) {
				$post_data['author'] = $this->get_author_data( $post_author );
			}
		}

		$taxonomies = get_object_taxonomies( $post, 'names' );
		if ( in_array( 'post_tag', $taxonomies, true ) ) {
			$post_data['tags'] = array();

			$terms = get_the_terms( $post, 'post_tag' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$post_data['tags'][] = $term->name;
				}
			}
		}

		/**
		 * Filters the prepared post data for the JSON feed of a specific post type.
		 *
		 * The dynamic part of the filter `$this->post_type->name` refers to the post type slug for the feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $post_data Prepared post data.
		 * @param WP_Post         $post      Post object.
		 * @param array           $schema    Schema data for a post in a feed.
		 * @param WP_REST_Request $request   Request object.
		 */
		$post_data = apply_filters( "wp_api_json_feed_post_data_{$this->post_type->name}", $post_data, $post, $schema, $request );

		/**
		 * Filters the prepared post data for a JSON feed.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $post_data      Prepared post data.
		 * @param WP_Post         $post           Post object.
		 * @param array           $schema         Schema data for a post in a feed.
		 * @param WP_REST_Request $request        Request object.
		 * @param string          $post_type_slug Post type slug for the feed.
		 */
		$post_data = apply_filters( 'wp_api_json_feed_post_data', $post_data, $post, $schema, $request, $this->post_type->name );

		return $post_data;
	}

	/**
	 * Gets an author data array for a specific user to show in the JSON feed.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param WP_User $user User object.
	 * @return array Associative array of feed author data.
	 */
	protected function get_author_data( $user ) {
		$author_data = array( 'name' => $user->display_name );

		if ( ! empty( $user->user_url ) ) {
			$author_data['url'] = $user->user_url;
		} else {
			$twitter_handle = get_user_meta( $user->ID, 'twitter', true );
			if ( ! empty( $twitter_handle ) ) {
				if ( 0 === strpos( $twitter_handle, '@' ) ) {
					$twitter_handle = substr( $twitter_handle, 1 );
				}

				$author_data['url'] = 'https://twitter.com/' . $twitter_handle;
			}
		}

		$avatar_url = get_avatar_url( $user->user_email, array( 'size' => 512 ) );
		if ( ! empty( $avatar_url ) ) {
			$author_data['avatar'] = $avatar_url;
		}

		return $author_data;
	}
}
