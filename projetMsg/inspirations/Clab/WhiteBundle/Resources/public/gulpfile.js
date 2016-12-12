var gulp = require('gulp');
var less = require('gulp-less');
var recess = require('gulp-recess');
var minifyCSS = require('gulp-minify-css');
var watch = require('gulp-watch');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var path = require('path');

/* Task to compile less */
gulp.task('less', function () {
    gulp.src(['./css/main.less','./vendors/font-awesome/css/font-awesome.css'])
        //.pipe(recess())
        .pipe(less())
        .pipe(minifyCSS())
        .pipe(gulp.dest('./dist/css/'));
});

gulp.task('fonts', function () {
    gulp.src('./fonts/*')
        .pipe(gulp.dest('./dist/fonts/'));
});

/* Task to watch less changes */
gulp.task('watch-less', function() {  
    gulp.watch('./css/*.less' , ['less']);
});

/* Task to concat js */
gulp.task('scripts', function() {
  return gulp.src([
        './vendors/SelectOrDie/_src/selectordie.min.js',
        './js/jquery.storelocator.js',
        './vendors/jquery-storelocator-plugin/libs/handlebars/handlebars-v4.0.5.js',
        './vendors/jcf/js/jcf.js',
        './vendors/jcf/js/jcf.radio.js',
        './vendors/jcf/js/jcf.checkbox.js',
        './js/order.js',
        './js/app.js'
    ])
    .pipe(concat('app.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('./dist/js/'));
});

/* Task to watch js changes */
gulp.task('watch-scripts', function() {  
    gulp.watch('./assets/js/**/*.js' , ['scripts']);
});

/* Task when running `gulp` from terminal */
gulp.task('default', ['less', 'scripts','fonts']);
