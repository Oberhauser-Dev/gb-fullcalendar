const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const webpackConfig = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		client: './src/client.js',
	},
};
module.exports = webpackConfig;
