var gulp = require('gulp');
var gulpif = require('gulp-if');
var uglify = require('gulp-uglify');
var uglifycss = require('gulp-uglifycss');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var env = process.env.GULP_ENV;


var path = {
    js : [
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/bootstrap/dist/js/bootstrap.min.js',
        'node_modules/vue/dist/vue.min.js',
        'node_modules/vue-resource/dist/vue-resource.min.js',
        'node_modules/vue-router/dist/vue-router.min.js',
        'node_modules/vuex/dist/vuex.min.js',
        'node_modules/cookies-js/dist/cookies.min.js',
        'src/AppBundle/Resources/public/js/component/github/client.js',
        'src/**/Resources/public/js/lib/**/*.js',
        'src/**/Resources/public/js/component/**/*.js',
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
    return gulp.src(path.js)
        .pipe(concat('build.js'))
        .pipe(gulpif(env === 'prod', uglify()))
        .pipe(sourcemaps.write('./'))
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
