/* eslint-disable no-undef */
// CommonJS style import for Laravel Mix
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

// Disable success notifications to prevent file watch issues
mix.disableSuccessNotifications();

// Configure Webpack
mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader'
                    // Options are now coming from babel.config.js
                }
            }
        ]
    },
    resolve: {
        modules: ['node_modules', 'resources/frontend'],
        extensions: ['.js', '.vue', '.json'],
        fallback: {
            "process": require.resolve("process/browser")
        }
    },
    plugins: [
        // Add process polyfill
        new (require('webpack')).ProvidePlugin({
            process: 'process/browser'
        })
    ]
});

// Create resources/css directory if it doesn't exist
const fs = require('fs');
if (!fs.existsSync('resources/css')) {
    fs.mkdirSync('resources/css', { recursive: true });
}

// Create empty files if they don't exist
['resources/css/app.css', 'resources/css/auth.css', 'resources/css/websocket.css', 'resources/css/role-management.css'].forEach(file => {
    if (!fs.existsSync(file)) {
        fs.writeFileSync(file, '/* CSS file */');
    }
});

// First compile babel helpers
mix.js('resources/js/babel-helpers.js', 'public/js');

// Then compile main application bundle
mix.js('resources/js/app.js', 'public/js');

// Copy standalone files
mix.copy('resources/frontend/debug.js', 'public/js')
   .copy('resources/frontend/WebSocketExample.js', 'public/js');

// Ensure public/sounds directory exists
if (!fs.existsSync('public/sounds')) {
    fs.mkdirSync('public/sounds', { recursive: true });
}

// CSS files
mix.postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss'),
    require('autoprefixer'),
])
.copy('resources/css/auth.css', 'public/css')
.copy('resources/css/websocket.css', 'public/css')
.copy('resources/css/role-management.css', 'public/css');

// Vue support if vue is installed
if (fs.existsSync('node_modules/vue')) {
    mix.vue();
}

// Production settings
if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
} 