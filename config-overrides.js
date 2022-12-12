const {
  addBabelPreset,
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
  fixBabelImports('@mui/material', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@mui/material/styles', {
    libraryDirectory: '../esm/styles',
    camel2DashComponentName: false
  }),
  fixBabelImports('@mui/icons-material', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@mui/system', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  }),
  fixBabelImports('@mui/utils', {
    libraryDirectory: 'esm',
    camel2DashComponentName: false
  })
)
