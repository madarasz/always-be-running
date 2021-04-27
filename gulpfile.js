process.env.DISABLE_NOTIFIER = true;
var elixir = require('laravel-elixir');
var gulp = require("gulp");
var shell = require("gulp-shell");
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
    var bracketPath = 'node_modules/jquery-bracket/dist',
        timepickerPath = 'node_modules/timepicker',
        toastrPath = 'node_modules/toastr/build/';
    mix.copy('resources/assets/fonts', 'public/fonts')
        .copy('resources/assets/img', 'public/img')
        .copy('resources/assets/favicons', 'public')
        .copy('resources/assets/vue', 'public/vue')
        .copy('node_modules/bootstrap-sass/assets/fonts', 'public/fonts')
        .copy('node_modules/bootstrap/dist/js/bootstrap.min.js', 'resources/assets/js')
        .copy('node_modules/tether/dist/js/tether.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.js', 'resources/assets/js')
        .copy(bracketPath + '/jquery.bracket.min.css', 'public/css')
        .copy(timepickerPath + '/jquery.timepicker.min.css', 'resources/assets/css')
        .copy(timepickerPath + '/jquery.timepicker.min.js', 'resources/assets/js')
        .copy('node_modules/vue/dist/' + vueFile, 'resources/assets/js')
        .copy('node_modules/axios/dist/axios.min.js', 'resources/assets/js')
        .copy(toastrPath + '/toastr.min.css', 'resources/assets/css')
        .copy(toastrPath + '/toastr.min.js', 'resources/assets/js')
        .copy('node_modules/marked/lib/marked.js', 'resources/assets/js')
        .copy('node_modules/v-autocomplete/dist/v-autocomplete.js', 'resources/assets/js')
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
            "abr-vue.js",
            "marked.js",
            "v-autocomplete.js"
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
            'toastr.min.css',
            'cookieconsent.min.css'
        ])
        .version(['css/all.css', 'js/all.js']);

    gulp.task("test", function() {
        shell('nightwatch -c tests/nightwatch/nightwatch.json');
    });
});