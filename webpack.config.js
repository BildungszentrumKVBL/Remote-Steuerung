// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')

  .addEntry('app', './assets/js/app.js')
  .addEntry('websocket', './assets/js/websocket.js')
  .addEntry('controller', './assets/js/controller.js')
  .addEntry('admin', './assets/js/admin.js')
  .addEntry('routing', './assets/js/routing.js')
  .addStyleEntry('theme-dark', './assets/scss/dark.scss')
  .addStyleEntry('theme-indigo', './assets/scss/indigo.scss')
  .addStyleEntry('theme-default', './assets/scss/default.scss')
  .splitEntryChunks()

  .enableSassLoader()
  .autoProvidejQuery()
  .autoProvideVariables({})
  .enableSourceMaps(!Encore.isProduction())
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .copyFiles([
    {
      from:    './assets/images/',
      context: './assets',
    }])
;
// export the final configuration
module.exports = Encore.getWebpackConfig();
