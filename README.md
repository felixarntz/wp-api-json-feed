[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/wp-api-json-feed.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-api-json-feed/)
[![WordPress](https://img.shields.io/wordpress/v/wp-api-json-feed.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-api-json-feed/)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/wp-api-json-feed/version)](https://packagist.org/packages/felixarntz/wp-api-json-feed)
[![License](https://poser.pugx.org/felixarntz/wp-api-json-feed/license)](https://packagist.org/packages/felixarntz/wp-api-json-feed)

# WP-API JSON Feed

Implements a JSON feed following the official JSON feed specification by means of a REST API endpoint.

## Features

* Adds JSON feeds following the official [version 1.1 spec](https://jsonfeed.org/version/1.1).
* Places a link tag to the posts feed inside the HTML head tag.
* Adds a new namespace `feed/v1` to the REST API.
* Allows adding individual endpoints per post type, simply by specifying an additional argument when registering the post type. By default a feed is only added for regular posts (posts of the `post` post type).
* Uses a proper REST API controller including schema for the endpoints.
* Contains several filters to modify the feed responses as necessary.

## Installation and usage

You can download the latest version from the [WordPress plugin repository](https://wordpress.org/plugins/wp-api-json-feed/).

Please see the [plugin repository instructions](https://wordpress.org/plugins/wp-api-json-feed/#installation) for detailed information on installation and usage.

## Contributions

If you have ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request right here on GitHub. Please refer to the [contributing guidelines](https://github.com/felixarntz/wp-api-json-feed/blob/main/CONTRIBUTING.md) to learn more and get started.

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-api-json-feed) to get started.
