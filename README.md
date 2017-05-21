[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/wp-api-json-feed.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-api-json-feed/)
[![WordPress](https://img.shields.io/wordpress/v/wp-api-json-feed.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-api-json-feed/)
[![Build Status](https://api.travis-ci.org/felixarntz/wp-api-json-feed.png?branch=master)](https://travis-ci.org/felixarntz/wp-api-json-feed)
[![Code Climate](https://codeclimate.com/github/felixarntz/wp-api-json-feed/badges/gpa.svg)](https://codeclimate.com/github/felixarntz/wp-api-json-feed)
[![Test Coverage](https://codeclimate.com/github/felixarntz/wp-api-json-feed/badges/coverage.svg)](https://codeclimate.com/github/felixarntz/wp-api-json-feed/coverage)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/wp-api-json-feed/version)](https://packagist.org/packages/felixarntz/wp-api-json-feed)
[![License](https://poser.pugx.org/felixarntz/wp-api-json-feed/license)](https://packagist.org/packages/felixarntz/wp-api-json-feed)

# WP-API JSON Feed

Implements a JSON feed following the version 1 spec by means of a REST API endpoint.

## Features

* Adds JSON feeds following the official [version 1 spec](https://jsonfeed.org/version/1).
* Adds a new namespace `feed/v1` to the REST API.
* Allows adding individual endpoints per post type, simply by specifying an additional argument when registering the post type. By default a feed is only added for regular posts (posts of the `post` post type).
* Uses a proper REST API controller including schema for the endpoints.
* Contains several filters to modify the feed responses as necessary.

## Installation and Setup

You can download the latest version from the [WordPress plugin repository](http://wordpress.org/plugins/wp-api-json-feed/) or directly from your WordPress backend.

Once the plugin is activated, it will work out of the box and provide a JSON feed for posts. If you want to provide JSON feeds for further post types, you need to specify an additional argument `show_json_feed` when registering the post type, and set it to a boolean `true`. You may also specify a `json_feed_base` argument being a string identifier that should be used in the feed URL. If no string identifier is provided, the post type slug will be used.

## Contributions and Bugs

If you have ideas on how to improve the plugin or if you discover a bug, I would appreciate if you shared them with me, right here on Github. In either case, please open a new issue [here](https://github.com/felixarntz/wp-api-json-feed/issues/new)!

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-api-json-feed) to get started.
