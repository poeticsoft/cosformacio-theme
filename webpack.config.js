const path = require('path')
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const EventHooksPlugin = require('event-hooks-webpack-plugin');

module.exports = {
  name: 'main',
  entry: './scss/main.scss',
  output: {
    path: 'E:\\playmotiv-cloud\\dev1.playmotiv.cloud\\wp-content\\themes\\trident\\'
  },
  context: __dirname,
  mode: 'production', // env == 'prod' ? 'production' : 'development',
  devtool: 'none', // 'none' : 'source-map',
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: [          
          { 
            loader: 'babel-loader',
            options: {
              presets: [
                '@babel/preset-env',
                '@babel/preset-react'
              ]
            }
          }
        ]
      },
      {
        test: /\.scss$/,
        exclude: /node_modules/,
        use: [
          { 
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader'
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
      },
      {
        test: /\.(woff2?|ttf|otf|eot)$/,
        exclude: /node_modules/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]',
          outputPath: '/fonts/',
          publicPath: 'fonts/'
        }
      },
      {
        test: /\.(jpg|jpeg|png|gif|svg)$/,
        exclude: /node_modules/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]',
          outputPath: '/assets/img/',
          publicPath: '/'
        }
      }
    ]
  },
  plugins: [
    new EventHooksPlugin({
        'done': () => {

          const filename = path.join(__dirname, 'main.js');
          console.log(filename)
          fs.unlinkSync(filename)
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
    extensions: ['.js', '.jsx']
  }
}