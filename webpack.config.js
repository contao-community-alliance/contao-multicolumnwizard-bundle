const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const postcssPresetEnv = require('postcss-preset-env');
const packageData = require('./package.json');
const GitRevisionPlugin = require('git-revision-webpack-plugin');
const gitRevisionPlugin = new GitRevisionPlugin();

const version = gitRevisionPlugin.version(); // same as `git describe`

module.exports = (env, argv) => {

    const isProd = (argv.mode !== 'development');

    return {
        devtool: isProd ? false : 'source-map',
        entry: './src/Resources/assets/js/multicolumnwizard.js',
        output: {
            path: path.resolve(__dirname, 'src/Resources/public'),
            filename: 'js/multicolumnwizard.js',
        },
        optimization: {
            minimize: isProd,
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    loader: 'babel-loader'
                },
                {
                    test: /\.(scss|sass|css)$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                ident: 'postcss',
                                plugins: () => [
                                    postcssPresetEnv({})
                                ]
                            }
                        },
                        {
                            loader: 'sass-loader',
                        },
                    ]
                }
            ]
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: 'css/multicolumnwizard.css'
            }),
            new webpack.BannerPlugin({
                banner: `
@name        ${packageData.name}
@version     ${version}
@copyright   Andreas Schempp 2011
@copyright   certo web & design GmbH 2011
@copyright   MEN AT WORK 2013
@package     MultiColumnWizard
@license     ${packageData.license}
@file        [name]
     `.trim()
            }),
        ],
    }
};
