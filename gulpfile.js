process.env.DISABLE_NOTIFIER = true;
var elixir = require('laravel-elixir');
var gulp = require("gulp");
var shell = require("gulp-shell");
var clean = require('gulp-rimraf');
var fs = require('fs');
var path = require('path');
var runSequence = require('run-sequence');
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

elixir(function (mix) {
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
        .copy('node_modules/marked/lib/marked.umd.js', 'resources/assets/js/marked.js')
        .copy('node_modules/v-autocomplete/dist/v-autocomplete.js', 'resources/assets/js')
        .copy('node_modules/vue-lazyload/vue-lazyload.js', 'resources/assets/js')
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
            "tournament.table.js",
            "marked.js",
            "v-autocomplete.js",
            "vue-lazyload.js"
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
});

/*
 |--------------------------------------------------------------------------
 | mix-manifest: Convert Elixir's rev-manifest.json → mix-manifest.json
 |--------------------------------------------------------------------------
 |
 | Laravel's mix() helper reads public/mix-manifest.json and expects:
 |   { "/css/all.css": "/build/css/all-<hash>.css", ... }
 |
 | Elixir's version task writes public/build/rev-manifest.json with:
 |   { "css/all.css": "css/all-<hash>.css", ... }
 |
 | This task translates between the two formats so that mix() always
 | serves the correct cache-busted URL after every build.
 |
 */
gulp.task('mix-manifest', function (done) {
    var revManifestPath = path.join('public', 'build', 'rev-manifest.json');
    var mixManifestPath = path.join('public', 'mix-manifest.json');

    if (!fs.existsSync(revManifestPath)) {
        console.warn('[mix-manifest] rev-manifest.json not found at ' + revManifestPath + '; skipping.');
        return done();
    }

    var revManifest = JSON.parse(fs.readFileSync(revManifestPath, 'utf8'));
    var mixManifest = {};

    Object.keys(revManifest).forEach(function (originalFile) {
        // Keys:   "css/all.css"          -> "/css/all.css"
        // Values: "css/all-<hash>.css"   -> "/build/css/all-<hash>.css"
        var key = '/' + originalFile;
        var value = '/build/' + revManifest[originalFile];
        mixManifest[key] = value;
    });

    fs.writeFileSync(mixManifestPath, JSON.stringify(mixManifest, null, 4) + '\n', 'utf8');
    console.log('[mix-manifest] Written ' + mixManifestPath);
    done();
});

/*
|--------------------------------------------------------------------------
| Default Task: Run full build pipeline
|--------------------------------------------------------------------------
|
| Running `gulp` (with no arguments) now executes the complete pipeline
| in sequential order to ensure proper dependency handling:
| 1. Prepare directories - Create public/css, public/js, public/build
| 2. sass - Compile Sass to CSS
| 3. scripts - Concatenate JS files
| 4. styles - Concatenate CSS files (needs sass output)
| 5. version - Version the CSS/JS files (needs styles/scripts output)
| 6. mix-manifest - Convert rev-manifest.json -> mix-manifest.json
|
| This ensures cache-busting works correctly after every build.
|
*/
gulp.task('prepare-dirs', function (done) {
    // Use shell to create directories (more reliable across Node versions)
    var exec = require('child_process').exec;
    exec('mkdir -p public/css public/js public/build public/img public/fonts', function (err) {
        done(err);
    });
});

gulp.task('default', function (done) {
    runSequence(
        'prepare-dirs',
        'sass',
        'scripts',
        'styles',
        'version',
        'mix-manifest',
        done
    );
});
