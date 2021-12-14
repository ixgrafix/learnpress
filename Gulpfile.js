/**
 * Gulp file by nhamdv.
 */
const gulp = require( 'gulp' );
const cache = require( 'gulp-cache' );
const lineec = require( 'gulp-line-ending-corrector' );
const notify = require( 'gulp-notify' );
const rename = require( 'gulp-rename' );
const sass = require( 'gulp-sass' )( require( 'sass' ) );
const replace = require( 'gulp-replace' );
const uglify = require( 'gulp-uglify-es' ).default;
const zip = require( 'gulp-vinyl-zip' );
const plumber = require( 'gulp-plumber' );
const sourcemaps = require( 'gulp-sourcemaps' );
const uglifycss = require( 'gulp-uglifycss' );
const del = require( 'del' );
const readFile = require( 'read-file' );
const wpPot = require( 'gulp-wp-pot' );

let currentVer = null;

const getCurrentVer = function( force ) {
	if ( currentVer === null || force === true ) {
		const current = readFile.sync( 'learnpress.php', { encoding: 'utf8' } ).match( /Version:\s*(.*)/ );
		currentVer = current ? current[ 1 ] : null;
	}

	return currentVer;
};

const releasesFiles = [
	'./**',
	'assets/src/**',
	'!assets/src/scss/**',
	'!assets/src/app/**',
	'!assets/**/*.js.map',
	'!assets/**/*.dev.js',
	'!assets/**/*bak*',
	'!assets/**/*bk*',
	'!assets/**/*.asset.php',
	'!assets/**/*.deps.json',
	'!releases/**',
	'!tests/**',
	'!tools/**',
	'!node_modules/**',
	'!vendor/**',
	'!*.json',
	'!*.js',
	'!*.map',
	'!*.xml',
	'!*.sublime-project',
	'!*.sublime-workspace',
	'!*.log',
	'!*.DS_Store',
	'!*.gitignore',
	'!TODO',
	'!*.git',
	'!*.ftppass',
	'!*.DS_Store',
	'!sftp.json',
	'!composer.lock',
	'!*.md',
	'!package.lock',
	'!*.dist',
	'!*.xml',
	'!editorconfig',
	'!.travis.yml',
	'!.babelrc',
];

// Clear cache.
gulp.task( 'clearCache', ( done ) => {
	return cache.clearAll( done );
} );

// Clean folder to releases.
gulp.task( 'cleanReleases', () => {
	return del( './releases/**' );
} );

// Copy folder to releases.
gulp.task( 'copyReleases', () => {
	return gulp.src( releasesFiles ).pipe( gulp.dest( './releases/learnpress/' ) );
} );

// Update file Readme
gulp.task( 'updateReadme', () => {
	return gulp.src( [ 'readme.txt' ] )
		.pipe( replace( /Stable tag: (.*)/g, 'Stable tag: ' + getCurrentVer( true ) ) )
		.pipe( gulp.dest( './releases/learnpress/', { overwrite: true } ) );
} );

// Zip learnpress in releases.
gulp.task( 'zipReleases', () => {
	const version = getCurrentVer();

	return gulp
		.src( './releases/learnpress/**', { base: './releases/' } )
		.pipe( zip.dest( './releases/learnpress.' + version + '.zip' ) );
} );

// Notice.
gulp.task( 'noticeReleases', () => {
	const version = getCurrentVer();

	return gulp.src( './' ).pipe(
		notify( {
			message: 'LearnPress version ' + version + ' build successfully!',
			onLast: true,
		} )
	);
} );

gulp.task( 'makepot', function() {
	return gulp.src( [ './**/*.php', '!node_modules/**', '!releases/**', '!vendor/**' ] )
		.pipe( wpPot( {
			domain: 'learnpress',
			package: 'learnpress',
		} ) )
		.pipe( gulp.dest( './languages/learnpress.pot' ) );
} );

gulp.task(
	'build',
	gulp.series(
		'clearCache',
		'cleanReleases',
		'copyReleases',
		'updateReadme',
		'zipReleases',
		( done ) => {
			done();
		}
	)
);

gulp.task( 'release', gulp.series( 'build', 'noticeReleases', ( done ) => {
	done();
} ) );
