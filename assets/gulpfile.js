/**
 * Gulpfile setup
 * @link https://ahmadawais.com/my-advanced-gulp-workflow-for-wordpress-themes/
 */

// Project configuration
var project 		= 'storms-framework',         // Project name, used for build zip.
    plugin_dir		= '../',					  // Plugin base dir
	wp_content		= '../../../';				  // WordPress wp-content/ dir

// Load plugins
var gulp          = require('gulp'),
	pipeline 	  = require('readable-stream').pipeline,
	debug         = require('gulp-debug'),
	notify        = require('gulp-notify'),

	pixrem		  = require('gulp-pixrem'), 	  // Generates pixel fallbacks for rem units
	autoprefixer  = require('gulp-autoprefixer'), // Autoprefixing magic
	plumber       = require('gulp-plumber'),      // Helps prevent stream crashing on errors
	sourcemaps    = require('gulp-sourcemaps'),
	cleanCSS 	  = require('gulp-clean-css'),	  // Used to minify the CSS
	postcss 	  = require('gulp-postcss'),
	stripcomments = require('gulp-strip-css-comments'),
	filter        = require('gulp-filter'),
	rename        = require('gulp-rename'),
	sass          = require('gulp-sass')(require('sass')),

	concat        = require('gulp-concat'),
	babel 		  = require('gulp-babel'),
	uglify        = require('gulp-terser'),

	imagemin      = require('gulp-imagemin'),
	newer         = require('gulp-newer'),		  // Helps to pass through newer files only
	rimraf        = require('gulp-rimraf'),       // Helps with removing files and directories in our run tasks
	zip           = require('gulp-zip');          // Using to zip up our packaged theme into a tasty zip file that can be installed in WordPress!

// Datestamp for cache busting
var getStamp = function() {
	var myDate = new Date();

	var myYear = myDate.getFullYear().toString();
	var myMonth = ('0' + (myDate.getMonth() + 1)).slice(-2);
	var myDay = ('0' + myDate.getDate()).slice(-2);
	var mySeconds = myDate.getSeconds().toString();

	return myYear + myMonth + myDay;
};

/**
 * Styles
 * Looking at src/sass and compiling the files into Expanded format, Autoprefixing and sending the files to the build folder
 * Sass output styles: https://web-design-weekly.com/2014/06/15/different-sass-output-styles/
 */
gulp.task('styles', async function () {
	gulp.src( './sass/*.scss' )
	//.pipe( debug() )			 // Debug Vinyl file streams to see what files are run through your Gulp pipeline
	.pipe( plumber() )			 // error tracking
	.pipe( sourcemaps.init() )
	.pipe( sass( {
		errLogToConsole: true,
		outputStyle: 'expanded', // 'compressed', 'compact', 'nested', 'expanded'
		precision: 10
	} ) )
	.pipe( pixrem() )
	.pipe( autoprefixer( 'last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4' ) )
	.pipe( stripcomments({ preserve : /^# sourceMappingURL=/ } ) ) // Strip comments from CSS - except for sourceMappingUrl
	.pipe(postcss([require('postcss-merge-rules')]))
	.pipe( sourcemaps.write( './maps' ) )
	.pipe( gulp.dest( './css/' ) )

	.pipe( filter( '**/*.css' ) )       // Filtering stream to only css files
	//.pipe( debug() )							// Debug Vinyl file streams to see what files are run through your Gulp pipeline
	.pipe( sourcemaps.init() )
	.pipe( rename({ suffix: '.min' } ) )
	.pipe( cleanCSS() )
	.pipe( sourcemaps.write( './maps' ) )
	.pipe( gulp.dest( './css/' ) )
	.pipe( notify( { message: 'Styles task complete', onLast: true } ) )
});

/**
 * Load assets
 * Copy the fonts, js and styles from used libs to the correct public place
 */
gulp.task('load-assets', async function() {

	gulp.src([
		'node_modules/jquery/dist/jquery.min.js'					// jQuery
	])
	.pipe(gulp.dest('js/jquery'))
	.pipe(notify({ message: 'Load jQuery scripts task complete', onLast: true }));
});

/**
 * Scripts
 * Look at /js/src files and concatenate those files, send them to /js where we then minimize the concatenated file.
 */
function scripts_source() {
	return pipeline(
		gulp.src( [
			'./js/src/**/*.js' // All our custom scripts
		] ),
		babel({
			presets: ['@babel/env']
		}),
		//debug(),
		gulp.dest( './js/' ),
		sourcemaps.init(),
		rename({ suffix: '.min' } ),
		uglify(),
		sourcemaps.write( './maps' ),
		gulp.dest( './js/' ),
		notify( { message: 'Source scripts task complete', onLast: true } )
	);
}
gulp.task('scripts', gulp.parallel(scripts_source));

/**
 * Images
 * Look at /img/raw, optimize the images and send them to /img
 */
gulp.task('images', async function() {
	gulp.src([
		'./img/raw/**/*.{png,jpg,gif,svg}'
	])
	//.pipe( debug() )
	.pipe(newer('./img/'))
	.pipe(rimraf({ force: true }))
	.pipe(imagemin({ optimizationLevel: 7, progressive: true, interlaced: true }))
	.pipe(gulp.dest('./img/'))
	.pipe( notify( { message: 'Images task complete', onLast: true } ) );
});

/**
 * Zip
 * Zip all theme for deploy
 */
gulp.task('zip', async function() {
	gulp.src([
		plugin_dir + '**/*',
		plugin_dir + '*.zip',
		'!' + plugin_dir + 'assets/node_modules',
		'!' + plugin_dir + 'assets/node_modules/**/*'
	])
	.pipe( zip(getStamp() + '-' + project + '.zip' ) )
	.pipe( gulp.dest( plugin_dir ) )
	.pipe( notify( { message: 'Zip task complete', onLast: true } ) );
});

// ==== TASKS ==== //

/**
 * Gulp Default Task
 * Compiles styles, fires-up browser sync, watches js and php files. Note browser sync task watches php files
 */

// Default Task
gulp.task('default', gulp.series(['load-assets', 'styles', 'scripts', 'images']));

// Watch Task
gulp.task('watch', gulp.series(['styles', 'scripts', 'images'], function () {
	gulp.watch('./sass/*.scss', gulp.series('styles'));
	gulp.watch('./js/src/**/*.js', gulp.series('scripts'));
	gulp.watch('./img/raw/**/*.{png,jpg,gif,svg}', gulp.series('images'));
}));
