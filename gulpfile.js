
	var gulp = require('gulp');
	var livereload = require('gulp-livereload');
	var sass = require('gulp-sass');
	var rename = require('gulp-rename');

	// SASS AND UPLOAD

	gulp.task('sass', function() {

		return gulp.src('./sass/custom/main.scss')
    .pipe(
      sass({
        // outputStyle: 'compressed'
      })
      .on('error', sass.logError)
    )
    .pipe(rename('style.css'))
    .pipe(gulp.dest('./'));
	});

	gulp.task('reload', [ 'sass' ], function () {

		setTimeout(function() {
			
			livereload.reload();
		}, 1000);
	});

	/* Watch */

	gulp.task('watch', function () {

    livereload.listen();

		gulp.watch(
			[
				'./sass/custom/**/*'
			], 
			['reload']
		);
	});

	/* ------------------------------------------------------------------------------------------ */

	gulp.task('default', ['watch']);