let gulp = require('gulp'),
  sass = require('gulp-sass')(require('sass')),
  sourcemaps = require('gulp-sourcemaps'),
  $ = require('gulp-load-plugins')(),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss'),
  autoprefixer = require('autoprefixer'),
  postcssInlineSvg = require('postcss-inline-svg'),
  browserSync = require('browser-sync').create()
pxtorem = require('postcss-pxtorem'),
  uglify = require('gulp-uglify'),
  postcssProcessors = [
    postcssInlineSvg({
      removeFill: true,
      paths: ['./node_modules/bootstrap-icons/icons']
    }),
    pxtorem({
      propList: ['font', 'font-size', 'line-height', 'letter-spacing', '*margin*', '*padding*'],
      mediaQuery: true
    })
  ];

const paths = {
  scss: {
    src: './scss/style.scss',
    dest: './css',
    watch: './scss/**/*.scss',
    bootstrap: './node_modules/bootstrap/scss/bootstrap.scss',
  },
  js: {
    bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
    popper: './node_modules/@popperjs/core/dist/umd/popper.min.js',
    flickity: './node_modules/flickity/dist/flickity.pkgd.min.js',
    vanilla_cc: './node_modules/vanilla-cookieconsent/dist/cookieconsent.umd.js',
    barrio: '../../contrib/bootstrap_barrio/js/barrio.js',
    dest: './js',
    watch: './js/**/*.js' // Add the watch pattern for JS files
  },
  css: {
    flickity: './node_modules/flickity/dist/flickity.min.css',
    vanilla_cc: './node_modules/vanilla-cookieconsent/dist/cookieconsent.css',
    dest: './css'
  }
}

// Compile sass into CSS & auto-inject into browsers
function styles() {
  return gulp.src([paths.scss.bootstrap, paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass({
      includePaths: [
        './node_modules/bootstrap/scss',
        '../../contrib/bootstrap_barrio/scss'
      ]
    }).on('error', sass.logError))
    .pipe($.postcss(postcssProcessors))
    .pipe(postcss([autoprefixer({
      browsers: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream())
}

// Move the javascript files into our js folder
function js() {
  return gulp.src([
    paths.js.bootstrap,
    paths.js.popper,
    paths.js.flickity,
    paths.js.vanilla_cc,
    paths.js.barrio
  ])
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// Copy CSS files to the css folder
function css() {
  return gulp.src([paths.css.flickity, paths.css.vanilla_cc])
    .pipe(gulp.dest(paths.css.dest))
    .pipe(browserSync.stream())
}

function minifyJs() {
  // Uglify non-minified javascript files
  return gulp.src([
    paths.js.dest + '/*.js',
    '!' + paths.js.dest + '/*.{min,umd}.js'
  ])
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream())
}

// Static Server + watching scss/html files
function serve() {
  browserSync.init({
    proxy: 'http://localhost:8088',
  })

  gulp.watch([paths.scss.watch,
  paths.scss.bootstrap,
  paths.js.watch
  ], gulp.series(styles, minifyJs)).on('change', browserSync.reload)
}

const build = gulp.series(styles, gulp.parallel(js, minifyJs, css, serve))

exports.styles = styles
exports.js = js
exports.minifyJs = minifyJs
exports.css = css
exports.serve = serve

exports.default = build
