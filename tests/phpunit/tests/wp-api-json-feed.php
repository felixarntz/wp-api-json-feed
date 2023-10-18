<?php
/**
 * Tests for WP_API_JSON_Feed
 *
 * @package WPAPIJSONFeed\Tests
 * @author Felix Arntz <hello@felix-arntz.me>
 */

class Tests_WP_API_JSON_Feed extends WP_UnitTestCase {
	public function set_up() {
		global $wp_rest_server;

		parent::set_up();

		$wp_rest_server = new Spy_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
	}

	public function tear_down() {
		global $wp_rest_server;

		$wp_rest_server = null;

		parent::tear_down();
	}

	public function test_instance() {
		$this->assertInstanceOf( 'WP_API_JSON_Feed', WP_API_JSON_Feed::instance() );
	}

	public function test_add_hooks() {
		$expected_actions = array(
			'rest_api_init',
			'wp_head',
		);
		foreach ( $expected_actions as $action ) {
			remove_all_actions( $action );
		}

		$expected_filters = array( 'register_post_type_args' );
		foreach ( $expected_filters as $filter ) {
			remove_all_filters( $filter );
		}

		$plugin = new WP_API_JSON_Feed();
		$plugin->add_hooks();

		foreach ( $expected_actions as $action ) {
			$this->assertTrue( has_action( $action ), sprintf( 'Failed asserting that actions for %s were added.', $action ) );
		}
		foreach ( $expected_filters as $filter ) {
			$this->assertTrue( has_filter( $filter ), sprintf( 'Failed asserting that filters for %s were added.', $filter ) );
		}
	}

	public function test_register_rest_routes() {
		register_post_type( 'content', array( 'show_json_feed' => true ) );

		$plugin = new WP_API_JSON_Feed();
		$plugin->register_rest_routes();

		_unregister_post_type( 'content' );

		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/feed/v1/content', $routes );
	}

	public function test_render_current_feed_link_tag() {
		$plugin = new WP_API_JSON_Feed();

		ob_start();
		$plugin->render_current_feed_link_tag();
		$output = ob_get_clean();

		// The expected output should be the same as when calling `render_feed_link_tag()` with parameter 'post'.
		ob_start();
		$plugin->render_feed_link_tag( 'post' );
		$expected = ob_get_clean();

		$this->assertSame( $expected, $output );
	}

	/**
	 * @dataProvider data_get_current_post_type
	 */
	public function test_get_current_post_type( $set_up, $expected ) {
		$plugin = new WP_API_JSON_Feed();

		$set_up();

		$this->assertSame( $expected, $plugin->get_current_post_type() );
	}

	public function data_get_current_post_type() {
		return array(
			'based on global post'       => array(
				static function () {
					$post            = new WP_Post( new stdClass() );
					$post->post_type = 'my_cpt';
					$post->filter    = 'raw'; // Prevent the filter method from unsetting the test data.
					$GLOBALS['post'] = $post;
				},
				'my_cpt',
			),
			'based on queried post type' => array(
				static function () {
					$query                          = new WP_Query();
					$query->is_post_type_archive    = true;
					$query->query_vars['post_type'] = 'page';
					$GLOBALS['wp_query']            = $query;
				},
				'page',
			),
			'based on queried post'      => array(
				static function () {
					$query                  = new WP_Query();
					$query->is_singular     = true;
					$query->post            = new WP_Post( new stdClass() );
					$query->post->post_type = 'product';
					$GLOBALS['wp_query']    = $query;
				},
				'product',
			),
			'based on fallback'          => array(
				static function () {
					$GLOBALS['wp_query'] = new WP_Query();
					unset( $GLOBALS['post'] );
				},
				'post',
			),
		);
	}

	public function test_render_feed_link_tag() {
		register_post_type( 'content', array( 'label' => 'Content', 'show_json_feed' => true ) );

		$plugin = new WP_API_JSON_Feed();

		ob_start();
		$plugin->render_feed_link_tag( 'content' );
		$output = ob_get_clean();

		_unregister_post_type( 'content' );

		$expected = sprintf( '<link rel="alternate" type="application/json" title="%1$s" href="%2$s" />', 'Content JSON Feed', rest_url( 'feed/v1/content' ) );
		$this->assertSame( $expected, trim( $output ) );
	}

	public function test_render_feed_link_tag_for_post_type_without_feed() {
		register_post_type( 'content', array() );

		$plugin = new WP_API_JSON_Feed();

		ob_start();
		$plugin->render_feed_link_tag( 'content' );
		$output = ob_get_clean();

		_unregister_post_type( 'content' );

		$this->assertEmpty( $output );
	}

	public function test_render_feed_link_tag_for_invalid_post_type() {
		$plugin = new WP_API_JSON_Feed();

		ob_start();
		$plugin->render_feed_link_tag( 'content' );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_filter_post_type_args() {
		$plugin = new WP_API_JSON_Feed();

		$expected = array(
			'show_json_feed' => false,
			'json_feed_base' => '',
		);

		$this->assertEqualSets( $expected, $plugin->filter_post_type_args( array(), 'page' ) );
	}

	public function test_filter_post_type_args_for_post() {
		$plugin = new WP_API_JSON_Feed();

		$expected = array(
			'show_json_feed' => true,
			'json_feed_base' => 'posts',
		);

		$this->assertEqualSets( $expected, $plugin->filter_post_type_args( array(), 'post' ) );
	}

	public function test_filter_post_type_args_filled() {
		$plugin = new WP_API_JSON_Feed();

		$expected = array(
			'show_json_feed' => true,
			'json_feed_base' => 'content',
		);

		$this->assertEqualSets( $expected, $plugin->filter_post_type_args( $expected, 'content' ) );
	}
}
