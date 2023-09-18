//* Vars
var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var sassGlob = require('gulp-sass-glob');
const cleanCSS = require('gulp-clean-css');

gulp.task('rent-fetch-style', function () {
    return gulp
        .src('css/rent-fetch-style.scss')
        .pipe(sassGlob())
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('css/'));
});

gulp.task('rent-fetch-style-prod', function () {
    return gulp
        .src('css/rent-fetch-style.scss')
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
    gulp.watch('css/**/*.scss', gulp.series(['rent-fetch-style', 'admin']));
});

gulp.task('prod', function () {
    gulp.watch(
        'css/**/*.scss',
        gulp.series(['rent-fetch-style-prod', 'admin'])
    );
});

gulp.task('default', gulp.series(['watch']));
