<?php
/**
 * Tests for WP_API_JSON_Feed_URLs
 *
 * @package WPAPIJSONFeed\Tests
 * @author Felix Arntz <hello@felix-arntz.me>
 */

class Tests_WP_API_JSON_Feed_URLs extends WP_UnitTestCase {
	public function test_get_feed_url_for_post_type() {
		$urls = new WP_API_JSON_Feed_URLs();

		$expected = rest_url( 'feed/v1/page' );
		$this->assertSame( $expected, $urls->get_feed_url_for_post_type( get_post_type_object( 'page' ) ) );
	}

	public function test_get_url_namespace() {
		$urls = new WP_API_JSON_Feed_URLs();

		$this->assertSame( 'feed/v1', $urls->get_url_namespace() );
	}

	public function test_get_url_base_for_post_type() {
		$urls = new WP_API_JSON_Feed_URLs();

		register_post_type( 'content', array( 'json_feed_base' => 'custom/content' ) );
		$result   = $urls->get_url_base_for_post_type( get_post_type_object( 'content' ) );
		_unregister_post_type( 'content' );

		$this->assertSame( 'custom/content', $result );
	}

	public function test_get_feed_url() {
		$urls = new WP_API_JSON_Feed_URLs();

		$this->assertSame( rest_url( 'test/v99/something' ), $urls->get_feed_url( 'test/v99', 'something' ) );
	}
}
