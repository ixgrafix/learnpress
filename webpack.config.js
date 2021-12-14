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
		'frontend/modal': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/modal.js' ),
		'frontend/single-course': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/single-course.js' ),
		'frontend/single-curriculum': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/single-curriculum.js' ),
		'frontend/question-types': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/question-types.js' ),
		'frontend/lesson': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/lesson.js' ),
		'frontend/quiz': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/quiz.js' ),
		'frontend/config': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/lp-configs.js' ),
		'frontend/custom': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/custom.js' ),
		'frontend/profile': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/profile.js' ),
		'frontend/widgets': path.resolve( process.cwd(), 'assets/src/apps/js/frontend/widgets.js' ),

		// Style
		// 'admin/admin': path.resolve( process.cwd(), 'assets/src/scss/admin/admin.scss' ),
		// 'admin/setup': path.resolve( process.cwd(), 'assets/src/scss/admin/setup.scss' ),
		// 'admin/statistic': path.resolve( process.cwd(), 'assets/src/scss/admin/statistic.scss' ),
		'frontend/learnpress': path.resolve( process.cwd(), 'assets/src/scss/learnpress.scss' ),
		'frontend/widget': path.resolve( process.cwd(), 'assets/src/scss/widgets.scss' ),
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
