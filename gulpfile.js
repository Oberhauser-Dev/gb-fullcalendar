const gulp = require( 'gulp' );
const grename = require( 'gulp-rename' );
const gzip = require( 'gulp-zip' );
const packageJson = require( './package.json' );

function zip() {
	return gulp.src( [
		'build/*',
		'php/*',
		'res/*',
		'gb-fullcalendar.php',
		'package.json',
		'README.md',
		'CHANGELOG.md',
		'LICENSE.md',
	], { base: './' } )
		.pipe( grename( function( file ) {
			file.dirname = 'gb-fullcalendar/' + file.dirname;
		} ) )
		.pipe( gzip( `gb-fullcalendar-v${ packageJson.version }.zip` ) )
		.pipe( gulp.dest( 'dist' ) );
}

exports.zip = zip;
