// Get Gulp module.
const { src, dest, watch } = require('gulp');

// Get minify, autoprefixer and sass modules.
const minify = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const sass = require('gulp-sass')(require('sass'));

// Set up paths.
const paths = {
	src: {
		css: './assets/css/src/**/*.scss',
		js: './assets/js/src/**/*.js',
	},
	dest: {
		css: './assets/css/dist',
		js: './assets/js/dist',
	},
};

// Compile script.
const compile = () => {
	return src(paths.src.css)
		.pipe(sass())
		.pipe(autoprefixer())
		.pipe(minify())
		.pipe(dest(paths.dest.css));
};

// Watch script.
const observe = () => {
	watch(paths.src.css, compile);
};

// Export scripts.
exports.sass = compile;
exports.watch = observe;
exports.default = observe;
