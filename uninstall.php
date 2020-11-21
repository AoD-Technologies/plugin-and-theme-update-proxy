<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

if ( ! is_plugin_active( 'plugin-and-theme-update-proxy-premium/plugin.php' ) ) {
    require_once( 'plugin.php' );

    PluginAndThemeUpdateProxy::uninstall();
}
