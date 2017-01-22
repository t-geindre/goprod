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
        'node_modules/vue/dist/vue.min.js',
        'node_modules/vue-resource/dist/vue-resource.min.js',
        'node_modules/cookies-js/dist/cookies.min.js',
        'src/AppBundle/Resources/public/js/component/github/client.js',
        'src/AppBundle/Resources/public/js/component/github/auth.js',
        'src/AppBundle/Resources/public/js/app.js'
    ],
    css: ['src/**/Resources/public/scss/**/*.scss'],
    img: ['src/**/Resources/public/img/**/*.*']
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

gulp.task('default', ['js', 'css', 'img']);

gulp.task('watch', function() {
    gulp.watch(path.js, ['js']);
    gulp.watch(path.css, ['css']);
    gulp.watch(path.img, ['img']);
});
