<?php
/**
 * @package WPAPIJSONFeed
 * @subpackage Tests
 */

class Tests_WP_API_JSON_Feed extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$GLOBALS['wp_rest_server'] = null;

		add_filter( 'wp_rest_server_class', array( $this, 'filter_wp_rest_server_class' ) );

		$this->server = rest_get_server();

		remove_filter( 'wp_rest_server_class', array( $this, 'filter_wp_rest_server_class' ) );
	}

	public function tearDown() {
		$GLOBALS['wp_rest_server'] = null;

		parent::tearDown();
	}

	public function test_instance() {
		$this->assertInstanceOf( 'WP_API_JSON_Feed', WP_API_JSON_Feed::instance() );
	}

	public function test_register_rest_routes() {
		register_post_type( 'content', array( 'show_json_feed' => true ) );

		$plugin = new WP_API_JSON_Feed();
		$plugin->register_rest_routes();

		_unregister_post_type( 'content' );

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/feed/v1/content', $routes );
	}

	public function test_render_feed_link_tag() {
		register_post_type( 'content', array( 'label' => 'Content', 'show_json_feed' => true ) );

		ob_start();

		$plugin = new WP_API_JSON_Feed();
		$plugin->render_feed_link_tag( 'content' );

		$output = ob_get_clean();

		_unregister_post_type( 'content' );

		$expected = sprintf( '<link rel="alternate" type="application/json" title="%1$s" href="%2$s" />', 'Content JSON Feed', rest_url( 'feed/v1/content' ) );
		$this->assertSame( $expected, trim( $output ) );
	}

	public function test_render_feed_link_tag_for_post_type_without_feed() {
		register_post_type( 'content', array() );

		ob_start();

		$plugin = new WP_API_JSON_Feed();
		$plugin->render_feed_link_tag( 'content' );

		$output = ob_get_clean();

		_unregister_post_type( 'content' );

		$this->assertEmpty( $output );
	}

	public function test_render_feed_link_tag_for_invalid_post_type() {
		ob_start();

		$plugin = new WP_API_JSON_Feed();
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

	public function filter_wp_rest_server_class() {
		return 'Spy_REST_Server';
	}
}
