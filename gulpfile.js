var gulp = require('gulp');
var gulpif = require('gulp-if');
var uglify = require('gulp-uglify');
var uglifycss = require('gulp-uglifycss');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var source = require('vinyl-source-stream');
var browserify = require('browserify');
var env = process.env.GULP_ENV;


var path = {
    js_entry: 'src/FrontBundle/Resources/public/js/app.js',
    js : [
        'src/**/Resources/public/js/**/*.js'
    ],
    css: [
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'src/**/Resources/public/scss/**/*.scss'
    ],
    font: [
        'node_modules/bootstrap/dist/fonts/*.*'
    ],
    img: [
        'src/**/Resources/public/img/**/*.*'
    ]
};

gulp.task('js', function () {
  browserify(path.js_entry)
    .bundle()
    .pipe(source('build.js'))
    .pipe(gulpif(env === 'prod', uglify()))
    .pipe(gulp.dest('web/js'));
});

gulp.task('css', function () {
    return gulp.src(path.css)
        .pipe(gulpif(/[.]scss/, sass()))
        .pipe(concat('styles.css'))
        .pipe(gulpif(env === 'prod', uglifycss()))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('web/css'));
});

gulp.task('img', function() {
    return gulp.src(path.img)
        .pipe(gulp.dest('web/img'));
});

gulp.task('font', function() {
    return gulp.src(path.font)
        .pipe(gulp.dest('web/fonts'));
});

gulp.task('default', ['js', 'css', 'img', 'font']);

gulp.task('watch', ['default'], function() {
    gulp.watch(path.js, ['js']);
    gulp.watch(path.css, ['css']);
    gulp.watch(path.img, ['img']);
    gulp.watch(path.img, ['font']);
});
