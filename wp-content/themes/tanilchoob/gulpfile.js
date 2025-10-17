// Import `src` and `dest` from gulp for use in the task.
const {series, src, dest, parallel, watch, task} = require("gulp")

// Import Gulp plugins.
const babel = require("gulp-babel")
const concat = require("gulp-concat")
const del = require("del")
const plumber = require("gulp-plumber")
const uglify = require("gulp-uglify")
const sassGlob = require('gulp-sass-glob')
const sass = require('gulp-sass')(require('sass'))
const sourcemaps = require('gulp-sourcemaps')
const prefix = require('gulp-autoprefixer')
const cssmin = require('gulp-cssnano')
const imagemin = require('gulp-imagemin')
const notify = require('gulp-notify')

const sassOptions = {
  outputStyle: 'expanded'
};

const prefixerOptions = {
  overrideBrowserslist: ['last 2 versions']
};

var onError = function (err) {
  notify.onError({
    title: "Gulp",
    subtitle: "Failure!",
    message: "Error: <%= error.message %>",
    sound: "Basso"
  })(err);
  this.emit('end');
};

function cssDeps(done) {
  return (
      src("./assets/frontend/src/vendors/css/**/*.css")
          .pipe(plumber({errorHandler: onError}))
          .pipe(cssmin({zindex: false}))
          // Combine these files into a single main.deps.js file.
          .pipe(concat("main.deps.css"))
          // Save the concatenated file to the tmp directory.
          .pipe(dest("./tmp"))
  )
}

function scssBuild(done) {
  return (
      src("./assets/frontend/src/scss/style.scss")
          .pipe(plumber({errorHandler: onError}))
          .pipe(sassGlob())
          .pipe(sourcemaps.init())
          .pipe(sass(sassOptions))
          .pipe(sourcemaps.write())
          .pipe(prefix(prefixerOptions))
          .pipe(concat("main.build.css"))
          .pipe(cssmin({zindex: false}))
          .pipe(dest("./tmp"))

  )
}

function cssConcat(done) {
  // An array of the two temp (concatenated) files.
  const files = ["./tmp/main.deps.css", "./tmp/main.build.css"]
  return (
      src(files,{ allowEmpty: true })
          .pipe(plumber({errorHandler: onError}))
          // Concatenate the third-party libraries and our
          // homegrown components into a single main.js file.
          .pipe(concat("styles.min.css"))
          // Save it to the final destination.
          .pipe(dest("./assets/frontend/dist/css"))
          .pipe(notify({message: "Styles Concat complete", onLast: true}))
  )
}

function cssClean(done) {
  // An array of the two temp (concatenated) files.
  const files = ["./tmp/main.deps.css", "./tmp/main.build.css"]

  return (
      del(files)
  )
}


function jsDeps(done) {
  return (
      src("./assets/frontend/src/vendors/js/**/*.js")
          .pipe(plumber({errorHandler: onError}))
          // Combine these files into a single main.deps.js file.
          .pipe(concat("main.deps.js"))
          // Save the concatenated file to the tmp directory.
          .pipe(dest("./tmp"))
  )
}

function jsBuild(done) {
  return (
      src("./assets/frontend/src/js/partials/**/*.js")
          .pipe(plumber({errorHandler: onError}))
          // Notice the name change.
          .pipe(concat("main.build.js"))
          .pipe(
              babel({
                presets: [
                  [
                    "@babel/env",
                    {
                      modules: false
                    }
                  ]
                ]
              })
          )
          // Minify the self-authored bundle.
          .pipe(uglify())
          // And the destination change.
          .pipe(dest("./tmp"))
  )
}

function jsConcat(done) {
  // An array of the two temp (concatenated) files.
  const files = ["./tmp/main.deps.js", "./tmp/main.build.js"]
  return (
      src(files, { allowEmpty: true })
          .pipe(plumber({errorHandler: onError}))
          // Concatenate the third-party libraries and our
          // homegrown components into a single main.js file.
          .pipe(concat("scripts.min.js"))
          // Save it to the final destination.
          .pipe(dest("./assets/frontend/dist/js"))
          .pipe(notify({message: "Js Concat complete", onLast: true}))
  )
}

// Add a jsClean() task to delete the temporary *.deps.js and
// *.build.js files from the temporary directory.
function jsClean(done) {
  // An array of the two temp (concatenated) files.
  const files = ["./tmp/main.deps.js", "./tmp/main.build.js"]

  return (
      del(files)
  )
}

task('images', function () {
  return src(['./assets/frontend/src/images/**/*', './assets/frontend/src/images/*'])
      .pipe(imagemin([
        imagemin.gifsicle({interlaced: true}),
        imagemin.mozjpeg({quality: 75, progressive: true}),
        imagemin.optipng({optimizationLevel: 5}),
        imagemin.svgo({
          plugins: [
            {removeViewBox: true},
            {cleanupIDs: false}
          ]
        })
      ]))
      .pipe(dest('./assets/frontend/dist/images'));
});

task('fonts', function () {
  return src('./assets/frontend/src/fonts/**')
      .pipe(dest('./assets/frontend/dist/fonts'));
});

task('styles', series(parallel(cssDeps, scssBuild), cssConcat, cssClean, function (cb) {
  cb()
}));

task('scripts', series(parallel(jsDeps, jsBuild), jsConcat, jsClean, function (cb) {
  cb()
}));

task('watch', series(function (cb) {
  watch(['./assets/frontend/src/vendors/css/**/*.css'], series('styles'));
  watch(['./assets/frontend/src/scss/**/*.scss'], series('styles'));
  watch(['./assets/frontend/src/vendors/js/**/*.js'], series('scripts'));
  watch(['./assets/frontend/src/js/partials/**/*.js'], series('scripts'));
  watch(['./assets/frontend/src/images'], series('images'));
  watch(['./assets/frontend/src/fonts'], series('fonts'));
  cb()
}));


task('default',
    series('styles', 'scripts', 'watch'));

task('deploy',
    series('styles', 'scripts', 'images', 'fonts'));