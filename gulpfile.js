/* ---- THE FOLLOWING CONFIG SHOULD BE EDITED ---- */

var pkg = require( './package.json' );

function parseKeywords( keywords ) {
	// These keywords are useful for Packagist/NPM/Bower, but not for the WordPress plugin repository.
	var disallowed = [ 'wordpress', 'plugin' ];

	k = keywords;
	for ( var i in disallowed ) {
		var index = k.indexOf( disallowed[ i ] );
		if ( -1 < index ) {
			k.splice( index, 1 );
		}
	}

	return k;
}

var keywords = parseKeywords( pkg.keywords );

var config = {
	pluginSlug: pkg.name,
	pluginName: 'WP-API JSON Feed',
	pluginURI: pkg.homepage,
	author: pkg.author.name,
	authorURI: pkg.author.url,
	authorEmail: pkg.author.email,
	description: pkg.description,
	version: pkg.version,
	license: 'GNU General Public License v3 (or later)',
	licenseURI: 'https://www.gnu.org/licenses/gpl-3.0.html',
	tags: keywords.join( ', ' ),
	contributors: [ 'flixos90' ].join( ', ' ),
	donateLink: 'https://leaves-and-love.net/wordpress-plugins/',
	minRequired: '4.7',
	testedUpTo: '4.8',
	translateURI: 'https://translate.wordpress.org/projects/wp-plugins/' + pkg.name,
	network: false
};

/* ---- DO NOT EDIT BELOW THIS LINE ---- */

// WP plugin header for main plugin file
var pluginheader = 	'Plugin Name: ' + config.pluginName + '\n' +
					'Plugin URI:  ' + config.pluginURI + '\n' +
					'Description: ' + config.description + '\n' +
					'Version:     ' + config.version + '\n' +
					'Author:      ' + config.author + '\n' +
					'Author URI:  ' + config.authorURI + '\n' +
					'License:     ' + config.license + '\n' +
					'License URI: ' + config.licenseURI + '\n' +
					'Text Domain: ' + config.pluginSlug + '\n' +
					( config.network ? 'Network:     true' + '\n' : '' ) +
					'Tags:        ' + config.tags;

// WP plugin header for readme.txt
var readmeheader =	'Plugin Name:       ' + config.pluginName + '\n' +
					'Plugin URI:        ' + config.pluginURI + '\n' +
					'Author:            ' + config.author + '\n' +
					'Author URI:        ' + config.authorURI + '\n' +
					'Contributors:      ' + config.contributors + '\n' +
					( config.donateLink ? 'Donate link:       ' + config.donateLink + '\n' : '' ) +
					'Requires at least: ' + config.minRequired + '\n' +
					'Tested up to:      ' + config.testedUpTo + '\n' +
					'Stable tag:        ' + config.version + '\n' +
					'Version:           ' + config.version + '\n' +
					'License:           ' + config.license + '\n' +
					'License URI:       ' + config.licenseURI + '\n' +
					'Tags:              ' + config.tags;

// header for minified assets
var assetheader =	'/*!\n' +
					' * ' + config.pluginName + ' (' + config.pluginURI + ')\n' +
					' * By ' + config.author + ' (' + config.authorURI + ')\n' +
					' * Licensed under ' + config.license + ' (' + config.licenseURI + ')\n' +
					' */\n';


/* ---- REQUIRED DEPENDENCIES ---- */

var gulp = require( 'gulp' );
var replace = require( 'gulp-replace' );

var paths = {
	php: {
		files: [ './*.php', './src/**/*.php' ]
	}
};

/* ---- MAIN TASKS ---- */

// general task
gulp.task( 'default', [ 'build' ]);

// build the plugin
gulp.task( 'build', [ 'author-replace', 'readme-replace' ], function() {
	gulp.start( 'header-replace' );
});

/* ---- SUB TASKS ---- */

// replace the author header in all PHP files
gulp.task( 'author-replace', function( done ) {
	gulp.src( paths.php.files, { base: './' })
		.pipe( replace( /\*\s@author\s[^\r\n]+/, '* @author ' + config.author + ' <' + config.authorEmail + '>' ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});

// replace the plugin header in the main plugin file
gulp.task( 'header-replace', function( done ) {
	gulp.src( './' + config.pluginSlug + '.php' )
		.pipe( replace( /((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/, '/*\n' + pluginheader + '\n*/' ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});

// replace the plugin header in readme.txt
gulp.task( 'readme-replace', function( done ) {
	gulp.src( './readme.txt' )
		.pipe( replace( /\=\=\= (.+) \=\=\=([\s\S]+)\=\= Description \=\=/m, '=== ' + config.pluginName + ' ===\n\n' + readmeheader + '\n\n' + config.description + '\n\n== Description ==' ) )
		.pipe( gulp.dest( './' ) )
		.on( 'end', done );
});
