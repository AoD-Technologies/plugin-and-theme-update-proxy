# Plugin and Theme Update Proxy

* Contributors: jhorowitz
* Donate link: https://aod-tech.com/donate/
* Tags: plugin, theme, update, updater, custom plugin, custom theme, premium plugin, premium theme, paid plugin, paid theme
* Requires at least: 3.5
* Tested up to: 5.5.3
* Requires PHP: 5.6
* Stable tag: master
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* GitHub Plugin URI: https://github.com/AoD-Technologies/plugin-and-theme-update-proxy

## Description

Allows you to specify another WordPress installation as the source of a plugin or theme’s updates (including custom or paid/premium plugins and themes!)

One site will function as a Hosting site, and other sites will connect to that site and be able to download any updates available to the Hosting site. If no update is available for a particular plugin or theme, the currently installed version will be visible as an update.

This plugin will also enable the Automatic Updates UI for any plugin or theme configured to update via a Hosting site (on WordPress 5.5 and above.)

#### Premium Version Features

The [Plugin and Theme Update Proxy Premium](https://aod-tech.com/products/plugin-and-theme-update-proxy-premium/?utm_source=github.com&utm_medium=referral&utm_term=plugin-and-theme-update-proxy-premium&utm_content=details&utm_campaign=github-com-ad) version lets you configure multiple source sites, multiple updatable plugins and themes, and enables one-click installation of plugins and themes directly from source sites!

## Installation

1. Upload the plugin files to the `/wp-content/plugins/plugin-and-theme-update-proxy` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. You’re Done!

## Frequently Asked Questions

### Why do I need this plugin?

Have you ever purchased a custom or paid/premium plugin or theme and wanted to use it on multiple sites you manage?
Assuming the plugin or theme’s license permits this (e.g. a GPL license), you are legally allowed to do so.
However, you may be limited to receiving automatic updates to the plugin or theme on a single site (either through license keys or some other means of verification on their update servers).
Therefore, to update the other sites, you would normally have to manually upload a zip file of the updated plugin or theme and overwrite the old version.

This plugin allows you to utilize the valid site as a pass-through for your other sites, letting you use WordPress’ built-in update functionality to easily propagate updates that the valid site can see to other sites to which you have installed the plugin or theme manually.

The other sites do not ask for updates directly from the plugin or theme’s update servers, but instead ask for the updates from another site that can receive those updates automatically, saving you precious time!

### What does this plugin do?

This plugin WILL:

* Allow you to receive updates for custom and paid/premium plugins and themes for which you normally would not be able to receive updates, by passing any available updates through a site that **can receive those updates**.
* Allow you to enable auto-updates for those plugins and themes (on WordPress 5.5 and above), even if you cannot normally enable auto-updates.

### What doesn’t this plugin do?

This plugin WILL NOT:

* Restore missing or incomplete functionality when used on a site for which a plugin or theme is not authorized to receive updates.
* Prevent nag messages regarding license keys or similar means of authenticating whether a valid license has been purchased.
* Make unlicensed websites eligible for support from the plugin or theme’s author.
* Examine plugin or theme licenses to see if you have the right to distribute updates. It is **YOUR RESPONSIBILITY** to use this plugin only for legally permissible distribution of updates!

### How do I use this plugin?

First, navigate to Settings -> Plugin and Theme Update Proxy. There, you will see two tabs: Hosting and Sources.

On the site that can receive updates for your plugins and themes, create an Authentication Token on the Hosting tab, and select which plugins or themes you wish to make available for updates for that token.
Make note of the Authentication Token and the Hosting URL (at the top of the Hosting tab).

Then, on the other site(s), create a Source on the Sources tab using the Hosting URL and Authentication Token from earlier.
You will then be able to select which plugin or theme you want to update from the other site.

From then on, the standard WordPress update mechanism will be able to install updates available to the other site for that plugin or theme!

### How do I update this plugin?

Please install and use the [GitHub Updater plugin](https://github.com/afragen/github-updater) to receive updates for this plugin.

## Screenshots

1. The Plugin and Theme Update Proxy Hosting interface.

2. The Plugin and Theme Update Proxy Sources interface.

## Changelog

### 1.02
* Do not check for updates when no plugins or themes are installed or selected to be checked.
* Avoid several PHP warnings.

### 1.01
* Prevent infinite recursion when propagating update checks to other Hosting URLs.

### 1.00
* The initial release.
