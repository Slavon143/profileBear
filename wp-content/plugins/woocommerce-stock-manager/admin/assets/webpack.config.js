const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = function webpackConfig(env, argv) {
  return {
    entry: {
      index: './src/index.js',
    },
    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, 'build'),
    },
    resolve: {
      extensions: ['.json', '.js'],
    },
    devtool: argv.mode === 'development' ? 'cheap-module-eval-source-map' : 'source-map',
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /(node_modules)/,
          use: 'babel-loader',
        },
        {
          test: /components\/.+\.s?css$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
              options: { sourceMap: true },
            },
            {
              loader: 'css-loader',
              options: { sourceMap: true, modules: true, importLoaders: 2 },
            },
            {
              loader: 'postcss-loader',
              options: { sourceMap: true, ident: 'postcss' },
            },
            {
              loader: 'sass-loader',
              options: { sourceMap: true },
            },
          ],
        },
      ],
    },
    plugins: [
      new CleanWebpackPlugin(),
      new MiniCssExtractPlugin({ filename: '[name].css' }),
    ],
  };
};
