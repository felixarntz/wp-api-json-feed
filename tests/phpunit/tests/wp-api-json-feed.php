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

	public function test_register_rest_routes() {
		register_post_type( 'content', array( 'show_json_feed' => true ) );

		$plugin = new WP_API_JSON_Feed();
		$plugin->register_rest_routes();

		_unregister_post_type( 'content' );

		$routes = rest_get_server()->get_routes();
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
}
