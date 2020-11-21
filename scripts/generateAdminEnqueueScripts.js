const fs = require('fs')
const path = require('path')

const { entrypoints } = require('../build/asset-manifest.json')

const UNDERSCORE_REPLACEMENT_REGEX = /[/\-.]/g

let output = `<?php

namespace AoDTechnologies\\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

`

const cssDeps = []
const jsDeps = []

entrypoints.forEach(entryPoint => {
    if (entryPoint.substr(-4) === '.css') {
        const regexHandle = entryPoint.replace(UNDERSCORE_REPLACEMENT_REGEX, '_')
        output += 'wp_enqueue_style( "{$this->underscoreTextDomain}_build_' + regexHandle + '", plugin_dir_url( __FILE__ ) . \'build/' + entryPoint + '\', array(' + cssDeps.join(', ') + '), $this->getVersion() );\n'
        cssDeps.push('"{$this->underscoreTextDomain}_build_' + regexHandle + '"')
    } else if (entryPoint.substr( -3 ) === '.js' ) {
        const regexHandle = entryPoint.replace(UNDERSCORE_REPLACEMENT_REGEX, '_')
        const lastSlashIndex = entryPoint.lastIndexOf('/')
        output += 'wp_enqueue_script( "{$this->underscoreTextDomain}_build_' + regexHandle + '", plugin_dir_url( __FILE__ ) . \'build/' + entryPoint + '\', array(' + jsDeps.join(',') + '), $this->getVersion()' + ( entryPoint.substr( lastSlashIndex + 1, 5 ) === 'main.' ? ', true' : '' ) + ' );\n'
        jsDeps.push('"{$this->underscoreTextDomain}_build_' + regexHandle + '"')
    }
})

fs.writeFileSync(path.join(process.cwd(), 'adminEnqueueScripts.php'), output)
