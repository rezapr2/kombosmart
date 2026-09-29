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
const mergeStream = require('merge-stream')
const fs = require('fs')
const {Transform} = require('stream')

const sassOptions = {
  outputStyle: 'expanded'
};

const prefixerOptions = {
  overrideBrowserslist: ['last 2 versions']
};

// Stamp output files with the build time. gulp-concat copies the newest *input* file's
// mtime, so styles.min.css kept style.scss's date and Frontend.php's filemtime()-based
// cache busting never changed after a rebuild.
function touch() {
  return new Transform({
    objectMode: true,
    transform(file, enc, cb) {
      if (file.stat) {
        file.stat.mtime = file.stat.atime = new Date()
      }
      cb(null, file)
    }
  })
}

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
          .pipe(cssmin({zindex: false, autoprefixer: false}))
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
          .pipe(cssmin({zindex: false, autoprefixer: false}))
          .pipe(dest("./tmp"))

  )
}

// Build standalone CSS bundles for page templates (do not merge into main styles)
function scssPageTemplatesBuild(done) {
  return (
      src("./assets/frontend/src/scss/partials/page-templates/*.scss")
          .pipe(plumber({errorHandler: onError}))
          .pipe(sassGlob())
          .pipe(sourcemaps.init())
          .pipe(sass(sassOptions))
          .pipe(sourcemaps.write())
          .pipe(prefix(prefixerOptions))
          .pipe(dest("./assets/frontend/dist/css/page-templates"))
  )
}

// Build standalone CSS bundles for theme templates (homepage, single, taxonomy).
// Each entry file in template-bundles/ imports its own mixins/variables, so they
// compile in isolation and are loaded conditionally per page type (see Frontend.php).
function scssTemplatesBuild(done) {
  return (
      src("./assets/frontend/src/scss/template-bundles/*.scss")
          .pipe(plumber({errorHandler: onError}))
          .pipe(sassGlob())
          .pipe(sourcemaps.init())
          .pipe(sass(sassOptions))
          .pipe(sourcemaps.write())
          .pipe(prefix(prefixerOptions))
          .pipe(dest("./assets/frontend/dist/css/templates"))
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
          .pipe(touch())
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
      // Exclude templates/** — those are built as standalone, conditionally-loaded
      // bundles (see jsTemplatesBuild) instead of being merged into scripts.min.js.
      src(["./assets/frontend/src/js/partials/**/*.js", "!./assets/frontend/src/js/partials/templates/**/*.js"])
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
          .pipe(touch())
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

// Build standalone, conditionally-loaded JS bundles — one per folder under
// js/partials/templates/ (e.g. single-product/ -> templates/single-product.js).
// Each file is a self-contained IIFE, so files are concatenated per folder, then
// transpiled and minified the same way as the main bundle. Loaded per page type
// (see Frontend.php) with the main 'scripts' handle as a dependency.
function jsTemplatesBuild(done) {
  const baseDir = "./assets/frontend/src/js/partials/templates"
  if (!fs.existsSync(baseDir)) { done(); return; }
  const folders = fs.readdirSync(baseDir).filter(function (name) {
    return fs.statSync(baseDir + "/" + name).isDirectory()
  })
  if (!folders.length) { done(); return; }

  return mergeStream(folders.map(function (folder) {
    return src(baseDir + "/" + folder + "/**/*.js")
        .pipe(plumber({errorHandler: onError}))
        .pipe(concat(folder + ".js"))
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
        .pipe(uglify())
        .pipe(dest("./assets/frontend/dist/js/templates"))
  }))
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

task('styles', series(parallel(cssDeps, scssBuild, scssPageTemplatesBuild, scssTemplatesBuild), cssConcat, cssClean, function (cb) {
  cb()
}));

task('scripts', series(parallel(jsDeps, jsBuild, jsTemplatesBuild), jsConcat, jsClean, function (cb) {
  cb()
}));

task('watch', series(function (cb) {
  watch(['./assets/frontend/src/vendors/css/**/*.css'], series('styles'));
  watch(['./assets/frontend/src/scss/**/*.scss'], series('styles'));
  watch(['./assets/frontend/src/scss/partials/page-templates/*.scss'], series(scssPageTemplatesBuild));
  watch(['./assets/frontend/src/scss/template-bundles/*.scss', './assets/frontend/src/scss/partials/templates/**/*.scss'], series(scssTemplatesBuild));
  watch(['./assets/frontend/src/vendors/js/**/*.js'], series('scripts'));
  watch(['./assets/frontend/src/js/partials/**/*.js', '!./assets/frontend/src/js/partials/templates/**/*.js'], series('scripts'));
  watch(['./assets/frontend/src/js/partials/templates/**/*.js'], series(jsTemplatesBuild));
  watch(['./assets/frontend/src/images'], series('images'));
  watch(['./assets/frontend/src/fonts'], series('fonts'));
  cb()
}));


task('default',
    series('styles', 'scripts', 'watch'));

task('deploy',
    series('styles', 'scripts', 'images', 'fonts'));
