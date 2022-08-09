<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_runtime_main_422d9428_js", plugin_dir_url( __FILE__ ) . 'build/static/js/runtime-main.422d9428.js', array(), $this->getVersion() );
wp_enqueue_style( "{$this->underscoreTextDomain}_build_static_css_2_65aa3b33_chunk_css", plugin_dir_url( __FILE__ ) . 'build/static/css/2.65aa3b33.chunk.css', array(), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_2_c6a10fb6_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/2.c6a10fb6.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_422d9428_js"), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_main_47ceccad_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/main.47ceccad.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_422d9428_js","{$this->underscoreTextDomain}_build_static_js_2_c6a10fb6_chunk_js"), $this->getVersion(), true );
