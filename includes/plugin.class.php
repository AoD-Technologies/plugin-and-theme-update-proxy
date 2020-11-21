<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( 'base.class.php' );

class PluginAndThemeUpdateProxy extends PluginAndThemeUpdateProxyBase {
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new PluginAndThemeUpdateProxy();
		}

		return self::$instance;
	}
}

PluginAndThemeUpdateProxy::getInstance();
