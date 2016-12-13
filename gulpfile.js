process.env.DISABLE_NOTIFIER = true;
var elixir = require('laravel-elixir');
var gulp = require("gulp");
var shell = require("gulp-shell");

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
        tetherPath = 'node_modules/tether/dist/js';
    mix.copy('resources/assets/fonts', 'public/fonts')
        .copy('resources/assets/img', 'public/img')
        .copy('resources/assets/favicons', 'public')
        .copy(bootstrapPath + '/fonts', 'public/fonts')
        .copy(bootstrap4Path + '/js/bootstrap.min.js', 'resources/assets/js')
        .copy(tetherPath + '/tether.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.css', 'public/css')
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
            "bootstrap.min.js",
            "jquery.calendario.js",
            "bootstrap-datepicker.js",
            "jquery.bracket.min.js"
        ])
        .styles([
            'font-awesome.css',
            'calendar.css',
            'calendario_abr.css',
            '../../../public/css/app.css',
            'bootstrap-datepicker.css',
            'netrunner.css',
            'main.css'
        ])
        .version(['css/all.css', 'js/all.js']);

    gulp.task("test", function() {
        shell('nightwatch -c tests/nightwatch/nightwatch.json');
    });
});

