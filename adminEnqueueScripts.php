<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_runtime_main_00fcfed4_js", plugin_dir_url( __FILE__ ) . 'build/static/js/runtime-main.00fcfed4.js', array(), $this->getVersion() );
wp_enqueue_style( "{$this->underscoreTextDomain}_build_static_css_2_65aa3b33_chunk_css", plugin_dir_url( __FILE__ ) . 'build/static/css/2.65aa3b33.chunk.css', array(), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_2_d125271e_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/2.d125271e.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_00fcfed4_js"), $this->getVersion() );
wp_enqueue_script( "{$this->underscoreTextDomain}_build_static_js_main_eaeee0a5_chunk_js", plugin_dir_url( __FILE__ ) . 'build/static/js/main.eaeee0a5.chunk.js', array("{$this->underscoreTextDomain}_build_static_js_runtime_main_00fcfed4_js","{$this->underscoreTextDomain}_build_static_js_2_d125271e_chunk_js"), $this->getVersion(), true );
