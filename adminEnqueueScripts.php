<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_runtime_main_704a2466_js", plugin_dir_url( __FILE__ ) . 'build/static/js/runtime-main.704a2466.js', array(), $this->getVersion() );
wp_enqueue_style( "{$this->underscoreTextDomain}_build_static_css_2_65aa3b33_chunk_css", plugin_dir_url( __FILE__ ) . 'build/static/css/2.65aa3b33.chunk.css', array(), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_2_6ce263d8_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/2.6ce263d8.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_704a2466_js"), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_main_bccff8aa_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/main.bccff8aa.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_704a2466_js","{$this->underscoreTextDomain}_build_static_js_2_6ce263d8_chunk_js"), $this->getVersion(), true );
