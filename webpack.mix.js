const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.copyDirectory('resources/frontend', 'public/frontend');
mix.copyDirectory('resources/backend', 'public/backend');
mix.copyDirectory('node_modules/desoslide/dist', 'public/desoslide');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/frontend/sass/bootstrap.scss', 'public/css')
   .sass('resources/sass/revealInput.scss', 'public/css');
