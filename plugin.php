<?php
/**
 * Plugin Name: Plugin and Theme Update Proxy
 * Description: Allows you to specify other WordPress installations as the source of some or all plugin and theme updates.
 * Version:     1.01
 * Author:      AoD Technologies LLC
 * Author URI:  https://aod-tech.com/
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: plugin-and-theme-update-proxy
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/AoD-Technologies/plugin-and-theme-update-proxy
 *
 * Plugin and Theme Update Proxy is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Plugin and Theme Update Proxy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Plugin and Theme Update Proxy. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( 'includes/plugin.class.php' );
