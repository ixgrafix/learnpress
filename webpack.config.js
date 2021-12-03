const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const MergeIntoSingleFilePlugin = require( 'webpack-merge-and-include-globally' );

module.exports = {
	...defaultConfig,
	entry: {
		'admin/editor/course': path.resolve( process.cwd(), 'assets/src/apps/js/admin/editor/course.js' ),
		'admin/editor/quiz': path.resolve( process.cwd(), 'assets/src/apps/js/admin/editor/quiz.js' ),
		'admin/editor/question': path.resolve( process.cwd(), 'assets/src/apps/js/admin/editor/question.js' ),
		'admin/pages/tools': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/tools.js' ),
		'admin/pages/setup': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/setup.js' ),
		'admin/pages/statistic': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/statistic.js' ),
		'admin/pages/sync-data': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/sync-data.js' ),
		'admin/pages/themes-addons': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/themes-addons.js' ),
		'admin/pages/dashboard': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/dashboard.js' ),
		'admin/pages/widgets': path.resolve( process.cwd(), 'assets/src/apps/js/admin/pages/widgets.js' ),
		utils: path.resolve( process.cwd(), 'assets/src/js/utils/index.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'assets/build' ),
		publicPath: 'auto',
	},
	plugins: [
		...defaultConfig.plugins,
		new MergeIntoSingleFilePlugin( {
			files: {
				'vendor/vue_libs.js': [
					'./assets/src/js/vendor/vue/vue.js',
					'./assets/src/js/vendor/vue/vuex.js',
					'./assets/src/js/vendor/vue/vue-resource.js',
				],
				'vendor/plugins.all.js': [
					'./assets/src/js/vendor/watch.js',
					'./assets/src/js/vendor/jquery/jquery-scrollTo.js',
					'./assets/src/js/vendor/jquery/jquery-timer.js',
					'./assets/src/js/vendor/jquery/jquery.tipsy.js',
				],
				'vendor/chart.js': [
					'./assets/src/js/vendor/chart.min.js',
				],
				'bundle.css': [
					'./assets/src/css/vendor/jquery.tipsy.css',
				],
				'admin.bundle.css': [
					'./assets/src/css/vendor/font-awesome.min.css',
					'./assets/src/css/vendor/jquery.tipsy.css',
				],
			},
			transform: {
				'bundle.css': ( code ) => require( 'uglifycss' ).processString( code ),
				'admin.bundle.css': ( code ) => require( 'uglifycss' ).processString( code ),
			},
		} ),
	],
};
