<?php
/*
  Plugin Name: WP Google Map
  Plugin URI: https://www.wpgooglemap.com?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
  Description: WP Google Map plugin allows creating Google Map with marker or location with a responsive interface. Marker supports text, images, links, videos, and custom icons. Simply, Just put the shortcode on the page, post, or widget to display the map anywhere.
  Author: WP Google Map
  Text Domain: gmap-embed
  Domain Path: /languages
  Author URI: https://www.wpgooglemap.com?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
  Version: 1.9.2
 */

use WGMSRM\Classes\Database;

if (!defined('ABSPATH')) {
	exit;
}

define('WGM_PLUGIN_VERSION', '1.9.2');
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
function tinymce_init()
{
	add_filter('mce_external_plugins', 'tinymce_plugin');
}

add_filter('init', 'tinymce_init');
/**
 * Added function for tinymce initialization
 *
 * @param $init
 *
 * @return mixed
 */
function tinymce_plugin($init)
{
	$init['keyup_event'] = WGM_PLUGIN_URL . 'admin/assets/js/tinymce_keyup_event.js';

	return $init;
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_gmap_embed()
{

	if (!class_exists('Appsero\Client')) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client('8aa8c415-a0e1-41a2-9f05-1b385c09e90b', 'WP Google Map', __FILE__);

	// Active insights
	$client->insights()->add_plugin_data()->init();

	// Active automatic updater
	//$client->updater();

}

appsero_init_tracker_gmap_embed();

/**
 * Run plugin initially
 */
function wgm_run()
{
	new \WGMSRM\Classes\Bootstrap();
}

/**
 * Install plugin db structures
 */
function wgm_install_plugin()
{
	new Database();
}



register_activation_hook(__FILE__, 'wgm_install_plugin');
wgm_run();
