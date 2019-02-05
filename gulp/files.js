var paths = {
  components: {
    normalizecss: './node_modules/normalize.css/',
  },
  css: {
    src: './webroot/css/src/',
    dist: './webroot/css/dist/',
    maps: './webroot/css/src/maps/'
  },
  js: {
    src: './webroot/js/src/',
    vendor: './webroot/js/vendor/',
    dist: './webroot/js/dist/',
    jshint: './'
  },
  images: {
    src: './webroot/images/src/',
    dist: './webroot/images/dist/'
  },
  sprites: {
    src: './webroot/images/src/icons/',
    dist: './webroot/images/dist/icons/'
  },
  sprite: {
    dist: './webroot/images/dist/'
  }
}

var globs = {
  js: {
    src: [
      paths.components.slick_slider + 'slick.js',
      paths.components.featherlight + 'featherlight.js',
      paths.js.src + 'main.js',
      paths.js.src + 'gallery.js',
    ],
    dist: {
      original: 'app.js',
      minified: 'app.min.js'
    },
    jshint: '.jshintrc'
  },
  css: {
    raw: [
      paths.css.src + '*.css'
    ],
    src: [
      paths.css.src + 'style.css',
    ],
    dist: {
      original: 'style.css',
      minified: 'style.min.css',
      temp: 'style.temp.css'
    },
    maps: [
      'config.yml',
      'bp.yml',
      'colors.yml',
      'fonts.yml'
    ],
  },
  images: {
    src: paths.images.src + '**',
    dist: paths.images.dist + '*.*'
  },
  sprites: {
    src: paths.sprites.src + '*.svg',
    dist: paths.sprites.dist
  },
  sprite: {
    src: paths.sprites.dist + 'svg-symbols.svg',
    dist: 'icons.svg'
  }
};

module.exports = {
  paths: paths,
  globs: globs
};