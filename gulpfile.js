const gulp = require( 'gulp' );
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
		.pipe( gzip( `gb-fullcalendar-v${ packageJson.version }.zip` ) )
		.pipe( gulp.dest( 'dist' ) );
}

exports.zip = zip;
