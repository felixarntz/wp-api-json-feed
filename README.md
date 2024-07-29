[![PHP Unit Testing](https://img.shields.io/github/actions/workflow/status/felixarntz/wp-api-json-feed/php-test.yml?style=for-the-badge&label=PHP%20Unit%20Testing)](https://github.com/felixarntz/wp-api-json-feed/actions/workflows/php-test.yml)
[![Codecov](https://img.shields.io/codecov/c/github/felixarntz/wp-api-json-feed?style=for-the-badge)](https://app.codecov.io/github/felixarntz/wp-api-json-feed)
[![Packagist version](https://img.shields.io/packagist/v/felixarntz/wp-api-json-feed?style=for-the-badge)](https://packagist.org/packages/felixarntz/wp-api-json-feed)
[![Packagist license](https://img.shields.io/packagist/l/felixarntz/wp-api-json-feed?style=for-the-badge)](https://packagist.org/packages/felixarntz/wp-api-json-feed)
[![WordPress plugin version](https://img.shields.io/wordpress/plugin/v/wp-api-json-feed?style=for-the-badge)](https://wordpress.org/plugins/wp-api-json-feed/)
[![WordPress tested version](https://img.shields.io/wordpress/plugin/tested/wp-api-json-feed?style=for-the-badge)](https://wordpress.org/plugins/wp-api-json-feed/)
[![WordPress plugin downloads](https://img.shields.io/wordpress/plugin/dt/wp-api-json-feed?style=for-the-badge)](https://wordpress.org/plugins/wp-api-json-feed/)

# WP-API JSON Feed

Implements JSON feeds following the official JSON feed specification by using the WordPress REST API.

## Features

* Adds JSON feeds following the official [version 1.1 spec](https://jsonfeed.org/version/1.1).
* Adds a JSON feed for posts to the REST API by default (e.g. at `/wp-json/feed/v1/posts`).
* Allows adding JSON feeds for other post types by using a `show_json_feed` argument when registering the post type.
* Places a link tag to the current feed inside the HTML head tag.
* Maintains backward compatibility with the previous JSON feed [version 1 spec](https://www.jsonfeed.org/version/1/).
* Contains extensive filters to modify the feed responses as necessary.

## Installation and usage

You can download the latest version from the [WordPress plugin repository](https://wordpress.org/plugins/wp-api-json-feed/).

Please see the [plugin repository instructions](https://wordpress.org/plugins/wp-api-json-feed/#installation) for detailed information on installation and usage.

## Contributions

If you have ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request right here on GitHub. Please refer to the [contributing guidelines](https://github.com/felixarntz/wp-api-json-feed/blob/main/CONTRIBUTING.md) to learn more and get started.

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-api-json-feed) to get started.
