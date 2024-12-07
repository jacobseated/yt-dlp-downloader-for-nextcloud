const path = require('path');

module.exports = {
    entry: './src/main.js',
    output: {
        path: path.resolve(__dirname, 'js'),
        filename: 'main.js',
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader'],
            },
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader', 'sass-loader'],
            },
        ],
    },
    plugins: [
        new (require('vue-loader').VueLoaderPlugin)(),
    ],
};
