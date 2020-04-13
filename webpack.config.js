const path = require('path')
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const EventHooksPlugin = require('event-hooks-webpack-plugin');

module.exports = {
  name: 'main',
  entry: './scss/custom/main.scss',
  output: {
    path: 'C:\\trabajo\\cos\\web\\wp-content\\themes\\academy\\'
  },
  context: __dirname,
  mode: 'development', // env == 'prod' ? 'production' : 'development',
  devtool: 'none', // 'none' : 'source-map',
  module: {
    rules: [
      {
        test: /\.scss$/,
        exclude: /node_modules/,
        use: [
          { 
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader?url=false'
          },
          {
            loader: 'sass-loader'
          }
        ]
      },
      {
        test: /\.css$/,
        include: /node_modules/,
        use: [
          'style-loader',
          'css-loader'
        ]
      }
    ]
  },
  plugins: [
    new EventHooksPlugin({
        'done': () => {

          /*
          const filename = path.join(__dirname, 'main.js');
          console.log(filename)
          fs.unlinkSync(filename)
          */
        }
    }),
    new MiniCssExtractPlugin({
      filename: 'style.css'
    }),
    new LiveReloadPlugin({
      protocol: 'http',
      hostname: 'localhost',
      delay: 0,
      appendScriptTag: false
    })
  ],
  resolve: {
    extensions: ['.scss']
  }
}