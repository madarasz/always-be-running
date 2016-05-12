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
    mix.copy(bootstrapPath + '/fonts', 'public/fonts')
        .copy(bootstrapPath + '/javascripts/bootstrap.min.js', 'resources/assets/js')
        .sass('app.scss')
        .scripts([
            "main.js",
            "jquery-2.2.3.min.js",
            "bootstrap.min.js"
        ])
        .styles([
            'main.css',
            '../../../public/css/app.css'
        ]);

    gulp.task("test", function() {
        shell('nightwatch -c tests/nightwatch/nightwatch.json');
    });
});

