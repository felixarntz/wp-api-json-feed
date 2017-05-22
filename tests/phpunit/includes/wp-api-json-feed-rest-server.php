<?php
/**
 * @package WPAPIJSONFeed
 * @subpackage Tests
 */

class WP_API_JSON_Feed_REST_Server extends Spy_REST_Server {
	public function is_registered_endpoint( $namespace, $route ) {
		return ! empty( $this->namespaces[ $namespace ][ $route ] );
	}
}
