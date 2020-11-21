const {
  addBabelPreset,
  addWebpackAlias,
  fixBabelImports,
  override
} = require('customize-cra')

const path = require('path')

module.exports = override(
  addBabelPreset([
    '@babel/preset-react', {
      runtime: 'automatic'
    }
  ]),
  addWebpackAlias({
    "react$": path.resolve(__dirname, 'node_modules/react')
  }),
  fixBabelImports('@material-ui/core', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/core/styles', {
    libraryDirectory: '../esm/styles',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/icons', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/lab', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/styles', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/system', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@material-ui/utils', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  })
)
