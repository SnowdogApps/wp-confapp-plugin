'use strict';

const gulp        = require('gulp'),
      plumber     = require('gulp-plumber'),
      gulpif      = require('gulp-if'),
      notify      = require("gulp-notify"),
      gutil       = require('gulp-util'),
      sass        = require('gulp-sass'),
      sourcemaps  = require('gulp-sourcemaps'),
      postcss     = require('gulp-postcss'),
      uglify      = require('gulp-uglify'),
      browserSync = require('browser-sync').create(),
      eslint      = require('gulp-eslint'),
      logger      = require('gulp-logger'),
      runSequence = require('run-sequence'),
      concat      = require('gulp-concat');

// PostCSS Plugins
const autoprefixer = require('autoprefixer'),
      stylelint    = require('stylelint'),
      reporter     = require('postcss-reporter');

gulp.task('default', ['dev', 'watch']);

// browser sync
gulp.task('dev', () => {
    browserSync.init({
        proxy: "confapp.dev"
    });
});

// watch tasks
gulp.task('watch', () => {
    gulp.watch('assets/scss/**/*.scss', ['sass', 'css-lint']);
    gulp.watch('assets/js/**/*.js', ['scripts']);
    gulp.watch(['assets/js/**/*.js', '**/*.php'], () => {
      browserSync.reload();
    });
});

// compile SASS
gulp.task('sass', () => {
    return gulp.src('assets/scss/*.scss')
        .pipe(plumber({
            errorHandler: notify.onError("Error: <%= error.message %>")
        }))
        .pipe(gulpif(!gutil.env.maps, sourcemaps.init()))
        .pipe(sass({
            outputStyle: 'compressed'
        }))
        .pipe(postcss([autoprefixer({
            browsers: ['> 1%', 'last 2 versions', 'not ie < 11', 'not OperaMini >= 5.0']
        })]))
        .pipe(gulpif(!gutil.env.maps, sourcemaps.write()))
        .pipe(gulp.dest('assets/css'))
        .pipe(browserSync.stream());
});

// lint and build custom scripts on changes
gulp.task('scripts', () => {
    return gulp.src('assets/js/dev/*.js')
        .pipe(plumber({
            errorHandler: notify.onError("ESLint found problems")
        }))
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(gulp.dest('assets/js/build/'));
});

gulp.task('css-lint', () => {
    return gulp.src('assets/css/*.css')
        .pipe(postcss([
            stylelint(),
            reporter({
                clearMessages: true
            })
        ]));
});


// bulid release
gulp.task('release', () => {
    gutil.env.maps = true;
    runSequence('sass', 'scripts');
});
