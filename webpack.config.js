const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry,
		client: './src/client.js',
	},
	resolve: {
		...defaultConfig.resolve,
	},
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.(scss|css)$/,
				use: ['style-loader', 'css-loader'],
			},
		]
	}
};
