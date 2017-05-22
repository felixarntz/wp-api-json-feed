<?php
/**
 * @package WPAPIJSONFeed
 * @subpackage Tests
 */

class Tests_WP_API_JSON_Feed_REST_Controller extends WP_Test_REST_Controller_Testcase {
	public function test_register_routes() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/feed/v1/posts', $routes );
	}

	public function test_get_item_basic() {
		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'version', $data );
		$this->assertArrayHasKey( 'title', $data );
		$this->assertArrayHasKey( 'home_page_url', $data );
		$this->assertArrayHasKey( 'feed_url', $data );
		$this->assertArrayHasKey( 'items', $data );
	}

	public function test_get_item_correct_json_feed_version() {
		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'version', $data );
		$this->assertSame( 'https://jsonfeed.org/version/1', $data['version'] );
	}

	public function test_get_item_count_posts() {
		$this->factory->post->create_many( 4 );

		$counts = wp_count_posts();

		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'items', $data );
		$this->assertSame( (int) $counts['publish'], count( $data['items'] ) );
	}

	public function test_get_item_count_posts_with_limit() {
		$this->factory->post->create_many( 4 );

		add_filter( 'pre_option_posts_per_rss', array( $this, 'filter_posts_per_rss' ) );

		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		remove_filter( 'pre_option_posts_per_rss', array( $this, 'filter_posts_per_rss' ) );

		$this->assertArrayHasKey( 'items', $data );
		$this->assertArrayHasKey( 'next_url', $data );
		$this->assertSame( 3, count( $data['items'] ) );
	}

	public function test_get_item_count_posts_with_limit_last_page() {
		$this->factory->post->create_many( 4 );

		$counts = wp_count_posts();

		$page = ceil( (int) $counts['publish'] / 3 );
		$expected = (int) $counts['publish'] % 3;

		add_filter( 'pre_option_posts_per_rss', array( $this, 'filter_posts_per_rss' ) );

		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$request->set_param( 'page', $page );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		remove_filter( 'pre_option_posts_per_rss', array( $this, 'filter_posts_per_rss' ) );

		$this->assertArrayHasKey( 'items', $data );
		$this->assertSame( $expected, count( $data['items'] ) );
	}

	public function test_get_item_with_invalid_page() {
		$request = new WP_REST_Request( 'GET', '/feed/v1/posts' );
		$request->set_param( 'page', 100 );
		$response = $this->server->dispatch( $request );
		$this->assertErrorResponse( 'rest_feed_invalid_page_number', $response, 400 );
	}

	public function test_get_item_schema() {
		$controller = new WP_API_JSON_Feed_REST_Controller( get_post_type_object( 'post' ) );
		$schema = $controller->get_item_schema();

		$this->assertSame( 'post_feed', $schema['title'] );

		$this->assertEquals( array(
			'version',
			'title',
			'home_page_url',
			'feed_url',
			'description',
			'user_comment',
			'next_url',
			'icon',
			'favicon',
			'author',
			'expired',
			'hubs',
			'items',
		), array_keys( $schema['properties'] ) );

		$this->assertEquals( array(
			'type',
			'url',
		), array_keys( $schema['properties']['hubs']['items']['properties'] ) );

		$this->assertEquals( array(
			'id',
			'url',
			'external_url',
			'title',
			'content_html',
			'content_text',
			'summary',
			'image',
			'banner_image',
			'date_published',
			'date_modified',
			'author',
			'tags',
			'attachments',
		), array_keys( $schema['properties']['items']['items']['properties'] ) );

		$this->assertEquals( array(
			'url',
			'mime_type',
			'title',
			'size_in_bytes',
			'duration_in_seconds',
		), array_keys( $schema['properties']['items']['items']['properties']['attachments']['items']['properties'] ) );
	}

	public function filter_posts_per_rss() {
		return 3;
	}
}
