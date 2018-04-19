process.env.DISABLE_NOTIFIER = true;
var elixir = require('laravel-elixir');
var gulp = require("gulp");
var shell = require("gulp-shell");
var nightwatch = require('gulp-nightwatch');
var clean = require('gulp-rimraf');
var vueFile = elixir.config.production ? "vue.min.js" : "vue.js";

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    var bootstrapPath = 'node_modules/bootstrap-sass/assets',
        bootstrap4Path = 'node_modules/bootstrap/dist',
        bracketPath = 'node_modules/jquery-bracket/dist',
        timepickerPath = 'node_modules/timepicker',
        tetherPath = 'node_modules/tether/dist/js',
        vuePath = 'node_modules/vue/dist/',
        axiosPath = 'node_modules/axios/dist/axios.min.js',
        toastrPath = 'node_modules/toastr/build/';
    mix.copy('resources/assets/fonts', 'public/fonts')
        .copy('resources/assets/img', 'public/img')
        .copy('resources/assets/favicons', 'public')
        .copy('resources/assets/vue', 'public/vue')
        .copy(bootstrapPath + '/fonts', 'public/fonts')
        .copy(bootstrap4Path + '/js/bootstrap.min.js', 'resources/assets/js')
        .copy(tetherPath + '/tether.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.css', 'public/css')
        .copy(timepickerPath + '/jquery.timepicker.min.css', 'resources/assets/css')
        .copy(timepickerPath + '/jquery.timepicker.min.js', 'resources/assets/js')
        .copy(vuePath + vueFile, 'resources/assets/js')
        .copy(axiosPath, 'resources/assets/js')
        .copy(toastrPath + '/toastr.min.css', 'resources/assets/css')
        .copy(toastrPath + '/toastr.min.js', 'resources/assets/js')
        .copy('node_modules/requirejs/require.js', 'resources/assets/js')
        .sass('app.scss')
        .scripts([
            "jquery-2.2.3.min.js",      // TODO: jquery from npm?
            "tether.js",
            "abr-calendar.js",
            "abr-map.js",
            "abr-table.js",
            "abr-main.js",
            "abr-stats.js",
            "abr-matches.js",
            "abr-flags.js",
            "bootstrap.min.js",
            "jquery.calendario.js",
            "bootstrap-datepicker.js",
            "jquery.bracket.min.js",
            "ekko-lightbox.min.js",
            "atc.min.js",
            "jquery.timepicker.min.js",
            "cookieconsent.min.js",
            vueFile,
            "axios.min.js",
            "toastr.min.js",
            "require.js",
            "abr-vue.js"
        ])
        .styles([
            'font-awesome.css',
            'calendar.css',
            'calendario_abr.css',
            '../../../public/css/app.css',
            'bootstrap-datepicker.css',
            'netrunner.css',
            'main.css',
            'ekko-lightbox.min.css',
            'jquery.timepicker.min.css',
            'toastr.min.css'
        ])
        .version(['css/all.css', 'js/all.js']);

    gulp.task("test", function() {
        shell('nightwatch -c tests/nightwatch/nightwatch.json');
    });
});

// run automated nightwatch tests
gulp.task('nightwatch:chrome', function(){
    return gulp.src('')
        .pipe(nightwatch({
            configFile: './tests/nightwatch/nightwatch.json',
            cliArgs: [ '--env chrome' ]
        }));
});
gulp.task('nightwatch:phantomjs', function(){
    return gulp.src('')
        .pipe(nightwatch({
            configFile: './tests/nightwatch/nightwatch.json',
            cliArgs: [ '--env phantomjs' ]
        }));
});
gulp.task('nightwatch:safari', function(){
    return gulp.src('')
        .pipe(nightwatch({
            configFile: './tests/nightwatch/nightwatch.json',
            cliArgs: [ '--env safari' ]
        }));
});
// deleting reports and screenshots of nightwatch tests
gulp.task('nightwatch:clean', function(){
    console.log('Cleaning reports and screenshots.');
    // TODO: it still deletes the .gitkeep file
    return gulp.src(['!tests/nightwatch/reports/screenshots/.gitkeep', 'tests/nightwatch/reports/*' ],
        { read: false, dot: true }).pipe(clean());
});
