var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var plumber = require('gulp-plumber');
var livereload = require('gulp-livereload');
var minifyCSS = require('gulp-minify-css');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");

// styles
gulp.task('styles', function () {
	gulp.src(['./scss/styles.scss', './basic-scss/basic.scss', './scss/admin-styles.scss'])
		.pipe(plumber({
			errorHandler: onError
		}))
		.pipe(sass())
		.pipe(gulp.dest('./css/'))
		.pipe(minifyCSS({compatibility: 'ie8'}))
		.pipe(rename(function (path) {
			path.basename = path.basename + ".min";
		}))
		.pipe(gulp.dest('./css/'))
		.pipe(livereload());
});

// scripts
gulp.task('scripts', function () {
	gulp.src(['./scripts/scripts.js'])
		.pipe(plumber({
			errorHandler: onError
		}))
		.pipe(uglify())
		.pipe(rename(function (path) {
			path.basename = path.basename + ".min";
		}))
		.pipe(gulp.dest('./scripts/'))
		.pipe(livereload());
});

// watch
gulp.task('watch', function () {
	livereload.listen();
	// Watch for changes
	gulp.watch(['./scss/**/*.scss', './scss/*.scss', './basic-scss/**/*.scss', './basic-scss/*.scss'], ['styles']);
	gulp.watch(['./scripts/*.js'], ['scripts']);
});

// running tasks
gulp.task('default', ['styles', 'scripts', 'watch']);

// helper
function onError(err) {
	console.log(err);
}
