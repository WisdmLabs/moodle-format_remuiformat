// Gulp
var gulp = require('gulp');

// Sass/CSS stuff
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var prefix = require('gulp-autoprefixer');
var minifycss = require('gulp-minify-css');
var shell  = require('gulp-shell');

// JS stuff
const minify = require('gulp-minify');

// Compile all your Sass.
gulp.task('sass', function (){
    gulp.src(['./styles/*.css'])
        .pipe(sass({
            includePaths: ['./sass'],
            outputStyle: 'expanded'
        }))
        .pipe(prefix(
            "last 1 version", "> 1%", "ie 8", "ie 7"
            ))
        .pipe(minifycss())
        .pipe(concat('styles.css'))
        .pipe(gulp.dest('.'));
});

gulp.task('compress', function() {
    gulp.src('./amd/src/*.js')
    .pipe(minify({
        ext:{
           min:'.js'
        },
        noSource: true,
        ignoreFiles: []
    }))
    .pipe(gulp.dest('./amd/build'))
});

gulp.task('purge', shell.task('php '+__dirname+'/../../admin/cli/purge_caches.php'));


gulp.task('watch', function() {
    gulp.watch('./amd/src/*.js', ['compress', 'purge']);
    gulp.watch('./styles/*.css', ['sass']);
});

gulp.task('default', ['watch', 'compress', 'sass', 'purge']);
