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
    var bootstrapPath = 'node_modules/bootstrap-sass/assets';
    mix.copy('resources/assets/fonts', 'public/fonts')
        .copy('resources/assets/img', 'public/img')
        .copy(bootstrapPath + '/fonts', 'public/fonts')
        .copy(bootstrapPath + '/javascripts/bootstrap.min.js', 'resources/assets/js')
        .sass('app.scss')
        .scripts([
            "jquery-2.2.3.min.js",
            "main.js",
            "bootstrap.min.js",
            "jquery.calendario.js"
        ])
        .styles([
            'font-awesome.css',
            'calendar.css',
            'calendario_abr.css',
            '../../../public/css/app.css',
            'main.css'
        ]);

    gulp.task("test", function() {
        shell('nightwatch -c tests/nightwatch/nightwatch.json');
    });
});

