//* Vars
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass')); // Ensure 'sass' is used here
var sourcemaps = require('gulp-sourcemaps');
var sassGlob = require('gulp-sass-glob');
const cleanCSS = require('gulp-clean-css');

gulp.task('rentfetch-style', function () {
	return gulp
		.src('css/rentfetch-style.scss')
		.pipe(sassGlob())
		.pipe(sourcemaps.init())
		.pipe(sass().on('error', sass.logError))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest('css/'));
});

gulp.task('rentfetch-style-prod', function () {
	return gulp
		.src('css/rentfetch-style.scss')
		.pipe(sassGlob())
		.pipe(sass().on('error', sass.logError))
		.pipe(cleanCSS({ compatibility: 'ie8' }))
		.pipe(gulp.dest('css/'));
});

gulp.task('admin', function () {
	return gulp
		.src('css/admin.scss')
		.pipe(sassGlob())
		.pipe(sass().on('error', sass.logError))
		.pipe(cleanCSS({ compatibility: 'ie8' }))
		.pipe(gulp.dest('css/'));
});

//* Watchers here
gulp.task('watch', function () {
	gulp.watch('css/**/*.scss', gulp.series(['rentfetch-style', 'admin']));
});

gulp.task('prod', function () {
	gulp.watch('css/**/*.scss', gulp.series(['rentfetch-style-prod', 'admin']));
});

gulp.task('default', gulp.series(['watch']));
