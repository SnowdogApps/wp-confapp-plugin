var gulp         = require('gulp');
var plumber      = require('gulp-plumber');
var gulpif       = require('gulp-if');
var notify       = require("gulp-notify");
var gutil        = require('gulp-util');
var sass         = require('gulp-sass');
var sourcemaps   = require('gulp-sourcemaps');
var postcss      = require('gulp-postcss');
var uglify       = require('gulp-uglify');
var browserSync  = require('browser-sync');
var reload       = browserSync.reload;
var eslint       = require('gulp-eslint');
var logger       = require('gulp-logger');
var runSequence  = require('run-sequence');
var concat       = require('gulp-concat');

// PostCSS Plugins
var autoprefixer = require('autoprefixer'),
    stylelint    = require('stylelint'),
    reporter     = require('postcss-reporter');

var esLintSettings = {
    configFile: './.eslintrc'
};

gulp.task('default', ['dev', 'watch', 'watch-css-and-lint', 'scripts']);

// browser sync
gulp.task('dev', function() {
    browserSync({
        proxy: "confapp.dev"
    });
});

// watch tasks
gulp.task('watch', function() {
    gulp.watch('assets/scss/**', ['sass']);
    gulp.watch(['assets/js/**', '**/*.php'], reload);
});

// compile SASS
gulp.task('sass', function() {
    return gulp.src('assets/scss/*.scss')
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(gulpif(gutil.env.maps, sourcemaps.init()))
        .pipe(sass({outputStyle: 'expanded', sourceComments: true}))
        .pipe(postcss([autoprefixer({ browsers: ['> 1%', 'last 2 versions', 'not ie < 11', 'not OperaMini >= 5.0'] })]))
        .pipe(gulpif(gutil.env.maps, sourcemaps.write()))
        .pipe(gulp.dest('assets/css'))
        .pipe(reload({stream: true}));
});


// watch and lint specified file
gulp.task('eslint', function() {
    if (gutil.env.file) {
        gutil.log(gutil.colors.red.bold('gutil.env.file: ') + gutil.colors.green(gutil.env.file));
        gulp.watch('assets/js' + gutil.env.file + '.js', function(event) {
            gulp.src(event.path)
                .pipe(plumber({errorHandler: notify.onError("ESLint found problems")}))
                .pipe(logger({display: 'name'}))
                .pipe(eslint(esLintSettings))
                .pipe(eslint.format());
        });
    }
    else {
        gutil.log(gutil.colors.red.bold('ERROR: Specify file name, for example: ') + gutil.colors.green('gulp eslint --file formValidator-2.2.8'));
    }
});

// lint and build custom scripts on changes
gulp.task('scripts', function() {
    gulp.watch('assets/js/dev/*.js', function(event) {
        gulp.src(event.path)
            .pipe(plumber({errorHandler: notify.onError("ESLint found problems")}))
            .pipe(logger({display: 'name'}))
            .pipe(eslint(esLintSettings))
            .pipe(eslint.format())
            .pipe(uglify())
            .pipe(gulp.dest('assets/js/build/'));
    });
});

// lint all css files
gulp.task('watch-css-and-lint', function() {
    gulp.watch('assets/css/*.css', function(event) {
        gulp.src(event.path)
            .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
            .pipe(logger({display: 'name'}))
            .pipe(postcss([
                stylelint(),
                reporter({
                    clearMessages: true
                })
            ]));
    });
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

// build all JS
gulp.task('uglify', function() {
    return gulp.src('assets/js/dev/*.js')
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(logger({display: 'name'}))
        .pipe(uglify())
        .pipe(gulp.dest('assets/js/build/'));
});

// bulid release
gulp.task('release', function() {
    runSequence('styles', 'css-lint', 'uglify');
});
