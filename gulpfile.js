const gulp = require( 'gulp' );
const grename = require( 'gulp-rename' );
const gzip = require( 'gulp-zip' );
const packageJson = require( './package.json' );

async function zip( snapshot = false ) {
	let suffix;
	if (snapshot) {
		suffix = await new Promise( ( resolve, reject ) => {
			const { exec } = require( 'child_process' );
			exec( 'git rev-parse --abbrev-ref HEAD', ( err, stdout, stderr ) => {
				if (err) {
					reject( err );
				}
				resolve( stdout.trim() );
			} );
		} );
		suffix = suffix.replace( /[\\~#%&*{}/:<>?|\"]/g, '' ) + '-SNAPSHOT';
	} else {
		suffix = 'v' + packageJson.version;
	}
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
		.pipe( gzip( `gb-fullcalendar-${ suffix }.zip` ) )
		.pipe( gulp.dest( 'dist' ) );
}

exports.zip = () => zip( false );
exports.zipSnapshot = () => zip( true );
