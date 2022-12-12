<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

wp_enqueue_style( "{$this->underscoreTextDomain}_build_static_css_main_ca273f62_css", plugin_dir_url( __FILE__ ) . 'build/static/css/main.ca273f62.css', array(), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_main_ecfb6517_js", plugin_dir_url( __FILE__ ) . 'build/static/js/main.ecfb6517.js', array(), $this->getVersion(), true );
