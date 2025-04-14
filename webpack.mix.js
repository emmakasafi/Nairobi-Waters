const mix = require('laravel-mix');

// This will compile your SASS files (if you have any) and your JavaScript files
mix
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .version(); // versioning for cache busting
