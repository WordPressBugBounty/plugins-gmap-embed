<?php
/*
  Plugin Name: WP Google Map
  Plugin URI: https://www.wpgooglemap.com?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=branding&utm_content=plugin-uri
  Description: WP Google Map plugin allows creating Google Map with marker or location with a responsive interface. Marker supports text, images, links, videos, and custom icons. Simply, Just put the shortcode on the page, post, or widget to display the map anywhere.
  Author: WP Google Map
  Text Domain: gmap-embed
  Domain Path: /languages
  Author URI: https://www.wpgooglemap.com?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=branding&utm_content=author-uri
  Version: 1.9.7
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use WGMSRM\Classes\Database;

if (!defined('ABSPATH')) {
	exit;
}

define('WGM_PLUGIN_VERSION', '1.9.7');
define('WGM_PLUGIN_DEV_VERSION', '20260725');
define('WGM_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WGM_PLUGIN_URL', trailingslashit(plugins_url('/', __FILE__)));
define('WGM_ICONS_DIR', WGM_PLUGIN_PATH . 'admin/assets/images/markers/icons/');
define('WGM_ICONS', WGM_PLUGIN_URL . 'admin/assets/images/markers/icons/');

require_once WGM_PLUGIN_PATH . 'autoload.php';
// Required helper functions.
require_once WGM_PLUGIN_PATH . '/includes/helper.php';

/**
 * Tinymce plugin initialization
 */
function gmap_embed_tinymce_init()
{
	add_filter('mce_external_plugins', 'gmap_embed_tinymce_plugin');
}

add_filter('init', 'gmap_embed_tinymce_init');
/**
 * Added function for tinymce initialization
 *
 * @param $init
 *
 * @return mixed
 */
function gmap_embed_tinymce_plugin($init)
{
	$init['keyup_event'] = WGM_PLUGIN_URL . 'admin/assets/js/tinymce_keyup_event.js';

	return $init;
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function gmap_embed_appsero_init_tracker_gmap_embed()
{

	if (!class_exists('GmapEmbed\Appsero\Client')) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new GmapEmbed\Appsero\Client('8aa8c415-a0e1-41a2-9f05-1b385c09e90b', 'WP Google Map', __FILE__);

	// Active insights
	$client->insights()->add_plugin_data()->init();

	// Active automatic updater
	//$client->updater();

}

gmap_embed_appsero_init_tracker_gmap_embed();

/**
 * Run plugin initially
 */
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function gmap_embed_run()
{
	\WGMSRM\Classes\Bootstrap::instance();
}

/**
 * Install plugin db structures
 */
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function gmap_embed_install_plugin()
{
	// Defensive: Only allow DB install for users with install_plugins capability
	if (current_user_can('install_plugins')) {
		new Database();
	}
}

register_activation_hook(__FILE__, 'gmap_embed_install_plugin');
gmap_embed_run();
