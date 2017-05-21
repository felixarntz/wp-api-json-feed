<?php
/**
 * @package WPAPIJSONFeed
 * @subpackage Tests
 */

class Tests_WP_API_JSON_Feed extends WP_UnitTestCase {
	public function test_instance() {
		$this->assertInstanceOf( 'WP_API_JSON_Feed', WP_API_JSON_Feed::instance() );
	}
}
