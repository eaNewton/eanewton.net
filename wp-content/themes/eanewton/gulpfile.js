var config = require('./gulp/config.json'),
  files = require('./gulp/files'),
  gulp = require('gulp'),
  del = require('del'),
  postcss = require('gulp-postcss'),
  sourcemaps = require('gulp-sourcemaps'),
  autoprefixer = require('autoprefixer'),
  cssnano = require('cssnano'),
  atImport = require('postcss-import'),
  mixins = require('postcss-mixins'),
  conditionals = require('postcss-conditionals'),
  postcssfor = require('postcss-for'),
  postcsseach = require('postcss-each'),
  compactmq = require('postcss-compact-mq'),
  stylelint = require('gulp-stylelint'),
  calc = require('postcss-calc'),
  simpleVars = require('postcss-simple-vars'),
  nested = require('postcss-nested'),
  map = require('postcss-map'),
  cssstats = require('cssstats'),
  rename = require('gulp-rename'),
  jshint = require('gulp-jshint'),
  uglify = require('gulp-uglify'),
  concat = require('gulp-concat'),
  sizereport = require('gulp-sizereport'),
  imagemin = require('gulp-imagemin'),
  svgSymbols = require('gulp-svg-symbols'),
  babel = require('gulp-babel');

gulp.task('default', ['sprite', 'css', 'js', 'js_map', 'js_gallery']);

gulp.task('images', function () {
  return gulp.src(files.globs.images.src)
    .pipe(imagemin([
      imagemin.gifsicle({ interlaced: true }),
      imagemin.jpegtran({ progressive: true }),
      imagemin.optipng({ optimizationLevel: 7 }),
      imagemin.svgo({ plugins: [{ removeViewBox: true, cleanupIDs: false }] })
    ]))
    .pipe(gulp.dest(files.paths.images.dist));
});

gulp.task('compile-sprite', ['images'], function () {
  return gulp.src(files.globs.sprites.src)
    .pipe(svgSymbols())
    .pipe(gulp.dest(files.globs.sprites.dist));
});

gulp.task('process-sprite-move', ['compile-sprite'], function () {
  return gulp.src(files.globs.sprite.src)
    .pipe(rename(files.globs.sprite.dist))
    .pipe(gulp.dest(files.paths.sprite.dist));
});

gulp.task('process-sprite-del', ['compile-sprite', 'process-sprite-move'], function () {
  return del(files.globs.sprites.dist);
});

gulp.task('sprite', ['compile-sprite', 'process-sprite-move', 'process-sprite-del'], function () {
  return;
});

gulp.task('lint-css', function () {
  return gulp.src(files.globs.css.raw)
    .pipe(sourcemaps.init())
    .pipe(stylelint(config.stylelint));
});

gulp.task('css', ['lint-css'], function () {
  var processors = [
    atImport(),
    map({ basePath: files.paths.css.maps, maps: files.globs.css.maps }),
    conditionals(),
    postcssfor(),
    postcsseach(),
    mixins(),
    compactmq(),
    nested(),
    calc(),
    simpleVars(),
    autoprefixer(config.autoprefixer),
    cssnano()
  ];

  return gulp.src(files.globs.css.src)
    .pipe(sourcemaps.init())
    .pipe(postcss(processors))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(files.paths.css.dist))
    .pipe(sizereport());
});

gulp.task('lint-js', function () {
  return gulp.src(files.globs.js.src)
    .pipe(jshint(files.paths.js.jshint + files.globs.js.jshint))
    .pipe(jshint.reporter(config.jshint_reporter));
});

gulp.task('js', ['lint-js'], function () {
  return gulp.src(files.globs.js.src)
    .pipe(sourcemaps.init())
    .pipe(babel(config.babel))
    .pipe(concat(files.globs.js.dist.original))
    .pipe(gulp.dest(files.paths.js.dist))
    .pipe(rename(files.globs.js.dist.minified))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(files.paths.js.dist))
    .pipe(sizereport());
});

gulp.task('lint-js_map', function () {
  return gulp.src(files.globs.js_map.src)
    .pipe(jshint(files.paths.js_map.jshint + files.globs.js_map.jshint))
    .pipe(jshint.reporter(config.jshint_reporter));
});

gulp.task('js_map', ['lint-js_map'], function () {
  return gulp.src(files.globs.js_map.src)
    .pipe(sourcemaps.init())
    .pipe(babel(config.babel))
    .pipe(concat(files.globs.js_map.dist.original))
    .pipe(gulp.dest(files.paths.js_map.dist))
    .pipe(rename(files.globs.js_map.dist.minified))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(files.paths.js_map.dist))
    .pipe(sizereport());
});

gulp.task('lint-js_gallery', function () {
  return gulp.src(files.globs.js_gallery.src)
    .pipe(jshint(files.paths.js_gallery.jshint + files.globs.js_gallery.jshint))
    .pipe(jshint.reporter(config.jshint_reporter));
});

gulp.task('js_gallery', ['lint-js_gallery'], function () {
  return gulp.src(files.globs.js_gallery.src)
    .pipe(sourcemaps.init())
    .pipe(babel(config.babel))
    .pipe(concat(files.globs.js_gallery.dist.original))
    .pipe(gulp.dest(files.paths.js_gallery.dist))
    .pipe(rename(files.globs.js_gallery.dist.minified))
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(files.paths.js_gallery.dist))
    .pipe(sizereport());
});
