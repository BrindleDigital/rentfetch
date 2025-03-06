const mix = require('laravel-mix');
const globImporter = require('node-sass-glob-importer');

mix.setPublicPath('./')
	.sass('css/rentfetch-style.scss', 'css', {
		sassOptions: {
			importer: globImporter(),
		},
	})
	.sass('css/admin.scss', 'css', {
		sassOptions: {
			importer: globImporter(),
		},
	})
	.options({
		processCssUrls: false, // Disable URL processing
	});
