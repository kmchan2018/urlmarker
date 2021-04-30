const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// install bootstrap
mix.copy('node_modules/jquery/dist/jquery.slim.min.js', 'public/js');
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css');
mix.copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js');

// install fontawesome
mix.copy('node_modules/@fortawesome/fontawesome-free/css/all.min.css', 'public/css');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.eot', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.svg', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.ttf', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.woff', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.woff2', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.eot', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.svg', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.ttf', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.eot', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.svg', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.ttf', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff', 'public/webfonts');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2', 'public/webfonts');

// install application styles and scripts
mix.sass('resources/sass/app.scss', 'public/css');
mix.js('resources/js/app.js', 'public/js').react();

